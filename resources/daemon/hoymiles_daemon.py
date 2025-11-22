#!/usr/bin/env python3

import time
import json
import hashlib
import base64
import requests
import os
import sys
import signal
import logging
import socket
import traceback
import paho.mqtt.client as mqtt

logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

JEEDOM_TMP = '/tmp/jeedom/hoymiles'
CONFIG_FILE = os.path.join(JEEDOM_TMP, 'config.json')
PID_FILE = os.path.join(JEEDOM_TMP, 'daemon.pid')

cycle = 0
shutdown_flag = False

def signal_handler(signum, frame):
    global shutdown_flag
    logger.info("Arrêt demandé...")
    shutdown_flag = True

signal.signal(signal.SIGINT, signal_handler)
signal.signal(signal.SIGTERM, signal_handler)

def write_pid():
    try:
        os.makedirs(JEEDOM_TMP, exist_ok=True)
        with open(PID_FILE, 'w') as f:
            f.write(str(os.getpid()))
    except Exception as e:
        logger.error(f"Erreur écriture PID: {e}")

def load_config():
    if os.path.exists(CONFIG_FILE):
        with open(CONFIG_FILE, 'r') as f:
            return json.load(f)
    logger.error(f"Config file not found: {CONFIG_FILE}")
    return {}

def encode_password(password: str) -> str:
    md5_hex = hashlib.md5(password.encode('utf-8')).hexdigest()
    sha256_bytes = hashlib.sha256(password.encode('utf-8')).digest()
    b64_sha256 = base64.b64encode(sha256_bytes).decode('utf-8')
    return f"{md5_hex}.{b64_sha256}"

def get_region_login_url(session, email, timeout=15):
    url = 'https://euapi.hoymiles.com/iam/pub/0/c/region_c'
    resp = session.post(url, json={'email': email},
                       headers={'Content-Type': 'application/json; charset=utf-8'},
                       timeout=timeout)
    resp.raise_for_status()
    j = resp.json()
    login_url = j.get('data', {}).get('login_url')
    if not login_url:
        raise RuntimeError(f'login_url not found: {j}')
    return login_url.rstrip('/')

def login_get_token(session, login_url, email, password, timeout=15):
    endpoint = f"{login_url}/iam/pub/0/c/login_c"
    payload = {'user_name': email, 'password': encode_password(password)}
    resp = session.post(endpoint, json=payload,
                       headers={'Content-Type': 'application/json; charset=utf-8'},
                       timeout=timeout)
    resp.raise_for_status()
    j = resp.json()
    token = j.get('data', {}).get('token')
    if not token:
        raise RuntimeError(f'token not found: {j}')
    return token

def get_station_data(session, host_base, token, plantId):
    url = f"{host_base}/pvm-data/api/0/station/data/count_station_real_data"
    headers = {'Content-Type': 'application/json; charset=utf-8', 'Authorization': token}
    payload = {'sid': plantId}
    resp = session.post(url, json=payload, headers=headers, timeout=20)
    resp.raise_for_status()
    try:
        return resp.json()
    except Exception:
        return {'raw': resp.content.hex()}

def mqtt_connect(cfg):
    try:
        client = mqtt.Client()
        if cfg.get('mqtt_user'):
            client.username_pw_set(cfg.get('mqtt_user'), cfg.get('mqtt_pass', ''))
        client.connect(cfg.get('mqtt_host', 'localhost'), int(cfg.get('mqtt_port', 1883)))
        client.loop_start()
        return client
    except Exception as e:
        logger.error(f"Erreur connexion MQTT: {e}")
        return None

def publish_data(client, plantId, data):
    if not client:
        return

    try:
        base = f"hoymiles/{plantId}"
        if 'data' in data:
            d = data['data']
            mapping = {
                'real_power': 'power',
                'today_eq': 'energy_today',
                'total_eq': 'energy_total',
                'month_eq': 'energy_month',
                'year_eq': 'energy_year'
            }
            for k, v in mapping.items():
                if k in d:
                    try:
                        client.publish(f"{base}/{v}", str(d[k]), retain=True)
                    except Exception as e:
                        logger.warning(f"Erreur publish {v}: {e}")

            client.publish(f"{base}/json", json.dumps(d), retain=True)
        else:
            client.publish(f"{base}/raw", json.dumps(data), retain=True)
    except Exception as e:
        logger.error(f"Erreur publication MQTT: {e}")

def send_to_jeedom(cfg, plantId, data):
    try:
        apikey = cfg.get('apikey')
        if not apikey:
            return

        if 'data' in data:
            d = data['data']
            mapping = {
                'real_power': 'power',
                'today_eq': 'energy_today',
                'total_eq': 'energy_total',
                'month_eq': 'energy_month',
                'year_eq': 'energy_year'
            }

            base_url = 'http://localhost/plugins/hoymiles/core/ajax/hoymiles.ajax.php?apikey=' + apikey + '&action=updateValue'


            for k, v in mapping.items():
                if k in d:
                    try:
                        url = base_url + '&logicalId=' + v + '&value=' + str(d[k])
                        logger.info(f"Envoi à Jeedom: {v} = {d[k]}")
                        response = requests.get(url, timeout=5)
                        logger.info(f"Réponse Jeedom: {response.status_code}")
                    except Exception as e:
                        logger.error(f"Erreur envoi {v}: {e}")
    except Exception as e:
        logger.error(f"Erreur envoi vers Jeedom: {e}")

def listen_socket(cfg):
    try:
        socketport = int(cfg.get('socketport', 55055))
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
        sock.bind(('127.0.0.1', socketport))
        sock.listen(1)
        sock.settimeout(1)
        logger.info(f"Socket listening on port {socketport}")
        return sock
    except Exception as e:
        logger.error(f"Erreur création socket: {e}")
        return None

def main():
    global cycle, shutdown_flag

    write_pid()
    logger.info("Démarrage du daemon Hoymiles")

    cfg = load_config()
    if not cfg:
        logger.error("Configuration manquante")
        sys.exit(1)

    interval = int(cfg.get('interval_min', 5)) * 60
    session = requests.Session()
    mqttc = mqtt_connect(cfg)

    sock = listen_socket(cfg)

    last_fetch = 0

    while not shutdown_flag:
        try:
            if sock:
                try:
                    conn, addr = sock.accept()
                    data = conn.recv(1024)
                    if data:
                        logger.info(f"Command received: {data.decode()}")
                    conn.close()
                except socket.timeout:
                    pass
                except Exception as e:
                    logger.debug(f"Socket error: {e}")

            current_time = time.time()
            if current_time - last_fetch >= interval:
                logger.info("Récupération des données Hoymiles...")

                try:
                    login_url = get_region_login_url(session, cfg['email'])
                    token = login_get_token(session, login_url, cfg['email'], cfg['password'])
                    host_base = login_url.split('/iam')[0]
                    data = get_station_data(session, host_base, token, cfg['plantId'])

                    logger.info(f"Données récupérées: {data}")

                    publish_data(mqttc, cfg['plantId'], data)
                    send_to_jeedom(cfg, cfg['plantId'], data)

                    last_fetch = current_time
                    cycle += 1

                except Exception as e:
                    logger.error(f"Erreur lors de la récupération: {e}")
                    logger.debug(traceback.format_exc())

            time.sleep(1)

        except Exception as e:
            logger.error(f"Erreur dans la boucle principale: {e}")
            logger.debug(traceback.format_exc())
            time.sleep(5)

    logger.info("Arrêt du daemon")
    if mqttc:
        mqttc.loop_stop()
        mqttc.disconnect()
    if sock:
        sock.close()

    try:
        os.remove(PID_FILE)
    except:
        pass

if __name__ == '__main__':
    main()

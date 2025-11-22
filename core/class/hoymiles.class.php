<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class hoymiles extends eqLogic {

    public static function dependancy_info() {
        $return = array();
        $return['log'] = log::getPathToLog('hoymiles_dep');
        $return['progress_file'] = jeedom::getTmpFolder('hoymiles') . '/dependance';

        $requests = shell_exec('pip3 list 2>/dev/null | grep requests');
        $paho = shell_exec('pip3 list 2>/dev/null | grep paho-mqtt');

        if ($requests !== null && $paho !== null && trim($requests) != '' && trim($paho) != '') {
            $return['state'] = 'ok';
        } else {
            $return['state'] = 'nok';
        }
        return $return;
    }

    public static function dependancy_install() {
        log::remove(__CLASS__ . '_dep');
        return array('script' => dirname(__FILE__) . '/../../resources/install_apt.sh', 'log' => log::getPathToLog(__CLASS__ . '_dep'));
    }

    public static function deamon_info() {
        $return = array();
        $return['log'] = log::getPathToLog('hoymiles_daemon');
        $return['state'] = 'nok';

        $pid_file = jeedom::getTmpFolder('hoymiles') . '/daemon.pid';
        if (file_exists($pid_file)) {
            if (@posix_getsid(trim(file_get_contents($pid_file)))) {
                $return['state'] = 'ok';
            } else {
                shell_exec(system::getCmdSudo() . 'rm -rf ' . $pid_file . ' 2>&1 > /dev/null');
            }
        }

        $return['launchable'] = 'ok';
        $email = config::byKey('email', 'hoymiles');
        $password = config::byKey('password', 'hoymiles');
        $plantId = config::byKey('plantId', 'hoymiles');
        $mqtt_host = config::byKey('mqtt_host', 'hoymiles');

        if ($email == '' || $password == '' || $plantId == '' || $mqtt_host == '') {
            $return['launchable'] = 'nok';
            $return['launchable_message'] = __('Veuillez configurer email, password, plantId et mqtt_host', __FILE__);
        }

        return $return;
    }

    public static function deamon_start() {
        self::deamon_stop();
        $deamon_info = self::deamon_info();
        if ($deamon_info['launchable'] != 'ok') {
            throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
        }

        $path = realpath(dirname(__FILE__) . '/../../resources/daemon');
        $config_file = jeedom::getTmpFolder('hoymiles') . '/config.json';

        $config = array(
            'email' => config::byKey('email', 'hoymiles'),
            'password' => config::byKey('password', 'hoymiles'),
            'plantId' => config::byKey('plantId', 'hoymiles'),
            'mqtt_host' => config::byKey('mqtt_host', 'hoymiles'),
            'mqtt_port' => intval(config::byKey('mqtt_port', 'hoymiles', 1883)),
            'mqtt_user' => config::byKey('mqtt_user', 'hoymiles', ''),
            'mqtt_pass' => config::byKey('mqtt_pass', 'hoymiles', ''),
            'interval_min' => intval(config::byKey('interval_min', 'hoymiles', 5)),
            'callback' => network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/core/api/jeeApi.php?apikey=' . jeedom::getApiKey('hoymiles') . '&plugin=hoymiles&type=event',
            'apikey' => jeedom::getApiKey('hoymiles'),
            'socketport' => config::byKey('socketport', 'hoymiles', 55055)
        );

        file_put_contents($config_file, json_encode($config));

        $cmd = 'python3 ' . $path . '/hoymiles_daemon.py';
        log::add('hoymiles', 'info', 'Lancement démon hoymiles : ' . $cmd);

        $result = exec(system::getCmdSudo() . 'python3 ' . $path . '/hoymiles_daemon.py >> ' . log::getPathToLog('hoymiles_daemon') . ' 2>&1 &');

        $i = 0;
        while ($i < 30) {
            $deamon_info = self::deamon_info();
            if ($deamon_info['state'] == 'ok') {
                break;
            }
            sleep(1);
            $i++;
        }
        if ($i >= 30) {
            log::add('hoymiles', 'error', __('Impossible de lancer le démon, vérifiez le log', __FILE__), 'unableStartDeamon');
            return false;
        }
        message::removeAll('hoymiles', 'unableStartDeamon');
        return true;
    }

    public static function deamon_stop() {
        $pid_file = jeedom::getTmpFolder('hoymiles') . '/daemon.pid';
        if (file_exists($pid_file)) {
            $pid = intval(trim(file_get_contents($pid_file)));
            system::kill($pid);
        }

        exec(system::getCmdSudo() . 'pkill -f hoymiles_daemon.py');

        $deamon_info = self::deamon_info();
        if ($deamon_info['state'] == 'ok') {
            sleep(1);
            exec(system::getCmdSudo() . 'pkill -9 -f hoymiles_daemon.py');
        }

        if (file_exists($pid_file)) {
            unlink($pid_file);
        }
    }

    public function postSave() {
        $power = $this->getCmd(null, 'power');
        if (!is_object($power)) {
            $power = new hoymilesCmd();
            $power->setName(__('Puissance', __FILE__));
            $power->setEqLogic_id($this->getId());
            $power->setLogicalId('power');
            $power->setType('info');
            $power->setSubType('numeric');
            $power->setUnite('W');
            $power->save();
        }

        $energy_today = $this->getCmd(null, 'energy_today');
        if (!is_object($energy_today)) {
            $energy_today = new hoymilesCmd();
            $energy_today->setName(__('Énergie Aujourd\'hui', __FILE__));
            $energy_today->setEqLogic_id($this->getId());
            $energy_today->setLogicalId('energy_today');
            $energy_today->setType('info');
            $energy_today->setSubType('numeric');
            $energy_today->setUnite('kWh');
            $energy_today->save();
        }

        $energy_total = $this->getCmd(null, 'energy_total');
        if (!is_object($energy_total)) {
            $energy_total = new hoymilesCmd();
            $energy_total->setName(__('Énergie Totale', __FILE__));
            $energy_total->setEqLogic_id($this->getId());
            $energy_total->setLogicalId('energy_total');
            $energy_total->setType('info');
            $energy_total->setSubType('numeric');
            $energy_total->setUnite('kWh');
            $energy_total->save();
        }

        $energy_month = $this->getCmd(null, 'energy_month');
        if (!is_object($energy_month)) {
            $energy_month = new hoymilesCmd();
            $energy_month->setName(__('Énergie Mois', __FILE__));
            $energy_month->setEqLogic_id($this->getId());
            $energy_month->setLogicalId('energy_month');
            $energy_month->setType('info');
            $energy_month->setSubType('numeric');
            $energy_month->setUnite('kWh');
            $energy_month->save();
        }

        $energy_year = $this->getCmd(null, 'energy_year');
        if (!is_object($energy_year)) {
            $energy_year = new hoymilesCmd();
            $energy_year->setName(__('Énergie Année', __FILE__));
            $energy_year->setEqLogic_id($this->getId());
            $energy_year->setLogicalId('energy_year');
            $energy_year->setType('info');
            $energy_year->setSubType('numeric');
            $energy_year->setUnite('kWh');
            $energy_year->save();
        }

        $refresh = $this->getCmd(null, 'refresh');
        if (!is_object($refresh)) {
            $refresh = new hoymilesCmd();
            $refresh->setName(__('Rafraichir', __FILE__));
            $refresh->setEqLogic_id($this->getId());
            $refresh->setLogicalId('refresh');
            $refresh->setType('action');
            $refresh->setSubType('other');
            $refresh->save();
        }
    }
}

class hoymilesCmd extends cmd {

    public function execute($_options = array()) {
        if ($this->getLogicalId() == 'refresh') {
            hoymiles::deamon_stop();
            sleep(2);
            hoymiles::deamon_start();
        }
    }
}

?>

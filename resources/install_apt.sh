#!/bin/bash
PROGRESS_FILE=/tmp/jeedom/hoymiles/dependance
touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}
echo "**********************************************************"
echo "*       Installation des dépendances Hoymiles           *"
echo "**********************************************************"
sudo apt-get clean
echo 10 > ${PROGRESS_FILE}
sudo apt-get update
echo 30 > ${PROGRESS_FILE}
echo "**********************************************************"
echo "*         Installation de Python3 et pip                *"
echo "**********************************************************"
sudo apt-get install -y python3 python3-pip
echo 50 > ${PROGRESS_FILE}
echo "**********************************************************"
echo "*         Installation des modules Python               *"
echo "**********************************************************"
sudo pip3 install requests paho-mqtt --break-system-packages 2>/dev/null || sudo pip3 install requests paho-mqtt
echo 80 > ${PROGRESS_FILE}
echo "**********************************************************"
echo "*         Vérification de l'installation                *"
echo "**********************************************************"
sudo pip3 list | grep requests
sudo pip3 list | grep paho-mqtt
echo 100 > ${PROGRESS_FILE}
echo "**********************************************************"
echo "*          Installation terminée avec succès             *"
echo "**********************************************************"
rm ${PROGRESS_FILE}

# Plugin Hoymiles pour Jeedom

## Description

Ce plugin permet d'intégrer vos équipements Hoymiles (onduleurs solaires) dans Jeedom et de publier les données vers un serveur MQTT.

## Configuration du plugin

Après installation du plugin, vous devez activer celui-ci puis configurer les paramètres suivants dans la page de configuration du plugin :

### Paramètres Hoymiles

- **Email Hoymiles** : Votre adresse email utilisée pour vous connecter à l'application Hoymiles
- **Mot de passe Hoymiles** : Votre mot de passe Hoymiles
- **Plant ID** : L'identifiant de votre installation (visible dans l'application Hoymiles)

### Paramètres MQTT

- **Serveur MQTT** : L'adresse IP ou le nom d'hôte de votre serveur MQTT (par défaut: localhost)
- **Port MQTT** : Le port du serveur MQTT (par défaut: 1883)
- **Utilisateur MQTT** : Nom d'utilisateur MQTT (optionnel)
- **Mot de passe MQTT** : Mot de passe MQTT (optionnel)

### Paramètres du daemon

- **Intervalle de polling** : Intervalle en minutes entre chaque récupération des données (par défaut: 5 minutes)
- **Port du daemon** : Port utilisé par le daemon (par défaut: 55055)

## Installation des dépendances

Cliquez sur le bouton "Relancer" dans la section "Dépendances" de la page de configuration du plugin.

Le plugin installera automatiquement :
- Python3
- pip3
- Les modules Python nécessaires (requests, paho-mqtt)

## Démarrage du daemon

Une fois les dépendances installées et la configuration complétée :

1. Vérifiez que tous les paramètres sont corrects
2. Cliquez sur "Démarrer" dans la section "Démon" de la page de configuration
3. Le daemon devrait passer au statut "OK"

## Création d'un équipement

1. Allez dans Plugins → Energie → Hoymiles
2. Cliquez sur "Ajouter"
3. Donnez un nom à votre équipement
4. Activez et rendez visible l'équipement
5. Sauvegardez

Les commandes suivantes seront automatiquement créées :
- **Puissance** : Puissance instantanée (W)
- **Énergie Aujourd'hui** : Production du jour (kWh)
- **Énergie Totale** : Production totale (kWh)
- **Énergie Mois** : Production du mois (kWh)
- **Énergie Année** : Production de l'année (kWh)
- **Rafraîchir** : Bouton pour redémarrer le daemon

## Topics MQTT

Les données sont publiées sur MQTT avec la structure suivante :

```
hoymiles/{plantId}/power          - Puissance instantanée
hoymiles/{plantId}/energy_today   - Énergie du jour
hoymiles/{plantId}/energy_total   - Énergie totale
hoymiles/{plantId}/energy_month   - Énergie du mois
hoymiles/{plantId}/energy_year    - Énergie de l'année
hoymiles/{plantId}/json           - Toutes les données en JSON
```

## Dépannage

### Le daemon ne démarre pas

- Vérifiez que tous les paramètres de configuration sont renseignés
- Vérifiez les logs du daemon dans Analyse → Logs → hoymiles_daemon
- Vérifiez que les dépendances sont bien installées

### Pas de données

- Vérifiez vos identifiants Hoymiles
- Vérifiez votre Plant ID
- Consultez les logs pour voir les éventuelles erreurs

### Problème MQTT

- Vérifiez que votre serveur MQTT est accessible
- Vérifiez les paramètres de connexion MQTT
- Testez la connexion avec un client MQTT (mosquitto_sub)

## Changelog

Voir le fichier [changelog.md](changelog.md)

## Support

Pour toute question ou problème, consultez le forum Jeedom.

# Plugin Hoymiles pour Jeedom

Plugin pour intégrer vos équipements Hoymiles (onduleurs solaires) dans Jeedom avec publication MQTT.

## Installation

1. Téléchargez le plugin ou clonez ce dépôt dans le dossier `/var/www/html/plugins/` de votre Jeedom
2. Renommez le dossier en `hoymiles` si nécessaire
3. Allez dans Jeedom → Plugins → Gestion des plugins
4. Recherchez "Hoymiles" et activez le plugin
5. Installez les dépendances
6. Configurez le plugin (voir documentation)
7. Démarrez le daemon

## Structure du plugin

```
hoymiles/
├── plugin_info/
│   ├── info.json                 # Informations du plugin
│   ├── configuration.php         # Page de configuration
│   ├── install.php              # Script d'installation
│   └── hoymiles_icon.png        # Icône du plugin
├── core/
│   ├── class/
│   │   └── hoymiles.class.php   # Classe principale
│   └── ajax/
│       └── hoymiles.ajax.php    # Endpoints AJAX
├── desktop/
│   ├── php/
│   │   └── hoymiles.php         # Interface utilisateur
│   └── js/
│       └── hoymiles.js          # JavaScript frontend
├── resources/
│   ├── daemon/
│   │   └── hoymiles_daemon.py   # Daemon Python
│   └── install_apt.sh           # Script installation dépendances
└── docs/
    └── fr_FR/
        ├── index.md             # Documentation
        └── changelog.md         # Historique des versions
```

## Fonctionnalités

- Connexion automatique à l'API Hoymiles
- Récupération des données de production solaire
- Publication vers serveur MQTT
- Métriques disponibles :
  - Puissance instantanée (W)
  - Énergie journalière (kWh)
  - Énergie totale (kWh)
  - Énergie mensuelle (kWh)
  - Énergie annuelle (kWh)
- Configuration complète via interface Jeedom
- Daemon avec polling configurable
- Support authentification MQTT

## Configuration requise

- Jeedom 4.0 ou supérieur
- Python 3
- Compte Hoymiles avec accès API
- Serveur MQTT (optionnel mais recommandé)

## Documentation

Consultez la [documentation complète](docs/fr_FR/index.md) pour plus d'informations.

## Support

Pour toute question ou problème :
- Forum Jeedom : https://community.jeedom.com/

## Licence

AGPL - Voir le fichier LICENSE

## Auteur

Développé Lamarcos pour Jeedom

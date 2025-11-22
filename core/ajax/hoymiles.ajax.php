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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

    if (init('action') == 'updateValue') {
    // Récupérer les paramètres
    $logicalId = init('logicalId');
    $value = init('value');

    if (!$logicalId || !$value) {
        ajax::error('Paramètres manquants : logicalId ou value');
    }

    // Trouver la commande par son logicalId
    //$cmd = cmd::byLogicalId($logicalId, 'hoymiles');
    
    $sql = 'SELECT id FROM cmd WHERE logicalId = :logicalId AND eqType = :eqType';
    $cmd = DB::Prepare($sql, array('logicalId' => $logicalId, 'eqType' => 'hoymiles'), DB::FETCH_TYPE_ROW);
    if (!$cmd) {
      ajax::error('Commande non trouvée en base de données pour logicalId : ' . $logicalId);
    }
    $cmd = cmd::byId($cmd['id']);
    if (!is_object($cmd)) {
      ajax::error('Impossible de charger la commande');
    }
    
    
    
    

    if (!is_object($cmd)) {
        ajax::error('Commande non trouvée pour le logicalId : ' . $logicalId);
    }

    // Mettre à jour la commande
    $cmd->event($value);

    // Répondre avec succès
    ajax::success();
}

    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

    ajax::init();

    if (init('action') == 'getHoymilesData') {
        $eqLogic = hoymiles::byId(init('id'));
        if (!is_object($eqLogic)) {
            throw new Exception(__('Hoymiles eqLogic non trouvé : ', __FILE__) . init('id'));
        }
        ajax::success($eqLogic);
        return;
    }

    if (init('action') == 'getConfig') {
        $config = array(
            'email' => config::byKey('email', 'hoymiles', ''),
            'password' => config::byKey('password', 'hoymiles', ''),
            'plantId' => config::byKey('plantId', 'hoymiles', ''),
            'mqtt_host' => config::byKey('mqtt_host', 'hoymiles', 'localhost'),
            'mqtt_port' => config::byKey('mqtt_port', 'hoymiles', '1883'),
            'mqtt_user' => config::byKey('mqtt_user', 'hoymiles', ''),
            'mqtt_pass' => config::byKey('mqtt_pass', 'hoymiles', ''),
            'interval_min' => config::byKey('interval_min', 'hoymiles', '5'),
            'socketport' => config::byKey('socketport', 'hoymiles', '55055')
        );
        ajax::success($config);
        return;
    }

    throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}
?>

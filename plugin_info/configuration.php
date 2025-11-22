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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>
<form class="form-horizontal">
    <fieldset>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Email Hoymiles}}</label>
            <div class="col-lg-4">
                <input class="configKey form-control" data-l1key="email" placeholder="votre@email.com"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Mot de passe Hoymiles}}</label>
            <div class="col-lg-4">
                <input type="password" class="configKey form-control" data-l1key="password" placeholder="Mot de passe"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Plant ID}}</label>
            <div class="col-lg-4">
                <input class="configKey form-control" data-l1key="plantId" placeholder="8141590"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Serveur MQTT}}</label>
            <div class="col-lg-4">
                <input class="configKey form-control" data-l1key="mqtt_host" placeholder="localhost"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Port MQTT}}</label>
            <div class="col-lg-4">
                <input class="configKey form-control" data-l1key="mqtt_port" placeholder="1883"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Utilisateur MQTT}}</label>
            <div class="col-lg-4">
                <input class="configKey form-control" data-l1key="mqtt_user" placeholder="(optionnel)"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Mot de passe MQTT}}</label>
            <div class="col-lg-4">
                <input type="password" class="configKey form-control" data-l1key="mqtt_pass" placeholder="(optionnel)"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Intervalle de polling (minutes)}}</label>
            <div class="col-lg-4">
                <input class="configKey form-control" data-l1key="interval_min" placeholder="5"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Port du daemon}}</label>
            <div class="col-lg-4">
                <input class="configKey form-control" data-l1key="socketport" placeholder="55055"/>
            </div>
        </div>
    </fieldset>
</form>

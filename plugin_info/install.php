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

function hoymiles_install() {
    $pluginDir = dirname(__FILE__) . '/..';

    config::save('email', '', 'hoymiles');
    config::save('password', '', 'hoymiles');
    config::save('plantId', '', 'hoymiles');
    config::save('mqtt_host', 'localhost', 'hoymiles');
    config::save('mqtt_port', '1883', 'hoymiles');
    config::save('mqtt_user', '', 'hoymiles');
    config::save('mqtt_pass', '', 'hoymiles');
    config::save('interval_min', '5', 'hoymiles');
    config::save('socketport', '55055', 'hoymiles');
}

function hoymiles_update() {
    $pluginDir = dirname(__FILE__) . '/..';

    if (config::byKey('socketport', 'hoymiles', '') == '') {
        config::save('socketport', '55055', 'hoymiles');
    }
}

function hoymiles_remove() {
    config::remove('email', 'hoymiles');
    config::remove('password', 'hoymiles');
    config::remove('plantId', 'hoymiles');
    config::remove('mqtt_host', 'hoymiles');
    config::remove('mqtt_port', 'hoymiles');
    config::remove('mqtt_user', 'hoymiles');
    config::remove('mqtt_pass', 'hoymiles');
    config::remove('interval_min', 'hoymiles');
    config::remove('socketport', 'hoymiles');
}

?>

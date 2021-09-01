<?php
/*
 * tailscale.inc
 *
 * part of pfSense (https://www.pfsense.org)
 * Copyright (c) 2021 Tailscale, Inc
 * All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once('globals.inc');
require_once('config.inc');
require_once('service-utils.inc');
require_once('util.inc');
require_once('system.inc');

function tailscale_resync_config() {
	global $config, $tailscale_config;
	if (is_array($config['installedpackages']['tailscale'])) {
		$tailscale_config =& $config['installedpackages']['tailscale']['config'][0];
	} else {
		$tailscale_config = array();
	}

	if ($tailscale_config['enable'] != "on") {
		if (is_process_running("tailscale")) {
			stop_service("tailscale");
		}
		unlink_if_exists('/usr/local/etc/rc.d/tailscale.sh');
		return;
	}

	tailscale_write_rcfile();
	start_service("tailscale");
}

function tailscale_write_rcfile() {
	global $config, $tailscale_config;
	$daemon_start = "mkdir -p /var/run/tailscale";
	$daemon_start .= "\n\t/usr/sbin/daemon -p /var/run/tailscaled.pid /usr/local/bin/tailscaled --port=41641 --state=/var/lib/tailscale/tailscaled.state --socket=/run/tailscale/tailscaled.sock";
	$daemon_stop = "killall tailscaled\n\tkillall tailscale web\n\t/sbin/ifconfig tailscale0 destroy >/dev/null 2>&1";

	$tailscaleup = "/usr/local/bin/tailscale";
	if ($tailscale_config['enable'] != "on") {
		$tailscaleup .= " down";
        } else {
		$tailscaleup .= " up";

		if ($tailscale_config['advertise_lan_route'] == 'on') {
			/*
			$cidr = $config['interfaces']['lan']['subnet'];
			$tailscaleup .= " --advertise-routes=" + $cidr;
			 */
		}
		if ($tailscale_config['advertise_exit_node'] == 'on') {
			$tailscaleup .= " --advertise-exit-node=true";
		}

		$tailscaleup .= " --accept-dns=false --accept-routes=true --reset";
        }

	$rc['file'] = 'tailscale.sh';
	$rc['start'] = $daemon_start;
	$rc['stop'] = "tailscale down\n\t" . $daemon_stop;
	write_rcfile($rc);
}

function tailscale_install() {
	unlink_if_exists("/usr/local/etc/rc.d/tailscale.sh");
	tailscale_resync_config();

	write_config("[tailscale] Package installed.");
}

function tailscale_deinstall() {
	unlink_if_exists("/usr/local/etc/rc.d/tailscale.sh");
}

function validate_input_tailscale($post, &$input_errors) {
}

?>

<?php
/*
 * tailscale_auth.php
 *
 * part of pfSense (https://www.pfsense.org)
 * Copyright (c) 2021 Tailscale, Inc.
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

require_once("guiconfig.inc");
require_once("priv.inc");

global $_SESSION;
$guiuser = getUserEntry($_SESSION['Username']);
$username = $guiuser['name'];
$is_admin = isAdminUID($username);

global $config, $tailscale_config;
if (is_array($config['installedpackages']['tailscale'])) {
	$tailscale_config =& $config['installedpackages']['tailscale']['config'][0];
} else {
	$tailscale_config = array();
}

$tailscaleup = "/usr/local/bin/tailscale --socket=/run/tailscale/tailscaled.sock";
if ($tailscale_config['enable'] != "on") {
	$tailscaleup .= " down";
} else {
	$tailscaleup .= " up --reset";

	if ($tailscale_config['advertise_lan_route'] == 'on') {
		$iflist = get_configured_interface_list();
		foreach ($iflist as $if => $ifname) {
			if ($ifname == "lan") {
				$if_ipv4 = get_interface_ip($if);
				$if_snbitsv4 = get_interface_subnet($if);
				$if_subnet = gen_subnet($if_ipv4, $if_snbitsv4);
				$tailscaleup .= " --advertise-routes=" . $if_subnet . "/" . $if_snbitsv4;
			}
		}
	}
	if ($tailscale_config['advertise_exit_node'] == 'on') {
		$tailscaleup .= " --advertise-exit-node=true";
	}

	$tailscaleup .= " --accept-dns=false --accept-routes=true --reset";
}

include("head.inc");
if ($is_admin) {
	echo "<div>" . $tailscaleup . "</div>";

	$fds = array(
		0 => array("pipe", "r"),
		1 => array("pipe", "w"),
		2 => array("pipe", "w")
	);
	$process = proc_open($tailscaleup, $fds, $pipes);
	if (is_resource($process)) {
		stream_set_blocking($pipes[1],0);
		stream_set_blocking($pipes[2],0);
		echo "<div>";
		$out = "";
		$loop = true;
		while ($loop) {
			$out .= fread($pipes[1], 1024);
			$out .= fread($pipes[2], 1024);
			if (strpos($out, "https://") !== false) {
				$loop = false;
			} else if (feof($pipes[1]) && feof($pipes[2])) {
				$loop = false;
			} else {
				usleep(500000);
			}
		}
		echo $out . "</div>";
	} else {
		echo "<div>Command failed</div>";
	}
	fclose($pipes[0]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	proc_close($process);
} else {
	echo "Error: " . $username . " is not an Admin.";
}

include("foot.inc");
?>

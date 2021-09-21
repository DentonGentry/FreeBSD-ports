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

if ($config['system']['webgui']['protocol'] == "https") {
        // Ensure that we have a webConfigurator CERT
        $cert =& lookup_cert($config['system']['webgui']['ssl-certref']);
        if (is_array($cert) && $cert['crt'] && $cert['prv']) {
        	$crt = base64_decode($cert['crt']);
        	$key = base64_decode($cert['prv']);
		putenv("TLS_CRT_PEM=" . $crt);
		putenv("TLS_KEY_PEM=" . $key);
	}
}

$tailscaleup = "/usr/local/bin/tailscale --socket=/run/tailscale/tailscaled.sock";
if ($tailscale_config['enable'] != "on") {
	$tailscaleup .= " down";
} else {
	$tailscaleup .= " web --listen=0.0.0.0:8443";
}

include("head.inc");
if ($is_admin) {
	shell_exec($tailscaleup);
	echo '<iframe width="400" height="500" src="https://10.1.10.79:8443/"></iframe>';
} else {
	echo "Error: " . $username . " is not an Admin.";
}

include("foot.inc");
?>

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

putenv("REQUEST_METHOD=GET");
putenv("SERVER_PROTOCOL=HTTP/1.1");
putenv("PFSENSE_USERNAME=" . $username);
if ($is_admin) {
	putenv("PFSENSE_IS_ADMIN=1");
}

include("head.inc");
$out = shell_exec("/usr/local/bin/tailscale --socket=/run/tailscale/tailscaled.sock web -cgi");
$lines = explode(PHP_EOL, $out);

/* chop off the 200 Status and other lines until we find <!doctype html> */
while ($line = array_shift($lines))
{
	if (strpos($line, 'doctype') !== false) {
		break;
	}
}

echo implode("\r\n", $lines);

include("foot.inc");
?>

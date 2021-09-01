<?php
/*
 * tailscale.priv.inc
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

global $priv_list;

$priv_list['page-vpn-tailscale'] = array();
$priv_list['page-vpn-tailscale']['name'] = "WebCfg - VPN : Tailscale package";
$priv_list['page-vpn-tailscale']['descr'] = "Tailscale package GUI";
$priv_list['page-vpn-tailscale']['match'] = array();
$priv_list['page-vpn-tailscale']['match'][] = "pkg.php?xml=tailscale.xml*";
$priv_list['page-vpn-tailscale']['match'][] = "pkg_edit.php?xml=tailscale.xml*";

?>

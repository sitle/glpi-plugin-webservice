<?php
/*
 * @version $Id: xmlrpc.php 306 2011-11-08 12:36:05Z remi $
 -------------------------------------------------------------------------
 webservices - WebServices plugin for GLPI
 Copyright (C) 2003-2011 by the webservices Development Team.

 https://forge.indepnet.net/projects/webservices
 -------------------------------------------------------------------------

 LICENSE

 This file is part of webservices.

 webservices is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 webservices is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with webservices. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Remi Collet
// Purpose of file: Manage list of config
// ----------------------------------------------------------------------
if (!function_exists("xmlrpc_encode")) {
   header("HTTP/1.0 500 Extension xmlrpc not loaded");
   die("Extension xmlrpc not loaded");
}

define('GLPI_ROOT', '../..');


function decodeFromUtf8Array(&$arg) {

   if (is_array($arg)) {
      foreach (array_keys($arg) as $key) {
         decodeFromUtf8Array($arg[$key]);
      }

   } else if (is_string($arg)) {
      $arg = Toolbox::decodeFromUtf8($arg);
   }
}


// define session_id before any other think
if (isset($_GET['session'])) {
   include_once ("inc/methodcommon.class.php");
   include_once ("inc/methodsession.class.php");
   $session = new PluginWebservicesMethodSession();
   $session->setSession($_GET['session']);
}

include (GLPI_ROOT . "/inc/includes.php");

Plugin::load('webservices', true);

Plugin::doHook("webservices");
plugin_webservices_registerMethods();

error_reporting(E_ALL);

if ($_SERVER["CONTENT_TYPE"] != "text/xml") {
   header("HTTP/1.0 500 Bad content type");
   die("Bad content type");
}

if (!isset($GLOBALS["HTTP_RAW_POST_DATA"]) || empty($GLOBALS["HTTP_RAW_POST_DATA"])) {
   header("HTTP/1.0 500 No content");
}

$method    = "";
$allparams = xmlrpc_decode_request($GLOBALS["HTTP_RAW_POST_DATA"],$method,'UTF-8');

if (empty($method) || !is_array($allparams)) {
   header("HTTP/1.0 500 Bad content");
}

$params = (isset($allparams[0]) && is_array($allparams[0]) ? $allparams[0] : array());
if (isset($params['iso8859'])) {
   $iso = true;
   unset($params['iso8859']);
} else {
   $iso = false;
}

$session = new PluginWebservicesMethodSession();
$resp = $session->execute($method, $params, WEBSERVICE_PROTOCOL_XMLRPC);

header("Content-type: text/xml");

if ($iso) {
   decodeFromUtf8Array($resp);
   echo xmlrpc_encode_request(NULL,$resp,array('encoding'=>'ISO-8859-1'));
} else {
   // request without method is a response ;)
   echo xmlrpc_encode_request(NULL,$resp,array('encoding'=>'UTF-8'));
}
?>
<?php

session_start();

require_once "include/beContent.inc.php";
require_once "include/content.inc.php";
require_once "include/entities.inc.php";

$info_panel = new Content($photoToFolderEntity);
$info_panel->setContent("service_link","admin-image-manager.php");
$info_panel->setContent("service_time",$_SERVER['REQUEST_TIME']);
echo $info_panel->get();
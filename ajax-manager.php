<?php

//session_start();

require_once "include/beContent.inc.php";


//require_once "include/auth.inc.php";


$entity = $database->getEntityByName($_REQUEST['table']);

if(isset($_REQUEST["value"]))
{
	$where_conditions = array($entity->fields[0]->name=>$_REQUEST["value"]);	
}

foreach($_REQUEST as $k=>$v)
{
	$where_conditions[$k]=$v;
}

$order_condition = isset($_REQUEST["orderby"])? array($_REQUEST["orderby"]) : NULL;  

/**
 * inserire l'ordinamento secondo la position
 */
$entity->retrieveOnly($where_conditions,NULL,$order_condition);

$presentation = $entity->getPresentation();
$presentation = explode(", ", $presentation['fields'] );


foreach($entity->instances as $instanceKey=>$instance)
{
	$instance->keyField=$instance->getKeyFieldValue();
	unset ($instance->fields);
	unset ($instance->linkingFields);
} 


if(isset($_REQUEST["mode"]))
{
	if($_REQUEST["mode"]=="onlyids")
	{
		$idsArray=null;
		foreach($entity->instances as $k=>$v)
		{
			$idsArray[]=$v->getKeyFieldValue();
		}
		echo json_encode($idsArray);
	}
}
else
	echo json_encode($entity->instances);
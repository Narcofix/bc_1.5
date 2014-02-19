<?php

session_start();

require "include/beContent.inc.php";
require "include/auth.inc.php";
require_once 'include/content.inc.php';
require_once(realpath(dirname(__FILE__)).'/include/view/template/InitGraphic.php');

$main = new Skin("system");

InitGraphic::getInstance()->createSystemGraphic($main);

$form = new Form("dataEntry",$photoToFolderEntity);

//controllo se si tratta di una add o una edit
$isAddMode = !( isset($_REQUEST["preload"]) && $_REQUEST["preload"]==1 );

//definisco l'output da mostrare

//se ho bisogno solo del form senza la skin
if( isset($_REQUEST["only_form"]) && $_REQUEST["only_form"]==1 ) $output_type = 1;
//se si tratta di un post ajax non mando niente
else if( isset($_REQUEST["ajax_post"]) && $_REQUEST["ajax_post"]==1 ) $output_type = 2;
//modo con skin 
else $output_type =	0;

//presente solo se non si tratta del solo form(caso 1)
if($output_type != 1)
	$form->addTitleForm("Image Management");

//mostro il file input solo nella add
if($isAddMode){
	$form->addSection('Seleziona un file dal tuo pc');
	$form->addFile("file", "sfoglia");
}

//campi presenti in tutte le modalita'
$form->addSection('image details');
$form->addLongDate("creationDate", "inserisci la data di acquisizione");

//alternativa per ajax html 
if( $output_type == 1){
	//mando solo l'html del form
	echo $form->requestAction();
}
//alternativa per ajax post
else if($output_type == 2){
	//non mando niente
	$form->requestAction();
}
//modo normale
else{
	//mando tutta la skin + il form
	$main->setContent("body", $form->requestAction());

	$main->close();
}
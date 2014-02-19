<?php

/**
 *Inizializzo la libreria
 */

//file di configurazione
require_once('PFConf.inc.php');
//eccezione di base
require_once(PFEXCPATH.'PFException.php');
//lista eccezioni
require_once(PFEXCPATH.'PFExceptionList.php');

//controllo se libreria GD caricata
if(PFGDNOTLOADED)throw new PFException_GDNotLoaded();

//libreria helper
require_once(PFRESPATH.'PFResourceHelper.php');
//interfaccia metodi delle risorse PF
require_once(PFRESPATH.'PFResourceInterface.php');
//risorsa di base da estendere per crearne una specializzata
require_once(PFRESPATH.'PFResourceBase.php');
//libreria che carica dinamicamente le operazioni da eseguire
require_once(PFBASEPATH.'PFOps.php');
//libreria factory
require_once(PFBASEPATH.'PhotoFactory.php');

?>
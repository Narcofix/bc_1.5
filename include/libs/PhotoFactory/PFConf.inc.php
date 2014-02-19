<?php
/**
 * estensione GD caricata?
 */
define("PFGDNOTLOADED", !extension_loaded('gd'));
/**
 * path di base
 */
define("PFBASEPATH", realpath( dirname(__FILE__) ). DIRECTORY_SEPARATOR);
/**
 * path file temporanei
 */
define("PFTEMPPATH",PFBASEPATH."PFTemp". DIRECTORY_SEPARATOR);
/**
 * path risorse supportate
 */
define("PFRESPATH",PFBASEPATH."PFResources". DIRECTORY_SEPARATOR);
/**
 * path eccezioni
 */
define("PFEXCPATH",PFBASEPATH."PFExceptions". DIRECTORY_SEPARATOR);
/**
 * path operazioni supportate
 */
define("PFOPSPATH",PFBASEPATH."PFOperations". DIRECTORY_SEPARATOR);
/**
 * controllo se attivo il flag per i file remoti
 */
define("PFREMOTEON",ini_get('allow_url_fopen'));
/**
 * controllo supporto opzione maxlength per la file_get_contents
 */
define("PFFGCMLON",function_exists("version_compare") && !version_compare(PHP_VERSION, '5.1.0','<'));
/**
 * prefisso delle classi delle operazioni
 */
define("PFOPPREFIX","PFOp"); 
/**
 * costanti modalita delle operazioni
 */
define("PFResizeNoRatio",0);
define("PFResizeIn",1);
define("PFResizeOut",2);
?>
<?php
ob_start( );
/**
 * Script per la generazione, nel caso in cui non esistano sul file system,
 * di thumbnail di dimensione dinamica, con successivo redirect al file.
 * Puo' essere utilizzato come src delle <img> ad esempio nei widget e
 * nelle preview
 */
 
require_once "include/beContent.inc.php";

//libreria PhotoFactory
$PhotoFactoryPath =  
	realpath(
		//include -->
		'include'. DIRECTORY_SEPARATOR .
		//libs -->
		'libs'. DIRECTORY_SEPARATOR .
		//PhotoFactory -->
		'PhotoFactory'. DIRECTORY_SEPARATOR
	). DIRECTORY_SEPARATOR.'PhotoFactoryInit.php';

//id immagine in ingresso
$imageId = 
	!isset( $_REQUEST["image_id"] ) ||  !is_numeric($_REQUEST["image_id"]) ? 
	NULL :
	$_REQUEST["image_id"];
	
//grandezza immagine in output
$imageSize =
	!isset( $_REQUEST["image_size"] ) ||  !is_numeric($_REQUEST["image_size"] ) ? 
	0 :
	$_REQUEST["image_size"];

//flag esito output
$OutputOk = FALSE;

//controllo se l'id in ingresso e' valido
if(!is_null($imageId)){

	//per il momento uso le cartelle altrimenti
	//uso photoToDb
	$entity = &$photoToFolderEntity;
	
	$entity->retrieveOnly(array("id"=>$imageId));
	
	//se ci sono risultati
	if( isset($entity->instances) ){
	
		foreach($entity->instances as $instanceKey=>$instance){
			$instance->keyField=$instance->getKeyFieldValue();
			unset ($instance->fields);
			unset ($instance->linkingFields);
		}
		
		$sizeError = FALSE;
		
		switch ($imageSize) {
			//caso preview
			case 0:
				$W = $H = 250;
				break;
			//caso gallery
			case 1:
				$W = $H = 100;
				break;
			//grandezza non definita
			default:
				$sizeError = TRUE;
				break;
		}
		
		if(!$sizeError){
		
			$dims = array("W"=>$W,"H"=>$H);
			
			//ho un risultato
			if(count($entity->instances)>0){
				//salvo ptr instanza
				$istance = &$entity->instances[0];
				//path della thumbnail sul file system 
				$photoOutput = 
					$istance->path.
					"thumb_".$dims["W"]."_".$dims["H"]."_".
					$istance->storefilename;
				
				
				$OutputOk = TRUE;
          
				//creo la thumb se non esiste
				if(!file_exists($photoOutput)){
					//carico libreria
					require_once $PhotoFactoryPath;
				
					try{
						//creo la risorsa
						$FileRes = PhotoFactory::getInstance()->doFactory( 
							$istance->path.$istance->storefilename 
						);
					
						//creo la thumb
						$FileRes->resize($dims["W"],$dims["H"],PFResizeIn);
                        
                        $FileRes->saveGdToFile(
                            $istance->filetypegd,
                            $photoOutput
                        );
						$FileRes = NULL;
					}
					catch(PFException $e){
						$OutputOk = FALSE;
					}
				}
                if($OutputOk){
                    ob_end_clean();
                    header('Cache-Control: no-cache');
                    header('Pragma: no-cache');
                    header('Content-Type: '.str_replace("jpeg","jpg",$istance->filetype));
                    header('Content-Length: ' .filesize($photoOutput) );
                    readfile($photoOutput);
                    exit(0);
                }
			}
		}
		//caso in cui la dimensione richiesta non e' definita
	}
	//caso non ci sono immagini con l'id in ingresso
}
//caso id in ingresso non valido

if(!$OutputOk){
	//immagine di errore
	$errorImg = $_SERVER['DOCUMENT_ROOT']."skins/system/images/imageNotFound";
	//estensioni+dim immagine di errore
	$errorImgDims = array("250.jpg","100.jpg");
	
	$photoOutput = $errorImg.$errorImgDims[$sizeError? 0 : $imageSize];
    
    //a questo punto sono sicuro di avere la thumb nella path $photoOutput
    header(
        'Location: '.substr( $photoOutput , strlen($_SERVER['DOCUMENT_ROOT'])), 
        true, 
        302
    );
}
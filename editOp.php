<?php
/**
 * testa la validita' di una chiave presente nell'array REQUEST
 * ed esegue altri test opzionali
 */
function TestAndGetRquestVal( $key ,$testFuncs=array(), $defaultVal = NULL ){
    
    if(isset( $_REQUEST[ $key ] ) ){
        $ok = TRUE;
        
        foreach ($testFuncs as $func){ 
            $ok &= $func($_REQUEST[ $key ] );
            if(!$ok) break;
        }
        
        if($ok)
            return $_REQUEST[ $key ];
    }
        
    return $defaultVal;
}
/**
 * esegue la funzione TestAndGetRquestVal su array di dati
 * con relativo array di test ritorna esito.
 * se esito positivo allora i valori sarranno settati nell'array Data
 */ 
function TestRequestData(&$Data,&$Tests){
    $ok = TRUE;
    
    //eseguo test dei valori in ingresso e li salvo
    foreach ($Data as $key => &$value) {
        $ok &= (
               $value = 
               TestAndGetRquestVal( $key, $Tests[ $key ] ,-1)
            ) != -1;
    
       if(!$ok) break;  
    }
    
    return $ok;
}
/**
 * testa se il valore e' positivo
 */
function IsPositive( $value ){
    return !(($value*1) < 0);
}
/**
 * testa se il valore non e' zero
 */
function IsNotZero( $value ){
    return ($value*1) != 0;
}
/**
 * ottengo i dati dal db
 */
function getImageData(&$entity,$imageId){
    $entity->retrieveOnly(array("id"=>$imageId));
    
    //se ci sono risultati
    if( isset($entity->instances) ){
    
        foreach($entity->instances as $instanceKey=>$instance){
            $instance->keyField=$instance->getKeyFieldValue();
            unset ($instance->fields);
            unset ($instance->linkingFields);
        }
        
        return TRUE;
    }
    else return FALSE;
}
function updateEntityImageInfos(&$entity,$imageId,$imagepath){
    $file_infos = getimagesize ( $imagepath );
    $where_condition = array($entity->fields[0]->name => $imageId);
    
    $values_condition['size']       = filesize( $imagepath );
    $values_condition['humansize']  = entityPhoto::human_filesize( $values_condition['size'] );
    $values_condition['width']      = $file_infos[0];
    $values_condition['height']     = $file_infos[1];
    
    $entity->update($where_condition,$values_condition);
    
}
/**
 * operazione di crop che salva una copia dell'originale
 * e salva l'originale con la copia
 */
function CropImage(&$entity){
    //elementi da testare nella request
    $cropData = array(
        "height"=> 0,
        "width" => 0,
        "y1"    => 0,
        "x1"    => 0,
        "image" => 0
    );
    
    $cropDataTests = array(
        "width"  => array("is_numeric","IsPositive","IsNotZero"),
        "x1"  => array("is_numeric","IsPositive")
    );
    //stesso test
    $cropDataTests["image"] = $cropDataTests["height"] = &$cropDataTests["width"];
    //stesso test
    $cropDataTests["y1"] = &$cropDataTests["x1"];
    
    //esco se non corretti i dati
    if(!TestRequestData($cropData,$cropDataTests)){
        echo "Errore dati in ingresso";
        exit(0);
    }
  
    if(!getImageData($entity,$cropData["image"])){
        echo "Immagine non trovata";
        exit(0);
    }
    
    //salvo ptr instanza
    $istance = &$entity->instances[0];
    
    //path dell'immagine originale
    $photoOriginal = $istance->path.$istance->storefilename;
    
    //path foto di backup = 
    $photoBackup = 
        $istance->path.
        "bak_".
        $istance->storefilename;
    
    //creo copia di backup solo se non esiste
    if(!file_exists($photoBackup)){
        //salvo copia di backup  
        copy($photoOriginal, $photoBackup);
    }
    //carico la libreria
    require_once PHOTOFACTORYPATH;
    $OutputOk = TRUE;
        
    try{
        //creo la risorsa
        $FileRes = PhotoFactory::getInstance()->doFactory( 
            $photoOriginal 
        );
        
        //creo la thumb
        $FileRes->crop(
            $cropData["x1"],
            $cropData["y1"],
            $cropData["width"],
            $cropData["height"]
        );

        $FileRes->saveGdToFile(
            $istance->filetypegd,
            $photoOriginal
        );
        
        $FileRes = NULL;
    }
    catch(PFException $e){
        $OutputOk = FALSE;
    }
    
    //se tutto ok
    if($OutputOk){
        //elimino tutte le thumbs     
        foreach (glob($istance->path."thumb*_".$istance->storefilename) as $fileFound)
            file_exists($fileFound) && !is_dir($fileFound) && unlink($fileFound);
        
        updateEntityImageInfos($entity,$cropData["image"],$photoOriginal);
    }
  
}
/**
 * operazione che resetta l'immagine a quella originale
 */
function ResetImage(&$entity){
    $resetData = array( "image" => 0 );
    $resetDataTests = array(
        "image"  => array("is_numeric","IsPositive","IsNotZero")
    );
    
    //esco se non corretti i dati
    if(!TestRequestData($resetData,$resetDataTests)){
        echo "Errore dati in ingresso";
        exit(0);
    }
    
    if(!getImageData($entity,$resetData["image"])){
        echo "Immagine non trovata";
        exit(0);
    }
    
    //salvo ptr instanza
    $istance = &$entity->instances[0];
    
    //path dell'immagine originale
    $photoOriginal = $istance->path.$istance->storefilename;
    
    //path foto di backup = 
    $photoBackup = 
        $istance->path.
        "bak_".
        $istance->storefilename;
    
    //Immagine di backup non trovata;    
    if(!file_exists($photoBackup)){
        exit(0);
    }
    
    //elimino originale
    unlink($photoOriginal);
    //rinomino backup
    rename($photoBackup, $photoOriginal);
    //elimino tutti i thumbs     
    foreach (glob($istance->path."*_".$istance->storefilename) as $fileFound)
        file_exists($fileFound) && !is_dir($fileFound) && unlink($fileFound);
    
    updateEntityImageInfos($entity,$resetData["image"],$photoOriginal);
    
}


$ok = !is_null( $op = TestAndGetRquestVal( "edit" ) );

require_once "include/beContent.inc.php";
//libreria PhotoFactory
define("PHOTOFACTORYPATH",  
    realpath(
        //include -->
        'include'. DIRECTORY_SEPARATOR .
        //libs -->
        'libs'. DIRECTORY_SEPARATOR .
        //PhotoFactory -->
        'PhotoFactory'. DIRECTORY_SEPARATOR
    ). DIRECTORY_SEPARATOR.'PhotoFactoryInit.php'
);


switch ($op) {
	case 'crop':
	   CropImage($photoToFolderEntity);
	   break;
    case 'reset':
       ResetImage($photoToFolderEntity);
        
	default:
		echo "Operazione non implementata";
		break;
}
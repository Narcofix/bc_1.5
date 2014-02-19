<?php
/**
 * entita' di base per le foto.
 * Ho escluso l'entita' file per poter includere successivamente
 * la politica di storage
 */

class entityPhoto extends Entity {
	
	/* crea un stringa con la dimensione leggibile*/
	public static function human_filesize($bytes, $decimals = 2) {
  		$sz = 'BKMGTP';
  		$factor = floor((strlen($bytes) - 1) / 3);
  		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}
	
	public function __construct($database, $name, $owner = "") {

		parent::__construct($database, $name, $owner);
		/**
		 * dati di base file rimpiazziare con una possibile entityFile
		 * generica che non presenta metodi per gestire lo storage
		 */
		//nome del file sul file system dell'utente
		$this -> addField("filename", VARCHAR, 255, MANDATORY);
		//dimensione in byte
		$this -> addField("size", INT, 5);
		//dimensione in byte
		$this -> addField("humansize", VARCHAR, 255, MANDATORY);
		//tipo file
		$this -> addField("filetype", VARCHAR, 255, MANDATORY);
		/**
		 * dati aggiuntivi legati alle immagini
		 */
		//tipo file usato da gd
		$this -> addField("filetypegd", INT, 5);
		//larghezza immagine
		$this -> addField("width", INT, 5);
		//altezza immagine
		$this -> addField("height", INT, 5);
		//data upload file
		$this -> addField("uploadDate", LONGDATE, MANDATORY);
		//data di cattura immessa dall'utente
		$this -> addField("creationDate", LONGDATE);
			
		$this -> setPresentation("filename");
	}


	/**
	 * @param $values_condition
	 * @return resource
	 */
	public function save($values_condition) {
		$values_condition["owner"] = $_SESSION["user"]["username"];

		if (Settings::getOperativeMode() == 'debug') {

		}

		if (isset($values_condition['file'])) {
			//di default uso il folder storage
			if(!isset($values_condition['storage']))
				$values_condition['storage'] = "Dir";	
			
			$file_infos = getimagesize ( $values_condition['file']['tmp_name'] );
			
			//se si tratta di una immaggine supportata
			if( $file_infos ){
				
				$values_condition['filename'] 	= $values_condition['file']['name'];
				$values_condition['size'] 		= $values_condition['file']['size'];
				$values_condition['humansize']  = self::human_filesize( $values_condition['size'] );
            	$values_condition['filetype'] 	= $file_infos["mime"];
				
				$values_condition['filetypegd'] = $file_infos[2];
				$values_condition['width'] 		= $file_infos[0];
				$values_condition['height'] 	= $file_infos[1];
				$values_condition['uploadDate'] = date('d/m/Y H:i:s');
				
				//$FileRes = PhotoFactory::getInstance()->doFactory( $values_condition['file']['tmp_name'] );
				//salvo il file
				switch ($values_condition['storage']) {
					default:
					case 'Dir':
						//se non settata la storage path
						if(!isset($values_condition['storagePath']))
							//storage path di default
							$values_condition['storagePath'] = entityPhotoToFolder::getStoragePath();
						
						$values_condition['storefilename'] = 
						uniqid().image_type_to_extension( $values_condition['filetypegd'] );
								
						//salvo il file uploadato
						move_uploaded_file(
							$values_condition["file"]["tmp_name"],
							$values_condition['storagePath'].$values_condition['storefilename']
						);
						//elimino la storage path
						unset ($values_condition['storagePath'] );
						
						break;
					case 'Db':
						$values_condition['data'] = 
							mysql_real_escape_string(
								base64_encode(
									file_get_contents( $values_condition['file']['tmp_name'] ) 
								) 
							);
						break;
				}
				
				unset($values_condition['storage']);
				unset($file_infos);
			}

			unset($values_condition['file']);
		}

		return parent::save($values_condition);
	}

}

$photoEntity = new entityPhoto($database, "sys_photo");

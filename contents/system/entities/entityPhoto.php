<?php
/**
 * entita' di base per le foto.
 * Ho escluso l'entita' file per poter includere successivamente
 * la politica di storage
 */

class entityPhoto extends Entity {

	public function __construct($database, $name, $owner = "") {

		parent::__construct($database, $name, $owner);
		/**
		 * dati di base file rimpiazziare con una possibile entityFile
		 * generica che non presenta metodi per gestire lo storage
		 */
		//nome del file
		$this -> addField("filename", VARCHAR, 255, MANDATORY);
		//dimensione in byte
		$this -> addField("size", INT, 5);
		//tipo file
		$this -> addField("filetype", VARCHAR, 255, MANDATORY);
		/**
		 * dati aggiuntivi legati alle immagini
		 */
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
			//analizzo informazioni immagine
			$file_infos = getimagesize ( $values_condition['file']['tmp_name'] );
			if( $file_infos ){
				$values_condition['filename'] = $values_condition['file']['name'];
				$values_condition['size'] = $values_condition['file']['size'];
            	$values_condition['filetype'] = $file_infos["mime"];
				
				$values_condition['width'] = $file_infos[0];
				$values_condition['height'] = $file_infos[1];
				$values_condition['uploadDate'] = date('d/m/Y H:i:s');
				//$FileRes = PhotoFactory::getInstance()->doFactory( $values_condition['file']['tmp_name'] );
				//salvo il file
				switch ($values_condition['storage']) {
					default:
					case 'Dir':
						move_uploaded_file(
							$values_condition["file"]["tmp_name"],
							$values_condition['storagePath'].uniqid()
						);
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

			}
			
		}

		return parent::save($values_condition);
	}

}

$photoEntity = new entityPhoto($database, "sys_photo");

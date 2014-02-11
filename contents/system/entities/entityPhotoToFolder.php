<?php

class entityPhotoToFolder extends EntityPhoto {
	
	private $storagePath;
	
	public static function getStoragePath(){
		//path salvataggio immagini
		$storagePath = 
			realpath( 'upload'. DIRECTORY_SEPARATOR ). DIRECTORY_SEPARATOR.
			date('Y/m/d/'). DIRECTORY_SEPARATOR;

		//creo la directory se non esiste
		if (!is_dir($storagePath)) {
			if(!mkdir($storagePath, 0777, true)) {
				die('Failed to create folders...');
			}
		}
		
		return $storagePath;
	}
	
	public function __construct($database, $name) {
		parent::__construct($database, $name, WITH_OWNER);
		//inizializzo path salvaggio immagini
		//$this -> storagePath = self::getStoragePath();
		
		$this -> addField("path", VARCHAR, 255, MANDATORY);
	}
	
	/**
	 * @param $values_condition
	 * @return resource
	 */
	public function save($values_condition) {
		$values_condition["owner"] = $_SESSION["user"]["username"];

		if (Settings::getOperativeMode() == 'debug') {

		}
		
		if ( isset($values_condition['file']) ) {
			$values_condition['storage'] = 'Dir';
			$values_condition['path'] =
				$values_condition['storagePath'] = 
					$this ->storagePath;
		}

		return parent::save($values_condition);
	}

	public function update($where_conditions, $set_parameters) {
		if (Settings::getOperativeMode() == 'release') {

		}

		parent::update($where_conditions, $set_parameters);
	}

}

$photoToFolderEntity = new entityPhotoToFolder($database, "sys_photo");

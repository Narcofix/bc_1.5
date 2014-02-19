<?php
//classe astratta method factory di base
require_once(PFRESPATH.'PFResourceCreator.php');

/**
 * factory concreta delle risorse
 */
class PhotoFactory extends PFResourceCreator{
	
	public static function getInstance() {
        if(! isset(self::$instance) ) {
            self::$_istance = new PhotoFactory();
        }
        return self::$_istance;
    }
	
	/**
	 * costruttore di classe
	 */
	function __construct() {
		//creo la cartella dei file temporanei di PF se non esiste
		self::checkTempDir();
	}
	
	protected function factoryMethod(PFResource $photoResNew = NULL){
		return $photoResNew;
	}
	
	public static function checkTempDir(){
		if (
			//se la cartella non esiste
			!file_exists(PFTEMPPATH) &&
			//se non la riesco a creare
    		!mkdir(PFTEMPPATH, 0777, true)
		){
			throw new PFException_TempDirNotExist();			
		}
		
		//se non ci posso scrivere
		if( !is_writable(PFTEMPPATH) ){
			throw new PFException_TempDirNotWritable();
		}
	}
}
?>
<?php
/**
 * Classe per la gestione di risorse binarie
 */
class FileUrlPFResource extends PFResourceBase implements PFResource{
		
	/**
	 * costruttore di classe
	 */
	function __construct($resource) {
		parent::__construct();
		
		$this->originalResource = $resource;
		
		//salvo il nome del file temporaneo nel quale
		//la classe ha salvato il file remoto
		$this->createGDResource($resource);
				
	}
	/**
	 * funzione che crea la risorsa GD specifica per questa classe
	 */	
	public function createGDResource($resource){
		//controllo risorsa
		if(!PFResourceHelper::isFileUrlResourse($resource))
			throw new PFException_InvalidFileUrl();
		
		//se fallisce la lattura del file remoto o locale
		if( FALSE === ( $contents = file_get_contents($resource) ) )
			throw new PFException_GetFileError(
				"Errore lettura file ".$resource
			);
			
		$this->GDResource = @imagecreatefromstring( $contents );
		if ( ! is_resource( $this->GDResource ) ){
			//sollevo eccezione il file non e' un immagine
		 	throw new PFException_InvalidImageFile();
		}
		
		imagealphablending($this->GDResource, FALSE);
		imagesavealpha($this->GDResource, TRUE);
	}
	
}
?>
<?php
/**
 * Classe per la gestione di risorse binarie
 */
class StringPFResource extends PFResourceBase implements PFResource{
		
	/**
	 * costruttore di classe
	 */
	function __construct($resource) {
		parent::__construct();
		$this->originalResource = $resource;
		$this->createGDResource($this->originalResource);
	}
	/**
	 * funzione che crea la risorsa GD specifica per questa classe
	 */	
	public function createGDResource($contents){
		if(!PFResourceHelper::isBinaryResource($contents, $binaryData))
			throw new PFException_InvalidBinaryString();
		
		$this->GDResource = @imagecreatefromstring( $binaryData );
		 if ( ! is_resource( $this->GDResource ) ){
		 	//sollevo eccezione il file non e' un immagine
		 	throw new PFException_InvalidImageFile();
		 }
		 
		imagealphablending($this->GDResource, FALSE);
		imagesavealpha($this->GDResource, TRUE);
	}
	
}
?>
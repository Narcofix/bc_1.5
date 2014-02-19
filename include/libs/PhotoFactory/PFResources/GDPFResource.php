<?php
/**
 * Classe per la gestione di risorse binarie
 */
class GDPFResource extends PFResourceBase implements PFResource{
		
	/**
	 * costruttore di classe
	 */
	function __construct($resource) {
		parent::__construct();
		//dato che si tratta giá di una risorsa GD  		
		$this->originalResource = $resource;
		//creo la risorsa GD
		$this->createGDResource($this->originalResource);
	}
	
	/**
	 * funzione che crea la risorsa GD specifica per questa classe
	 */	
	public function createGDResource($contents){
		
		if(!PFOps::isGDResourse($contents))
			throw new PFException_InvalidGDResource();
		
		//trasformo la risorsa gd in truecolor se necessario
		PFOps::convertGDToTT($contents);
		
		$this->GDResource = $contents;
		
		imagealphablending($this->GDResource, FALSE);
		imagesavealpha($this->GDResource, TRUE);
		
	}
}
?>
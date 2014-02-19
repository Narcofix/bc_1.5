<?php
/**
 * classe astratta che definisce la base di una risorsa PF
 */
abstract class PFResourceBase {
	//risorsa originale
	protected $originalResource = NULL;
	//risorsa GD
	protected $GDResource = NULL;
	//array nomi file temporaney
	protected $tempFileArray;
	//id univoco file temporanei
	protected $tempFileUID;
	//costruttore
	function __construct() {
		//carico la classe GDPFResource utile ai metodi che non eseguono le modifiche in loco
		PFResourceHelper::loadClass('GDPFResource', PFRESPATH . "GDPFResource.php");

		$this -> tempFileArray = array();
		$this -> tempFileUID = uniqid();
	}

	/*fine funzioni statiche*/

	//distruttore
	function __destruct() {
		$this -> cleanTempFiles();
		PFOps::destroyGDResource($this -> GDResource);
		//libero la memoria utilizzata dalla risorsa originale
		$this -> originalResource = NULL;
	}

	//elimina i file temporanei
	protected function cleanTempFiles() {
		for ($i = 0; $i < count($this -> tempFileArray); $i++)
			file_exists($this -> tempFileArray[$i]) && unlink($this -> tempFileArray[$i]);
	}

	/*
	 * crea un file temporaneo nella cartella impostata
	 * nel file di configurazione e ritorna il nome
	 */
	protected function getTempFileName() {
		$tmpName = tempnam(PFTEMPPATH, $this -> tempFileUID);
		array_push($this -> tempFileArray, $tmpName);
		return $tmpName;
	}

	public function getResUniquid(){
		return 	$this -> tempFileUID;
	}

	/**
	 * funzione di test
	 */
	public function getTF() {
		return $this -> getTempFileName();
	}

	public function getOriginalResource() {
		return $this -> originalResource;
	}

	public function getGDResource() {
		return $this -> GDResource;
	}
    
    public function saveGdToFile($type=3,$path){
        $imagefunction = "image".substr( image_type_to_extension($type), 1 );
        
        return $imagefunction(
            $this -> GDResource,
            $path
        );
    }
    
    public function outputGdToBrowser($type = 3){
        $imagefunction = "image".substr( image_type_to_extension($type), 1 );
        
        return $imagefunction( $this -> GDResource );
    }
    
	protected function replaceGDResource($newGDRes){
		if( !PFOps::isGDResourse($newGDRes) )
			throw new PFException_InvalidGDResource();
		//elimino risorsa precedente
		PFOps::destroyGDResource($this -> GDResource);
		//salvo nuova risorsa
		$this -> GDResource = $newGDRes;
	}

	/**
	 * metodo che crea un clone della classe
	 * di tipo GDPFResource
	 */
	public function cloneIt() {

		return new GDPFResource(PFOps::cloneGDResource($this -> GDResource));
	}

	/**
	 * operazione di crop in loco
	 */
	public function crop($left = 0, $top = 0, $width = NULL, $height = NULL) {
		
		$this->replaceGDResource(
			PFOps::call(
				"crop",
				array(
					//risorsa GD originale
					"GDres" => &$this -> GDResource,
					//posizione left
					"Lcrop" => $left,
					//posizione top
					"Tcrop" => $top,
					//larghezza del ritaglio
					"Wcrop" => $width,
					//altezza del ritaglio
					"Hcrop" => $height
				)
			)		
		);
	}

	/**
	 * operazione di crop che crea una nuova risorsa
	 */
	public function getCropped($left = 0, $top = 0, $width = NULL, $height = NULL) {

		return new GDPFResource(
			PFOps::call(
				"crop", 
				array(
					//risorsa GD originale
					"GDres" => &$this -> GDResource,
					//posizione left
					"Lcrop" => $left,
					//posizione top
					"Tcrop" => $top,
					//larghezza del ritaglio
					"Wcrop" => $width,
					//altezza del ritaglio
					"Hcrop" => $height
				)
			)
		);
	}

	/**
	 * opereazione di resize in loco
	 */
	public function resize($width = NULL, $height = NULL, $mode = NULL) {
		$this->replaceGDResource(
			PFOps::call(
				"resize",
				array(
					"GDres" 	=> &$this -> GDResource,
					"Wresize" 	=> $width,
					"Hresize" 	=> $height,
					"Modo" 		=> $mode
				)
			)
		);
	}

	/**
	 * opereazione di resize che crea una nuova risorsa
	 */
	public function getResized($width = NULL, $height = NULL, $mode = NULL) {

		return new GDPFResource(
			PFOps::call(
				"resize",
				array(
					"GDres" 	=> &$this -> GDResource,
					"Wresize" 	=> $width,
					"Hresize" 	=> $height,
					"Modo" 		=> $mode
				)
			)
		);
	}

	/*
	 * Funzione che esegue il merge con la risorsa passata in ingresso, in loco
	 */
	public function merge($PFResource, $left = 0, $top = 0, $pct = 100) {
		//non ho bisogno di rimpiazzare la risorsa della classe
		PFOps::call(
			"merge",
			array(
				"GDres" 	=> &$this -> GDResource, 
				"GDresOver" => $PFResource -> getGDResource(),
				"Lmerge" 	=> $left,
				"Tmerge" 	=> $top,
				"pct" 		=> $pct
			)
		);
	}
	
	/*
	 * Funzione che esegue il merge con la risorsa passata in ingresso, creandone pero' una nuova
	 */
	public function getMerged($PFResource, $left = 0, $top = 0, $pct = 100) {
		//clono la risorsa in ingresso
		$newPFResource = $this -> cloneIt();
		PFOps::call(
			"merge",
			array(
				"GDres" 	=> $newPFResource -> getGDResource(), 
				"GDresOver" => $PFResource -> getGDResource(), 
				"Lmerge" 	=> $left,
				"Tmerge" 	=> $top,
				"pct" 		=> $pct
			)
		);
		return $newPFResource;
	}
	
	/**
	 * possibilita' di chiamata a funzione custom che esegue l'op
	 * sulla risorsa appartente alla classe chiamante (in loco)
	 */
	public function custom($opname,$bundle){
		/**
		 * importante che questa chiave non sia definita
		 * poiche' viene passata con la risorsa gd di questa classe
		 */
		if(isset($bundle["GDres"]) )
			throw new PFException_OpArgsError("Chiave GDres definita nel bundle");
		
		//includo se non definita la risorsa gd della classe
		$bundle["GDres"] = &$this -> GDResource;
		 
		 //rimpiazzo la risorsa della classe con quella creata dalla custom op
		$this->replaceGDResource( PFOps::call( $opname, $bundle ) );
		
	}
	/**
	 * possibilita' di chiamata a funzione custom che esegue l'op
	 * sulla risorsa appartente alla classe e ne ritorna una nuova
	 */
	public function getCustomized($opname,$bundle){
		/**
		 * importante che questa chiave non sia definita
		 * poiche' viene passata con la risorsa gd di questa classe
		 */
		if(isset($bundle["GDres"]) )
			throw new PFException_OpArgsError("Chiave GDres definita nel bundle");
		
		//includo se non definita la risorsa gd della classe
		$bundle["GDres"] = &$this -> GDResource;
		
		return new GDPFResource( PFOps::call( $opname, $bundle ) );
		
	}
}
?>
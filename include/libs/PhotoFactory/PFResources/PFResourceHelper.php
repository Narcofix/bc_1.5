<?php

class PFResourceHelper{
	/**
	 * funzione che identifica il tipo di risorsa da instanziare
	 * che successivamente verra data in pasto al metodo factory.
	 * Questo passo puó essere omesso se il programmatore conosce a priori
	 * il tipo di risorsa.
	 */
	public static function getResType($resource){
		
		if( !isset($resource) || is_null($resource) ){
			throw new PFException_PFResourceHelperError(
				"PFResourceHelper ha bisogno di un argomento non nullo"
			);
			return;
		}
		
		//tipo di risorsa trovata da questo algoritmo
		$FPFoundType = '';
		//controllo subito se si tratta di una stringa
		$isString = is_string($resource);
		
		//verifico se si tratta di una risorsa PFResource
		if(
			!$isString && 
			$resource instanceof PFResource
		){
			//clono la risorsa
			return $resource->cloneIt();
		}
		
		//risorsa GD
		if (
			!$FPFoundType &&
			!$isString && 
			PFOps::isGDResourse($resource)
		){
			$FPFoundType = "GDPFResource";
		}
		
		// Check for stringa binaria
		if (
			!$FPFoundType &&
			$isString &&
			self::isBinaryResource($resource,$binRes)
		){
			$FPFoundType = "StringPFResource";
		}
		/*
		if ($isString && $resource == '')
			$FPFoundType = 'StringPFResource';
		//immagini uploadate, accesso tramite index
		if (
			//ometto il controllo su $FPFoundType poiche' l'index al file potrebbe essere la stringa vuota
			//caso upload normale
			isset($_FILES[$resource]) &&
			//caso upload array 
			isset($_FILES[$resource]['tmp_name'])
		)
			$FPFoundType = "FileUpPFResource";*/
		
		// verifico se si tratta di un file esistente locale o remoto
		if (
			!$FPFoundType && 
			$isString &&
			self::isFileUrlResourse($resource) 
		)
			$FPFoundType = "FileUrlPFResource";
		
		//se non trovato
		if(!$FPFoundType){
			//sollevo eccezione tipo risorsa non trovata
			throw new PFException_PFResourceHelperError(
				"Tipo di risorsa non supportata.".
				($isString?"\nPotrebbe essere un errore nella path o un file corrotto.": "")
			);
			return;
		}
		//caso in cui ho trovato la risorsa giusta
		
		//load dinamico della classe da istanziare
		self::loadClass($FPFoundType,PFRESPATH.$FPFoundType.".php");
		
		//istanzio l'oggetto e gli passo come parametro la risorsa
		return new $FPFoundType($resource);
	}
	
	/**
	 * funzione che carica una classe se non definita
	 */
	static function loadClass($class,$classPath){
		$realClassPath = realpath($classPath);
		//se la classe non e' stata ancora caricata
		if (!class_exists($class)){
			//se il file della classe non esiste
			if(!file_exists($realClassPath)){
				//sollevo eccezione: classe non trovata
				throw new PFException_ClassPathError();
				return;
			}
			//altrimenti richiedo la classe
			require_once($realClassPath);
		}
	}
	
	/**
	 * controlla se si tratta di una risorsa locale o remota
	 * Questo metodo presenta degli hack per supportare
	 * tutte le versioni di php
	 */
	 static function isFileUrlResourse($resource){
	 	return 
	 	//se e' attivo il flag allow_url_fopen
		PFREMOTEON ?
			//verifica possibile solo se flag allow_url_fopen attiva
			!(FALSE === @fopen($resource,"rb") ) : 
			//leggo un solo byte per vedere se esiste o se ci posso accedere
			!(FALSE ===
				(PFFGCMLON ?
					//solo php >= 5.1.0
					file_get_contents($resource,0,null,0,1):
					//tutti gli altri
					file_get_contents($resource,0)
				)
			);
	 }
	/**
	 * Controlla se si tratta di un' immagine 
	 * in stringa binaria.
	 * Necessita di un secondo parametro passato per riferimento,
	 * nel quale verra' salvata la stringa binaria, 
	 * nel caso in cui la stringa originale sia nel formato base64,
	 * o altro... (da implementare) 
	 */
	 static function isBinaryResource($resource,&$binaryData){
	 	$binaryData = $resource;
		
		//controllo se era compressa base64
		if( ( $decodedRes = @base64_decode($resource,TRUE) ) ) 
			$binaryData = $decodedRes;
		
		return @imagecreatefromstring($binaryData)!== FALSE;
	 }
}
?>
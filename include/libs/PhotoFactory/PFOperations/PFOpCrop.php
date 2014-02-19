<?php

require_once(PFOPSPATH.'PFOpInterface.php' );

/**
 * eccezioni relative a questa classe
 */

/**
 * eccezione crop
 */
class PFException_Crop extends PFException {}
 
/**
 * classe per l'operazione di cropping
 */

class PFOpCrop implements PFOpInterface{

	public static $RequiredArgs = 
		array(
			"GDres",
			"Lcrop",
			"Tcrop",
			"Wcrop",
			"Hcrop"
		); 

	public static function checkArgs($bundle){
		return TRUE;
	}
	
	public static function execute($bundle){
		
		//risorsa GD originale
		$GDres = &$bundle["GDres"];
		//posizione left
		$Lcrop = &$bundle["Lcrop"];
		//posizione top
		$Tcrop = &$bundle["Tcrop"];
		//larghezza del ritaglio
		$Wcrop = &$bundle["Wcrop"];
		//altezza del ritaglio
		$Hcrop = &$bundle["Hcrop"];

		//larghezza risorsa gd
		$WGDRes = imagesx($GDres);
		//altezza risorsa gd
		$HGDRes = imagesy($GDres);
		
		/**
		 * setto i parametri non corretti = ai loro defaults
		 */
		if( !is_numeric($Lcrop) ) $Lcrop = 0;
		if( !is_numeric($Tcrop) ) $Tcrop = 0;
		if( is_null($Wcrop) || !is_numeric($Wcrop) )$Wcrop = $WGDRes;
		if( is_null($Hcrop) || !is_numeric($Hcrop) )$Hcrop = $HGDRes;
		
		//restituisco l'immagine originale poiche' le sue dimensioni 
		//sono uguali ai parametri in ingresso 
		if( 
			$Lcrop == ( $Tcrop == 0 ) &&
			$Wcrop == $WGDRes && 
			$Hcrop == $HGDRes 
		) return $GDres;
		
		/*
		 * se la posizione left e' negativa la aggiungo alla larghezza
		 * e la imposto a zero per non inserire parti vuote		
		 */
		if ($Lcrop < 0){
			$Wcrop = $Lcrop + $Wcrop;
			$Lcrop = 0;
		}

		/*
		 * se la larghezza del ritaglio esce dalla larghezza dell'immagine
		 * la imposto come la larghezza dell'immagine
		 */
		if ($Wcrop > $WGDRes - $Lcrop) $Wcrop = $WGDRes - $Lcrop;
		
		
		if ($Tcrop < 0){
			$Hcrop = $Tcrop + $Hcrop;
			$Tcrop = 0;
		}

		if ($Hcrop > $HGDRes - $Tcrop) $Hcrop = $HGDRes - $Tcrop;

		if ($Wcrop <= 0 || $Hcrop <= 0)
			throw new PFException_Crop("Ritaglio fuori dallo spazio dell'immagine");

		return PFOps::imageCopyResize($GDres,0, 0, $Lcrop, $Tcrop, $Wcrop, $Hcrop, $Wcrop, $Hcrop);
	}

}
?>
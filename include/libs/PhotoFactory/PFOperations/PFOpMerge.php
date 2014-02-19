<?php

require_once(PFOPSPATH.'PFOpInterface.php' );

/**
 * eccezioni relative a questa classe
 */
 
/**
 * classe per l'operazione di merge di due immagini.
 * di default applica l'immagine di overlay sulla base.
 * per creare un terza immagine basta passare in ingresso 
 * un clone della base
 */

class PFOpMerge implements PFOpInterface{

	public static $RequiredArgs = 
		array(
			"GDres",
			"GDresOver",
			"Lmerge",
			"Tmerge",
			"pct"
		);
		
	public static function checkArgs($bundle){
		return TRUE;
	}

	public static function execute($bundle){
			
		//risorsa GD originale base
		$GDres 		= &$bundle["GDres"];
		//risorsa GD originale overlay
		$GDresOver 	= &$bundle["GDresOver"];
		//posizione left
		$Lmerge 	= &$bundle["Lmerge"];
		//posizione top
		$Tmerge 	= &$bundle["Tmerge"];
		//valore che va da 0 a 100 che definisce la % di trasparenza dell'overlay
		$pct 		= &$bundle["pct"];

		//larghezza risorsa gd
		$WGDRes = imagesx($GDres);
		//altezza risorsa gd
		$HGDRes = imagesy($GDres);
		//larghezza risorsa gd overlay
		$WGDResOver = imagesx($GDresOver);
		//altezza risorsa gd overlay
		$HGDResOver = imagesy($GDresOver);
		
		/**
		 * setto i parametri non corretti = ai loro defaults
		 */
		if( is_null($Lmerge)|| !is_numeric($Lmerge) ) $Lmerge = 0;
		if( is_null($Tmerge)|| !is_numeric($Tmerge) ) $Tmerge = 0;
		if( is_null($pct) 	|| !is_numeric($pct) )$pct = 100;
		
		//se la trasparenza e' massima non applico nessuna op
		if($pct > 0){
    		imagealphablending($GDres, TRUE);
    		imagealphablending($GDresOver, TRUE);
			imagesavealpha($GDresOver, TRUE);
			PFOps::imageMerge($GDres, $GDresOver, 0, 0, $Lmerge, $Tmerge, $WGDResOver, $HGDResOver, $pct);
			imagealphablending($GDres, FALSE);
		}
		
		return $GDres; 
	}

}
?>
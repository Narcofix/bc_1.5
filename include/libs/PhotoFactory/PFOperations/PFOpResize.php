<?php

require_once(PFOPSPATH.'PFOpInterface.php' );

/**
 * eccezioni relative a questa classe
 */
/**
 * eccezione crop
 */
class PFException_Resize extends PFException {}
/**
 * classe per l'operazione di resize
 */
class PFOpResize implements PFOpInterface {

	public static $RequiredArgs = 
		array(
			"GDres",
			"Wresize",
			"Hresize",
			"Modo"
		);

	public static function checkArgs($bundle){
		return TRUE;
	}

	public static function execute($bundle) {
		
		//risorsa GD originale
		$GDres 		= &$bundle["GDres"];
		//altezza del resize
		$Wresize 	= &$bundle["Wresize"];
		//larghezza del resize
		$Hresize 	= &$bundle["Hresize"];
		//modo del ritaglio
		$Modo 		= &$bundle["Modo"];
		
		//larghezza risorsa gd
		$WGDRes = imagesx($GDres);
		//altezza risorsa gd
		$HGDRes = imagesy($GDres);
		
		$newDims = self::resizeDimensions($WGDRes,$HGDRes,$Wresize,$Hresize,$Modo);
		
		
		if ($newDims['W'] <= 0 || $newDims['H'] <= 0){
			throw new PFException_Resize("Una delle dimensioni e < 0");
			return;
		}
			
		return PFOps::imageCopyResize($GDres,0, 0, 0, 0, $newDims['W'],$newDims['H'],$WGDRes,$HGDRes);
	}


	/**
	 * algoritmo di resize delle dimensioni (trovato navigando, rivisitato),
	 * che sistema le dimensioni in 3 diversi modi:
	 * 0 non rispetta l'aspect ratio
	 * 1 (default) se presenti entrambe le dimensioni di destinazione,
	 * calcola le dimensioni che rispettano l'aspect ratio e che 
	 * generano tra le due l'immagine con area minore.
	 * 2 calcola le dimensioni che rispettano l'aspect ratio e che 
	 * generano tra le due l'immagine con area maggiore.
	 **/ 

	 static function resizeDimensions($WGDRes,$HGDRes, $Wdest, $Hdest, $mode = NULL) {
		
		if ($Wdest === NULL && $Hdest === NULL)
			return array($WGDRes,$HGDRes);
		
		if ($Wdest !== NULL)
			$rx = $WGDRes / $Wdest;
		else
			$rx = NULL;

		if ($Hdest !== NULL)
			$ry = $HGDRes / $Hdest;
		else
			$ry = NULL;

		if ($rx === NULL && $ry !== NULL) {
			$rx = $ry;
			$Wdest = round($WGDRes / $rx);
		}

		if ($ry === NULL && $rx !== NULL) {
			$ry = $rx;
			$Hdest = round($HGDRes / $ry);
		}

		if ($Wdest === 0 || $Hdest === 0)
			return array(0,0);

		if(is_null($mode))$mode = PFResizeIn;

		switch ($mode) {
			case PFResizeNoRatio:
				$ratio = 0;
				break;
			case PFResizeIn:
				$ratio = ($rx > $ry) ? $rx : $ry;
				break;
			case PFResizeOut:
				$ratio = ($rx < $ry) ? $rx : $ry;
				break;
		}
		
		return (
		$ratio <=0 ?
			array('W' => $Wdest, 'H' => $Hdest ):
		    array('W' => round($WGDRes / $ratio), 'H' => round($HGDRes / $ratio) )
		);
	}

}
?>
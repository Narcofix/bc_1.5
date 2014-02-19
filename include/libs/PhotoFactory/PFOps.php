<?php
/**
 * classe statica delle operazioni
 * che si possono svolgere sulle risorse
 * Le operazioni sono divise in classi
 * in modo tale che l'utente puo' implementare
 * anche la sua funzione personale
 */
class PFOps{
	static protected $cachedOps = array();
		
	static function call($opName,$bundle){		
		//se il primo argomento non e' una stringa o e' una stringa vuota
		if(
			//ometto controllo fatto precedentemente
			//!isset( $arg_list[0] ) ||
			//se il primo argomento non e' una stringa
			!is_string( $opName )  ||
			//o se la stringa e' vuota 
			!( strlen($opName) > 0 )
		){
			throw new PFException_OpNameError();
			return;
		}		
		
		//preparo il nome della classe unendo il prefisso all'operazione
		$opName = PFOPPREFIX.ucfirst(strtolower($opName));
		
		//controllo se la classe e' presente nella cache
		if(!isset(self::$cachedOps[$opName])){
			//carico la classe richiesta una sola volta
			PFResourceHelper::loadClass($opName,PFOPSPATH.$opName.".php");
			//salvo copia nella cache
			self::$cachedOps[$opName] = TRUE;
		}
		
		if(
			//controllo argomenti richiesti dalla classe
			!self::checkRequiredArgs($bundle, $opName::$RequiredArgs ) ||
			//altri controlli specializzati della classe
			!$opName::checkArgs($bundle)
		){
			throw new PFException_OpArgsError();
			return;
		}
		
		return $opName::execute($bundle);		
	}

	/**
	 * funzione che controlla se le chiavi richieste
	 * esistono
	 */
	public static function checkRequiredArgs(&$bundle,&$required){
		if( count( array_intersect_key( array_flip($required), $bundle) ) === count($required) )
			return TRUE;
		else
			return FALSE;
	}

	//funzione di test
	public static function getCachedOps(){
		return self::$cachedOps;
	}
	/**
	 * funzione che clona una risorsa GD
	 */
	public static function cloneGDResource($gd){
    	//inizio cattura
    	ob_start();
		//salvo buffer risorsa gd
    	imagegd2($gd);
		//fine cattura
		$cloneBuffer = ob_get_clean();
		//creo la nuova risorsa a partire dal buffer
    	return imagecreatefromstring($cloneBuffer);
	}
	
	/*funzioni statiche disponibili per tutte le sottoclassi*/
	public static function destroyGDResource(&$GDResource){
		if($GDResource){
            //libero la memoria utilizzata dalla risorsa gd
			imagedestroy($GDResource);
   		}
	}
	/**
	 * Controlla se si tratta di una risorsa GD
	 */
	static function isGDResourse($resource){
		return (
			@is_resource($resource) && 
			@get_resource_type($resource) == 'gd'
		);
	}
	/**
	 * funzione statica che crea una nuova risorsa gd truecolor
	 * a partire da larghezza e altezza 
	 */	
	public static function newGDResourceTT($W, $H){
		//se l'area e' uguale a zero o la larghezza e' negativa
		if ($W * $H <= 0 || $W < 0) throw new PFException_InvalidGDDimensions();
		
		//crea la risorsa
		return imagecreatetruecolor($W, $H);
	}
	/**
	 * funzione statica che crea una nuova risorsa gd no truecolor
	 * a partire da larghezza e altezza 
	 */	
	public static function newGDResourcePL($W, $H){
		//se l'area e' uguale a zero o la larghezza e' negativa
		if ($W * $H <= 0 || $W < 0) throw new PFException_InvalidGDDimensions();
		
		//crea la risorsa
		return imagecreate($W, $H);
	}
	/**
	 * converte una risorsa GD palette in truecolor
	 */
	public static function convertGDToTT(&$srcGD,$srcGDW=NULL,$srcGDH=NULL){
		
		if( !function_exists('imagepalettetotruecolor')	){
        	//se e' gia' true color..	
        	if( imageistruecolor($srcGD) ) return TRUE;
        	
        	//se non presenti calcolo dimensioni
			if( is_null($srcGDW) )$srcGDW = imagesx($srcGD);
			if( is_null($srcGDH) )$srcGDH = imagesy($srcGD);
			
        	$dstGD = imagecreatetruecolor($srcGDW, $srcGDH);
			
			if (imagecolortransparent($srcGD) >= 0 )
				self::copyGDTransparence($srcGD,$dstGD);	
			
        	imagecopy($dstGD, $srcGD, 0, 0, 0, 0, $srcGDW, $srcGDH);
			
        	imagedestroy($srcGD);

        	$srcGD = $dstGD;

        	return TRUE;
    	}
		else 
			return imagepalettetotruecolor ( $srcGD ); 
	}
	/**
	 * funzione statica che copia la trasparenza da una risorsa GD
	 * in un altra 
	 */	
	public static function copyGDTransparence(&$fromGDres,&$toGDres){
			
		$cols = imagecolorstotal($fromGDres);
		$transcol = imagecolortransparent($fromGDres);
		
		if ($transcol >= $cols && $cols > 0) return;
				
		$rgba = imageColorsForIndex($fromGDres, $transcol);
			
		$rgba['alpha'] = 127;
			
		$color = imageColorAllocateAlpha(
			$toGDres, 
			$rgba['red'],
			$rgba['green'],
			$rgba['blue'],
			$rgba['alpha']
		); 
		
		imagecolortransparent($toGDres, $color);
	
		imagefill($toGDres,0,0, $color);
	}
	
	/**
	 * crea una copia ridimensionata della risorsa Gd originale, preservando la trasparenza, 
	 * di dimesioni pari a quelle parametri.
	 */
	
	public static function imageCopyResize(&$fromGDres,$dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h){
		/**
		 * eseguo verifica presenza trasparenza per preservarla
		 * (tratto dal web..)
		 */
		//se trasparente
		if (imagecolortransparent($fromGDres) >= 0 ){
			//creo la nuova risorsa gd nella quale salvo il ritaglio
			$newGDres = self::newGDResourcePL($dst_w, $dst_h);
			self::copyGDTransparence($fromGDres,$newGDres);

			if (!imagecopyresized($newGDres, $fromGDres, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) )
				throw new PFException_Op("Errore imagecopyresized");
		}
		else{
			//creo la nuova risorsa gd nella quale salvo il ritaglio
			$newGDres = self::newGDResourceTT($dst_w, $dst_h);
			
			imagealphablending($newGDres, FALSE);
			imagesavealpha($newGDres, TRUE);
			
			if (!imagecopyresampled($newGDres, $fromGDres, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) )
				throw new PFException_Op("Errore imagecopyresampled");
		}
		return $newGDres;
	}
	
	/**
	 * copia la risorsa gd overlay su quella base seguendo le posizioni x,y in ingresso
	 */
	public static function imageMerge(&$baseGDres,&$overGDres,$base_x, $base_y, $over_x, $over_y,$over_w, $over_h, $pct){
		
		if (($pct > 0 && $pct < 100)){
			self::imagecopymerge_alpha($baseGDres, $overGDres, $over_x, $over_y, $base_x, $base_y, $over_w, $over_h, $pct);
		}
		else{
			if ( !imagecopy($baseGDres,	$overGDres, $over_x, $over_y, $base_x, $base_y, $over_w, $over_h) )
				throw new PFException_Op("Errore imagecopy");
		}
	}
	/**
	 * Fix per Bug trasparenze imagecopymerge
	 * merge two true colour images with variable opacity while maintaining alpha
	 * transparency of both images.
	 *
	 * @access public
	 *
	 * @param  resource $dst  Destination image link resource
	 * @param  resource $src  Source image link resource
	 * @param  int      $dstX x-coordinate of destination point
	 * @param  int      $dstY y-coordinate of destination point
	 * @param  int      $srcX x-coordinate of source point
	 * @param  int      $srcY y-coordinate of source point
	 * @param  int      $w    Source width
	 * @param  int      $h    Source height
	 * @param  int      $pct  Opacity of source image
	 ******************************************************************************/
	public static function imagecopymerge_alpha($dst, $src, $dstX, $dstY, $srcX, $srcY, $w, $h, $pct){
	    $pct /= 100;
	 
	    /* make sure opacity level is within range before going any further */
	    $pct  = max(min(1, $pct), 0);
	 
	    if ($pct == 0)
	    {
	        /* 0% opacity? then we have nothing to do */
	        return;
	    }
	 
	    /* work out if we need to bother correcting for opacity */
	    if ($pct < 1)
	    {
	        /* we need a copy of the original to work from, only copy the cropped */
	        /* area of src                                                        */
	        $srccopy  = imagecreatetruecolor($w, $h);
	 
	        /* attempt to maintain alpha levels, alpha blending must be *off* */
	        imagealphablending($srccopy, false);
	        imagesavealpha($srccopy, true);
	 
	        imagecopy($srccopy, $src, 0, 0, $srcX, $srcY, $w, $h);
	 
	        /* we need to know the max transaprency of the image */
	        $max_t = 0;
	 
	        for ($y = 0; $y < $h; $y++)
	        {
	            for ($x = 0; $x < $w; $x++)
	            {
	                $src_c = imagecolorat($srccopy, $x, $y);
	                $src_a = ($src_c >> 24) & 0xFF;
	 
	                $max_t = $src_a > $max_t ? $src_a : $max_t;
	            }
	        }
	        /* src has no transparency? set it to use full alpha range */
	        $max_t = $max_t == 0 ? 127 : $max_t;
	 
	        /* $max_t is now being reused as the correction factor to apply based */
	        /* on the original transparency range of  src                         */
	        $max_t /= 127;
	 
	        /* go back through the image adjusting alpha channel as required */
	        for ($y = 0; $y < $h; $y++)
	        {
	            for ($x = 0; $x < $w; $x++)
	            {
	                $src_c  = imagecolorat($src, $srcX + $x, $srcY + $y);
	                $src_a  = ($src_c >> 24) & 0xFF;
	                $src_r  = ($src_c >> 16) & 0xFF;
	                $src_g  = ($src_c >>  8) & 0xFF;
	                $src_b  = ($src_c)       & 0xFF;
	 
	                /* alpha channel compensation */
	                $src_a = ($src_a + 127 - (127 * $pct)) * $max_t;
	                $src_a = ($src_a > 127) ? 127 : (int)$src_a;
	 
	                /* get and set this pixel's adjusted RGBA colour index */
	                $rgba  = ImageColorAllocateAlpha($srccopy, $src_r, $src_g, $src_b, $src_a);
	 
	                /* ImageColorAllocateAlpha returns -1 for PHP versions prior  */
	                /* to 5.1.3 when allocation failed                               */
	                if ($rgba === false || $rgba == -1)
	                {
	                    $rgba = ImageColorClosestAlpha($srccopy, $src_r, $src_g, $src_b, $src_a);
	                }
	 
	                imagesetpixel($srccopy, $x, $y, $rgba);
	            }
	        }
	 
	        /* call imagecopy passing our alpha adjusted image as src */
	        imagecopy($dst, $srccopy, $dstX, $dstY, 0, 0, $w, $h);
	 
	        /* cleanup, free memory */
	        imagedestroy($srccopy);
	        return;
	    }
	 
	    /* still here? no opacity adjustment required so pass straight through to */
	    /* imagecopy rather than imagecopymerge to retain alpha channels          */
	    imagecopy($dst, $src, $dstX, $dstY, $srcX, $srcY, $w, $h);
	    return;
	}
}

?>
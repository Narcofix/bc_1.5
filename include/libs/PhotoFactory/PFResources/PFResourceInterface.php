<?php
/**
 * funzioni che devono essere implementate in tutti i prodotti specializzati
 */
interface PFResource{
	
	public function getOriginalResource();
	
	public function getGDResource();

	public function createGDResource($contents);
	
	public function cloneIt();

}
?>
<?php
/**
 * classe astratta che definisce la base di una Operazione PF
 */
interface PFOpInterface{
	public static function execute($bundle);
	
	public static function checkArgs($bundle);
}
?>
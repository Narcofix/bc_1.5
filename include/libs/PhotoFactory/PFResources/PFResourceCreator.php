<?php
//PhotoResourceCreator.php
abstract class PFResourceCreator{
	//istanza singleton
	protected static $_istance;
		
	protected abstract function factoryMethod(PFResource $photoResNew);
	
	public function doFactory($resource = NULL){
	
		return $this->factoryMethod(
			PFResourceHelper::getResType(
				$resource 
			)
		);
	}
}
?>
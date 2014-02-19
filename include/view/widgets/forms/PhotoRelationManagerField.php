<?php
require_once(realpath(dirname(__FILE__)) . '/../../../../include/view/widgets/FormWidget.php');
require_once(realpath(dirname(__FILE__)) . '/../../../../include/settings.inc.php');


class PhotoRelationManagerField extends FormWidget {

	/**
	 * @access public
	 * @param preload
	 * @ParamType preload  string
	 */
	public function build($preload) {
		$content="";
		switch ($this->orientation) {
			case RIGHT:
				$mainEntity = $this->form->entity->entity_1;
				$secondaryEntity = $this->form->entity->entity_2;
				$mainEntityRoleName=$this->form->entity->roleName1;
				$secondaryEntityRoleName=$this->form->entity->roleName2;
				break;
			case LEFT:
				$mainEntity = $this->form->entity->entity_2;
				$secondaryEntity = $this->form->entity->entity_1;
				$mainEntityRoleName=$this->form->entity->roleName2;
				$secondaryEntityRoleName=$this->form->entity->roleName1;
				break;
		}

        if(Settings::getOperativeMode() == 'debug'){
            echo '<br />Relation Manager Field';
            echo ' entity_1';
            var_dump($mainEntity->name);
            echo ' entity_2';
            var_dump($secondaryEntity->name);
            echo '<br />Orientation';
            echo $this->orientation;
        }
		
		$preloadedIds = array();
		
		if($preload==PRELOAD && $mainEntity->loaded)
		{
			$where_conditions=array($mainEntity->fields[0]->name."_".$mainEntity->name=>$mainEntity->instances[0]->getKeyFieldValue());
			$this->form->entity->retrieveAndLink($where_conditions);
			
			foreach($this->form->entity->instances as $relationInstanceKey=>$relationInstance){
				$preloadedIds[] = $relationInstance->getFieldValue($secondaryEntityRoleName);
			}
			
		}

		$key = $this->form->formHash."_";
		$name = "{$secondaryEntity->fields[0]->name}_{$secondaryEntity->name}_".$key;
		
		$widget = new Skinlet("widget/PhotoField");
		$widget->setContent("multiselect", TRUE);
		$widget->setContent("label", $this->label);
		//array associativo nomi variabili
		$widget->setContent("name",$name);
		//hash del form
		$widget->setContent("formHash",$key);
		$widget->setContent("loggedUsername",$_SESSION["user"]["username"]);
		//array delle immagini precentemente selezionate
		$widget->setContent("preloadedImageId",json_encode($preloadedIds,TRUE));
		return $widget->get();
	}
}

/**
 * Factory for the checkbox widget
 * @author nicola
 *
 */

class PhotoRelationManagerFieldFactory implements FormWidgetFactory
{
	public function create($form)
	{
		return new PhotoRelationManagerField($form);
	}
}
?>
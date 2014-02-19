<?php
require_once ("Form.php");

/**
 *
 */
class PhotoForm extends Form
{
    private $attributeName;

    public function addImage($name, $label, $mandatory = "off",$entity=null)
    {
        $factory=new ImageFieldFactory();
        $newField = $factory->create($this);
        $this->attributeName=$name;
        $newField->name= $name;
        $newField->type = "hierarchicalPosition";
        $newField->label = $label;
        $newField->mandatory = $mandatory;
        $newField->entity = $entity;
        $this->elements[] = $newField;
    }

    public function emitHTML($operation, $page, $preload)
    {
        if(isset($_REQUEST["value"]) && $preload==PRELOAD)
        {
            $where_values=array($this->entity->fields[0]->name=>$_REQUEST['value']);
            $this->entity->retrieveAndLink($where_values);
        }
        return parent::emitHTML($operation, $page, $preload);
    }

    public function edit($entity=null)
    {
        $baseEntityPrimaryKeyName=$entity->fields[0]->name;
        $baseEntityPrimaryKeyValue=$_REQUEST[$entity->fields[0]->name];
        $where_conditions=array($entity->fields[0]->name => $baseEntityPrimaryKeyValue);

        if (Settings::getOperativeMode() == 'debug'){
            echo '<br> edit in ImageForm debug';
            echo '<br>$_REQUESTin imageform<br>';
            var_dump($_REQUEST);
        }

		$set_values = array(
			$this->attributeName => 
				isset( $_REQUEST[$this->formHash."_".$this->attributeName] ) ?
					$_REQUEST[$this->formHash."_".$this->attributeName] : 
					0
		);

        $entity->update($where_conditions,$set_values);
    }

    public function add($entity=null)
    {
        $baseEntityPrimaryKeyName = $entity->fields[0]->name;
        $baseEntityPrimaryKeyValue=$_REQUEST[$entity->fields[0]->name];
        $where_conditions=array($entity->fields[0]->name => $baseEntityPrimaryKeyValue);

        if(Settings::getOperativeMode() == 'debug'){
            echo '<br> add in ImageForm debug';
            echo '<br>$_REQUESTin imageform<br>';
            var_dump($_REQUEST);
        }

		$set_values = array(
			$this->attributeName => 
				isset( $_REQUEST[$this->formHash."_".$this->attributeName] ) ?
					$_REQUEST[$this->formHash."_".$this->attributeName] : 
					0
		);

        $entity->update($where_conditions,$set_values);
    }

}
<?php defined("BASEPATH") or exit("No direct script access allowed");

define('ENTITY_CHANGE_ENTITY', 'entity');
define('ENTITY_CHANGE_MAIN', 'main');
define('ENTITY_CHANGE_MIXIN', 'mixin');

class EntityChange {
	public function __construct($type, $obj, $property, $value) {
		$this->type = $type;
		$this->obj = $obj;
		$this->property = $property;
		if(isset($obj->property))
			$this->oldValue = $obj->$property;
		$this->value = $value;
	}
}

class Mixin {
	public function getID() {
		if(isset($this->entity) && $this->entity->getMainObject()) {
			$obj = $this->entity->getMainObject();
			return $obj->id;
		}
		return -1;
	}

	public function _propertyNames() {
		return array();
	}
	public function model($name, $alias = null) {
		if(is_array($name)) {
			$ret = array();
			foreach($name as $n) {
				$ret []= $this->model($n);
			}
			return $ret;
		}

		$this->CI->load->model($name);
		if($alias) {
			$this->$alias = $this->CI->$name;
		}
		else {
			$this->$name = $this->CI->$name;
		}

		return $this->CI->$name;
	}

	public function init() {
		$this->CI = get_instance();
	}

	public function apply() { // Apply the changes to the database
	}
	
	public function __get($key) {
		if(isset($this->$entity) && isset($this->entity->key)) {
			return $this->entity->$key;
		}
		return $this->$key;
	}
}

/**
 * The entity class to host as super object.
 *
 */
class Entity {
	public function __construct($main_model = null) {
		$this->setPropertyMap();
		if($main_model)
			$this->setModel($main_model);
	}

	public function getChanges() {
		if(!isset($this->__e_changes)) {
			$this->__e_changes = array();
		}
		return $this->__e_changes;
	}

	public function clearChanges() {
		$this->__e_changes = array();
	}

	private function addChange($type, $obj, $property, $value) {
		$this->getChanges();
		var_dump($type);
		var_dump($property);
		var_dump($value);
		array_push($this->__e_changes, new EntityChange($type, $obj, $property, $value));
	}

	private function setPropertyMap($map = array()) {
		$this->__e_propertyMap = $map;
	}

	private function getPropertyMap() {
		if(isset($this->__e_propertyMap))
			return $this->__e_propertyMap;
		return null;
	}

	public function getModel() {
		if(isset($this->__e_main_model)) {
			return $this->__e_main_model;
		}
		return null;
	}

	public function getMainObject() {
		if(isset($this->__e_main_object))
			return $this->__e_main_object;
		return null;
	}

	public function setModel($main_model) {
		if(!(is_object($main_model) && is_subclass_of($main_model, 'Pinet_Model'))) {
			ci_error('Must be subclass of Pinet_Model', $main_model);
			return ;
		}

		$this->__e_main_model = $main_model;
	}

	public function load($id) {
		if(isset($this->__e_main_model)) {
			$obj = $this->__e_main_model->load($id);
			if($obj) {
				$this->__e_main_object = $obj;
			}
		}
	}

	public function __get($property) {
		// Test for entity itself first
		if(isset($this->$property)) {
			return $this->$property;
		}
		// The main object is the second place to find the property
		$obj = $this->getMainObject();
		if($obj) {
			if(isset($obj->$property)) {
				return $obj->$property;
			}
		}

		// Then let's try model
		$model = $this->getModel();
		if(isset($model->$property)) {
			return $model->$property;
		}

		// Let's try the mixins
		$propertyMap = $this->getPropertyMap();
		if(isset($propertyMap[$property])) {
			$mixin = $propertyMap[$property];
			return $mixin->$property;
		}
		ci_error('Can\'t find any property named %s in entity', $this, $property);
		return null;
	}

	public function __set($property, $value) {
		if(strpos($property, '__e_') === 0) { // If start with the prefix, it must be the property of Entity itself
			$this->$property = $value;
			$this->addChange(ENTITY_CHANGE_ENTITY, $this, $property, $value);
			return;
		}

		// Let's try mixins
		$propertyMap = $this->getPropertyMap();

		if(isset($propertyMap[$property])) {
			$mixin = $propertyMap[$property];
			$mixin->$property = $value;
			$this->addChange(ENTITY_CHANGE_MIXIN, $mixin, $property, $value);
			return;
		}

		// The main object is the final destination
		$obj = $this->getMainObject();
		$obj->$property = $value;
		$this->addChange(ENTITY_CHANGE_MAIN, $obj, $property, $value);
	}

	public function addMixin($mixin) {
		if(!(is_object($mixin) && is_subclass_of($mixin, 'Mixin'))) {
			ci_error('Must be subclass of Mixin', $mixin);
			return ;
		}
		foreach($mixin->_propertyNames() as $name) {
			$this->__e_propertyMap[$name] = $mixin;
			$mixin->entity = $this;
		}
	}

	public function apply() {
		$main_changed = false;
		foreach($this->getChanges() as $change) {
			switch($change->type) {
			case ENTITY_CHANGE_ENTITY: // The property of the entity changed, won't affect anything
				break;
			case ENTITY_CHANGE_MAIN: // The main has been changed
				$main_changed = true;
				break;
			case ENTITY_CHANGE_MIXIN: // The mixin has been changed
				$change->obj->apply($change->property, $change->value); // When the mixin has been changed, tell mixin to apply the changes
				break;
			}
		}
		if($main_changed && $this->getModel()) {
			$obj = $this->getMainObject();
			$this->getModel()->update($obj->id, (array) $obj);
		}
		$this->clearChanges();
	}
}

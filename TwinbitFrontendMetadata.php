<?php

/**
 * @file
 * Provides an abstract metadata wrapper allowing easy usage of the entity metadata.
 */

class TwinbitFrontendMetadata {
	private $_wrapper;
	private $_type;
	private $_entity;


	/**
   * Construct a new wrapper object.
   *
   * @param $type
   *   The type of the passed data.
   * @param $entity
   * 	 Optional. Drupal entity object
   *
   * @param $wrapper
   *   Optional. EntityDrupalWrapper instance.
   */
	public function __construct($type, $entity = null, $wrapper = null) {
		$this->setType($type);

		if (!$wrapper) {
			$this->setWrapper(entity_metadata_wrapper($type, $entity));
		}
		else {
			$this->setWrapper($wrapper);
		}

		$wrapper = $this->getWrapper();
		if ($entity) {
			if (is_numeric($entity)) {
				$entity = entity_load($type, array($entity));
				$entity = array_pop($entity);
			}
		  $this->setEntity($entity);
		}
		else {
			$entity = entity_load($type, array($wrapper->getIdentifier()));
      if ($entity) {
				$entity = array_pop($entity);
				$entity->type = $type;
			  $this->setEntity($entity);
			}
		}
	}

	/**
   * Gets the info about the given property
   *
   * @param $field_name
   *   Optional. Field name of the entity.
   *
   * @return
   *   An array of info about the property.
   */
	public function getProperties($field_name = '') {
		$wrapper = $this->getWrapper();
		if ($field_name) {
			$info = $wrapper->{$field_name}->getPropertyInfo();
		}
		$info = $wrapper->getPropertyInfo();
		return $info;
	}

	/**
	 * Example:
	 *
	 *  // Get all the titles of node referenced
	 *  $frontend = new TwinbitFrontend('node', $entity);
   *  $files = $frontend->get_wrapper_field('field_product_reference_file');
   *  foreach ($files as $file) {
   *    $titles[] = $file->get_field_value('title'));
   *  }
   *
   * 	// Access to nodes referenced and get all taxonomies of type "field_file_type"
   * 	$terms = array();
   *  $referenced_entity_files = $object->metadata_get_referenced_wrappers('field_product_reference_file', TRUE);
   *  // Recupero l'identificativo del termine del logo
   *  foreach ($referenced_entity_files as $file) {
   *    $terms[] = $file->metadata_get_field_value('field_file_type'); // load taxonomy object attached to
   *    $file_ref = $file->getEntity(); // proxy method to get the raw entity (in this case node(@type=file) referenced)
   *  }
   *
	 * @param $field_name
   *   Field name to load.
   *
   * @param $full
   *   False: return just the existing entity_metadata_wrapper object (this is useful if you need just plain values, examples "$node->title" or $term->tid)
   *   True: return a full TwinbitFrontendMetadata object
   *
	 * @return Collection of TwinbitFrontend instances
	 */
	public function get_referenced_wrappers($field_name, $full = false) {
		$wrapper = $this->getWrapper();
		if (!$wrapper->{$field_name}->value()) {
			return array();
		}
		$instances = array();
		foreach ($wrapper->{$field_name} as $wrapper) {
			$type = $wrapper->type();
			$id = $wrapper->getIdentifier();
			if ($full) {
				$instances[] = new TwinbitFrontendMetadata($type, $wrapper->getIdentifier());
			}
		  else {
		  	$instances[] = new TwinbitFrontendMetadata($type, null, $wrapper);
		  }
		}
		return $instances;
	}

	/**
	 * This a wrapper around the EntityDrupalWrapper->value() method
	 *
	 * @param $field_name
	 *   Entity field name
	 * @param $attr
	 * 	Collection attribute, return element attribute if exists
	 * @param $options
   *   An array of options. Known keys:
   *   - identifier: If set to TRUE for a list of entities, it won't be returned
   *     as list of fully loaded entity objects, but as a list of entity ids.
   *     Note that this list may contain ids of stale entity references.
	 */
	public function get_field_value_single($field_name, $attr = false, $options = array()) {
		$res = $this->get_field_value($field_name, 0, $attr, $options);
		return $res;
	}


	/**
	 * This a wrapper around the EntityDrupalWrapper->value() method
	 *
	 * @param $field_name
	 *   Entity field name
	 * @param $index
	 * 	Collection index
	 * @param $attr
	 * 	Collection attribute, this can be used just when $index is active
	 * @param $options
   *   An array of options. Known keys:
   *   - identifier: If set to TRUE for a list of entities, it won't be returned
   *     as list of fully loaded entity objects, but as a list of entity ids.
   *     Note that this list may contain ids of stale entity references.
	 */
	public function get_field_value($field_name, $index = null, $attr = null, $options = array()) {
	  $wrapper = $this->getWrapper();
	  $properties = $this->getProperties();
	  $entity = $this->getEntity();
	  $collection = array();

	  // some "bad" fields could not to be accesible from metadata_wrapper try to lookup within the entity
	  if (!array_key_exists($field_name, $properties)) {
	  	if (isset($entity->{$field_name})) {
	  		$field = $entity->{$field_name};
	  		if (isset($field[LANGUAGE_NONE])) {
	  			$values = $field[LANGUAGE_NONE];
	  			// we can't make any more assumption here, just return raw values.
	  			return $values;
	  		}
	  	}
	  }
	  else {
		  // check if the count method exists (cardinality > 1)
		  if (method_exists($wrapper->{$field_name}, 'count')) {
		  	if ($index) {
					$object = $wrapper->{$field_name}->get($index)->value($options);
					// put the single value in array
					if ($object) {
						$collection[] = $object;
					}
		  	}
		  	else {
		  		// this is already a collection, overwrite the array
		  	  if ($value = $wrapper->{$field_name}->value($options)) {
		  	  	$collection = $value;
		  	  }
		  	}
		  }
		  else {
		  	$object = $wrapper->{$field_name}->value($options);
		  	if ($object) {
		  		$collection[] = $wrapper->{$field_name}->value($options);
		  	}
		  }

		  // check index and attributes
			if (is_numeric($index) && (count($collection)))	{
				if (isset($collection[$index])) {
					$collection = $collection[$index];
					if ($attr) {
						if (isset($collection[$attr])) {
							$collection = $collection[$attr];
						}
					}
				}
			}
	  }
	  return $collection;
	}

	/**
	 * Get a field property
	 *
   * Only for field that are not a reference to another entity
   * (i.e. not for field collections, files, images, taxonomy terms)
   *
	 * Example:
	 * $node_metadata = new TwinbitFrontendMetadata('node', $node);
	 * $link_title = $node_metadata->get_field_property('field_link', 'title');
	 * $link_url = $node_metadata->get_field_property('field_link', 'url');
	 *
	 * @param $fieldname
	 * 	Entity field name
	 * @param $property
	 *  Field property name
	 *
	 * @return Mixed Property value
	 */
	public function get_field_property($field_name, $property) {
	  $wrapper = $this->getWrapper();
	  $propertyInfo = $this->getProperties($field_name);
	  if (!$wrapper->{$field_name}->value()) {
	  	return false;
	  }
	  if (!array_key_exists($property, $propertyInfo)) {
	  	return false;
	  }
	  $output = $wrapper->{$field_name}->{$property}->value();
	  return $output;
	}


	private function setType($type) {
		$this->_type = $type;
	}

	public function getType() {
		return $this->_type;
	}


	public function getEntity() {
		return $this->_entity;
	}

	private function setEntity($entity) {
		$this->_entity = $entity;
	}

	public function getWrapper() {
		return $this->_wrapper;
	}

	private function setWrapper($wrapper) {
		$this->_wrapper = $wrapper;
	}

	/**
	 *  OLD PROXY METHOD (retrocompatibility)
	 * @return Collection of TwinbitFrontend instances
	 */
	public function metadata_get_field_value($field_name, $index = false, $attr = false, $options = array()) {
	  return $this->get_field_value($field_name, $options);
	}

		/**
	 *  OLD PROXY METHOD (retrocompatibility)
	 * @return Collection of TwinbitFrontend instances
	 */
	public function metadata_get_referenced_wrappers($field_name, $full = false) {
		return $this->get_referenced_wrappers($field_name, $full);
	}


	/**
	 * OLD PROXY METHOD (retrocompatibility)
	 */
	public function metadata_get_field_property($field_name, $property) {
	  return $this->get_field_property();
	}
}

<?php

/**
 * @file
 * Provides an simple way to render a set of files from an entity.
 *
 *
 * Example: Render a set of file uploads from an Node as images using a style and some attributes
 *
 *  $image_renderers = new TwinbitFrontendFile('node', $file_ref, 'field_file_upload');
 *  foreach ($image_renderers->renderers as $image_render) {
 *    $image_render->setStyle('product_photo');
 *    $image_render->setAttributes(array('alt' => $alt, 'title' => $alt, 'attributes' => array('class' => 'test')));
 *    $html .= $image_render->render();
 *    // you can also pass custom theme functions, for example calling "theme_custom_image_formatter"
 * 	  $html .= $image_render->render('image', array('#theme' => 'custom_image_formatter'));
 *  }
 *
 *  Example: Render a set of file uploads from an Node as links using a style and some attributes
 *
 *  $file_renderers = new TwinbitFrontendFile('node', $file_ref, 'field_file_upload');
 *  foreach ($file_renderers->renderers as $file_render) {
 *    $file_render->setStyle('product_photo');
 *    $file_render->setAttributes(array('alt' => $alt, 'title' => $alt, 'attributes' => array('class' => 'test')));
 *    // please note that this function will use "theme_file_link" theme function (https://api.drupal.org/api/drupal/modules!file!file.module/function/theme_file_link/7)
 *    $html .= $file_render->render('link');
 * 	  // you can also pass custom theme functions, for example calling "theme_custom_image_formatter"
 * 	  $html .= $file_render->render('link', array('#theme' => 'custom_link_formatter'));
 *
 *  }
 *
 */
class TwinbitFrontendFile {
	private $_type;
	private $_entity;
	private $_field;
	private $_image;
	private $_field_name;
	public $renderers = array();


	/**
	 * @param @type
	 * 	Entity type (node, user, taxonomy_term)
	 * @param $entity
	 * 	Entity object
	 * @param $field_name
	 *  Entity field name
	 */
	public function __construct($type, $entity, $field_name) {
	  $this->setType($type);
    $field_metadata_wrapper = new TwinbitFrontendMetadata($type, $entity);
    $this->setField($field_metadata_wrapper);
    $this->setFieldName($field_name);
    $this->setEntity($entity);

    // generate image array
    $renderers = $this->generateRenderers();
	}


	private function generateRenderers() {
		$images = field_view_field($this->getType(), $this->getEntity(), $this->getFieldName());
		foreach ($images as $key => $item) {
			if (is_numeric($key)) {
			  if (isset($item['#file'])) {
			  	$file = $item['#file'];
			  }
			  if (isset($item['#item'])) {
			  	$file = $item['#item'];
			  }
			  // check if file exists
			  if ($file) {
			  	$this->renderers[] = new TwinbitFrontendFileRender($file);
			  }
			}
		}
	}

	public function count() {
		return count($this->renderers);
	}

	public function getRenderers() {
		return $this->renderers;
	}

	private function setFieldName($name) {
		$this->_field_name = $name;
	}

	private function getFieldName() {
		return $this->_field_name;
	}

	private function setType($type) {
		$this->_type = $type;
	}

	private function getType() {
		return $this->_type;
	}

	private function setEntity($entity) {
		$this->_entity = $entity;
	}

	private function getEntity() {
		return $this->_entity;
	}

	private function setField($field) {
		$this->_field = $field;
	}

	public function getField() {
		return $this->field;
	}
}
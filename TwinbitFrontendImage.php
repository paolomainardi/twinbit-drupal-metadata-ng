<?php

/**
 * @file
 * Proxy file for retrocompatibility, use TwinbitFrontendFile
 * 
 */
class TwinbitFrontendImage extends TwinbitFrontendFile {
  public function __construct($type, $entity, $field_name) {
  	parent::__construct($type, $entity, $field_name);
  }
}
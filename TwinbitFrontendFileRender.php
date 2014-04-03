<?php

/**
 * @file
 * Provides an abstract way to render a file object as image or link.
 *
 * It is almost used from withing the TwinbitFrontendFile class, but it can be used standalon, for example:
 *
 * $file = file_load($fid);
 * $render = new TwinbitFrontendFileRender($file);
 * $image_html = $image->render('image');
 * $link_html = $image->render('link');
 *
 * // custom theme functions
 * $image_html_custom = $image->render('image', array('#theme' => 'custom_image_formatter'));
 * $link_html_custom = $image->render('link', array('#theme' => 'custom_link_formatter'));
 */

class TwinbitFrontendFileRender {
    private $file;
    private $renderable = array();

    /**
     * @param $file
     *  Drupal file object
     */
    public function __construct($file) {
        $this->file = (array) $file;
    }

    /**
     * @param $style
     *  Image style name
     *
     * @return $this instance
     */
    public function setStyle($style) {
        $this->renderable['#image_style'] = $style;
        return $this;
    }

    /**
     * Example:
     * 
     * $entity is the entity
     * 
     * $image_renderers = new TwinbitFrontendImage('node', $entity, 'field_file_image');
     *  foreach ($image_renderers->renderers as $image_render) {
     *   $image_render->setStyle('product_photo')
     *                ->setAttributes(array('alt' => $alt, 'title' => $alt, 'width' => 100, 'height' => 100, 'attributes' => array('class' => 'test')));
     *   $html .= $image_render->render();
     * }
     *
     * @param $options
     *  Optional. Set file attributes on image file (actually is used just on theme_image_formatter())
     *
     * @return $this instance
     */
    public function setAttributes($options = array()) {
        $this->file = array_merge($this->file, $options);
        return $this;
    }

    /**
     * @return Drupal file object
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * @return file as image html rendered
     */
    public function renderAsImage($options) {
        $this->renderable['#theme'] = $options['#theme'];
        $this->renderable['#item'] = &$this->file;
        return render($this->renderable);
    }

    /**
     * @return file as html link rendered
     */
    public function renderAsLink($options) {
      $this->renderable['#theme'] = $options['#theme'];
      $this->renderable['#file'] = (object) $this->file;
      return render($this->renderable);
    }

    /**
     * Render file.
     * @type Link or Image renderer
     */
    public function render($type = 'image', $options = array()) {
      if ($type == 'image') {
        $default_options = array('#theme' => 'image_formatter');
        $options = array_merge($default_options, $options);
        $output = $this->renderAsImage($options);
      }
      if ($type == 'link') {
        $default_options = array('#theme' => 'file_link');
        $options = array_merge($default_options, $options);
        $output = $this->renderAsLink($options);
      }
      return $output;
    }
}
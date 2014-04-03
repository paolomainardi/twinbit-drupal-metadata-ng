# Twinbit entity frontend metadata classes

As written in the class documentation *Provides an abstract metadata wrapper allowing easy usage of the entity metadata*, but what does it means ?

Well, Drupal offers many ways to access entity fields data and attributes (ex: title attribute of a link field), one of them is based on MetadatWrapper object exposed by [EntityAPI module](https://drupal.org/project/entity), while it is very powerful, it is far from being easily used by frontend developers.

We create this wrapper in order to simplify, the fields handling.

## Examples


### Working with nodes

#### Access to field attributes

```
$wrapper = new TwinbitFrontendMetadata('node', $node);

// get the url attribute from link field
$url = $node_wrapper->get_field_value('field_link', 0, 'display_url'); 

// get the title attribute from link field
$link_title = $node_wrapper->get_field_value('field_link', 0, 'title');

// get the node title
$node_title = $node_wrapper->get_field_value('title');
```

#### Access to nested wrappers, for example file fields or taxonomy references

```
$files = $frontend->get_wrapper_field('field_files');
foreach ($files as $file) {
  $file_titles[] = $file->get_field_value('title'));
  $file = $file->getEntity(); // access the raw entity
}
```
#### Working with files

#### Render a set of file uploads from an Node as images using a style and some attributes

```  	
$image_renderers = new TwinbitFrontendFile('node', $file_ref, 'field_file_upload');
foreach ($image_renderers->renderers as $image_render) {
  $image_render->setStyle('product_photo');
  $image_render->setAttributes(array('alt' => $alt, 'title' => $alt, 'attributes' => array('class' => 'test')));
  $html .= $image_render->render();
  // you can also pass custom theme functions, for example calling "theme_custom_image_formatter"
	  $html .= $image_render->render('image', array('#theme' => 'custom_image_formatter')); 
}
```


#### Render a set of file uploads from an Node as links using a style and some attributes

```	
$file_renderers = new TwinbitFrontendFile('node', $file_ref, 'field_file_upload');
foreach ($file_renderers->renderers as $file_render) {
  $file_render->setStyle('product_photo');
  $file_render->setAttributes(array('alt' => $alt, 'title' => $alt, 'attributes' => array('class' => 'test')));
  // please note that this function will use "theme_file_link" theme function (https://api.drupal.org/api/drupal/modules!file!file.module/function/theme_file_link/7)
  $html .= $file_render->render('link'); 
  // you can also pass custom theme functions, for example calling "theme_custom_image_formatter"
  $html .= $file_render->render('link', array('#theme' => 'custom_link_formatter')); 	
}
```

## Contacts

If you need some help, feel free to use the issue tracker, we are more than happy to help you.
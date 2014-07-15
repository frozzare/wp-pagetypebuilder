<?php

/**
 * Page Type Builder Property Functions.
 *
 * @package PageTypeBuilder
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Check if it's ends with '_property'.
 *
 * @param string $str
 * @since 1.0
 *
 * @return bool
 */

function _ptb_is_property_type_key ($str = '') {
  $pattern = PTB_PROPERTY_TYPE_KEY;
  $pattern = str_replace('_', '\_', $pattern);
  $pattern = str_replace('-', '\-', $pattern);
  $pattern = '/' . $pattern . '$/';
  return preg_match($pattern, $str);
}

/**
 * Get the right key for a property type.
 *
 * @param string $str
 * @since 1.0
 *
 * @return string
 */

function _ptb_property_type_key ($str = '') {
  return $str . PTB_PROPERTY_TYPE_KEY;
}

/**
 * Get property key.
 *
 * @param string $str
 * @since 1.0.0
 *
 * @return string
 */

function _ptb_property_key ($str) {
  return _f(_ptbify($str));
}

/**
 * Returns only values in the array and removes `{x}_property` key and value.
 *
 * @param array $a
 * @since 1.0
 *
 * @return array
 */

function _ptb_get_only_property_values ($a = array()) {
  foreach ($a as $key => $value) {
    if (_ptb_is_property_type_key($key)) {
      unset($a[$key]);
    }
  }
  return $a;
}

/**
 * Get property type by the given type.
 *
 * @param string $type
 * @since 1.0.0
 *
 * @return object|null
 */

function _ptb_get_property_type ($type) {
  if (is_object($type) && isset($type->type) && is_string($type->type)) {
    $type = $type->type;
  }
  if (is_null($type) || empty($type)) {
    return null;
  }

  return PTB_Property::factory($type);
}

/**
 * Get property options.
 *
 * @param array $options
 * @since 1.0.0
 *
 * @return object|null
 */

function _ptb_get_property_options ($options) {
  $defaults = array(
    'title'      => _ptb_random_title(),
    'no_title'   => false,
    'disable'    => false,
    'slug'       => '',
    'custom'     => new stdClass,
    'table'      => true,
    'sort_order' => 0,
    'value'      => '',
    'type'       => '',
    'colspan'    => '',
    'lang'       => PTB_Language::$default,
    'old_slug'   => ''
  );

  $options = array_merge($defaults, $options);
  $options = (object)$options;

  if ($options->no_title) {
    $options->title = '';
    $options->colspan = 2;
  }

  if (empty($options->slug)) {
    // Generate a random title if no name is set and title is empty.
    // This make wp_editor and other stuff that go by name/id attributes to work.
    if (empty($options->title)) {
      $title = _ptb_random_title();
    } else {
      $title = $options->title;
    }

    $options->slug = _ptb_slugify($title);
  }

  if (is_array($options->lang)) {
    // If we have a array and Polylang is supported we can get the right lang.
    if (_ptb_polylang()) {
      $lang = _ptb_get_lang_code();
      if (in_array($lang, $options->lang)) {
        $options->lang = $lang;
      }
    } else {
      // Can't handle multilanguage without Polylang.
      $lang = array_shift($options->lang);
    }
  }

  // Add language code to the slug name.
  $options->slug = _ptb_get_lang_field_slug($options->slug, $options->lang);

  // Generate a vaild Page Type Builder meta name.
  $options->slug = _ptb_name($options->slug);

  if (!empty($options->old_slug)) {
    $options->old_slug = _ptb_name($options->old_slug);
  }

  // Generate colspan attribute
  if (!empty($options->colspan)) {
    $options->colspan = _ptb_attribute('colspan', $options->colspan);
  }

  // Get meta value for the field
  $options->value = ptb_field($options->slug, null, null, $options->lang, $options->old_slug);

  return $options;
}

/**
 * Render a property the right way.
 *
 * @param object $property
 * @since 1.0.0
 */

function _ptb_render_property ($property) {
  if (empty($property->type)) {
    return;
  }

  $property_type = _ptb_get_property_type($property->type);

  if (is_null($property_type)) {
    return;
  }

  $property_type->set_options($property);


  // Only render if it's the right language if the definition exist.
  if (defined('PTB_LANG_CODE') && !empty($property->lang)) {
    $render = _ptb_lang_exist(PTB_LANG_CODE) && $property->lang === PTB_LANG_CODE;
  } else {
    $render = true;
  }

  // Render the property.
  if ($render) {
    $property_type->assets();
    $property_type->render();
    $property_type->hidden();
  }
}

/**
 * Render properties the right way.
 *
 * @param array $properties
 * @since 1.0.0
 */

function _ptb_render_properties ($properties) {
  // Don't proceed without any properties
  if (!is_array($properties) || empty($properties)) {
    return;
  }

  // If it's a tab the tabs class will
  // handle the rendering of the properties.
  if (isset($properties[0]->tab) && $properties[0]->tab) {
    new PTB_Admin_Meta_Box_Tabs($properties);
  } else {
    if ($properties[0]->table) {
      echo '<table class="ptb-table">';
        echo '<tbody>';
    }

    foreach ($properties as $property) {
      _ptb_render_property($property);
    }

    if ($properties[0]->table) {
        echo '</tbody>';
      echo '</table>';
    }
  }
}
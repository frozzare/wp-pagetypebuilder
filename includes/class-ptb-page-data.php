<?php

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Page Type Builder Page Data.
 *
 * @package PageTypeBuilder
 * @version 1.0.0
 */

abstract class PTB_Page_Data {

  /**
   * Contains all register properties on this page.
   * Will only contain root level properties.
   *
   * @var array
   * @since 1.0.0
   * @access private
   */

  private $properties = array();

  /**
   * Remove post type support array.
   *
   * @var array
   * @since 1.0.0
   * @access private
   */

  private $remove_post_type_support = array();

  /**
   * Constructor.
   *
   * @since 1.0.0
   */

  public function __construct () {
    // Setup globals.
    $this->setup_globals();
  }

  /**
   * Setup globals.
   *
   * @since 1.0.0
   * @access private
   */

  private function setup_globals () {
    // Setup page type meta.
    $this->setup_page_type_meta();
  }

  /**
   * Setup page type meta.
   *
   * @since 1.0.0
   * @access private
   */

  private function setup_page_type_meta () {
    if (method_exists($this, 'page_type')) {
      $this->page_type_meta = static::page_type();
      $this->page_type_meta = (object)$this->page_type_meta;
    }
  }

  /**
   * Add new meta box with properties.
   *
   * @param string $title.
   * @param array $options
   * @param array $items
   * @since 1.0.0
   */

  protected function box ($title = '', $options = array(), $properties = array()) {
    // Options is optional value.
    if (empty($properties)) {
      $properties = $options;
      $options = array();
    }

    // Move title into options.
    if (!isset($options['title'])) {
      $options['title'] = $title;
    }

    $post_type = _ptb_get_wp_post_type();

    if (!$this->has_post_type($post_type)) {
      return;
    }

    // Add post type to the options array.
    $options['post_type'] = $post_type;

    // Create a new box.
    $this->box = new PTB_Admin_Meta_Box($options, $properties);
  }

  /**
   * Add new property to the page.
   *
   * @param array $options
   * @since 1.0.0
   *
   * @return array
   */

  protected function property ($options = array()) {
    $options = _ptb_get_property_options($options);

    if (is_array($options)) {
      $this->properties = array_merge($this->properties, $options);
    } else {
      $this->properties[] = $options;
    }

    return $options;
  }

  /**
   * Remove post type support. Runs once, on page load.
   *
   * @param array $post_type_support
   * @since 1.0.0
   */

  protected function remove ($remove_post_type_support = array()) {
    if (is_string($remove_post_type_support)) {
      $remove_post_type_support = array($remove_post_type_support);
    }

    $this->remove_post_type_support = array_merge($this->remove_post_type_support, $remove_post_type_support);

    add_action('init', array($this, 'remove_post_type_support'));
  }

  /**
   * Remove post type support action.
   *
   * @since 1.0.0
   */

  public function remove_post_type_support () {
    // Get post type.
    $post_type = _ptb_get_wp_post_type();

    // Can't proceed without a post type.
    if (empty($post_type) || is_null($post_type)) {
      return;
    }

    // Loop through all post type support to remove.
    foreach ($this->remove_post_type_support as $post_type_support) {
      remove_post_type_support($post_type, $post_type_support);
    }
  }

  /**
   * Add a new tab.
   *
   * @param string $title
   * @param array $options
   * @param array $properties
   * @since 1.0
   *
   * @return object
   */

  protected function tab ($title, $options = array(), $properties = array()) {
    if (empty($properties)) {
      $properties = $options;
      $options = array();
    }

    if (!is_array($options)) {
      $options = array();
    }

    return (object)array(
      'title'      => $title,
      'tab'        => true,
      'options'    => (object)$options,
      'properties' => $properties
    );
  }

  /**
   * Get post type for the page type.
   *
   * @since 1.0
   *
   * @return array
   */

  protected function get_post_types () {
    if (is_null($this->page_type_meta)) {
      $this->setup_page_type_meta();
    }

    if (isset($this->page_type_meta->post_types)) {
      return is_array($this->page_type_meta->post_types) ?
        $this->page_type_meta->post_types :
        array($this->page_type_meta->post_type);
    }

    return array('page');
  }

  /**
   * Check if the given post is allowed to use the page type.
   *
   * @param string $post_type
   * @since 1.0.0
   *
   * @return bool
   */

  protected function has_post_type ($post_type) {
    return in_array($post_type, $this->get_post_types());
  }

  /**
   * Get all root properties.
   *
   * @since 1.0.0
   *
   * @return array
   */

  public function get_properties () {
    return $this->properties;
  }
}
<?php

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Page Type Builder - Property List
 */

class PropertyList extends PTB_Property {

  /**
   * Get the html for output.
   *
   * @since 1.0
   *
   * @return string
   */

  public function html () {
    $this->counter = 0;
    $this->name = $this->get_options()->name;
    $properties = $this->get_options()->properties;
    $values = $this->get_options()->value;

    if (!is_array($values)) {
      $values = array(array());
    }

    $names = array();

    $html = <<< EOF
    <div class="ptb-property-list">
      <div class="pr-inner">
        <div class="pr-actions">
          {$this->label()}
          <a class="pr-add-new-item" href="#">Add new</a>
        </div>
        <ul class="pr-template hidden">
          <li>
            <a class="pr-remove-item" href="#">Remove</a>
EOF;
    foreach ($properties as $property) {
      $name =  _ptbify(strtolower($property->type));

      if (isset($names[$name])) {
        $name .= strval($names[$name]);
        $names[$name]++;
      } else {
        $name .= '1';
        $names[$name] = 1;
      }

      $property->name = $name;
      $property->value = '';
      $property = $this->property($property);
      $property = $this->template($property);
      $html .= $property->callback_args->html;
    }

    $html .= <<< EOF
          </li>
        </ul>
        <ul class="pr-items">
EOF;

      foreach ($values as $value) {
        $html .= <<< EOF
          <li>
            <a class="pr-remove-item" href="#">Remove</a>
EOF;

          foreach ($properties as $property) {
            $name =  _ptbify(strtolower($property->type));

            if (isset($names[$name])) {
              $name .= strval($names[$name]);
              $names[$name]++;
            } else {
              $name .= '1';
              $names[$name] = 1;
            }

            if (isset($value[$property->name])) {
              $property->value = $value[$property->name];
            }

            $property = $this->property($property);
            $html .= $property->callback_args->html;
          }
          $this->counter++;

        $html .= '</li>';
      }

      $html .= <<< EOF
        </ul>
      </div>
    </div>
EOF;

    return $html;
  }

  /**
   * Regenerate the property with modified values.
   *
   * @param object $property
   * @since 1.0
   *
   * @return object
   */

  public function property ($property) {
    // Remove old html.
    unset($property->callback_args);

    // This is a bit ugly to use PTB_Base again.
    // But all we need to create the property again is in there.
    // TODO: Make a new class that we can reuse here and in PTB_Base.
    $base = new PTB_Base(false);

    // Don't make this a table row.
    $property->table = false;

    // Don't show the random title if we don't have a title.
    if (!isset($property->title) || empty($property->title) || strpos($property->title, '_PTB') === 0) {
      $property->no_title = true;
    }

    // Generate new property data.
    $property = $base->property($property);

    // Property name.
    $property_name = $this->name . '[' . $this->counter . ']' . '[' . str_replace('ptb_ptb', 'ptb', $property->name) . '_property]';
    $property->callback_args->html = str_replace('name="' . $property->name . '_property' . '"', 'name="' . $property_name . '"', $property->callback_args->html);

    // Input name.
    $input_name = $this->name . '[' . $this->counter . ']' . '[' . str_replace('ptb_ptb', 'ptb', $property->name) . ']';
    $property->callback_args->html = str_replace('name="' . $property->name . '"', 'name="' . $input_name . '"', $property->callback_args->html);

    return $property;
  }

  /**
   * Change all name attributes to data-name.
   *
   * @param object $property
   * @since 1.0
   *
   * @return object
   */

  public function template ($property) {
    $property->callback_args->html = str_replace('name=', 'data-name=', $property->callback_args->html);

    return $property;
  }

  /**
   * Output custom JavaScript for the property.
   *
   * @since 1.0
   */

  public function js () {
    ?>
    <script type="text/javascript">
      (function ($) {

        /**
         * Update array number in html name.
         *
         * @param {String} html
         * @param {Int} num
         *
         * @return {String}
         */

        function update_html_array_num (html, num) {
          var pattern = /name\=\"\ptb_\w+(\[\d+\]).+?\"/g
            , generated = '[' + num + ']';
          return html.replace(pattern, function (match, value) {
            return match.replace(value, generated);
          });
        }

        // Add new item and update the array index in html name.

        $('.ptb-property-list').on('click', '.pr-add-new-item', function (e) {
          e.preventDefault();

          var $template = $('ul.pr-template > li').clone()
            , counter = $('ul.pr-items').children().length
            , html = $template.html()
            , pattern = /data\-name\=/g;

          html = html.replace(pattern, 'name=');
          html = update_html_array_num(html, counter);

          $template.html(html).appendTo('ul.pr-items');
        });

        // Remove item

        $('.ptb-property-list').on('click', '.pr-remove-item', function (e) {
          e.preventDefault();

          $(this).closest('li').remove();
        });

      })(window.jQuery);
    </script>
    <?php
  }

  /**
   * Output custom CSS for the property.
   *
   * @since 1.0
   */

  public function css () {
    ?>
    <style type="text/css">
      .hidden {
        display: none;
      }

      .ptb-property-list {
         background: #fafafa;
         border: 1px #eaeaea solid;
         padding: 5px;
      }

      .ptb-property-list .pr-inner {
        padding: 5px;
        background: #fff;
        border: 1px #eaeaea solid;
      }

      .ptb-property-list .pr-inner .pr-actions {
        height: 20px;
        /* background: #fafafa; */
        border-bottom: 1px #eaeaea solid;
        padding: 5px;
      }

      .ptb-property-list .pr-inner .pr-actions label {
        float: left;
        margin-top: -1px;
      }


      .ptb-property-list .pr-inner .pr-right {
        margin-right: 15px;
      }

      .ptb-property-list ul {}
      .ptb-property-list ul li {
        padding: 5px;
        border-bottom: 1px #eaeaea solid;
      }

      .ptb-property-list ul li:nth-child(odd) {
        background: #fff;
      }

      .ptb-property-list ul li:nth-child(even) {
        /*background: #fafafa; */
      }

      .ptb-property-list .pr-inner .pr-actions a.pr-add-new-item {
        width: 16px;
        height: 16px;
        position: absolute;
        /* text-indent: -9999px; */
        right: 35px;
        text-decoration: none;
      }

      .ptb-property-list .pr-inner a.pr-remove-item {
        width: 16px;
        height: 16px;
        position: absolute;
        /* text-indent: -9999px; */
        right: 35px;
        text-decoration: none;
      }
    </style>
    <?php
  }

  /**
   * Render the final html that is displayed in the table.
   *
   * @since 1.0
   *
   * @return string
   */

  public function render () {
    if ($this->get_options()->table) {
      $html = PTB_Html::td($this->html(), array('colspan' => 2));
      $html = PTB_Html::tr($html);
      $html .= $this->helptext(false);
      return $html;
    }
    return '&nbsp;' . $this->html() . $this->helptext(false);
  }

  /**
   * Convert the value of the property before we output it to the application.
   *
   * @param mixed $value
   * @since 1.0
   *
   * @return array
   */

  public function convert ($value) {
    return array_values($value);
  }

}
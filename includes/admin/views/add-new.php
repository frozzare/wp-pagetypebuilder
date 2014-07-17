<?php
  // Get the right name for the normal page.
  $post_type = _ptb_get_wp_post_type();
  $post_type_obj = get_post_type_object($post_type);
  $name = $post_type_obj->labels->singular_name;

  $settings = _ptb_get_settings();

  // Check if we should show standard page or not.
  $show_standard_page = true;
  if (isset($settings[$post_type]) && isset($settings[$post_type]['show_standard_page'])) {
    $show_standard_page = $settings[$post_type]['show_standard_page'];
  }
?>

<div id="wrap">
  <h2><?php echo __('Add New', 'ptb') . ' ' . $name; ?></h2>
  <p><?php echo __('Select the type of page to create from the list or search', 'ptb'); ?>: <input type="text" name="add-new-page-search" class="ptb-search" /></p>
  <?php $page_types = _ptb_get_all_page_types(); ?>
  <ul class="ptb-box-list">
    <?php foreach ($page_types as $key => $value): ?>
      <li>
        <a href="<?php echo _ptb_get_page_new_url ($value->file_name, $post_type); ?>"><?php echo $value->name; ?></a>
        <p><?php echo $value->description; ?></p>
      </li>
    <?php endforeach; ?>

    <?php if ($show_standard_page): ?>
    <li>
      <a href="post-new.php?post_type=page"><?php _e('Standard Page', 'ptb'); ?></a>
      <p><?php _e('Just the normal WordPress page', 'ptb'); ?></p>
    </li>
    <?php endif; ?>
  </ul>
</div>


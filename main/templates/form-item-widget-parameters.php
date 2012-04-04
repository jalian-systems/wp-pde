<?php ?>
<p class="description description-thin">
  <label for="edit-form-item-title-<?php echo $item_id; ?>">
    <?php _e ( 'Name' ) ?><br />
    <input type="text" id="edit-form-item-title-<?php echo $item_id; ?>" class="widefat edit-form-item-title" name="db-<?php echo $item_id; ?>[title]" value="<?php echo esc_attr( isset( $item->title ) ? $item->title : '' ); ?>" />
  </label>
</p>
<p class="description description-thin">
  <label for="edit-form-item-theme-<?php echo $item_id; ?>">
    <?php _e('Theme'); ?><br />
    <select id="edit-form-item-theme-<?php echo $item_id; ?>" class="widefat code edit-form-item-theme" name="db-<?php echo $item_id; ?>[theme]">
<?php
      $filter = 'pde_plugin_item_theme_for_widget' ;
      $options = apply_filters($filter, array());
      foreach( $options as $option ) {
      if(isset($item->theme)) {
        $a = unserialize( $item->theme );
        $theme_value = $a['value'];
      }
?>
      <option value="<?php echo esc_attr(serialize($option)); ?>" <?php selected( !empty( $theme_value ) ? $theme_value : '', $option['value'] ); ?>><?php _e($option['display']); ?></option>
<?php
      }
?>
    </select>
  </label>
</p>
<p class="field-display-do-wrap description-wide">
  <label for="edit-form-item-do-wrap-<?php echo $item_id; ?>">
    <input type="checkbox" id="edit-form-item-do-wrap-<?php echo $item_id; ?>" value="do_wrap" name="db-<?php echo $item_id; ?>[do_wrap]"<?php checked( isset( $item->do_wrap ) ? $item->do_wrap : '', 'do_wrap' ); ?> />
    <?php _e( 'Do not wrap the widget with before_widget and after_widget markup' ); ?>
  </label>
</p>
<p class="description description-wide">
  <label for="edit-form-item-description-<?php echo $item_id; ?>">
    <?php _e( 'Description' ); ?><br />
    <textarea id="edit-form-item-description-<?php echo $item_id; ?>" class="widefat edit-form-item-description" rows="3" cols="20" name="db-<?php echo $item_id; ?>[description]"><?php echo esc_html( isset( $item->description ) ? $item->description : '' ); // textarea_escaped ?></textarea>
  </label>
</p>

<p class="field-value description description-wide">
  <label for="edit-form-item-description-html-escape-<?php echo $item->db_id; ?>">
    <input type="checkbox" id="edit-form-item-description-html-escape-<?php echo $item->db_id; ?>" value="description_html_escape" name="db-<?php echo $item->db_id; ?>[display_when]"<?php checked( isset( $item->description_html_escape ) ? $item->description_html_escape : '', 'description_html_escape' ); ?> />
    <?php _e( 'Escape HTML in description' ); ?>
  </label>
</p>

<p class="description description-thin">
  <label for="edit-form-item-width-<?php echo $item_id; ?>">
    <?php _e( 'Width' ); ?><br />
    <input type="text" id="edit-form-item-width-<?php echo $item_id; ?>" class="widefat code edit-form-item-width" name="db-<?php echo $item_id; ?>[width]" value="<?php echo esc_attr( isset( $item->width ) ? $item->width : '' ); ?>" />
    <span class="description-small"><?php echo __('Provide if width is more than 250px'); ?></span>
  </label>
</p>
<p class="description description-thin">
  <label for="edit-form-height-<?php echo $item_id; ?>">
    <?php _e( 'Height' ); ?><br />
    <input type="text" id="edit-form-height-<?php echo $item_id; ?>" class="widefat code edit-form-height" name="db-<?php echo $item_id; ?>[height]" value="<?php echo esc_attr( isset( $item->height ) ? $item->height : '' ); ?>" />
    <span class="description-small"><?php echo _e('Not used currently'); ?></span>
  </label>
</p>


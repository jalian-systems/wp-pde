<?php ?>
<p class="description description-thin">
  <label for="edit-form-item-title-<?php echo $item_id; ?>">
    <?php _e( 'Label' ); ?><br />
    <input type="text" id="edit-form-item-title-<?php echo $item_id; ?>" class="widefat edit-form-item-title" name="db-<?php echo $item_id; ?>[title]" value="<?php echo esc_attr( isset( $item->title ) ? $item->title : '' ); ?>" />
  </label>
</p>
<p class="description description-thin">
  <label for="edit-form-item-default-value-<?php echo $item_id; ?>">
    <?php _e('Rows'); ?><br />
    <input type="text" id="edit-form-item-default-value-<?php echo $item_id; ?>" class="widefat edit-form-item-default-value" name="db-<?php echo $item_id; ?>[rows]" value="<?php echo esc_attr( isset( $item->default_value ) ? $item->default_value : '' ); ?>" />
  </label>
</p>
<p class="field-description description description-wide">
  <label for="edit-form-item-description-<?php echo $item_id; ?>">
    <?php _e( 'Description' ); ?><br />
    <textarea id="edit-form-item-description-<?php echo $item_id; ?>" class="widefat edit-form-item-description" rows="3" cols="20" name="db-<?php echo $item_id; ?>[description]"><?php echo esc_html( isset( $item->description ) ? $item->description : '' ); // textarea_escaped ?></textarea>
  </label>
</p>

<p class="field-value description description-wide">
  <label for="edit-form-item-description-html-escape-<?php echo $item->db_id; ?>">
    <input type="checkbox" id="edit-form-item-description-html-escape-<?php echo $item->db_id; ?>" value="description_html_escape" name="db-<?php echo $item->db_id; ?>[description_html_escape]"<?php checked( isset( $item->description_html_escape ) ? $item->description_html_escape : '', 'description_html_escape' ); ?> />
    <?php _e( 'Escape HTML in description' ); ?>
  </label>
</p>

<p class="field-php-variable description description">
  <label for="edit-form-item-php-variable-<?php echo $item_id; ?>">
    <?php _e( 'Script Variable' ); ?><br />
    <input type="text" id="edit-form-item-php-variable-<?php echo $item_id; ?>" class="widefat code edit-form-item-php-variable" name="db-<?php echo $item_id; ?>[php_variable]" value="<?php echo esc_attr( isset( $item->php_variable ) ? $item->php_variable : '' ); ?>" />
    <span class="description-small"><?php echo sprintf(__('Currently using: $%s'), $item->get_php_variable()); ?></span>
  </label>
</p>

<div class='field-stripping'><p class="field-strip-tags description-thin">
  <label for="edit-form-item-strip-tags-<?php echo $item_id; ?>">
    <input type="checkbox" id="edit-form-item-strip-tags-<?php echo $item_id; ?>" value="strip_tags" name="db-<?php echo $item_id; ?>[strip_tags]"<?php checked( isset( $item->strip_tags ) ? $item->strip_tags : '', 'strip_tags' ); ?> />
    <?php _e( 'Strip tags from the input' ); ?>
  </label>
</p>

<p class="field-strip-slashes description-thin">
  <label for="edit-form-item-strip-slashes-<?php echo $item_id; ?>">
    <input type="checkbox" id="edit-form-item-strip-slashes-<?php echo $item_id; ?>" value="strip_slashes" name="db-<?php echo $item_id; ?>[strip_slashes]"<?php checked( isset( $item->strip_slashes ) ? $item->strip_slashes : '', 'strip_slashes' ); ?> />
    <?php _e( 'Strip slashes from the input' ); ?>
  </label>
</p></div>

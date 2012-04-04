<?php ?>
<p class="description description-thin">
  <label for="edit-form-item-title-<?php echo $item_id; ?>">
    <?php _e( 'Label' ); ?><br />
    <input type="text" id="edit-form-item-title-<?php echo $item_id; ?>" class="widefat edit-form-item-title" name="db-<?php echo $item_id; ?>[title]" value="<?php echo esc_attr( isset( $item->title ) ? $item->title : '' ); ?>" />
  </label>
</p>
<p class="description description-thin">
  <label for="edit-form-item-default-value-<?php echo $item_id; ?>">
    <?php _e( 'Default Value' ); ?><br />
    <input type="text" id="edit-form-item-default-value-<?php echo $item_id; ?>" class="widefat edit-form-item-default-value" name="db-<?php echo $item_id; ?>[default_value]" value="<?php echo esc_attr( isset( $item->default_value ) ? $item->default_value : '' ); ?>" />
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
    <input type="checkbox" id="edit-form-item-description-html-escape-<?php echo $item->db_id; ?>" value="description_html_escape" name="db-<?php echo $item->db_id; ?>[display_when]"<?php checked( isset( $item->description_html_escape ) ? $item->description_html_escape : '', 'description_html_escape' ); ?> />
    <?php _e( 'Escape HTML in description' ); ?>
  </label>
</p>

<p class="field-value description description-wide">
  <label for="edit-form-item-value-<?php echo $item_id; ?>">
    <?php _e( 'Options' ); ?><br />
    <input type="text" id="edit-form-item-value-<?php echo $item_id; ?>" class="widefat code edit-form-item-value" name="db-<?php echo $item_id; ?>[options]" value="<?php echo esc_attr( isset( $item->options ) ? $item->options : '' ); ?>" />
    <span class="description-small"><?php echo __('Comma seperated list of options'); ?></span>
  </label>
</p>
<div>&nbsp;</div>

<p class="field-php-variable description description">
  <label for="edit-form-item-php-variable-<?php echo $item_id; ?>">
    <?php _e( 'Script Variable' ); ?><br />
    <input type="text" id="edit-form-item-php-variable-<?php echo $item_id; ?>" class="widefat code edit-form-item-php-variable" name="db-<?php echo $item_id; ?>[php_variable]" value="<?php echo esc_attr( isset( $item->php_variable ) ? $item->php_variable : '' ); ?>" />
    <span class="description-small"><?php echo sprintf(__('Currently using: $%s'), $item->get_php_variable()); ?></span>
  </label>
</p>

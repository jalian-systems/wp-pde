<?php ?>
<p class="description description-thin">
  <label for="edit-form-item-title-<?php echo $item_id; ?>">
    <?php _e( 'Label' ); ?><br />
    <input type="text" id="edit-form-item-title-<?php echo $item_id; ?>" class="widefat edit-form-item-title" name="db-<?php echo $item_id; ?>[title]" value="<?php echo esc_attr( isset( $item->title ) ? $item->title : '' ); ?>" />
  </label>
</p>
<p class="description description-thin">
  <label for="edit-form-item-default-value-<?php echo $item_id; ?>">
    <?php _e( '' ); ?><br />
    <input type="checkbox" id="edit-form-item-default-value-<?php echo $item_id; ?>" class="edit-form-item-default-value" name="db-<?php echo $item_id; ?>[default_value]"<?php checked( isset( $item->default_value ) ? $item->default_value : '', $item->title ); ?> value="<?php echo esc_attr( $item->title ); ?>" />
    <?php _e( 'Initially Selected' ); ?>
  </label>
</p>
<p class="description description-wide">
  <label for="edit-form-item-cb-label-<?php echo $item_id; ?>">
    <?php _e( 'Checkbox Label' ); ?><br />
    <input type="text" id="edit-form-item-cb-label-<?php echo $item_id; ?>" class="widefat code edit-form-item-cb-label" name="db-<?php echo $item_id; ?>[cb_label]" value="<?php echo esc_attr( isset( $item->cb_label ) ? $item->cb_label : '' ); ?>" />
  </label>
</p>
<p class="field-value description description-wide">
    <?php if( empty( $item->display_when ) ) $item->display_when = 'display_always'; ?>
    <?php _e( 'Display child items:' ); ?><br />
  <label for="edit-form-item-display-always-<?php echo $item->db_id; ?>">
    <input type="radio" id="edit-form-item-display-always-<?php echo $item->db_id; ?>" value="display_always" name="db-<?php echo $item->db_id; ?>[display_when]"<?php checked( isset( $item->display_when ) ? $item->display_when : '', 'display_always' ); ?> />
    <?php _e( 'Always' ); ?>
  </label>
  <label for="edit-form-item-display-selected-<?php echo $item->db_id; ?>">
    <input type="radio" id="edit-form-item-display-selected-<?php echo $item->db_id; ?>" value="display_when_selected" name="db-<?php echo $item->db_id; ?>[display_when]"<?php checked( isset( $item->display_when ) ? $item->display_when : '', 'display_when_selected' ); ?> />
    <?php _e( 'When selected' ); ?>
  </label>
  <label for="edit-form-item-display-unselected-<?php echo $item->db_id; ?>">
    <input type="radio" id="edit-form-item-display-unselected-<?php echo $item->db_id; ?>" value="display_when_unselected" name="db-<?php echo $item->db_id; ?>[display_when]"<?php checked( isset( $item->display_when ) ? $item->display_when : '', 'display_when_unselected' ); ?> />
    <?php _e( 'When unselected' ); ?>
  </label>
</p>

<p class="field-description description description-wide">
  <label for="edit-form-item-description-<?php echo $item_id; ?>">
    <?php _e( 'Description' ); ?><br />
    <textarea id="edit-form-item-description-<?php echo $item_id; ?>" class="widefat edit-form-item-description" rows="3" cols="20" name="db-<?php echo $item_id; ?>[description]"><?php echo esc_textarea( isset( $item->description ) ? $item->description : '' ); // textarea_escaped ?></textarea>
  </label>
</p>

<p class="field-value description description-wide">
  <label for="edit-form-item-description-html-escape-<?php echo $item->db_id; ?>">
    <input type="checkbox" id="edit-form-item-description-html-escape-<?php echo $item->db_id; ?>" value="description_html_escape" name="db-<?php echo $item->db_id; ?>[description_html_escape]"<?php checked( isset( $item->description_html_escape ) ? $item->description_html_escape : '', 'description_html_escape' ); ?> />
    <?php _e( 'Escape HTML in description' ); ?>
  </label>
</p>

<p class="field-value description description-wide">
  <label for="edit-form-item-value-<?php echo $item_id; ?>">
    <?php _e( 'Value' ); ?><br />
    <input type="text" id="edit-form-item-value-<?php echo $item_id; ?>" class="widefat code edit-form-item-value" name="db-<?php echo $item_id; ?>[value]" value="<?php echo esc_attr( isset( $item->value ) ? $item->value : '' ); ?>" />
    <span class="description-small"><?php echo sprintf( __('Plugin receives this value: (%s)'), $item->get_title()); ?></span>
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

<p class="description description-wide">
  <label for="edit-form-item-title-<?php echo $item_id; ?>">
    <?php _e( 'Label' ); ?><br />
    <input type="text" id="edit-form-item-title-<?php echo $item_id; ?>" class="widefat edit-form-item-title" name="db-<?php echo $item_id; ?>[title]" value="<?php echo esc_attr( isset( $item->title ) ? $item->title : '' ); ?>" />
  </label>
</p>

<?php if( empty( $item->display_style ) ) $item->display_style = 'html'; ?>
<p class="field-value description description-wide">
    <?php _e( 'Style' ); ?><br />
  <label for="edit-form-item-display-style-html-<?php echo $item->db_id; ?>">
    <input type="radio" id="edit-form-item-display-style-html-<?php echo $item->db_id; ?>" value="html" name="db-<?php echo $item->db_id; ?>[display_style]"<?php checked( isset( $item->display_style ) ? $item->display_style : '', 'html' ); ?> />
    <?php _e( 'HTML' ); ?>
  </label>
  <label for="edit-form-item-display-style-markdown-<?php echo $item->db_id; ?>">
    <input type="radio" id="edit-form-item-display-style-markdown-<?php echo $item->db_id; ?>" value="markdown" name="db-<?php echo $item->db_id; ?>[display_style]"<?php checked( isset( $item->display_style ) ? $item->display_style : '', 'markdown' ); ?> />
    <?php _e( 'Markdown' ); ?>
  </label>
</p>

<p class="field-description description description-wide">
  <label for="edit-form-item-markup-<?php echo $item_id; ?>">
    <?php _e( 'Markup' ); ?><br />
    <textarea id="edit-form-item-markup-<?php echo $item_id; ?>" class="widefat edit-form-item-markup" rows="5" cols="20" name="db-<?php echo $item_id; ?>[markup]"><?php echo esc_html( isset( $item->markup ) ? $item->markup : '' ); // textarea_escaped ?></textarea>
  </label>

<p class="field-value description description-wide">
  <label for="edit-form-item-raw-markup-<?php echo $item->db_id; ?>">
    <input type="checkbox" id="edit-form-item-raw-markup-<?php echo $item->db_id; ?>" value="raw_markup" name="db-<?php echo $item->db_id; ?>[raw_markup]"<?php checked( isset( $item->raw_markup ) ? $item->raw_markup : '', 'raw_markup' ); ?> />
    <?php _e( 'Raw markup (will be copied as is)' ); ?>
  </label>
</p>

<p class="field-php-variable description description-wide">
  <label for="edit-form-item-php-variable-<?php echo $item_id; ?>">
    <?php _e( 'Script Variable' ); ?><br />
    <input type="text" id="edit-form-item-php-variable-<?php echo $item_id; ?>" class="widefat code edit-form-item-php-variable" name="db-<?php echo $item_id; ?>[php_variable]" value="<?php echo esc_attr( isset( $item->php_variable ) ? $item->php_variable : '' ); ?>" />
    <span class="description-small"><?php echo sprintf(__('Currently using: $%s'), $item->get_php_variable()); ?></span>
  </label>
</p>

</p>

<p class="description description-wide">
  <label for="edit-form-item-title-<?php echo $item_id; ?>">
    <?php _e( 'Label' ); ?><br />
    <input type="text" id="edit-form-item-title-<?php echo $item_id; ?>" class="widefat edit-form-item-title" name="db-<?php echo $item_id; ?>[title]" value="<?php echo esc_attr( isset( $item->title ) ? $item->title : '' ); ?>" />
  </label>
</p>

<p class="field-value description description-wide">
    <?php if( empty( $item->display_style ) ) $item->display_style = 'none'; ?>
    <?php _e( 'Style' ); ?><br />
  <label for="edit-form-item-display-style-none-<?php echo $item->db_id; ?>">
    <input type="radio" id="edit-form-item-display-style-none-<?php echo $item->db_id; ?>" value="none" name="db-<?php echo $item->db_id; ?>[display_style]"<?php checked( isset( $item->display_style ) ? $item->display_style : '', 'none' ); ?> />
    <?php _e( 'None' ); ?>
  </label>
  <label for="edit-form-item-display-style-h1-<?php echo $item->db_id; ?>">
    <input type="radio" id="edit-form-item-display-style-h1-<?php echo $item->db_id; ?>" value="h1" name="db-<?php echo $item->db_id; ?>[display_style]"<?php checked( isset( $item->display_style ) ? $item->display_style : '', 'h1' ); ?> />
    <?php _e( 'h1' ); ?>
  </label>
  <label for="edit-form-item-display-style-h2-<?php echo $item->db_id; ?>">
    <input type="radio" id="edit-form-item-display-style-h2-<?php echo $item->db_id; ?>" value="h2" name="db-<?php echo $item->db_id; ?>[display_style]"<?php checked( isset( $item->display_style ) ? $item->display_style : '', 'h2' ); ?> />
    <?php _e( 'h2' ); ?>
  </label>
  <label for="edit-form-item-display-style-h3-<?php echo $item->db_id; ?>">
    <input type="radio" id="edit-form-item-display-style-h3-<?php echo $item->db_id; ?>" value="h3" name="db-<?php echo $item->db_id; ?>[display_style]"<?php checked( isset( $item->display_style ) ? $item->display_style : '', 'h3' ); ?> />
    <?php _e( 'h3' ); ?>
  </label>
  <label for="edit-form-item-display-style-h4-<?php echo $item->db_id; ?>">
    <input type="radio" id="edit-form-item-display-style-h4-<?php echo $item->db_id; ?>" value="h4" name="db-<?php echo $item->db_id; ?>[display_style]"<?php checked( isset( $item->display_style ) ? $item->display_style : '', 'h4' ); ?> />
    <?php _e( 'h4' ); ?>
  </label>
  <label for="edit-form-item-display-style-h5-<?php echo $item->db_id; ?>">
    <input type="radio" id="edit-form-item-display-style-h5-<?php echo $item->db_id; ?>" value="h5" name="db-<?php echo $item->db_id; ?>[display_style]"<?php checked( isset( $item->display_style ) ? $item->display_style : '', 'h5' ); ?> />
    <?php _e( 'h5' ); ?>
  </label>
  <label for="edit-form-item-display-style-h6-<?php echo $item->db_id; ?>">
    <input type="radio" id="edit-form-item-display-style-h6-<?php echo $item->db_id; ?>" value="h6" name="db-<?php echo $item->db_id; ?>[display_style]"<?php checked( isset( $item->display_style ) ? $item->display_style : '', 'h6' ); ?> />
    <?php _e( 'h6' ); ?>
  </label>
</p>

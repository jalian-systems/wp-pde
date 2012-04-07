<?php

class PDE_Color_Picker {

  static function custom_form_items($items) {
    $items[] = array('value' => 'color_picker', 'display' => 'Color Picker');
    return $items;
  }

  static function get_markup( $item ) {
  ?>
  <input type="hidden" name="db-<?php echo $item->db_id; ?>[deletable]; ?>" value="true" />
  <p class="description description-thin">
    <label for="edit-form-item-title-<?php echo $item->db_id; ?>">
      <?php _e( 'Title' ); ?><br />
      <input type="text" id="edit-form-item-title-<?php echo $item->db_id; ?>" class="widefat edit-form-item-title" name="db-<?php echo $item->db_id; ?>[title]" value="<?php echo esc_attr( isset( $item->title ) ? $item->title : '' ); ?>" />
    </label>
  </p>
  <p class="description description-thin">
    <label for="edit-form-item-default-value-<?php echo $item->db_id; ?>">
      <?php _e( 'Default Value' ); ?><br />
      <input type="text" id="edit-form-item-default-value-<?php echo $item->db_id; ?>" class="widefat edit-form-item-default-value" name="db-<?php echo $item->db_id; ?>[default_value]" value="<?php echo esc_attr( isset( $item->default_value ) ? $item->default_value : '' ); ?>" />
      <span class="description-small"><?php echo __('Default value in #XXXXXX format'); ?></span>
    </label>
  </p>

  <p class="field-description description description-wide">
    <label for="edit-form-item-description-<?php echo $item->db_id; ?>">
      <?php _e( 'Description' ); ?><br />
      <textarea id="edit-form-item-description-<?php echo $item->db_id; ?>" class="widefat edit-form-item-description" rows="3" cols="20" name="db-<?php echo $item->db_id; ?>[description]"><?php echo esc_html( isset( $item->description ) ? $item->description : '' ); // textarea_escaped ?></textarea>
    </label>
  </p>

  <p class="field-value description description-wide">
    <label for="edit-form-item-description-html-escape-<?php echo $item->db_id; ?>">
      <input type="checkbox" id="edit-form-item-description-html-escape-<?php echo $item->db_id; ?>" value="description_html_escape" name="db-<?php echo $item->db_id; ?>[description_html_escape]"<?php checked( isset( $item->description_html_escape ) ? $item->description_html_escape : '', 'description_html_escape' ); ?> />
      <?php _e( 'Escape HTML in description' ); ?>
    </label>
  </p>

  <p class="field-php-variable description description-wide">
    <label for="edit-form-item-php-variable-<?php echo $item->db_id; ?>">
      <?php _e( 'Script Variable' ); ?><br />
      <input type="text" id="edit-form-item-php-variable-<?php echo $item->db_id; ?>" class="widefat code edit-form-item-php-variable" name="db-<?php echo $item->db_id; ?>[php_variable]" value="<?php echo esc_attr( isset( $item->php_variable ) ? $item->php_variable : '' ); ?>" />
      <span class="description-small"><?php echo sprintf(__('Currently using: $%s'), $item->get_php_variable()); ?></span>
    </label>
  </p>
  <?php
  }

  static function set_default_args( $args ) {
    $args['deletable'] = true ;
    return $args ;
  }

  static function enqueue_css( $args ) {
?>
	   wp_enqueue_script( 'farbtastic' );
	   wp_enqueue_style( 'farbtastic' );
<?php
  }
}

add_filter( 'pde_custom_form_items', array( 'PDE_Color_Picker', 'custom_form_items') );
add_action( 'pde_custom_form_item_get_markup_for_color_picker', array( 'PDE_Color_Picker', 'get_markup') );

add_filter( 'pde_custom_form_item_defaults_for_color_picker', array( 'PDE_Color_Picker', 'set_default_args' ) );
add_action( 'pde_custom_form_item_enqueue_css_for_color_picker', array( 'PDE_Color_Picker', 'enqueue_css' ) ) ;
?>

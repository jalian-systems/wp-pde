<?php

class PDE_Date_Picker {

  static function custom_form_items($items) {
    $items[] = array('value' => 'date_picker', 'display' => 'Date Picker');
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

  <?php if( empty( $item->display_style ) ) $item->display_style = 'inline' ; ?>
  <p class="description description-thin">
    <label for="edit-form-item-display-style-<?php echo $item->db_id; ?>">
      <?php _e( 'Display Style' ); ?><br />
      <select id="edit-form-item-display-style-<?php echo $item->db_id; ?>" class="widefat edit-form-item-display-style" name="db-<?php echo $item->db_id; ?>[display_style]">
        <option value="inline"<?php selected( !empty( $item->display_style ) ? $item->display_style : '', 'inline' ); ?> >Inline</option>
        <option value="dropdown"<?php selected( !empty( $item->display_style ) ? $item->display_style : '', 'dropdown' ); ?> >Dropdown</option>
      </select>
    </label>
  </p>

  <?php if( empty( $item->display_format ) ) $item->display_format = 'mm/dd/yyyy' ; ?>
  <p class="description description-thin">
    <label for="edit-form-item-display-format-<?php echo $item->db_id; ?>">
      <?php _e( 'Display Format' ); ?><br />
      <select id="edit-form-item-display-format-<?php echo $item->db_id; ?>" class="widefat edit-form-item-display-format" name="db-<?php echo $item->db_id; ?>[display_format]">
        <option value="mm/dd/yy"<?php selected( !empty( $item->display_format ) ? $item->display_format : '', 'mm/dd/yy' ); ?> >mm/dd/yy </option>
        <option value="dd/mm/yy"<?php selected( !empty( $item->display_format ) ? $item->display_format : '', 'dd/mm/yy' ); ?> >dd/mm/yy</option>
        <option value="ISO_8601"<?php selected( !empty( $item->display_format ) ? $item->display_format : '', 'ISO_8601' ); ?> >ISO_8601</option>
        <option value="RFC_822"<?php selected( !empty( $item->display_format ) ? $item->display_format : '', 'RFC_822' ); ?> >RFC_822</option>
        <option value="RFC_850"<?php selected( !empty( $item->display_format ) ? $item->display_format : '', 'RFC_850' ); ?> >RFC_850</option>
        <option value="RFC_1036"<?php selected( !empty( $item->display_format ) ? $item->display_format : '', 'RFC_1036' ); ?> >RFC_1036</option>
        <option value="RFC_1123"<?php selected( !empty( $item->display_format ) ? $item->display_format : '', 'RFC_1123' ); ?> >RFC_1123</option>
        <option value="RFC_2822"<?php selected( !empty( $item->display_format ) ? $item->display_format : '', 'RFC_2822' ); ?> >RFC_2822</option>
      </select>
    </label>
  </p>

<script type="text/javascript">
(function($) {
	$(document).ready( function() {
    $('#edit-form-item-display-format-<?php echo $item->db_id; ?> option').each (function (index, item ) {
      value = $(item).attr('value') ;
      if(value.lastIndexOf('RFC', 0) == 0 || value.lastIndexOf('ISO', 0) == 0)
        value = $.datepicker[value] ;
      $(item).html($(item).attr('value') + ' (' + $.datepicker.formatDate(value, new Date()) + ')' );
    });
  });
})(jQuery);
</script>

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
	   wp_enqueue_script( 'jquery-ui-datepicker' );
     wp_enqueue_style( 'jquery-style', plugins_url( 'styles/jquery-ui-1.8.18.custom.css', dirname( __FILE__ ) ) );
<?php
  }
}

add_filter( 'pde_custom_form_items', array( 'PDE_Date_Picker', 'custom_form_items') );
add_action( 'pde_custom_form_item_get_markup_for_date_picker', array( 'PDE_Date_Picker', 'get_markup') );

add_filter( 'pde_custom_form_item_defaults_for_date_picker', array( 'PDE_Date_Picker', 'set_default_args' ) );
add_action( 'pde_custom_form_item_enqueue_css_for_date_picker', array( 'PDE_Date_Picker', 'enqueue_css' ) ) ;
?>

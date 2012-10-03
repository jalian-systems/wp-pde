<?php

class PDE_Action_Button {

  static function custom_form_items($items) {
    $items[] = array('value' => 'action_button', 'display' => 'Action Button');
    return $items;
  }

  static function get_markup( $item ) {
  ?>
  <input type="hidden" name="db-<?php echo $item->db_id; ?>[deletable]; ?>" value="true" />
  <p class="description description-wide">
    <label for="edit-form-item-title-<?php echo $item->db_id; ?>">
      <?php _e( 'Title' ); ?><br />
      <input type="text" id="edit-form-item-title-<?php echo $item->db_id; ?>" class="widefat edit-form-item-title" name="db-<?php echo $item->db_id; ?>[title]" value="<?php echo esc_attr( isset( $item->title ) ? $item->title : '' ); ?>" />
    </label>
  </p>

  <p class="description description-thin">
    <label for="edit-form-item-use-default-<?php echo $item->db_id; ?>">
      <?php _e( 'Default button' ); ?><br />
      <input type="checkbox" id="edit-form-item-use-default-<?php echo $item->db_id; ?>" value="use_default" name="db-<?php echo $item->db_id; ?>[use_default]"<?php checked( isset( $item->use_default ) ? $item->use_default : '', 'use_default' ); ?> />
      <?php _e( 'Use save button' ); ?>
    </label>
  </p>

  <p class="description description-thin">
    <label for="edit-form-item-button-type-<?php echo $item->db_id; ?>">
      <?php _e('Button Type'); ?><br />
      <select id="edit-form-item-button-type-<?php echo $item->db_id; ?>" class="widefat code edit-form-item-theme" name="db-<?php echo $item->db_id; ?>[button_type]">
        <option value="primary" <?php selected( !empty( $item->button_type ) ? $item->button_type : '', 'primary' ); ?>>Primary</option>
        <option value="secondary" <?php selected( !empty( $item->button_type ) ? $item->button_type : '', 'secondary' ); ?>>Secondary</option>
        <option value="delete" <?php selected( !empty( $item->button_type ) ? $item->button_type : '', 'delete' ); ?>>Delete</option>
      </select>
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

  static function form( $form_data, $item, $walker ) {
    $text = null ;
    if( empty( $item->use_default ) )
      $text = $item->get_title();
    $type = 'primary' ;
    if( empty( $item->use_default ) )
      $type = $item->button_type ;
    $name = 'submit' ;
    if( empty( $item->use_default ) )
      $name = $item->get_php_variable();
    ob_start();
?>
    @><div class="pde-form-field pde-form-markup markup-style-html"><@php
    submit_button( '<?php echo $text; ?>', '<?php echo $type; ?>', '<?php echo $name; ?>', false, array( 'id' => 'id-<?php echo $name; ?>' ) );
    @></div><@php
<?php if( empty( $item->use_default ) ): ?>
    @>
    <script type="text/javascript">
    (function($) {
      $(document).ready(function(e) {
        $('#id-<?php echo $name; ?>').bind('click', function(e) {
          $('#action').val('<?php echo $name; ?>');
          return true ;
        });
      });
    })(jQuery);
    </script>
    <@php
<?php endif; ?>
<?php
    $form_data = ob_get_clean();
    return $form_data;
  }

  static function set_default_args( $args ) {
    $args['deletable'] = true ;
    return $args ;
  }

}

add_filter( 'pde_custom_form_items', array( 'PDE_Action_Button', 'custom_form_items') );
add_action( 'pde_custom_form_item_get_markup_for_action_button', array( 'PDE_Action_Button', 'get_markup') );

add_filter( 'pde_custom_form_item_form_for_action_button', array( 'PDE_Action_Button', 'form'), 10, 3);
add_filter( 'pde_custom_form_item_defaults_for_action_button', array( 'PDE_Action_Button', 'set_default_args' ) );
?>

<?php

class PDE_Dropdown_Item {

  static function custom_form_items($items) {
    $items[] = array('value' => 'dropdown_item', 'display' => 'Dropdown Item');
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
      <span class="description-small"><?php echo __('Used it this is first item in the group.'); ?></span>
    </label>
  </p>

  <p class="field-value description description-wide">
    <label for="edit-form-item-value-<?php echo $item->db_id; ?>">
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

  <p class="field-value description description-wide">
    <label for="edit-form-item-value-<?php echo $item->db_id; ?>">
      <?php _e( 'Dropdown Group' ); ?><br />
      <input type="text" id="edit-form-item-value-<?php echo $item->db_id; ?>" class="widefat code edit-form-item-value" name="db-<?php echo $item->db_id; ?>[dropdown_group]" value="<?php echo esc_attr( isset( $item->dropdown_group ) ? $item->dropdown_group : '' ); ?>" />
      <span class="description-small"><?php echo __('All dropdown items will be grouped using this group.'); ?></span>
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
      <input type="checkbox" id="edit-form-item-description-html-escape-<?php echo $item->db_id; ?>" value="description_html_escape" name="db-<?php echo $item->db_id; ?>[display_when]"<?php checked( isset( $item->description_html_escape ) ? $item->description_html_escape : '', 'description_html_escape' ); ?> />
      <?php _e( 'Escape HTML in description' ); ?>
    </label>
  </p>


  <p class="field-value description description-thin">
    <label for="edit-form-item-value-<?php echo $item->db_id; ?>">
      <?php _e( 'Value' ); ?><br />
      <input type="text" id="edit-form-item-value-<?php echo $item->db_id; ?>" class="widefat code edit-form-item-value" name="db-<?php echo $item->db_id; ?>[value]" value="<?php echo esc_attr( isset( $item->value ) ? $item->value : '' ); ?>" />
      <span class="description-small"><?php echo sprintf( __('Plugin receives this value: (%s)'), $item->get_value()); ?></span>
    </label>
  </p>

  <p class="field-php-variable description description-thin">
    <label for="edit-form-item-php-variable-<?php echo $item->db_id; ?>">
      <?php _e( 'Script Variable' ); ?><br />
      <input type="text" id="edit-form-item-php-variable-<?php echo $item->db_id; ?>" class="widefat code edit-form-item-php-variable" name="db-<?php echo $item->db_id; ?>[php_variable]" value="<?php echo esc_attr( isset( $item->php_variable ) ? $item->php_variable : '' ); ?>" />
      <span class="description-small"><?php echo sprintf(__('Currently using: $%s'), $item->get_php_variable()); ?></span>
    </label>
  </p>
  <?php
  }

  static function get_php_variable($php_variable, $item) {
    if( !empty( $item->dropdown_group ) )
      return str_replace( '-', '_', sanitize_title_with_dashes( $item->dropdown_group ) );
    return '';
  }

  static function check_item($messages, $item) {
    if( empty( $item->dropdown_group ) )
      WpPDEPlugin::messages( 'error', sprintf( __( 'Provide a name for the dropdown group for <strong>%s</strong>' ), $item->title ), $messages );
    return $messages;
  }

  static function form_defaults( $form_default, $item, $walker ) {
    if( !isset( $walker->dropdown_items ) )
      $walker->dropdown_items = array();
    $php_variable = $item->get_php_variable();
    if( in_array( $php_variable, $walker->dropdown_items ) )
      return '';
    $walker->dropdown_items[] = $php_variable;
    return $form_default;
  }

  static function widget_update( $widget_update, $item, $walker ) {
    if( !isset( $walker->dropdown_items ) )
      $walker->dropdown_items = array();
    $php_variable = $item->get_php_variable();
    if( in_array( $php_variable, $walker->dropdown_items ) )
      return '';
    $walker->dropdown_items[] = $php_variable;
    $default_val = $item->get_default_value();
    ob_start();
  ?>
      if( isset( $new_instance['<?php echo $php_variable; ?>'] ) )
        $instance['<?php echo $php_variable; ?>'] = $new_instance['<?php echo $php_variable; ?>'] ;
      else
        $instance['<?php echo $php_variable; ?>'] = '<?php echo $default_val; ?>' ;
  <?php
    $widget_update = ob_get_clean();
    return $widget_update;
  }

  static function widget_widget( $widget_widget, $item, $walker ) {
    if( !isset( $walker->dropdown_items ) )
      $walker->dropdown_items = array();
    $php_variable = $item->get_php_variable();
    if( in_array( $php_variable, $walker->dropdown_items ) )
      return '';
    $walker->dropdown_items[] = $php_variable;
    $widget_widget = "    \$$php_variable = \$instance['$php_variable'];\n";
    return $widget_widget;
  }

  static function form( $form_data, $item, $walker ) {
    if( !isset( $walker->dropdown_items ) )
      $walker->dropdown_items = array();
    $php_variable = $item->get_php_variable();
    if( in_array( $php_variable, $walker->dropdown_items ) )
      return '';
    $walker->dropdown_items[] = $php_variable;

    $form = PDEForm::get( $item->form_id );
    $all_items = $form->get_form_field_items();
    $values = array();
    foreach( $all_items as $dropdown ) {
      if( $dropdown->get_php_variable() == $php_variable )
        $values[] = $dropdown->get_value();
    }

    $title = $item->get_title();
    $field_id = sanitize_html_class($item->get_title());
    if( empty( $item->description_html_escape ) )
      $description = $item->get_description() ;
    else
      $description = '<@php _e( \'' . esc_html( $item->get_description() ) . '\' ); @>' ;
    ob_start();
  ?>
  @>
    <div class="pde_form_field pde_form_dropdown_items <?php echo $php_variable; ?>">
      <select class="wp_pde_dropdown_item widefat" name="<@php echo $this->get_field_name('<?php echo $php_variable; ?>'); @>" id="<@php echo $this->get_field_id('<?php echo $php_variable; ?>'); @>">
<?php foreach( $values as $v ) {
				$value = esc_attr($v);
        $esc_value = esc_html($v); ?>
        <option id='<@php echo \$this->get_field_id( '<?php echo sanitize_html_class( $v ); ?>' ); @>' value="<?php echo $value; ?>"<@php selected( $instance['<?php echo $php_variable; ?>'], '<?php echo $value; ?>' ); @>><@php _e('<?php echo $esc_value; ?>'); @></option>
<?php } ?>
      </select>
      <br />
<?php if( !empty( $description ) ): ?>
      <div class="description-small"><?php echo $description; ?></div>
<?php endif; ?>
    </div> <!-- <?php echo $php_variable; ?> -->
  <@php 
  <?php
    $form_data = ob_get_clean();
    return $form_data;
  }

  static function form_start_level( $start_level, $item, $walker ) {
    $start_level = "@>\n";
    if( !isset( $item->display_when ) )
      $item->display_when = 'display_always' ;
    $start_level .= "   <div class='{$item->display_when}' id='group-<@php echo \$this->get_field_id( \"" . sanitize_html_class($item->get_title()) . "\"); @>'>\n";
    if( $item->display_when == 'display_when_selected' || $item->display_when == 'display_when_unselected') {
      ob_start();
      if( !isset( $walker->dropdown_script ) ) {
        $walker->dropdown_script = true ;
  ?>
  <script type="text/javascript">
  (function($) {
    $('#<@php echo $this->get_field_id("wp_pde_form"); @>').on('change', '.wp_pde_dropdown_item', function (e) {
      $(e.target).children('option').each( function (index, item) {
        group = '#group-' + $(item).attr('id');
        if($(group).size() > 0 && !$(group).hasClass('display_always')) {
          if( ( $(item).attr('selected') != 'selected' && $(group).hasClass('display_when_unselected') )
                || ( $(item).attr('selected') == 'selected' && $(group).hasClass('display_when_selected') ) )
            d = 'block' ;
          else
            d = 'none';
          $(group).css('display', d);
        }
      });
    });
  })(jQuery);
  </script>
  <?php
      }
  ?>
  <script type="text/javascript">
  (function($) {
    $('.wp_pde_dropdown_item').trigger('change');
  })(jQuery);
  </script>
  <?php
      $start_level .= ob_get_clean();
    }
    $start_level .= "<@php\n";
    return $start_level;
  }

  static function preface( $item_preface, $item, $form ) {
    if( !isset( $form->dropdown_items ) )
      $form->dropdown_items = array();
    $php_variable = $item->get_php_variable();
    if( in_array( $php_variable, $form->dropdown_items ) )
      return '';
    $form->dropdown_items[] = $php_variable;

    $all_items = $form->get_form_field_items();
    $values = array();
    foreach( $all_items as $dropdown ) {
      if( $dropdown->get_php_variable() == $php_variable )
        $values[] = $dropdown->get_value();
    }

    $item_preface = ' * $' . $php_variable . ' ' . $item->type_label;
    $item_preface .= ' (values: '. implode(',', $values) . ' )';
    $item_preface .= "\n";
    return $item_preface;
  }

  static function set_default_args( $args ) {
    $args['deletable'] = true ;
    return $args ;
  }

}

add_filter( 'pde_custom_form_items', array( 'PDE_Dropdown_Item', 'custom_form_items') );
add_action( 'pde_custom_form_item_get_markup_for_dropdown_item', array( 'PDE_Dropdown_Item', 'get_markup') );
add_filter( 'pde_custom_form_item_get_php_variable_for_dropdown_item', array( 'PDE_Dropdown_Item', 'get_php_variable'), 10, 2 );
add_filter( 'pde_custom_form_item_check_item_for_dropdown_item', array( 'PDE_Dropdown_Item', 'check_item'), 10, 2);

add_filter( 'pde_custom_form_item_form_defaults_for_dropdown_item', array( 'PDE_Dropdown_Item', 'form_defaults'), 10, 3);
add_filter( 'pde_custom_form_item_widget_update_for_dropdown_item', array( 'PDE_Dropdown_Item', 'widget_update'), 10, 3);
add_filter( 'pde_custom_form_item_widget_widget_for_dropdown_item', array( 'PDE_Dropdown_Item', 'widget_widget'), 10, 3);
add_filter( 'pde_custom_form_item_form_for_dropdown_item', array( 'PDE_Dropdown_Item', 'form'), 10, 3);
add_filter( 'pde_custom_form_item_form_start_level_for_dropdown_item', array( 'PDE_Dropdown_Item', 'form_start_level'), 10, 3);
add_filter( 'pde_custom_form_item_preface_for_dropdown_item', array( 'PDE_Dropdown_Item', 'preface'), 10, 3);
add_filter( 'pde_custom_form_item_defaults_for_dropdown_item', array( 'PDE_Dropdown_Item', 'set_default_args' ) );
?>

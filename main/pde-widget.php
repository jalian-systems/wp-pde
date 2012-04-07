<?php

class PDEWidget extends PDEForm {

  function get_widget_file() {
    return strtolower(sanitize_file_name($this->get_name())) . '.php';
  }

  function get_classname($plugin) {
    $suffix = '';
    if ($plugin->plugin_version)
      $suffix = '_v' . $plugin->plugin_version;
    return preg_replace('/[^a-zA-Z0-9_]/', '_', ucwords($plugin->plugin_name . $suffix . '_w_' . $this->get_name()));
  }

  function get_name() {
    $info = $this->_get_info();
    return $info->title;
  }

  function get_description() {
    $info = $this->_get_info();
    return isset( $info->description ) ? $info->description : '' ;
  }

  function get_theme_value() {
    $info = $this->_get_info();
    if( !isset( $info->theme ) )
      return '';
    $a = unserialize( $info->theme );
    if( !isset( $a['value'] ) )
      return '';
    return $a['value'];
  }

  function get_theme_file() {
    $info = $this->_get_info();
    if( !isset( $info->theme ) )
      return '';
    $a = unserialize( $info->theme );
    if( !isset( $a['file'] ) )
      return '';
    return $a['file'];
  }

  function get_width() {
    $info = $this->_get_info();
    return isset($info->width) ? $info->width : 0;
  }

  function get_height() {
    $info = $this->_get_info();
    return isset($info->height) ? $info->height : 0;
    return $info->height;
  }

  function _get_info() {
    if ( !empty($this->widget_info) )
      return $this->widget_info ;
    
    $this->widget_info = $this->get_form_info();
    return $this->widget_info;
  }

  function do_wrap() {
    $info = $this->_get_info();
    return !isset($info->do_wrap);
  }

  function update_source_preface($items, &$messages, $first = false) {
    $source = $this->get_source( $messages );
    $preface = $this->get_preface( $items, $first );

    if( preg_match( '/^\/\*.*\*\//msU', $source ) )
      $newcontent = preg_replace( '/^\/\*.*\*\//msU', $preface, $source );
    else
      $newcontent = $preface . "\n" . $source ;
    $this->update_source($newcontent, $messages);
  }

  function get_preface( $items, $first ) {
    $preface =  "/**\n";
    $preface .= " * The following variables are available from the form:\n";
    foreach( $items as $item ) {
      $php_variable = $item->get_php_variable();
      if( empty( $php_variable ) || $item->param_type == 'widget parameters')
        continue ;
      $item_preface = ' * $' . $php_variable . ' ' . $item->type_label;
      if( $item->param_type == 'radio' || $item->param_type == 'dropdown' )
        $values = $item->get_option_values();
      else if( $item->param_type == 'checkbox' )
        $values[] = $item->get_value();
      if( isset( $values ) )
        if( count($values) > 1 )
          $item_preface .= ' (values: '. implode(',', $values) . ' )';
        else
          $item_preface .= ' (value: '. implode(',', $values) . ' )';
      $item_preface .= "\n";
      $item_preface = apply_filters( 'pde_custom_form_item_preface_for_' . $item->param_type, $item_preface, $item, $this );
      $preface .= $item_preface;
    }
    $preface .= " */";
    if( $first ) {
      $preface .= "\n" . '$title = apply_filters( "widget_title", $title );' . "\n";
    $preface .= 'if ( ! empty( $title ) )' . "\n";
    $preface .= '  echo $sidebar["before_title"] . $title . $sidebar["after_title"];' . "\n";
    }
    return $preface ;
  }

  static function default_styles( $styles ) {
    $files = scandir( dirname( __FILE__ ) . '/styles' );
    foreach( $files as $f ) {
      if( preg_match( '/^pde-widget-/', $f ) ) {
        $value = sanitize_title_with_dashes( basename( $f, '.css' ) ) ;
        $file = dirname( __FILE__ ) . '/styles/' . $f ;
        $display = ucwords( str_replace('-', ' ', substr( basename( $f, '.css' ), 11 )) ); 
        $styles[] = compact( array( 'value', 'display', 'file' ) );
      }
    }
    return $styles;
  }

}

class Walker_widget_update extends Walker_PDE_Form {

	function start_el(&$output, $item, $depth, $args) {
    $php_var = $item->get_php_variable();
    if ( empty( $php_var ) )
      return;
    if( in_array( $item->param_type, array( 'text', 'password', 'textarea' ) ) ) {
      $lhs = "\$new_instance['$php_var']";

      if ( isset( $item->strip_tags ) )
        $lhs = 'strip_tags( ' . $lhs . ' )';

      if ( $item->param_type == 'textarea' )
        $lhs = 'stripslashes ( '. $lhs . ' )';

      $widget_update = "    \$instance['$php_var'] = " . $lhs . ";\n";
    } else {
      ob_start();
?>
    if( isset( $new_instance['<?php echo $php_var; ?>'] ) )
      $instance['<?php echo $php_var; ?>'] = $new_instance['<?php echo $php_var; ?>'] ;
    else
      $instance['<?php echo $php_var; ?>'] = '' ;
<?php
      $widget_update = ob_get_clean();
    }
    if( !isset( $widget_update ) )
      $widget_update = '';
    $widget_update = apply_filters( 'pde_custom_form_item_widget_update_for_' . $item->param_type, $widget_update, $item, $this );
    $output .= $widget_update;
  }

  function end_el(&$output, $item, $depth) {}
}

class Walker_widget_widget extends Walker_PDE_Form {

	function start_el(&$output, $item, $depth, $args) {
    $php_var = $item->get_php_variable();
    if ( empty( $php_var ) )
      return;
    $widget_widget = "    \$$php_var = \$instance['$php_var'];\n";
    $widget_widget = apply_filters( 'pde_custom_form_item_widget_widget_for_' . $item->param_type, $widget_widget, $item, $this );
    $output .= $widget_widget;
  }

  function end_el(&$output, $item, $depth) {}
}

add_filter( 'pde_plugin_item_theme_for_widget', array( 'PDEWidget', 'default_styles' ) );
?>

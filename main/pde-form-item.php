<?php

class PDEFormItem {

	static function create( $form_id, $plugin_id, $args, &$messages ) {
    if( in_array ( $args['param_type'], array( 'text', 'textarea', 'password' ) ) ) {
			if( isset( $args['html_option'] ) ) {
      	$accept_html = $args['html_option'] !== 'No';
      	$args['strip_tags'] = $accept_html ? '' : 'strip_tags';
      	$args['strip_slashes'] = $accept_html ? '' : 'strip_slashes';
			}
		} else {
			unset( $args['strip_tags'] );
			unset( $args['strip_slashes'] );
		}

    $args['description_html_escape'] = 'description_html_escape';
		return PDEFormItem::update( $form_id, $plugin_id, 0, $args, $messages );
	}

	static function get( $id, &$messages ) {
		$post = get_post( $id );
		if( !$post || is_wp_error( $post ) ) {
			if( !$post )
				WpPDEPlugin::messsages( 'error', sprintf( __('Internal Error: could not retrieve post with id <strong>%d</strong>'), $id ), $messages);
			else
				WpPDEPlugin::messsages( 'error', sprintf( __('Internal Error: could not retrieve post with id <strong>%d</strong>: %s'), $id, $post->get_error_message() ), $messages);
			return false;
		}
		return PDEFormItem::setup( $post );
	}

  function delete(&$messages) {
    if (wp_delete_post($this->db_id)) {
      WpPDEPlugin::messages('updated fade', __('Selected item deleted'), $messages);
      return true ;
    }
    WpPDEPlugin::messages('error', __('Internal Error: Deleting item failed'), $messages);
    return false ;
  }

  static function is_field($item) {
      return in_array( $item->param_type, array( "text", "password", "radio", "checkbox", "textarea", "dropdown", "label", "markup" ) )
              || has_action( 'pde_custom_form_item_get_markup_for_' . $item->param_type );
  }

  static function is_deletable( $item ) {
      return in_array( $item->param_type, array( "text", "password", "radio", "checkbox", "textarea", "dropdown", "label", "markup" ) ) ||
              ( !empty($item->deletable) && $item->deletable );
  }

  static function is_info($item) {
      return $item->param_type == 'widget parameters';
  }

  function get_option_values() {
    if( !isset( $this->options ) )
      return array();
    $values = array_map( 'trim', str_getcsv( $this->options ) );
    $keys = array_map( 'sanitize_title_with_dashes', $values ) ;
    return array_combine($keys, $values);
  }

  function get_rows_attr() {
    if ( !empty( $this->rows ) )
      return "rows = '$this->rows' " ;
    return '';
  }

  function get_value() {
    if( !empty( $this->value ) )
      return $this->value ;
    return $this->title;
  }

	function get_display_on_single_line() {
		if( empty( $this->single_line ) )
			return false ;
		return $this->single_line == 'single_line' ;
	}

  function get_default_value() {
    if ( !empty( $this->default_value ) )
      return $this->default_value ;
    return '';
  }

  function get_title() {
    $title = $this->title;
    $pos = strpos($title, '__') ;
    if ($pos === 0) {
      return '';
    }
    return $title;
  }

  function get_description() {
    return isset( $this->description ) ? $this->description : '' ;
  }

  function get_label() {
    return isset( $this->cb_label ) ? $this->cb_label : '' ;
  }

  function get_php_variable() {
    if (! empty($this->php_variable))
      return $this->php_variable ;
    if( $this->param_type == 'label')
      return '';
    $var = $this->title;
    $pos = strpos($var, '__') ;
    if ($pos === 0) {
      $var = substr($var, 2);
    }
    return apply_filters( 'pde_custom_form_item_get_php_variable_for_' . $this->param_type,
                            str_replace( '-', '_', sanitize_title_with_dashes( $var ) ), $this );
  }

  function get_select_text() {
    if(!empty($this->select_text))
      return $this->select_text ;
    return 'Select an item' ;
  }

  function get_style() {
    return ( !isset( $this->display_style ) || $this->display_style == 'none' ) ? '' : $this->display_style ;
  }

  static function _check_item($item, &$messages) {
    if ($item->param_type == 'dropdown') {
      if ( empty($item->options) ) {
        WpPDEPlugin::messages('error', sprintf(__('Provide options for the dropdown list %s'), esc_html($item->title)), $messages);
      }
    } else if ($item->param_type == 'radio') {
      if ( empty($item->options) ) {
        WpPDEPlugin::messages('error', sprintf(__('Provide options for the radio group %s'), esc_html($item->title)), $messages);
      }
    }
    $messages = apply_filters( 'pde_custom_form_item_check_item_for_' . $item->param_type, $messages, $item );
  }

  static function update( $form_id, $plugin_id, $form_item_db_id = 0, $args = array(), &$messages ) {
    $form_id = (int) $form_id ;
    $plugin_id = (int) $plugin_id;
    $form_item_db_id = (int) $form_item_db_id;

    if( !isset( $args['position'] ) || 0 == (int) $args['position'] ) {
      $args['position'] = 999;
    }

    if( isset( $args['param_type'] ) )
      $args = apply_filters( 'pde_custom_form_item_defaults_for_' . sanitize_title_with_dashes( $args['param_type'] ), $args );

    // Populate the plugin item object
    $post = array(
      'menu_order' => $args['position'],
      'ping_status' => 0,
      'post_status' => 'publish',
      'post_parent' => $form_id,
      'post_title' => $args['title'],
      'post_type' => 'pde_plugin_item',
      'tax_input' => array( 'pde_plugin' => array( intval( $plugin_id ) ) ),
    );

    if( isset( $args['param_type'] ) )
      $post['post_excerpt'] = $args['param_type'] ;

    unset( $args['title'] );
    unset( $args['position'] );
    unset( $args['param_type'] );

    if( !isset( $args['parent_id'] ) ) {
      $args['parent_id'] = 0 ;
    }

    $post['post_content'] = serialize( array_map( 'base64_encode', $args ) );

    $post['ID'] = $form_item_db_id;
    if ( 0 == $form_item_db_id )
      $form_item_db_id = wp_insert_post( $post );
    else {
      wp_update_post( $post );
    }
   
    if ( ! $form_item_db_id || is_wp_error( $form_item_db_id ) )
      return $form_item_db_id;

    $item = PDEFormItem::setup( get_post($form_item_db_id) );
    PDEFormItem::_check_item( $item, $messages );
    return $item ;
  }

  static function setup( $post ) {
		$form_item = new PDEFormItem;
    $form_item->db_id = (int) $post->ID;
    $form_item->title = $post->post_title;
    $form_item->param_type = $post->post_excerpt;
    if( empty( $form_item->type_label ) )
      $form_item->type_label = ucwords(preg_replace('/[_-]/', ' ', $form_item->param_type));
		$form_item->position = $post->menu_order;
    $form_item->form_id = $post->post_parent;

    $args = array_map( 'base64_decode', unserialize( stripslashes( $post->post_content ) ) );
    foreach( $args as $k => $v )
      $form_item->$k = $v;
    return $form_item;
  }

  static function isa( $form_item_id = 0 ) {
    return ( ! is_wp_error( $form_item_id ) && ( 'pde_plugin_item' == get_post_type( $form_item_id ) ) );
  }

  /**
   * Returns all plugin items of a PDE plugin
   *
   * @return mixed $items array of plugin items, else false.
   */
  static function get_items( $form_id ) {

    $terms = wp_get_post_terms ( $form_id, 'pde_plugin' );
    if ( !$terms || empty ($terms) )
      return false ;

    $plugin = PDEPlugin::get( $terms[0]->term_id );

    if ( ! $plugin || is_wp_error ( $plugin ) )
      return false;

    static $fetched = array();

    $items = get_objects_in_term( $plugin->plugin_id, 'pde_plugin' );

    if ( empty( $items ) )
      return $items;

    $args = array( 'order' => 'ASC', 'orderby' => 'menu_order', 'post_type' => 'pde_plugin_item',
      'post_status' => 'publish', 'output' => ARRAY_A, 'output_key' => 'menu_order', 'nopaging' => true,
      'update_post_term_cache' => false, 'post_parent' => $form_id );
    if ( count( $items ) > 1 )
      $args['include'] = implode( ',', $items );
    else
      $args['include'] = $items[0];

    $items = get_posts( $args );

    if ( is_wp_error( $items ) || ! is_array( $items ) )
      return false;

    if ( ARRAY_A == $args['output'] ) {
      $GLOBALS['_form_item_sort_prop'] = $args['output_key'];
      usort($items, array('PDEFormItem', 'sort_items'));
      $i = 1;
      foreach( $items as $k => $item ) {
        $items[$k]->$args['output_key'] = $i++;
      }
    }

    $items = array_map( array('PDEFormItem', 'setup'), $items );

    return $items;
  }

  static function sort_items( $a, $b ) {
    global $_form_item_sort_prop;

    if ( empty( $_form_item_sort_prop ) )
      return 0;

    if ( ! isset( $a->$_form_item_sort_prop ) || ! isset( $b->$_form_item_sort_prop ) )
      return 0;

    $_a = (int) $a->$_form_item_sort_prop;
    $_b = (int) $b->$_form_item_sort_prop;

    if ( $a->$_form_item_sort_prop == $b->$_form_item_sort_prop )
      return 0;
    elseif ( $_a == $a->$_form_item_sort_prop && $_b == $b->$_form_item_sort_prop )
      return $_a < $_b ? -1 : 1;
    else
      return strcmp( $a->$_form_item_sort_prop, $b->$_form_item_sort_prop );
  }

}
?>

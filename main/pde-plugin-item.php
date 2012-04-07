<?php

class PDEPluginItem {

  function update_source($newcontent, &$messages) {
    if( !empty( $this->binary ) ) {
      $newcontent = addcslashes( convert_uuencode( $newcontent ), "\\" ) ;
    }
    if (wp_update_post(array('ID' => $this->db_id, 'post_content' => $newcontent))) {
      if( $messages )
        WpPDEPlugin::messages('updated fade', __('Contents saved'), $messages);
      return true ;
    }
    if( $messages )
      WpPDEPlugin::messages('error', __('Internal Error: Updating source failed'), $messages);
    return false ;
  }

  function get_source(&$messages) {
    return $this->content;
  }

  function delete_source(&$messages, $plugin_id) {
    if (PDEPluginItem::is_widget($this)) {
      $plugin = PDEPlugin::get( $plugin_id );
      $src_item = $plugin->get_source_item('widgets/' . $this->get_widget_file());
      if( $src_item && !is_wp_error( $src_item ) )
        $src_item->delete_source($messages, $plugin_id);
    }

		if (PDEPluginItem::is_form($this)) {
      $children = $this->get_form_items();
      foreach($children as $child) {
        if (!wp_delete_post($child->db_id))
          WpPDEPlugin::messages('error', sprintf( __('Could not delete child item %d'), $child->ID), $messages);
      }
    }

    if (wp_delete_post($this->db_id)) {
      WpPDEPlugin::messages('updated fade', __('Selected item deleted'), $messages);
      return true ;
    }
    WpPDEPlugin::messages('error', __('Internal Error: Deleting source item failed'), $messages);
    return false ;
  }

  function get_action_src() {
    $args = array();
    for ($i = 1; $i < $this->hook_args + 1; $i++)
      $args[] = '$arg' . $i ;
    $a = implode(', ', $args);
    if( $this->param_type == 'filter' )
      $r = "\n  return \$arg1;" ;
    else
      $r = '';
    return "static function " . $this->hook_method . "( $a ) {\n$r\n}\n" ;
  }

  static function is_action($plugin_item) {
		return $plugin_item->param_type == 'action';
  }

  static $form_types = array( 'widget' );

  function is_form_item() {
    return false ;
  }

  static function is_form($plugin_item) {
    return $plugin_item->is_form_item();
  }

  static function is_widget($plugin_item) {
		return $plugin_item->param_type == 'widget';
  }

  static function is_filter($plugin_item) {
		return $plugin_item->param_type == 'filter';
  }

  static function is_hook($plugin_item) {
    return $plugin_item->param_type === 'action' || $plugin_item->param_type == 'filter';
  }

  static function is_source_file($plugin_item) {
		return $plugin_item->param_type == 'plugin_source' ;
  }

	static function is_generated_file($plugin_item) {
		return !empty($plugin_item->generated) && $plugin_item->generated ;
	}

	static function is_external_file($plugin_item) {
		return PDEPluginItem::is_source_file($plugin_item) && !$plugin_item->generated ;
	}

  static function get($id) {
    $post = get_post( $id );
    if ( !$post || is_wp_error( $post ) )
      return new WP_Error( 'post_not_available', sprintf( __('The plugin item <strong>%d</strong> is not available'), $id ) );
        
    return PDEPluginItem::setup( $post );
  }

  static function create( $plugin_id, $title, $param_type, $param_args, $content = '') {
    $binary = empty( $param_args['binary'] ) ? false : true ;

    $post = array(
			'ID' => 0,
      'menu_order' => 0,
      'ping_status' => 0,
      'post_content' => $binary ? addcslashes( convert_uuencode( $content ), "\\" ) : $content ,
      'post_excerpt' => serialize( array ( 'type' => $param_type, 'args' => $param_args) ),
      'post_parent' => 0,
      'post_title' => $title,
      'post_type' => 'pde_plugin_item',
			'post_status' => 'publish',
    );

    $post['tax_input'] = array( 'pde_plugin' => array( intval( $plugin_id ) ) );

    $plugin_item_db_id = wp_insert_post( $post );
    if ( ! $plugin_item_db_id || is_wp_error( $plugin_item_db_id ) )
      return new WP_Error( 'post_not_created', sprintf( __('The plugin item <strong>%s</strong> could not be created'), $title ) );

    $post = get_post( $plugin_item_db_id );
    if ( ! $post || is_wp_error( $post ) )
      return new WP_Error( 'post_not_available', sprintf( __('The plugin item <strong>%s</strong> could not be read back.'), $title ) );

    return PDEPluginItem::setup( $post );
  }

  static function setup( $post ) {
    if ( !$post || is_wp_error( $post ) )
      return false ;

		$param_type = unserialize( $post->post_excerpt );
    if( $param_type['type'] == 'widget' )
      $plugin_item = new PDEWidget;
    else if( in_array( $param_type['type'], PDEPluginItem::$form_types ) )
      $plugin_item = new PDEForm;
    else {
		  $plugin_item = new PDEPluginItem;
      $filter = 'pde_custom_plugin_item_new_item_for_' . $param_type['type'] ;
      $plugin_item = apply_filters( $filter, $plugin_item );
    }

    $plugin_item->db_id = (int) $post->ID;
    $plugin_item->title = $post->post_title;
    $plugin_item->param_type = $param_type['type'] ;
		if ($param_type['args'])
			foreach ($param_type['args'] as $key => $value) {
				$plugin_item->$key = $value ;
    }
    if( !empty( $plugin_item->binary ) )
      $plugin_item->content = convert_uudecode( $post->post_content ) ;
    else
      $plugin_item->content = $post->post_content ;

    return $plugin_item;
  }

  static function isa( $plugin_item_id = 0 ) {
    return ( ! is_wp_error( $plugin_item_id ) && ( 'pde_plugin_item' == get_post_type( $plugin_item_id ) ) );
  }

  function update( $args, &$messages ) {
    $post = get_post( $this->db_id );
    if ( ! $post || is_wp_error( $post ) ) {
      WpPDEPlugin::messages( 'error', sprintf( __('The plugin item <strong>%s</strong> could not be read back.'), $this->title ), $messages );
      return ;
    }

		$param_type = unserialize( $post->post_excerpt );
 
    $current_args = empty($param_type['args']) ? array() : $param_type['args'] ;
    foreach( $args as $key => $value ) {
      if( empty( $value ) )
        $current_args[$key] = '';
      else
        $current_args[$key] = $value ;
    }

    $param_type['args'] = $current_args;
    $post_excerpt = serialize( $param_type );

    $id = wp_update_post( array( 'ID' => $post->ID, 'post_excerpt' => $post_excerpt ) );
    if( !$id )
      WpPDEPlugin::messages('error', 'updated failed');
  }

}
?>

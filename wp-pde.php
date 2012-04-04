<?php
/*
Plugin Name: WpPDE
Plugin URI: http://marathontesting.com
Description: Plugin development environment for Wordpress
Version: 0.9
Author: Dakshinamurthy Karra
Author URI: http://marathontesting.com
License: GPL2
*/
/*  Copyright 2012  Dakshinamurthy Karra (dakshinamurthy.karra@jaliansystems.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define ('WP_PDE_PATH', dirname(__FILE__) . '/');

require_once WP_PDE_PATH . 'main/pde-plugin.php';

/**
 * The WpPDE plugin.
 *
 * Initialize the plugin and attach the WpPDE page
*/
class WpPDEPlugin {

  /**
   * Extract the required parameters or die.
   */

  static function ajax_params($pairs, $atts, &$messages) {
    $atts = (array)$atts;
    $out = array();
    foreach($pairs as $name) {
      if ( array_key_exists($name, $atts) )
        $out[$name] = $atts[$name];
      else {
        WpPDEPlugin::messages('error', sprintf(__('Internal Error: Ajax request required argument %s is missing'), $name), $messages);
        return false;
      }
    }
    return $out;
  }

  /**
   * Hook for admin_init: register our scripts and styles
   */
  static function admin_init(){
    wp_register_script('wp-pde', plugins_url( 'main/js/wp-pde.dev.js', __FILE__), array('jquery-ui-sortable'));
    wp_register_script('ace_0.2', plugins_url( 'main/js/ace-0.2/ace-noconflict.js', __FILE__));
    wp_register_script('ace_0.2-mode-php', plugins_url( 'main/js/ace-0.2/mode-php-noconflict.js', __FILE__));
    wp_register_script('ace_0.2-mode-markdown', plugins_url( 'main/js/ace-0.2/mode-markdown-noconflict.js', __FILE__));
    wp_register_style('wp-pde', plugins_url( 'main/css/wp-pde.dev.css', __FILE__));
    wp_register_style('wp-pde-colors', plugins_url( 'main/css/colors.dev.css', __FILE__));
  }

  /**
   * Register our post types
   */
  static function register_ww_types() {
    register_taxonomy( 'pde_plugin', array ('pde_plugin_item'), array(
      'public' => false,
      'hierarchical' => false,
      'labels' => array(
        'name' => __( 'PluginPDE Plugins' ),
        'singular_name' => __( 'PluginPDE Plugin' ),
      ),
      'query_var' => false,
      'rewrite' => false,
      'show_ui' => false,
      '_builtin' => false,
      'show_in_nav_menus' => false,
    ) );
    register_post_type( 'pde_plugin_item', array(
      'labels' => array(
        'name' => __( 'PluginPDE Plugin Items' ),
        'singular_name' => __( 'PluginPDE Plugin Item' ),
      ),
      'public' => false,
      '_builtin' => false,
      'hierarchical' => false,
      'rewrite' => false,
      'query_var' => false,
    ) );
  }

  /**
   * Hook for admin_menu: Adds main page menu and setup action for loading time (if needed)
   */
  static function admin_menu(){
      $page = add_menu_page('Wp Plugin Development Environment', 'WpPDE', 'activate_plugins', 'wp_pde', array('WpPDEPlugin', 'render_page'),
                             plugins_url( 'main/images/wp-pde-icon16.png', __FILE__));
      add_action('load-' . $page, array('WpPDEPlugin', 'load_page'));
  }

  static function load_page(){
		PDEPlugin::ww_pde_plugin_setup(get_current_screen());
	}

  /**
   * Callback from add_menu_page: Renders our page
   */
  static function render_page(){
    require_once(dirname(__FILE__) . '/main/wp-pde-plugin-page.php');
  }

  /************** AJAX Methods ********************/

  /**
   * Hook for wp_ajax_add-form-item: Called when a plugin item (fields: text, textarea, password etc.) is added.
   */
  static function ajax_add_form_item() {
    if ( ! is_super_admin() )
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', "You don't have permissions to do that :("), '');

    if ( get_magic_quotes_gpc() ) {
      $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
    }

    $messages = array();
    $form_item_data = WpPDEPlugin::ajax_params(array('plugin_id', 'form_id', 'param_type', 'title', 'html_option', 'position'), $_REQUEST, $messages);
    if (!$form_item_data)
      WpPDEPlugin::json_die('error', $messages, '');

    if (!check_ajax_referer( 'add_form_item_' . $form_item_data['plugin_id'], 'add_form_item_nonce', false ))
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', "You don't have permissions to do that :("), '');
      
    $args = WpPDEPlugin::ajax_params(array('param_type', 'title', 'html_option', 'position'), $_REQUEST, $messages);
    $item = PDEFormItem::create( $form_item_data['form_id'], $form_item_data['plugin_id'], $args, $messages );

    if ( is_wp_error( $item ) )
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', $item->get_error_message()), '');

    $args = array( 'after' => '', 'before' => '', 'link_after' => '', 'link_before' => '', 'walker' => new Walker_PDE_Form,);
    $r = Walker_PDE_Form::walk_tree( array($item), 0, (object) $args);
    WpPDEPlugin::json_die(!empty($r) ? 'success' : 'error', $messages, $r);
  }

  /**
   * Hook for wp_ajax_add-pdeplugin-item: Called when a action/filter is added.
   */
  static function ajax_add_pdeplugin_item() {
    if ( ! is_super_admin() )
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', "You don't have permissions to do that :("), '');

    if ( get_magic_quotes_gpc() ) {
      $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
    }

    $messages = array();
    $args = WpPDEPlugin::ajax_params(array ( 'plugin_id', 'pluginitem_type', 'pluginitem_name', 'item_args'), $_REQUEST, $messages) ;
    if (!$args)
      WpPDEPlugin::json_die('error', $messages, '');

    if (!check_ajax_referer( 'add-pdeplugin-item-' . $_REQUEST['plugin_id'], 'add-pdeplugin-item-nonce', false))
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', "You don't have permissions to do that :("), '');

    $plugin = PDEPlugin::get($_REQUEST['plugin_id']);

    if ( is_wp_error( $plugin ) )
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', $plugin->get_error_message()), '') ;

    $item_args = wp_parse_args( $args['item_args'], array() );

    if ($args['pluginitem_type'] == 'widget')
      $action_item = $plugin->create_widget($args['pluginitem_name'], $messages);
    else if( $args['pluginitem_type'] == 'action' || $args['pluginitem_type'] == 'filter' ) {
      $action_item = $plugin->create_hook($args['pluginitem_type'], $args['pluginitem_name'], $item_args, $messages) ;
    }
    else {
      $custom_action = 'pde_custom_plugin_item_create_' . $args['pluginitem_type'] ;
      if( has_filter( $custom_action ) )
        $action_item = apply_filters( $custom_action, null, $args['pluginitem_name'], $item_args, $plugin );
      else
        $action_item = new WP_Error('unknown-plugin-item', sprintf( __( 'Plugin item %s is not known' ), $args['pluginitem_type']) );
    }

    if ( is_wp_error( $action_item ) ) {
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', $action_item->get_error_message(), $messages), '') ;
    }

    global $pde_plugin_selected_id;
    $pde_plugin_selected_id = $args['plugin_id'];

    $r = $plugin->_emit_editor_actions($args['pluginitem_type'], $action_item->db_id);
    WpPDEPlugin::json_die(!empty($r) ? 'success' : 'error', $messages, $r);
  }

  /**
   * Hook for wp_ajax_save-file-contents: Called when editor contents are saved.
   */
  static function ajax_save_file_contents() {
    if ( !is_super_admin() )
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', "You don't have permissions to do that :("), '');

    if ( get_magic_quotes_gpc() ) {
      $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
    }

    $messages = array();
    $args = WpPDEPlugin::ajax_params(array ('file_id', 'newcontent', 'form_data', 'source'), $_REQUEST, $messages) ;
    if (!$args)
      WpPDEPlugin::json_die('error', $messages, '');

    if (!check_ajax_referer( 'save-file-contents-' . $args['file_id'], 'save-file-contents-nonce', false))
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', "You don't have permissions to do that :("), '');

    $item = PDEPluginItem::get ( $args['file_id'] ) ;
    if ( PDEPluginItem::is_form( $item ) && $args['source'] == 'false') {
      $r = $item->update_form_items ( wp_parse_args($args['form_data'], array()), $messages ) ;
    } else {
      $r = $item->update_source($args['newcontent'], $messages) ;
    }
    WpPDEPlugin::json_die($r ? 'success' : 'error', $messages, '');
  }

  /**
   * Hook for wp_ajax_edit-file-contents: Called when a file is selected to edit in the editor.
   */
  static function ajax_edit_file() {
    if ( ! is_super_admin() )
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', "You don't have permissions to do that :("), '');

    if ( get_magic_quotes_gpc() ) {
      $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
    }

    $messages = array();
    $args = WpPDEPlugin::ajax_params(array ('file_id'), $_REQUEST, $messages) ;
    if (!$args)
      WpPDEPlugin::json_die('error', $messages, '');

    if (!check_ajax_referer('edit-file-' . $args['file_id'], '_wpnonce', false))
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', "You don't have permissions to do that :("), '');

    $item = PDEPluginItem::get( $args['file_id'] );
    if ( is_wp_error( $item ) ) {
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', "Unable to access file: " . $item->get_error_message()), '');
    }
    $mime_type = 'unknown' ;
		$mode = 'readwrite' ;
		$ace_mode = 'php';
    $can_add_items = false ;
    if (PDEPluginItem::is_source_file( $item ) || PDEPluginItem::is_hook( $item ) ||
            (PDEPluginItem::is_form( $item ) && isset( $_REQUEST['form-source'] ))) {
      $r = $item->get_source( $messages ) ;
      $mime_type = "text" ;
			if( $item->title == 'readme.txt' )
				$ace_mode = 'markdown';
			else if( PDEPluginItem::is_hook( $item ) ) {
				$ace_mode = 'php' ;
			} else {
				$ext = pathinfo($item->title, PATHINFO_EXTENSION);
				if( $ext == 'php' )
					$ace_mode = 'php' ;
        else if( $ext == 'css' )
          $ace_mode = 'css' ;
        else if( $ext == 'js' )
          $ace_mode = 'javascript';
        else if( $ext == 'md' )
          $ace_mode = 'markdown';
			}

			if(PDEPluginItem::is_generated_file( $item ) )
				$mode = 'readonly' ;
    } else if ( PDEPluginItem::is_form ( $item ) ) {
      $r =  $item->get_edit_markup();
      $mime_type = 'form-markup';
      $can_add_items = $item->get_can_add_items();
    }
    else {
      $r = 'Unknown stuff';
      WpPDEPlugin::messages('error', var_export($item, true), $messages);
    }

		if(PDEPluginItem::is_generated_file( $item ) )
			$mode = 'readonly' ;

    if ($r !== false) {
      global $current_user ;
      $terms = wp_get_post_terms ( $args['file_id'], 'pde_plugin' );
      if ( $terms && !empty ($terms) ) {
        $plugin_id = current( $terms )->term_id ;
        update_user_meta ( $current_user->ID, 'pde_file_recently_edited-' . $plugin_id, $args['file_id'] );
      }
    }

    WpPDEPlugin::json_die($r !== false ? 'success' : 'error', $messages, $r,
			array('file_id' => $args['file_id'], 'save_nonce' => wp_create_nonce('save-file-contents-'.$args['file_id']), 'mime-type' => $mime_type, 'mode' => $mode,
							'ace_mode' => $ace_mode, 'can_add_items' => $can_add_items));
  }

  /**
   * Hook for wp_ajax_delete-file: Called when a file is trashed
   */
  static function ajax_delete_file() {
    if ( ! is_super_admin() )
      die('-1');

    if ( get_magic_quotes_gpc() ) {
      $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
    }

    $messages = array();
    $args = WpPDEPlugin::ajax_params(array ('file_id', 'plugin_id'), $_REQUEST, $messages) ;
    if (!$args)
      WpPDEPlugin::json_die('error', $messages, '');

    if (!check_ajax_referer('delete-file-' . $args['file_id'], '_wpnonce', false))
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', "You don't have permissions to do that :("), '');
    

    $item = PDEPluginItem::get( $args['file_id'] );
    if( is_wp_error( $item ) )
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', $item->get_error_message(), $messages), '');
    $r = $item->delete_source($messages, $args['plugin_id']);
    WpPDEPlugin::json_die($r ? 'success' : 'error', $messages, '');
  }

  /**
   * Hook for wp_ajax_delete-item: Called when a item is removed from the UI.
   */
  static function ajax_delete_item() {
    if ( ! is_super_admin() )
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', "You don't have permissions to do that :("), '');

    if ( get_magic_quotes_gpc() ) {
      $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
    }

    $messages = array();
    $args = WpPDEPlugin::ajax_params(array ('item_id'), $_REQUEST, $messages) ;
    if (!$args)
      WpPDEPlugin::json_die('error', $messages, '');

    if (!check_ajax_referer('delete-item-' . $args['item_id'], '_wpnonce', false))
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', "You don't have permissions to do that :("), '');

		$item = PDEFormItem::get( $args['item_id'], $messages );
		if (!$item)
    	WpPDEPlugin::json_die('error', $messages, '');

    $r = $item->delete($messages);
    WpPDEPlugin::json_die($r ? 'success' : 'error', $messages, '');
  }

  static function ajax_download_plugin() {
    if ( ! is_super_admin() )
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', "You don't have permissions to do that :("), '');

    if ( get_magic_quotes_gpc() ) {
      $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
    }

    $messages = array();
    $args = WpPDEPlugin::ajax_params(array ('plugin_id'), $_REQUEST, $messages) ;
    
    if (!$args)
      WpPDEPlugin::json_die('error', $messages, '');

    if (!check_ajax_referer('download-plugin-' . $args['plugin_id'], '_wpnonce', false))
      WpPDEPlugin::json_die('error', WpPDEPlugin::messages('error', "You don't have permissions to do that :("), '');

    require_once dirname (__FILE__) . '/main/download.php' ;

  }

  static function json_die($error, $messages, $data, $extra = array()) {
    $message = implode("\n", $messages);
    $r = array_merge(array('error' => $error, 'message' => $message, 'data' => !$data ? '' : $data), $extra);
    echo json_encode($r);
    die();
  }

  static function messages($error, $message, &$messages = false) {
    if (!$messages)
      $messages = array();
    $messages[] = '<div class="' . $error . '"><p>' .  $message . '</p></div>';
    return $messages;
  }

  static function update_user_meta($meta_id, $obj_id, $meta_key, $meta_value) {
    if( !in_array( $meta_key, array('metaboxhidden_toplevel_page_wp_pde', 'meta-box-order_toplevel_page_wp_pde') ) )
      return ;
    global $current_user ;

    $recently_edited = (int) get_user_option( 'pde_plugin_recently_edited' );
    if( !$recently_edited )
      return ;
    $meta_key = 'wp_pde_' . $meta_key . '_' . $recently_edited ;
		update_user_meta( $current_user->ID, $meta_key, $meta_value );
  }

  static function get_metabox_hidden($result, $option) {
    $default_hidden_metaboxes = array( 'wp-pde-options', 'add-meta-information', 'add-external-files');

    $plugin_id = isset( $_REQUEST['plugin'] ) ? (int) $_REQUEST['plugin'] : 0 ;
    if( $plugin_id == 0 ) {
      $recently_edited = (int) get_user_option( 'pde_plugin_recently_edited' );
      if( !$recently_edited )
        return $default_hidden_metaboxes;
      $plugin_id = $recently_edited;
    }
    $option = 'wp_pde_' . $option . '_' . $plugin_id ;
    $hidden = get_user_option( $option );
    if( $hidden === false )
      return $default_hidden_metaboxes;
    return $hidden;
  }

  static function get_metabox_order($result, $option) {
    $plugin_id = isset( $_REQUEST['plugin'] ) ? (int) $_REQUEST['plugin'] : 0 ;
    if( $plugin_id == 0 )
      return false ;
    $option = 'wp_pde_' . $option . '_' . $plugin_id ;
    $order = get_user_option( $option );
    if( !$order )
      return false;
    return $order;
  }

}

WpPDEPlugin::register_ww_types();
PDEPlugin::load_test_plugins();
add_action('admin_init', array('WpPDEPlugin', 'admin_init'));
add_action('admin_menu', array('WpPDEPlugin', 'admin_menu'));

add_action('wp_ajax_add-form-item', array('WpPDEPlugin', 'ajax_add_form_item'));
add_action('wp_ajax_add-pdeplugin-item', array('WpPDEPlugin', 'ajax_add_pdeplugin_item'));

add_action('wp_ajax_save-file-contents', array('WpPDEPlugin', 'ajax_save_file_contents'));
add_action('wp_ajax_wp-pde-edit-file', array('WpPDEPlugin', 'ajax_edit_file'));
add_action('wp_ajax_wp-pde-delete-item', array('WpPDEPlugin', 'ajax_delete_item'));
add_action('wp_ajax_delete-file', array('WpPDEPlugin', 'ajax_delete_file'));

add_filter('admin_body_class', array('PDEForm', 'max_depth'));

add_action('wp_ajax_download-plugin', array('WpPDEPlugin', 'ajax_download_plugin'));

add_action('update_user_meta', array('WpPDEPlugin', 'update_user_meta'), 10, 4);
add_filter('get_user_option_metaboxhidden_toplevel_page_wp_pde', array('WpPDEPlugin', 'get_metabox_hidden'), 10, 2);
add_filter('get_user_option_meta-box-order_toplevel_page_wp_pde', array('WpPDEPlugin', 'get_metabox_order'), 10, 2);

do_action( 'pde_plugin_loaded', '' );
?>

<?php

function _bt($bt) {
  $str = '';
  $r = false ;
  foreach($bt as $item) {
    if (isset($item['file']) && isset($item['line']))
      $str .= $item['file'] . ':' . $item['line'] . "\n";
    if (strpos($str, 'wp-pde.php') !== false)
      $r = true ;
  }
  if (!$r) return $r;
  return $str;
}

function stacktrace_error_handler($errno, $errstr, $errfile, $errline)
{
    if($errno) {
      if (strpos($errstr, "Walker") === false) {
        $bt = _bt(debug_backtrace());
        if ($bt) {
          js_debug("Error $errstr: $errfile:$errline\ncalled from:");
				  js_debug(_bt(debug_backtrace()));
          wp_die('I am done with this');
        }
      }
    }
    return true; // to execute the regular error handler
}
// set_error_handler("stacktrace_error_handler");

if (!function_exists('js_debug')) {
  function js_debug($m) {
  ?>
    <script type="text/javascript" language="javascript">
    //<![CDATA[
    console.log("<?php echo esc_js($m); ?>");
    //]]>
    </script> 
  <?php
  }
}

if (!function_exists('wp_get_current_user')) {
  require_once ABSPATH . 'wp-includes/pluggable.php';
}

require_once dirname(__FILE__) . '/pde-plugin-item.php' ;
require_once dirname(__FILE__) . '/pde-form-walker.php' ;
require_once dirname(__FILE__) . '/pde-form.php' ;
require_once dirname(__FILE__) . '/pde-form-item.php' ;
require_once dirname(__FILE__) . '/pde-widget.php' ;

require_once dirname(__FILE__) . '/pde-radio.php' ;
require_once dirname(__FILE__) . '/pde-dropdown.php' ;

if( !function_exists( 'Markdown' ) ) {
  if( isset($wp_version) ) {
    $wp_version_bak = $wp_version ;
    unset( $wp_version );
  }
  require 'php-markdown-1.0.1o/markdown.php';
  if( isset($wp_version_bak) ) {
    $wp_version = $wp_version_bak ;
    unset( $wp_version_bak );
  }
}

class PDEPlugin {

  static function get($plugin_id) {
    if ( ! $plugin_id || !($term = get_term($plugin_id, 'pde_plugin')))
      return new WP_Error( 'plugin_not_available', sprintf( __('The plugin id <strong>%d</strong> is not available'), $plugin_id ) );

    return PDEPlugin::_setup( $term );
  }

  static function get_all( ) {
    // This is so wrong!!!. We are turning off the cache by setting a different cache_domain for each call.
    // This looks like a quick fix (for the time being) for the bug where PDEPlugin::get_all() returns plugin that
    // is just deleted and created.
    $args = array( 'hide_empty' => false, 'orderby' => 'name', 'cache_domain' => microtime() );
    return array_map( array ('PDEPlugin', '_setup'), get_terms( 'pde_plugin',  $args) );
  }

  static function _setup( $term ) {
    $a = unserialize($term->name);
    $plugin = new PDEPlugin;
    $plugin->plugin_id = $term->term_id;
    $plugin->plugin_name = $a['name'];
    $plugin->plugin_version = $a['version'];
    return $plugin;
  }

  static $testing_plugin = false;

  static function load_test_plugins() {
    $_pde_plugins = PDEPlugin::get_all();
    foreach( $_pde_plugins as $index => $plugin_object ) {
      $options = (array) $plugin_object->get_options();
      $project_dir = $plugin_object->get_project_dir();

      $plugin_test = isset($options['test']);
      if (! $plugin_test )
        continue ;

      $plugin_file = $project_dir . '/' . $plugin_object->get_plugin_file();
      if (!is_readable($plugin_file)) {
        continue ;
      }
      $testoption = 'test_'. str_replace('-', '_', sanitize_title_with_dashes($plugin_file)) ;
      if (isset($options[$testoption])) {
        $plugin_object->update_option('test', false);
        $plugin_object->update_option($testoption, false);
        continue;
      }
      $plugin_object->update_option($testoption, true);
      register_shutdown_function(array('PDEPlugin', 'shutdown'));
      PDEPlugin::$testing_plugin = true ;
      include $plugin_file;
      PDEPlugin::$testing_plugin = false ;
      $plugin_object->update_option($testoption, false);
    }
  }

  static function shutdown() {
    $last_error = error_get_last();
    if (PDEPlugin::$testing_plugin && $last_error) {
      echo "<br/><strong>Plugin code generated an error. No worry, refresh the page and the plugin will be disabled</strong><br/>";
    }
  }

  static function create($plugin_data, &$messages) {

    $name_parts = array ('name' => $plugin_data['plugin-name'],
                                  'version' =>( isset( $plugin_data['plugin-version']   ) ? $plugin_data['plugin-version']    : '' ));
    $term_name = serialize($name_parts);
    $args = array(
      'description' => '',
      'name'        => $term_name,
      'parent'      => 0,
      'slug'        => sanitize_title($name_parts['name'] . ' v' . $name_parts['version']),
    );

    if ( get_term_by( 'name', $term_name, 'pde_plugin' ) )
      return new WP_Error( 'plugin_exists', sprintf( __('The plugin name <strong>%s</strong> conflicts with another plugin name. Please try another.'), esc_html( $plugin_data['plugin-name'] ) ) );

    $term = wp_insert_term( $term_name, 'pde_plugin', $args );

    if ( is_wp_error( $term ) )
      return $term;

    return PDEPlugin::_setup( get_term( $term['term_id'], 'pde_plugin' ) );
  }

  function update( $plugin_data = array() ) {
    $plugin_name = ( isset( $plugin_data['plugin-name']   ) ? $plugin_data['plugin-name']    : $this->plugin_name );
    $plugin_version = ( isset( $plugin_data['plugin-version']   ) ? $plugin_data['plugin-version']    : $this->plugin_version );

    if ($plugin_name == $this->plugin_name && $plugin_version == $this->plugin_version)
      return $this ;

    $term_name = serialize(array ('name' =>( isset( $plugin_data['plugin-name']   ) ? $plugin_data['plugin-name']    : $this->plugin_name ),
                                  'version' =>( isset( $plugin_data['plugin-version']   ) ? $plugin_data['plugin-version']    : $this->plugin_version )));

    $update_response = wp_update_term( $this->plugin_id, 'pde_plugin', array ( 'name' => $term_name ) );

    $existing = get_term_by( 'name', $term_name, 'pde_plugin' );
    if ( $existing && $existing->term_id != $this->plugin_id )
      return new WP_Error( 'plugin_exists', sprintf( __('The plugin name <strong>%s</strong> conflicts with another plugin name. Please try another.'), esc_html( $plugin_data['plugin-name'] ) ) );

    if ( is_wp_error( $update_response ) )
      return $update_response;

    $this->plugin_name = $plugin_name ;
    $this->plugin_version = $plugin_version ;

    return $this;
  }

  function get_options() {
    return (array) get_option('wp_pde_options[' . $this->plugin_id . ']', array());
  }

  function create_project(&$messages, $export_mode = 'test') {
    $project_dir = $this->get_project_dir($export_mode);
    if (! file_exists($project_dir) ) {
      if (!mkdir($project_dir, 0777)) {
        WpPDEPlugin::messages('error', sprintf(__('Error creating project directory %s.'), $project_dir), $messages);
        return;
      }
    }

    $this->export_project($project_dir, $export_mode, $messages);
  }

  function export_project($project_dir, $export_mode, &$messages) {
    // Set the variables used in the template
    $plugin = $this ;
    $plugin_name = $this->plugin_name ;
    $plugin_version = $this->plugin_version;
    $plugin_classname = $this->get_classname();

    $meta = $this->get_meta();
    extract($meta);

		$toremove = $this->get_generated_file_items();

		$this->generate_file(dirname(__FILE__) . '/templates/plugin.php.php', $this->get_plugin_file(), $export_mode, array( 'plugin' => $this), $toremove);

    if ($meta_provided && $meta_use_for_readme) {
			$this->generate_file(dirname(__FILE__) . '/templates/readme.txt.php', 'readme.txt', $export_mode, array( 'plugin' => $this), $toremove);
    }

    if ($meta_provided && $meta_use_for_md_readme) {
			$this->generate_file(dirname(__FILE__) . '/templates/readme.md.php', 'readme.md', $export_mode, array( 'plugin' => $this), $toremove);
    }

    $this->generate_widgets( $export_mode, $toremove, $project_dir );

    $custom_plugin_items = apply_filters('pde_custom_plugin_items', array());
    foreach( $custom_plugin_items as $plugin_item ) {
      $filter = 'pde_custom_plugin_item_generate_files_for_' . $plugin_item['value'] ;
      if( has_filter( $filter ) )
       $toremove = apply_filters( $filter, $toremove, $this, $export_mode, $project_dir ); 
    }

		foreach( $toremove as $item ) {
			$ignore_messages = array();
			$item->delete_source($ignore_messages, $this->plugin_id);
		}

    $plugin_items = $this->get_source_file_items();
    foreach ($plugin_items as $item) {
      $filename = $project_dir . '/' . $item->title ;
      if (dirname($filename) != $project_dir) {
        if( !is_dir( dirname( $filename ) ) )
          mkdir( dirname ($filename), 0777, true);
      }
      $fp = fopen($filename, "wb") ;
      fwrite ($fp, $item->content);
      fclose($fp);
			chmod( $filename, 0777 );
    }
  }

  function generate_widgets( $export_mode, &$toremove, $project_dir ) {
    $widgets = $this->get_widget_items();
    foreach( $widgets as $widget ) {
		  $this->generate_file(dirname(__FILE__) . '/templates/widget.php.php', 'widgets/' . $widget->get_widget_file(), $export_mode, array( 'plugin' => $this, 'widget' => $widget), $toremove);
      $theme_file = $widget->get_theme_file();
      if( $theme_file ) {
        if( !is_dir( $project_dir . '/widgets' ) )
          mkdir( $project_dir . '/widgets' );
        $dest = basename( $theme_file );
        copy( $theme_file, $project_dir . '/widgets/' . $dest );
      }
    }
  }

	function generate_file($template, $file, $export_mode, $args, &$toremove) {

		extract($args);

		ob_start();
		include($template);
		$content = ob_get_clean();
		$src_item = $this->get_source_item($file) ;
    $content = str_replace('<@php', '<?php', $content);
    $content = str_replace('@>', '?>', $content);
		if( !$src_item )
			$this->create_generated_file( $file, $content );
		else {
			$src_item->update_source($content, $messages);
      foreach( $toremove as $k => $remove )
        if ($src_item->db_id == $remove->db_id) {
          unset($toremove[$k]);
          break ;
        }
    }
	}

  static function get_projects_dir() {
    global $current_user;
    if ($current_user == NULL)
      $current_user = wp_get_current_user();
    $projects_dir = WP_PDE_PATH . 'projects/' . $current_user->user_login;
    // If the projects folder doesn't exist for the user, try to create it
    if (!is_readable($projects_dir))
      @mkdir($projects_dir, 0777, true);
    return $projects_dir ;
  }

  function get_project_dir( $mode = 'test' ) {
    $projects_dir = PDEPlugin::get_projects_dir();
    if( $mode == 'test' ) {
      $version = $this->plugin_version ? '-' . sanitize_file_name($this->plugin_version) : '';
      $project_dir_name = strtolower(sanitize_file_name($this->plugin_name . $version));
    } else {
      $project_dir_name = $mode . '-' . strtolower(sanitize_file_name($this->plugin_name));
    }
    return $projects_dir . '/' . $project_dir_name;
  }

  function delete_project(&$messages) {
    $this->rrmdir($this->get_project_dir());
  }

  function rrmdir($dir) {
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
       }
     }
     reset($objects);
     rmdir($dir);
   }
  }

  function get_available_file () {
    $widget_items = $this->get_widget_items();
    if ( !empty( $widget_items ) )
      return current( $widget_items )->db_id ;

    $action_items = $this->get_action_items();
    if ( !empty( $action_items ) )
      return current( $action_items )->db_id ;
    $filter_items = $this->get_filter_items();
    if ( !empty( $filter_items ) )
      return current ( $filter_items )->db_id ;
    $src_items = $this->get_source_file_items();
    if ( !empty( $src_items ) )
      return current ( $src_items )->db_id ;
    return false ;
  }

  function get_source_item($file_name) {
    $plugin_items = $this->get_source_file_items();
    foreach ($plugin_items as $item)
      if ($item->title == $file_name)
        return $item ;
    return false;
  }

  function get_action_id( $method_name ) {
    $plugin_items = $this->get_action_items();
    foreach ($plugin_items as $item)
      if( $item->title == $method_name )
        return $item->db_id ;
    return false;
  }

  function get_filter_id( $method_name ) {
    $plugin_items = $this->get_filter_items();
    foreach ($plugin_items as $item)
      if( $item->title == $method_name )
        return $item->db_id ;
    return false;
  }

  function get_option($option) {
    $options = get_option('wp_pde_options[' . $this->plugin_id . ']');
    if (!isset($options[$option]))
      return null;
    return $options[$option];
  }

  function update_option($option, $value) {
    $options = (array) get_option('wp_pde_options[' . $this->plugin_id . ']');
    if ($value)
      $options[$option] = $value ;
    else
      unset($options[$option]);
    update_option('wp_pde_options[' . $this->plugin_id . ']', $options);
  }

  function update_options($options) {
    update_option('wp_pde_options[' . $this->plugin_id . ']', $options);
  }

  function get_plugin_file() {
    return strtolower(sanitize_file_name($this->plugin_name)) . '.php';
  }

  function get_classname() {
    $suffix = '';
    if ($this->plugin_version)
      $suffix = '_v' . $this->plugin_version;
    return preg_replace('/[^a-zA-Z0-9_]/', '_', ucwords($this->plugin_name . $suffix));
  }

  static function isa( $plugin_id ) {
    if ( ! $plugin_id )
      return false;

    $plugin_obj = PDEPlugin::get( $plugin_id );

    return $plugin_obj && ! is_wp_error( $plugin_obj );
  }

  function delete(&$messages) {
    $plugin_objects = get_objects_in_term( $this->plugin_id, 'pde_plugin' );
    if ( ! empty( $plugin_objects ) ) {
      foreach ( $plugin_objects as $item ) {
        wp_delete_post( $item );
      }
    }

    $result = wp_delete_term( $this->plugin_id, 'pde_plugin' );

    delete_option ( 'wp_pde_options[' . $this->plugin_id . ']');

    $this->delete_project($messages);
    return $result;
  }

  function update_meta ($meta) {
      $meta   = array_map( 'stripslashes_deep', $meta );

    $defaults = array (
      "meta_provided" => true,
      "meta_short_description" => '',
      "meta_plugin_uri" => '',
      "meta_author" => '',
      "meta_author_uri" => '',
      "meta_copyright" => '',
      "meta_license" => '',
      "meta_license_blurb" => '',
      "meta_contributors" => '',
      "meta_donate_link" => '',
      "meta_tags" => '',
      "meta_requires_at_least" => '',
      "meta_tested_upto" => '',
      "meta_stable_tag" => '',
      "meta_long_description" => '',
      "meta_installation" => '',
      "meta_faq" => '',
      "meta_screenshots" => '',
      "meta_changelog" => '',
      "meta_upgrade_notice" => '',
      "meta_extra" => '',
      );
    $meta = shortcode_atts($defaults, $meta);
    $options = $this->get_options();
    $options = wp_parse_args($meta, $options);
    $this->update_options($options);
  }

  function get_meta() {
    $defaults = array (
      "meta_provided" => false,
			"meta_use_for_readme" => 'use_for_readme',
			"meta_use_for_md_readme" => 'use_for_md_readme',
      "meta_short_description" => '',
      "meta_plugin_uri" => '',
      "meta_author" => '',
      "meta_author_uri" => '',
      "meta_copyright" => '',
      "meta_license" => '',
      "meta_license_blurb" => '',
      "meta_contributors" => '',
      "meta_donate_link" => '',
      "meta_tags" => '',
      "meta_requires_at_least" => '',
      "meta_tested_upto" => '',
      "meta_stable_tag" => '',
      "meta_long_description" => '',
      "meta_installation" => '',
      "meta_faq" => '',
      "meta_screenshots" => '',
      "meta_changelog" => '',
      "meta_upgrade_notice" => '',
      "meta_extra" => '',
      );
    return shortcode_atts($defaults, $this->get_options());
  }

  static function ww_pde_plugin_setup($screen) {
	  add_meta_box( 'add-pdeplugin-items', __('Plugin Elements'), array('PDEPlugin', 'pde_plugin_add_pdeplugin_item_meta_box'), $screen, 'side', 'high');
	  add_meta_box( 'add-form-items', __('Form Elements'), array('PDEForm', 'pde_form_items_meta_box'), $screen, 'side', 'high' );
	  add_meta_box( 'add-external-files', __('External Files'), array('PDEPlugin', 'pde_plugin_external_files_meta_box'), $screen, 'side', 'low');
	  add_meta_box( 'wp-pde-options', __('Editor Options'), array('PDEPlugin', 'pde_plugin_options_meta_box'), $screen, 'side', 'low' );
	  add_meta_box( 'add-meta-information', __('About this Plugin'), array('PDEPlugin', 'pde_plugin_meta_info_meta_box'), $screen, 'normal', 'low');
	  add_filter( 'manage_'.$screen->id.'_columns', array('PDEPlugin', 'pde_plugin_manage_resources'));
    PDEPlugin::pde_plugin_setup_help($screen, 'wp_pde' );
  }

  static function pde_plugin_setup_help($screen, $menu_slug) {
    $help_tabs = array();
    $help_files = scandir( dirname( __FILE__ ) . '/help' );
    foreach( $help_files as $help_file ) {
      if( preg_match( '/^[^-]*-' . get_bloginfo('language') . '-' . $menu_slug . '-' . '.*.md$/', $help_file ) ) {
        $display = preg_replace( '/[^-]*-' . get_bloginfo('language') . '-' . $menu_slug . '-' . '/', '', basename( $help_file, '.md' ) );
        $display = ucwords( str_replace( '-', ' ', $display ) );
        $help_tabs[] = array( sanitize_html_class( $help_file ), $display, $help_file );
      } else if( get_bloginfo('language') . '-' . $menu_slug . '-sidebar.md' == $help_file ) {
        $sidebar = $help_file ;
      }
    }
    foreach( $help_tabs as $tab )
      $screen->add_help_tab( array(
        'id' => $tab[0],
        'title' => $tab[1],
        'content' => Markdown( file_get_contents( dirname( __FILE__ ) . '/help/' . $tab[2] ) )
      ) );

    if( isset( $sidebar ) )
      $screen->set_help_sidebar( Markdown( file_get_contents( dirname( __FILE__ ) . '/help/' . $sidebar ) ) );
  }

  static function go_pro() {
    if( class_exists( 'WpPDEProPlugin' ) )
      return ;
  ?>
    <div id="side-sortables" class="meta-box-sortables"><div id="add-pdeplugin-items" class="postbox " >
      <div class="handlediv" title="Click to toggle"><br /></div><h3 class='hndle'><span>Go Pro</span></h3>
        <div class="inside">
          <p>
            Want to create custom taxonomies, post types, mataboxes and admin pages using PDE?
          </p>
          <p>
            <a href="http://wp-pde.jaliansystems.com/wp-pde-pro-add-on-pack-for-wppde/">WpPDE Pro</a> plugin adds this functionality (and more)  to your
            PDE installation.
          </p>
          <p style="text-align:center"><a href="http://wp-pde.jaliansystems.com/buy-now/" class="submit button-primary">Buy Now</a></p>
        </div>
      </div>
    </div>
  <?php
  }

  static function pde_plugin_add_pdeplugin_item_meta_box( $args ) {

    extract ( $args );
    $plugin_id = $plugin ? $plugin->plugin_id : 0 ;
    ?>
<div class="pdepluginitemdiv" id="pdepluginitemdiv">

  <?php wp_nonce_field('add-pdeplugin-item-' . $plugin_id, 'add-pdeplugin-item-nonce'); ?>
  <input type="hidden" value="<?php echo $plugin_id; ?>" name="plugin" />

  <p id="pdeplugin-item-type-wrap">
    <label class="metabox-side-label" for="pdeplugin-item-type"><?php _e('Type:'); ?></label>
      <select id="pdeplugin-item-type" name="pluginitem_type" class=" widefat metabox-side-input">
        <option value="action" selected><?php _e('Action'); ?></option>
        <option value="filter" ><?php _e('Filter'); ?></option>
        <option value="widget" ><?php _e('Widget'); ?></option>
  <?php
  $custom_plugin_items = apply_filters('pde_custom_plugin_items', array());
  foreach( $custom_plugin_items as $plugin_item ) {
          echo "          <option value='". esc_attr($plugin_item['value']) . "' >" . esc_attr($plugin_item['display']) . "</option>\n";
  }
  ?>
      </select>
  </p>
  <script type="text/javascript">
  window.onload=function(){jQuery('#pdeplugin-item-type').val('action');}
  </script>

    <div id="pdeplugin-item-action-filter-params">
      <p id="pdeplugin-item-name-wrap" class="pdeplugin-item-p-param">
        <label class="metabox-side-label" for="pdeplugin-item-name">
          <span>
            <span class='pdeplugin-item-optional enable-for-action'><?php _e('Action:'); ?></span>
            <span style="display:none" class='pdeplugin-item-optional enable-for-filter'><?php _e('Filter:'); ?></span>
            <span style="display:none" class='pdeplugin-item-optional enable-for-widget'><?php _e('Widget:'); ?></span>
  <?php
  foreach( $custom_plugin_items as $plugin_item ) {
  ?>
            <span style='display:none' class='pdeplugin-item-optional enable-for-<?php echo $plugin_item["value"]; ?>'><?php _e($plugin_item["name"]); ?>:</span>
  <?php
  }
  ?>
          </span>
        </label>
          <input id="pdeplugin-item-name" name="pluginitem_name" type="text" class=" widefat metabox-side-input input-with-default-title" title="<?php esc_attr_e('Name'); ?>" />
      </p>

  <div id='pdeplugin-item-options-action-filter' class='pdeplugin-item-optional enable-for-action enable-for-filter'>
      <p id="pdeplugin-item-method-wrap" class="pdeplugin-item-p-param">
        <label class="metabox-side-label" for="pdeplugin-item-method">
          <span><?php _e('Method:'); ?></span>
        </label>
          <input id="pdeplugin-item-method" name="hook_method" type="text" class=" widefat metabox-side-input input-with-default-title" title="<?php esc_attr_e('Method Name'); ?>" />
      </p>

      <p id="pdeplugin-item-priority-wrap" class="pdeplugin-item-p-param">
        <label class="metabox-side-label" for="pdeplugin-item-priority">
          <span><?php _e('Priority:'); ?></span>
        </label>
          <input id="pdeplugin-item-priority" name="hook_priority" type="text" class=" widefat metabox-side-input" value="10"/>
      </p>

      <p id="pdeplugin-item-args-wrap" class="pdeplugin-item-p-param">
        <label class="metabox-side-label" for="pdeplugin-item-args">
          <span><?php _e('Number of Arguments:'); ?></span>
        </label>
          <input id="pdeplugin-item-args" name="hook_args" type="text" class=" widefat metabox-side-input" value="1"/>
      </p>
    </div>
  </div>

  <?php
  $custom_plugin_items = apply_filters('pde_custom_plugin_items', array());
  foreach( $custom_plugin_items as $plugin_item ) {
    $r = apply_filters( 'pde_custom_plugin_item_get_add_markup_for_' . $plugin_item['value'], '' );
    if( !empty( $r ) ) {
?>
  <div style="display:none;" id='pdeplugin-item-options-<?php echo $plugin_item["value"]; ?>' class='pdeplugin-item-optional enable-for-<?php echo $plugin_item["value"]; ?>'>
  <?php echo $r; ?>
  </div>
<?php
    }
  }
  ?>
  <p class="button-controls">
    <span class="add-to-plugin">
      <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
      <input type="submit"<?php disabled( $plugin_id, 0 ); ?> class="button-secondary submit-add-to-plugin" value="<?php esc_attr_e('Add to Plugin'); ?>" name="add-pdeplugin-item" id="submit-pdepluginitemdiv" />
    </span>
  </p>

</div><!-- /.pdepluginitemdiv -->
	<?php
}

  static function pde_plugin_meta_info_meta_box( $args ) {

    extract( $args );
    $meta = $plugin->get_meta();
    extract($meta);
	?>
<div class="metadiv" id="metadiv"><form id="meta-information-form" action="#" method="post" enctype="multipart/form-data">
    <input type="hidden" value="update-meta" name="action" />
    <input type="hidden" value="<?php echo $plugin->plugin_id; ?>" name="plugin" />
    <?php wp_nonce_field('update-meta-' . $plugin->plugin_id) ; ?>
    <p id="metadiv-use-for-readme-wrap">
      <input id="metadiv-use-for-readme" name="meta_use_for_readme" <?php checked( $meta_use_for_readme, 'use_for_readme'); ?> value="use_for_readme"  type="checkbox" /><label class="metabox-normal-label" for="metadiv-use-for-readme"><?php _e(' Use this information to generate readme.txt'); ?></label>
    </p>

    <p id="metadiv-use-for-readme-md-wrap">
      <input id="metadiv-use-for-readme-md" name="meta_use_for_readme-md" <?php checked( $meta_use_for_md_readme, 'use_for_md_readme'); ?> value="use_for_md_readme"  type="checkbox" /><label class="metabox-normal-label" for="metadiv-use-for-md-readme"><?php _e(' Use this information to generate readme.md'); ?></label>
    </p>

    <p id="metadiv-description-wrap">
      <label class="metabox-normal-label" for="metadiv-description"><?php _e('Description:'); ?></label>
        <input id="metadiv-description" name="meta_short_description" value="<?php echo esc_attr( $meta_short_description ); ?>"  type="text" class=" widefat metabox-normal-input "  />
    </p>

    <p id="metadiv-plugin-uri-wrap">
      <label class="metabox-normal-label" for="metadiv-plugin-uri"><?php _e('Plugin URI:'); ?></label>
        <input id="metadiv-plugin-uri" name="meta_plugin_uri" value="<?php echo esc_attr( $meta_plugin_uri ); ?>"  type="text" class=" widefat metabox-normal-input "  />
    </p>

    <p id="metadiv-author-wrap">
      <label class="metabox-normal-label" for="metadiv-author"><?php _e('Author:'); ?></label>
        <input id="metadiv-author" name="meta_author" value="<?php echo esc_attr( $meta_author ); ?>"  type="text" class=" widefat metabox-normal-input "  />
    </p>

    <p id="metadiv-author-uri-wrap">
      <label class="metabox-normal-label" for="metadiv-author-uri"><?php _e('Author URI:'); ?></label>
        <input id="metadiv-author-uri" name="meta_author_uri" value="<?php echo esc_attr( $meta_author_uri ); ?>"  type="text" class=" widefat metabox-normal-input "  />
    </p>

    <p id="metadiv-copyright-wrap">
      <label class="metabox-normal-label" for="metadiv-copyright"><?php _e('Copyright:'); ?></label>
      <input id="metadiv-copyright" name="meta_copyright" value="<?php echo esc_attr( $meta_copyright ); ?>"  type="text" class=" widefat metabox-normal-input "  />
    </p>

    <p id="metadiv-license-wrap">
      <label class="metabox-normal-label" for="metadiv-license"><?php _e('License (code):'); ?></label>
        <input id="metadiv-license" name="meta_license" value="<?php echo esc_attr( $meta_license ); ?>"  type="text" class=" widefat metabox-normal-input "  />
    </p>

    <p id="metadiv-license-blurb-wrap">
      <label class="metabox-normal-label metabox-normal-label-full" for="metadiv-license-blurb"><?php _e('License (details):'); ?></label>
        <textarea id="metadiv-license-blurb" name="meta_license_blurb" class=" widefat metabox-normal-input metabox-normal-input-textarea" ><?php echo esc_textarea($meta_license_blurb); ?></textarea>
    </p>

    <p id="metadiv-contributors-wrap">
      <label class="metabox-normal-label" for="metadiv-contributors"><?php _e('Contributors:'); ?></label>
        <input id="metadiv-contributors" name="meta_contributors" value="<?php echo esc_attr( $meta_contributors ); ?>"  type="text" class=" widefat metabox-normal-input "  />
    </p>

    <p id="metadiv-donate-link-wrap">
      <label class="metabox-normal-label" for="metadiv-donate-link"><?php _e('Donate link:'); ?></label>
      <input id="metadiv-donate-link" name="meta_donate_link" value="<?php echo esc_attr( $meta_donate_link ); ?>"  type="text" class=" widefat metabox-normal-input metabox-normal-input-url "  />
    </p>

    <p id="metadiv-tags-wrap">
      <label class="metabox-normal-label" for="metadiv-tags"><?php _e('Tags:'); ?></label>
      <input id="metadiv-tags" name="meta_tags" value="<?php echo esc_attr( $meta_tags ); ?>"  type="text" class=" widefat metabox-normal-input "  />
    </p>

    <p id="metadiv-requires-at-least-wrap">
      <label class="metabox-normal-label" for="metadiv-requires-at-least"><?php _e('Requires at least:'); ?></label>
        <input id="metadiv-requires-at-least" name="meta_requires_at_least" value="<?php echo esc_attr( $meta_requires_at_least ); ?>"  type="text" class=" widefat metabox-normal-input metabox-normal-input-version "  />
    </p>

    <p id="metadiv-tested-upto-wrap">
      <label class="metabox-normal-label" for="metadiv-tested-upto"><?php _e('Tested upto:'); ?></label>
      <input id="metadiv-tested-upto" name="meta_tested_upto" value="<?php echo esc_attr( $meta_tested_upto ); ?>"  type="text" class=" widefat metabox-normal-input metabox-normal-input-version "  />
    </p>

    <p id="metadiv-stable-tag-wrap">
      <label class="metabox-normal-label" for="metadiv-stable-tag"><?php _e('Stable tag:'); ?></label>
      <input id="metadiv-stable-tag" name="meta_stable_tag" value="<?php echo esc_attr( $meta_stable_tag ); ?>"  type="text" class=" widefat metabox-normal-input metabox-normal-input-version "  />
    </p>

    <p id="metadiv-long-description-wrap">
      <label class="metabox-normal-label metabox-normal-label-full" for="metadiv-long-description"><?php _e('Long description:'); ?></label>
      <textarea id="metadiv-long-description" name="meta_long_description" class=" widefat metabox-normal-input metabox-normal-input-textarea" ><?php echo esc_textarea($meta_long_description); ?></textarea>
    </p>

    <p id="metadiv-installation-wrap">
      <label class="metabox-normal-label metabox-normal-label-full" for="metadiv-installation"><?php _e('Installation:'); ?></label>
      <textarea id="metadiv-installation" name="meta_installation" class=" widefat metabox-normal-input metabox-normal-input-textarea" ><?php echo esc_textarea($meta_installation); ?></textarea>
    </p>

    <p id="metadiv-faq-wrap">
      <label class="metabox-normal-label metabox-normal-label-full" for="metadiv-faq"><?php _e('Frequently asked questions:'); ?></label>
      <textarea id="metadiv-faq" name="meta_faq" class=" widefat metabox-normal-input metabox-normal-input-textarea" ><?php echo esc_textarea($meta_faq) ?></textarea>
    </p>

    <p id="metadiv-screenshots-wrap">
      <label class="metabox-normal-label metabox-normal-label-full" for="metadiv-screenshots"><?php _e('Screenshots:'); ?></label>
      <textarea id="metadiv-screenshots" name="meta_screenshots" class=" widefat metabox-normal-input metabox-normal-input-textarea" ><?php echo esc_textarea($meta_screenshots); ?></textarea>
    </p>

    <p id="metadiv-changelog-wrap">
      <label class="metabox-normal-label metabox-normal-label-full" for="metadiv-changelog"><?php _e('Changelog:'); ?></label>
      <textarea id="metadiv-changelog" name="meta_changelog" class=" widefat metabox-normal-input metabox-normal-input-textarea" ><?php echo esc_textarea($meta_changelog); ?></textarea>
    </p>

    <p id="metadiv-upgrade-notice-wrap">
      <label class="metabox-normal-label metabox-normal-label-full" for="metadiv-upgrade-notice"><?php _e('Upgrade notice:'); ?></label>
      <textarea id="metadiv-upgrade-notice" name="meta_upgrade_notice" class=" widefat metabox-normal-input metabox-normal-input-textarea" ><?php echo esc_textarea($meta_upgrade_notice); ?></textarea>
    </p>

    <p id="metadiv-extra-wrap">
      <label class="metabox-normal-label metabox-normal-label-full" for="metadiv-extra"><?php _e('Extra sections:'); ?></label>
      <textarea id="metadiv-extra" name="meta_extra" class=" widefat metabox-normal-input metabox-normal-input-textarea" ><?php echo esc_textarea($meta_extra); ?></textarea>
    </p>

  <p class="button-controls">
    <span class="add-to-plugin">
      <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
      <input type="submit"<?php disabled( $plugin->plugin_id, 0 ); ?> class="button-secondary submit-add-to-plugin" value="<?php esc_attr_e('Update Meta'); ?>" name="add-custom-plugin-item" id="submit-metadiv" />
    </span>
  </p>

</form></div><!-- /.metadiv -->
	<?php
}

  static function ace_theme_option($file) {
    if (strpos($file, 'theme-') === false || strpos($file, '-noconflict.js') === false || strpos($file, 'uncompressed') !== false)
      return ;
?>
        <option value="<?php echo $file; ?>" <?php selected(get_user_option('wp_pde_ace_theme'), $file) ?>><?php echo substr($file, 6, -14); ?></option>
<?php
  }
  static function get_ace_themes() {
    $files = scandir(dirname(__FILE__) . '/js/ace-0.2');
    array_map(array('PDEPlugin', 'ace_theme_option'), $files);
  }

  static function pde_plugin_options_meta_box( $args ) {

    extract($args);
	?>
<div class="optionsdiv" id="optionsdiv">

		<form id="update-actions-form" action="#" method="post" enctype="multipart/form-data">
    <input type="hidden" value="update-options" name="action" />
    <?php global $current_user; wp_nonce_field('update-options-'. $current_user->user_login) ; ?>
    <p id="options-div-editor-option">
      <label class="metabox-side-label" for="editor-option"><?php _e('Editor:'); ?></label>
        <select id="editor-option" name="editor-option" class=" widefat metabox-side-input input-with-default-title">
          <option value="TextArea" <?php selected(get_user_option('wp_pde_editor'), 'TextArea') ?>><?php _e('Use Textarea'); ?></option>
          <option value="Ace" <?php selected(get_user_option('wp_pde_editor'), 'Ace') ?>><?php _e('Use Ace'); ?></option>
        </select>
    </p>

    <div id="ace-editor-options" style="display:<?php echo (get_user_option('wp_pde_editor') === 'Ace' ? 'block' : 'none');  ?>" >
      <p id="options-div-ace-editor-theme">
        <label class="metabox-side-label" for="ace-editor-theme"><?php _e('Theme:'); ?></label>
          <select id="ace-editor-theme" name="ace-editor-theme" class=" widefat metabox-side-input input-with-default-title">
            <?php PDEPlugin::get_ace_themes(); ?>
          </select>
      </p>

      <p id="options-div-ace-editor-display-gutter">
        <label class="metabox-side-label" for="ace-editor-display-gutter"> <span><?php _e('Gutter:'); ?></span></label>
          <select id="ace-editor-display-gutter" name="ace-editor-display-gutter" class=" widefat metabox-side-input input-with-default-title">
            <option value="Yes" <?php selected(get_user_option('wp_pde_ace_display_gutter'), 'Yes') ?>><?php _e('Display'); ?></option>
            <option value="No" <?php selected(get_user_option('wp_pde_ace_display_gutter'), 'No') ?>><?php _e('Hide'); ?></option>
          </select>
      </p>

      <p id="options-div-ace-editor-font-size">
        <label class="metabox-side-label" for="ace-editor-font-size"><?php _e('Font Size:'); ?></label>
          <select id="ace-editor-font-size" name="ace-editor-font-size" class=" widefat metabox-side-input input-with-default-title">
            <option value="10px" <?php selected(get_user_option('wp_pde_ace_font_size'), '10px') ?>><?php _e('10px'); ?></option>
            <option value="11px" <?php selected(get_user_option('wp_pde_ace_font_size'), '11px') ?>><?php _e('11px'); ?></option>
            <option value="12px" <?php selected(get_user_option('wp_pde_ace_font_size'), '12px') ?>><?php _e('12px'); ?></option>
            <option value="14px" <?php selected(get_user_option('wp_pde_ace_font_size'), '14px') ?>><?php _e('14px'); ?></option>
            <option value="16px" <?php selected(get_user_option('wp_pde_ace_font_size'), '16px') ?>><?php _e('16px'); ?></option>
          </select>
      </p>

      <p id="options-div-ace-editor-print-margin">
        <label class="metabox-side-label" for="ace-editor-print-margin"><?php _e('Margin:'); ?></label>
          <select id="ace-editor-print-margin" name="ace-editor-print-margin" class=" widefat metabox-side-input input-with-default-title">
            <option value="Yes" <?php selected(get_user_option('wp_pde_ace_print_margin'), 'Yes') ?>><?php _e('Display'); ?></option>
            <option value="No" <?php selected(get_user_option('wp_pde_ace_print_margin'), 'No') ?>><?php _e('Hide'); ?></option>
          </select>
      </p>

      <p id="options-div-ace-editor-wrap-mode">
        <label class="metabox-side-label" for="ace-editor-wrap-mode"><?php _e('Wrap Mode:'); ?></label>
          <select id="ace-editor-wrap-mode" name="ace-editor-wrap-mode" class=" widefat metabox-side-input input-with-default-title">
            <option value="Yes" <?php selected(get_user_option('wp_pde_ace_wrap_mode'), 'Yes') ?>><?php _e('Wrap'); ?></option>
            <option value="No" <?php selected(get_user_option('wp_pde_ace_wrap_mode'), 'No') ?>><?php _e('No Wrap'); ?></option>
          </select>
      </p>
    </div> <!-- #ace-editor-options -->

    <p class="button-controls">
      <span class="update-options">
        <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
        <input type="submit" class="button-secondary submit-add-to-plugin" value="<?php esc_attr_e('Update Options'); ?>" name="update-options" id="submit-update-options" />
      </span>
    </p>
  </form>

</div><!-- /.optionsdiv -->
	<?php
  }

  static function pde_plugin_external_files_meta_box($args) {
    extract( $args );
    $plugin_id = $plugin ? $plugin->plugin_id : 0 ;

    $external_files = $plugin ? $plugin->get_external_file_items() : array();
  ?>
<?php if( !empty( $external_files ) ): ?>
<form id="pde-plugin-add-file" action="#" class="pde-plugin-add-files" method="post" enctype="multipart/form-data">
  <?php wp_nonce_field('add-pdeplugin-file-' . $plugin_id); ?>
  <input type="hidden" value="<?php echo $plugin_id; ?>" name="plugin" />
  <input type="hidden" value="add-file" name="action" />

  <?php foreach( $external_files as $file ) { ?>
    <ul>
      <?php PDEPlugin::emit_file_markup( $file ); ?>
    </ul>
  <?php } ?>
  <p class="button-controls">
    <span class="update-options">
      <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
      <input type="submit"<?php disabled( $plugin_id, 0 ); ?> class="button-secondary submit-add-to-plugin" value="<?php esc_attr_e('Update Options'); ?>" name="add-pdeplugin-file" id="submit-pdepluginaddfile"/>
    </span>
  </p>
  <div class="clear"></div>
</form>
<?php endif; ?>

<form id="pde-plugin-add-file" action="#" class="pde-plugin-add-files" method="post" enctype="multipart/form-data">
  <?php wp_nonce_field('add-pdeplugin-file-' . $plugin_id); ?>
  <input type="hidden" value="<?php echo $plugin_id; ?>" name="plugin" />
  <input type="hidden" value="add-file" name="action" />
  <div id="html-upload-ui">
    <p id="metadiv-add-files">
      <label class="metabox-side-label" for="metadiv-path"><?php _e('Folder:'); ?></label>
        <input id="metadiv-path" name="file_path" value=""  type="text" class=" widefat metabox-side-input metabox-side-input-path "  />
    </p>

    <p id="async-upload-wrap">
      <input type="file" name="async-upload" id="async-upload" class="widefat" />
    </p>
    <p class="button-controls">
      <span class="update-options">
        <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
        <input type="submit"<?php disabled( $plugin_id, 0 ); ?> class="button-secondary submit-add-to-plugin" value="<?php esc_attr_e('Add to Plugin'); ?>" name="add-pdeplugin-file" id="submit-pdepluginaddfile"/>
      </span>
    </p>
    <div class="clear"></div>
  </div>
</form>
  <?php
  }

  static function emit_file_markup( $file ) {
		$ext = pathinfo($file->title, PATHINFO_EXTENSION);
    $require = false ;
    if( $ext == 'php' ) {
      $require = true ;
    }
    $enque_script = false ;
    if( $ext == 'js' ) {
      $enque_script = true ;
    }
    $enque_style = false ;
    if( $ext == 'css' ) {
      $enque_style = true ;
    }

  ?>
      <li>
        <strong><?php echo $file->title; ?></strong><br/>
        <div style="padding: 5px 0 0 5px;">
        <?php if( $require ) : ?>
          <input type="hidden" value="" name="db-<?php echo $file->db_id; ?>[require]" />
          <input type="checkbox" <?php checked(empty($file->require) ? '' : $file->require, 'require'); ?> value="require" name="db-<?php echo $file->db_id; ?>[require]"> <?php _e('Include using require'); ?><br/>
        <?php endif; ?>
        <?php if( $enque_script ) : ?>
          <input type="hidden" value="" name="db-<?php echo $file->db_id; ?>[wp_enqueue_scripts]" />
          <input type="checkbox" <?php checked(empty($file->wp_enqueue_scripts) ? '' : $file->wp_enqueue_scripts, 'wp_enqueue_scripts'); ?> value="wp_enqueue_scripts" name="db-<?php echo $file->db_id; ?>[wp_enqueue_scripts]"> <?php _e('Include using wp_enqueue_scripts'); ?><br/>
          <input type="hidden" value="" name="db-<?php echo $file->db_id; ?>[admin_enqueue_scripts]" />
          <input type="checkbox" <?php checked(empty($file->admin_enqueue_scripts) ? '' : $file->admin_enqueue_scripts, 'admin_enqueue_scripts'); ?> value="admin_enqueue_scripts" name="db-<?php echo $file->db_id; ?>[admin_enqueue_scripts]"> <?php _e('Include using admin_enqueue_scripts'); ?><br/>
        <?php endif; ?>
        <?php if( $enque_style ) : ?>
          <input type="hidden" value="" name="db-<?php echo $file->db_id; ?>[wp_enqueue_styles]" />
          <input type="checkbox" <?php checked(empty($file->wp_enqueue_styles) ? '' : $file->wp_enqueue_styles, 'wp_enqueue_styles'); ?> value="wp_enqueue_styles" name="db-<?php echo $file->db_id; ?>[wp_enqueue_styles]"> <?php _e('Include using wp_enqueue_scripts'); ?><br/>
          <input type="hidden" value="" name="db-<?php echo $file->db_id; ?>[admin_enqueue_styles]" />
          <input type="checkbox" <?php checked(empty($file->admin_enqueue_styles) ? '' : $file->admin_enqueue_styles, 'admin_enqueue_styles'); ?> value="admin_enqueue_styles" name="db-<?php echo $file->db_id; ?>[admin_enqueue_styles]"> <?php _e('Include using admin_enqueue_scripts'); ?><br/>
        <?php endif; ?>
        </div>
      </li>
  <?php
  }

  static function pde_plugin_manage_resources() {
    $core = array(
      '_title' => __('Show plugin resources'),
      'cb' => '<input type="checkbox" />',
      'widget' => __('Widgets'),
      'action' => __('Actions'),
      'filter' => __('Filters'),
      'generated_source' => __('Generated Sources'),
    );
    $custom_plugin_items = apply_filters('pde_custom_plugin_items', array());
    foreach( $custom_plugin_items as $plugin_item ) {
      $core[$plugin_item['value']] = $plugin_item['display_plural'];
    }
    return $core ;
  }

  function add_test_button($id) {
    $plugin_test = $this->get_option('test');
    submit_button( $plugin_test ? __('Disable Plugin') : __('Enable Plugin' ), 'button-secondary plugin-test', 'test_plugin', false, array( 'id' => $id ) );
  }

  function add_export_button($id) {
    $url = admin_url('admin-ajax.php');
    $url .= '?action=download-plugin';
    $url .= '&plugin_id=' . $this->plugin_id;
    $nonce = wp_create_nonce('download-plugin-' . $this->plugin_id);
    $url .= '&_wpnonce=' . $nonce;
?>
<button id='export-button' class='button-secondary' >Export</button>
<script type="text/javascript">
(function($) {
  $('#export-button').click(function (e) {
    e.preventDefault();
    location.href='<?php echo $url; ?>';
    return false;
  });
})(jQuery);
</script>
<?php
  }

  function get_items_by_type( $type ) {
    $items = $this->get_items() ;
    $r = array();
    foreach( $items as $item ) {
      if( $item->param_type == $type )
        $r[] = $item ;
    }
    return $r;
  }

  function get_widget_items() {
    $items = $this->get_items() ;
    return array_filter($items, array('PDEPluginItem', 'is_widget'));
  }

  function get_action_items() {
    $items = $this->get_items() ;
    return array_filter($items, array('PDEPluginItem', 'is_action'));
  }

  function get_filter_items() {
    $items = $this->get_items() ;
    return array_filter($items, array('PDEPluginItem', 'is_filter'));
  }

  function get_source_file_items() {
    $items = $this->get_items() ;
    return array_filter($items, array('PDEPluginItem', 'is_source_file'));
  }

  function get_generated_file_items() {
    $items = $this->get_items() ;
    return array_filter( $items, array( 'PDEPluginItem', 'is_generated_file' ) );
  }

  function get_external_file_items() {
    $items = $this->get_items() ;
    return array_filter( $items, array( 'PDEPluginItem', 'is_external_file' ) );
  }

  function create_generated_file($filename, $content = '') {
    return PDEPluginItem::create( $this->plugin_id, $filename,  'plugin_source', array( 'generated' => true ), $content);
  }

  function create_external_file($filename, $content = '') {
		if ( $this->get_source_item( $filename ) )
			return new WP_Error( 'file-exists', sprintf( __( 'A file with the given path <strong>%s</strong> is already taken.' ), $filename ) );
    return PDEPluginItem::create( $this->plugin_id, $filename,  'plugin_source', array( 'generated' => false, 'binary' => $this->is_binary($content) ), $content);
  }

  function is_binary($content) {
    return preg_match( '/[^\001-\177]/', substr( $content, 0, 512 ) );
  } 

  function create_hook($pluginitem_type, $pluginitem_name, $item_args, &$messages) {
    extract($item_args);

		if ( !preg_match( '/^[a-zA-Z][a-zA-Z0-9_]*$/', $hook_method ) )
			return new WP_Error( 'invalid-action-filter', sprintf( __( 'Invalid action/filter name <strong>%s</strong>' ), $hook_method ) );

		if ( $this->get_action_id( $hook_method ) || $this->get_filter_id( $hook_method ) )
			return new WP_Error( 'method-exists', sprintf( __( 'Method with the given name <strong>%s</strong> is already taken.' ), $hook_method ) );

		$param_args = compact( array ('pluginitem_name', 'hook_method', 'hook_priority', 'hook_args') );
    $item =  PDEPluginItem::create( $this->plugin_id, $hook_method, $pluginitem_type, $param_args);
    $item->update_source( $item->get_action_src(), $messages);
    return $item;
  }

  function create_widget($pluginitem_name, &$messages) {
    $widget = PDEPluginItem::create( $this->plugin_id, $pluginitem_name,  'widget', array());
    if ( !$widget || is_wp_error ( $widget ) )
      return $widget ;
    $this->create_widget_info_item( $widget, $messages );
    $title_item = $this->create_title_item( $widget, $messages );
    $widget->update_source_preface(array($title_item), $messages, true);
    return $widget ;
  }

  function create_title_item ( $widget, &$messages ) {
    $args = array(
      'title' => 'Title',
      'param_type' => 'text',
      'description' => 'If given, the title is displayed at the top of the widget.',
      'strip_tags' => 'strip_tags',
      'strip_slashes' => 'strip_slashes',
      'position' => 2,
    );

    return PDEFormItem::create( $widget->db_id, $this->plugin_id, $args, $messages );
  }

  function create_widget_info_item ( $widget, &$messages ) {
    $args = array(
      'title' => $widget->title,
      'param_type' => 'widget parameters',
      'description' => '',
      'strip_tags' => '',
      'strip_slashes' => '',
      'position' => 1,
    );

    return PDEFormItem::create( $widget->db_id, $this->plugin_id, $args, $messages );
  }

  function get_items() {
    $items = get_objects_in_term( $this->plugin_id, 'pde_plugin' );

    if ( empty( $items ) )
      return $items;

    $args = array( 'order' => 'ASC', 'orderby' => 'title', 'post_type' => 'pde_plugin_item',
      'post_status' => 'publish', 'output' => ARRAY_A, 'output_key' => 'menu_order', 'nopaging' => true,
      'update_post_term_cache' => false, 'post_parent' => 0 );
    if ( count( $items ) > 1 )
      $args['include'] = implode( ',', $items );
    else
      $args['include'] = $items[0];

    $items = get_posts( $args );

    if ( is_wp_error( $items ) || ! is_array( $items ) )
      return false;

    return array_map( array('PDEPluginItem', 'setup'), $items );
  }

  static function setup_editor(){
		wp_enqueue_style('wp-pde');
		wp_enqueue_style('wp-pde-colors');
    $update = false ;
    $editor = get_user_option('wp_pde_editor');
    if (!$editor) {
      $update = true ;
      $editor = 'Ace';
    }
    $ace_theme = get_user_option('wp_pde_ace_theme');
    if (!$ace_theme)
      $ace_theme = 'theme-twilight-noconflict.js';
    $ace_display_gutter = get_user_option('wp_pde_ace_display_gutter');
    if (!$ace_display_gutter)
      $ace_display_gutter = 'Yes';
    $ace_font_size = get_user_option('wp_pde_ace_font_size');
    if (!$ace_font_size)
      $ace_font_size = '11px';
    $ace_print_margin = get_user_option('wp_pde_ace_print_margin');
    if (!$ace_print_margin)
      $ace_print_margin = 'No';
    $ace_wrap_mode = get_user_option('wp_pde_ace_wrap_mode');
    if (!$ace_wrap_mode)
      $ace_wrap_mode = 'No';

    if ($update) {
		  global $current_user;
		  update_user_meta( $current_user->ID, 'wp_pde_editor', $editor );
		  update_user_meta( $current_user->ID, 'wp_pde_ace_theme', $ace_theme );
		  update_user_meta( $current_user->ID, 'wp_pde_ace_display_gutter', $ace_display_gutter );
		  update_user_meta( $current_user->ID, 'wp_pde_ace_font_size', $ace_font_size );
		  update_user_meta( $current_user->ID, 'wp_pde_ace_print_margin', $ace_print_margin );
		  update_user_meta( $current_user->ID, 'wp_pde_ace_wrap_mode', $ace_wrap_mode );
    }

    wp_enqueue_script('wp-pde');
    wp_localize_script( 'wp-pde', 'wpPDEPluginVar', array(
      'noResultsFound' => _x('No results found.', 'search results'),
      'warnDeletePlugin' => __( "You are about to permanently delete this plugin. \n 'Cancel' to stop, 'OK' to delete." ),
      'warnDeleteItem' => __( "You are about to permanently delete this source. \n 'Cancel' to stop, 'OK' to delete." ),
      'saveAlert' => __('The changes you made will be lost if you navigate away from this page.'),
      'warnLoseEditorContents' => __( "You will lose changes to the current file. \n 'Cancel' to stop, 'OK' to continue." ),
      'duplicateProject' => __( "Either the name or version changed in the project. PDE can duplicate the project. \n 'Cancel' to continue, 'OK' to duplicate." ),
      'errorIllegalValues' => __( "One or more of the fields have an empty label. Labels are mandatory. Use __ as prefix to hide them in plugin configuration" ),
      'editor' => $editor,
      'ace_theme' => substr($ace_theme, 6, -14),
      'ace_display_gutter' => $ace_display_gutter,
      'ace_font_size' => $ace_font_size,
      'ace_print_margin' => $ace_print_margin,
      'ace_wrap_mode' => $ace_wrap_mode,
    ) );

		wp_register_script('ace_0.2-' . substr($ace_theme, 6, -14), plugins_url( 'js/ace-0.2/' . $ace_theme, __FILE__));
		wp_enqueue_script('ace_0.2');
		wp_enqueue_script('ace_0.2-' . substr($ace_theme, 6, -14));
		wp_enqueue_script('ace_0.2-mode-php');
		wp_enqueue_script('ace_0.2-mode-markdown');
		wp_enqueue_script('ace_0.2-mode-css');
		wp_enqueue_script('ace_0.2-mode-javascript');
  }

  function emit_editor_widgets($current_file) {
    $widgets = $this->get_widget_items();
    if( empty( $widgets ) )
      return '';
    ob_start();
    foreach( $widgets as $widget )
      $this->emit_editor_widget( $widget, $current_file );
    return ob_get_clean();
  }

  function emit_editor_widget($item, $current_file) {
    $this->output_item($item, $current_file) ;
    $this->output_item($item, $current_file, true);
  }

  function output_item($item, $current_file, $child = false) {
    if (!$child && $current_file == $item->db_id)
      $cls = ' class="highlight edit-file-link edit-file-link-' . $item->db_id . '" ';
    else
      $cls = ' class="edit-file-link edit-file-link-' . $item->db_id . '" ';

    global $pde_plugin_selected_id;
    if( $child )
      $delete_url = '' ;
    else
      $delete_url = "<a href='" . wp_nonce_url(add_query_arg(array('action' => 'delete-file', 'file_id' => $item->db_id, 'plugin_id' => $pde_plugin_selected_id), admin_url('admin.php?page=wp_pde')), 'delete-file-' . $item->db_id) . "' class='delete-file-link'><img alt='delete' style='display:inline;vertical-align:middle;margin-right:5px;float:right;' src='" . plugins_url('images/delete.png', __FILE__) . "' /></a>";
    $edit_url =  add_query_arg(array ('action' => 'wp-pde-edit-file', 'file_id' => $item->db_id), admin_url('admin-ajax.php'));
    if ($child)
      $edit_url = add_query_arg(array('form-source' => 'true'), $edit_url);
    $edit_url = wp_nonce_url($edit_url, 'edit-file-' . $item->db_id);
    $file = $child ? esc_html($item->title.':display()') : esc_html($item->title);
    $o = '<li' . $cls . '><a class="edit-file-link" href="' . $edit_url . '">' . $file . "</a>$delete_url</li>\n" ;
    echo $o;
  }

  function _emit_editor_actions($type, $current_file) {
    if( $type == 'widget' )
      return $this->emit_editor_widgets( $current_file );

    if( $type == 'action' )
      $items = $this->get_action_items();
    else if( $type == 'filter' )
      $items = $this->get_filter_items();
    else if( $type == 'generated_source' )
      $items = $this->get_generated_file_items();
    else if( $type == 'external_file' )
      $items = $this->get_external_file_items();
    else {
      $filter = 'pde_custom_plugin_item_emit_editor_list_for_' . $type ;
      if( has_filter( $filter ) ) {
        return apply_filters( $filter, '', $this, $current_file) ;
      } else {
        js_debug('Unknown emit for ' . $type);
        return '' ;
      }
    }

    if( empty( $items ) )
      return '';
    ob_start();
    foreach( $items as $item )
      $this->emit_editor_action( $item, $current_file );
    return ob_get_clean();
  }

	function emit_editor_action($item, $current_file) {
    if ($current_file == $item->db_id)
      $cls = ' class="highlight edit-file-link edit-file-link-' . $item->db_id . '" ';
    else
      $cls = ' class="edit-file-link edit-file-link-' . $item->db_id . '" ';

    global $pde_plugin_selected_id;
    if (PDEPluginItem::is_generated_file($item))
      $delete_url = '';
    else
      $delete_url = "<a href='" . wp_nonce_url(add_query_arg(array('action' => 'delete-file', 'file_id' => $item->db_id, 'plugin_id' => $pde_plugin_selected_id), admin_url('admin.php?page=wp_pde')), 'delete-file-' . $item->db_id) . "' class='delete-file-link'><img alt='delete' style='display:inline;vertical-align:middle;margin-right:5px;float:right;' src='" . plugins_url('images/delete.png', __FILE__) . "' /></a>";
    $edit_url = wp_nonce_url(add_query_arg(array ('action' => 'wp-pde-edit-file', 'file_id' => $item->db_id), admin_url('admin-ajax.php')), 'edit-file-' . $item->db_id);
    $o = '<li' . $cls . '><a class="edit-file-link" href="' . $edit_url . '">' . esc_html($item->title) . "</a>$delete_url" ;
		if( isset( $item->pluginitem_name ) )
			$o .= "<br/>&nbsp;&nbsp;...". $item->pluginitem_name ;
		$o .= "</li>\n" ;
    echo $o;
  }

  static function duplicate( $src_id, $plugin_data, &$messages ) {
    $src = PDEPlugin::get( $src_id );
    if( !$src || is_wp_error( $src ) )
      return $src ;

    $plugin = PDEPlugin::create( $plugin_data, &$messages );
    if( !$plugin || is_wp_error( $plugin ) )
      return $plugin ;

    $items = get_objects_in_term( $src->plugin_id, 'pde_plugin' );

    if ( empty( $items ) )
      return $plugin;

    $args = array( 'order' => 'ASC', 'orderby' => 'title', 'post_type' => 'pde_plugin_item',
      'post_status' => 'publish', 'output' => ARRAY_A, 'output_key' => 'menu_order', 'nopaging' => true,
      'update_post_term_cache' => false );
    if ( count( $items ) > 1 )
      $args['include'] = implode( ',', $items );
    else
      $args['include'] = $items[0];

    $items = get_posts( $args );

    $walker = new Walker_PDE_Duplicate ;
    $o = $walker->walk( $items, 0, array(), $plugin->plugin_id );

    return $plugin;
  }

}

class Walker_PDE_Duplicate extends Walker {
	var $tree_type = array( 'custom' );

	var $db_fields = array( 'parent' => 'post_parent', 'id' => 'ID' );

  var $parents = array( 0 );

  var $last_item = 0 ;

	function start_lvl(&$output) {
    $this->parents[] = $this->last_item ;
  }

	function end_lvl(&$output) {
    array_pop($this->parents);
	}

	function start_el(&$output, $item, $depth, $args, $plugin_id) {
    $a_item = (array) $item;
    $a_item['ID'] = 0 ;
    end($this->parents);
    $a_item['post_parent'] = current($this->parents);
    reset($this->parents);
    $a_item['tax_input'] = array( 'pde_plugin' => array( intval( $plugin_id ) ) );
    $item_id = wp_insert_post( $a_item );
    $this->last_item = $item_id ;
  }

	function end_el(&$output, $item, $depth) {
  }
}

?>

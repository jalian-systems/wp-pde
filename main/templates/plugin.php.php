<@php
<?php
	function esc_comments($s) { return str_replace('*/', '*\\/', $s); }
	$x_plugin_name = $plugin->plugin_name . ($export_mode === 'test' ? ' - dev' . (empty($plugin->plugin_version) ? '' : ' - ' . $plugin->plugin_version) : '');
	$meta = $plugin->get_meta();
	extract($meta);
?>
/*
Plugin Name: <?php echo(esc_comments($x_plugin_name));; ?>

Plugin URI: <?php if (isset($meta_plugin_uri)) echo(esc_comments($meta_plugin_uri));; ?>

Description: <?php if (isset($meta_short_description)) echo(esc_comments($meta_short_description)); ?>

Version: <?php echo(esc_comments($plugin->plugin_version)); ?>

Author: <?php if (isset($meta_author)) echo(esc_comments($meta_author)); ?>

Author URI: <?php if (isset($meta_author_uri)) echo(esc_comments($meta_author_uri)); ?>

License: <?php if (isset($meta_license)) echo(esc_comments($meta_license)); ?>

Code Generator: WpPDE (<?php echo 'http://wp-pde.jaliansystems.com/'; ?>)
*/

<?php if (isset($meta_copyright)) echo( "/* " . esc_comments($meta_copyright) . " */\n"); ?>

/*
<?php if (isset($meta_license_blurb)) echo str_replace("\r\n", "\n", $meta_license_blurb); ?>
*/

<?php
  $admin_print_styles = array();
  $admin_print_scripts = array();
  $print_styles = array();
  $print_scripts = array();
  $external_files = $plugin->get_external_file_items();
  foreach( $external_files as $ex ) {
    if( !empty($ex->admin_enqueue_styles) )
      $admin_print_styles[] = $ex ;
    if( !empty($ex->admin_enqueue_scripts) )
      $admin_print_scripts[] = $ex ;
    if( !empty($ex->wp_enqueue_styles) )
      $print_styles[] = $ex ;
    if( !empty($ex->wp_enqueue_scripts) )
      $print_scripts[] = $ex ;
    if( !empty($ex->require) )
      echo "require_once dirname(__FILE__) . '/" . $ex->title . "';\n";
  }
  reset($external_files);
?>

/**
 * <?php echo esc_comments($x_plugin_name); ?> plugin class
 */
class <?php $classname = $plugin->get_classname(); echo $classname; ?> {

<?php $items = $plugin->get_action_items();
      foreach( $items as $item ) {
        echo "  " . addcslashes( str_replace("\n", "\n  ", $item->content), '\\' ); echo "\n";
      }
      unset($items);
      $items = $plugin->get_filter_items();
      foreach( $items as $item ) {
        echo "  " . addcslashes( str_replace("\n", "\n  ", $item->content), '\\' ); echo "\n";
      }
      unset($items);
?>

<?php if( !empty( $print_styles ) || !empty( $print_scripts) ) : ?>
  static function add_styles() {
<?php foreach( $print_styles as $print_style ) { ?>
    $style_url = plugins_url('<?php echo $print_style->title; ?>', __FILE__);
    $style_file = dirname( __FILE__ ) . '/<?php echo $print_style->title; ?>';
    if( file_exists( $style_file ) ) {
      wp_register_style('<?php echo sanitize_title_with_dashes(basename($print_style->title, '.css')); ?>', $style_url) ;
      wp_enqueue_style('<?php echo sanitize_title_with_dashes(basename($print_style->title, '.css')); ?>');
    }
<?php      } ?>
<?php foreach( $print_scripts as $print_script ) { ?>
    $script_url = plugins_url('<?php echo $print_script->title; ?>', __FILE__);
    $script_file = dirname( __FILE__ ) . '/<?php echo $print_script->title; ?>';
    if( file_exists( $script_file ) ) {
      wp_register_script('<?php echo sanitize_title_with_dashes(basename($print_script->title, '.js')); ?>', $script_url) ;
      wp_enqueue_script('<?php echo sanitize_title_with_dashes(basename($print_script->title, '.js')); ?>');
    }
<?php      } ?>
  }

<?php endif; ?>

<?php if( !empty( $admin_print_styles ) || !empty( $admin_print_scripts) ) : ?>
  static function add_admin_styles() {
<?php foreach( $admin_print_styles as $print_style ) { ?>
    $style_url = plugins_url('<?php echo $print_style->title; ?>', __FILE__);
    $style_file = dirname( __FILE__ ) . '/<?php echo $print_style->title; ?>';
    if( file_exists( $style_file ) ) {
      wp_register_style('<?php echo sanitize_title_with_dashes(basename($print_style->title, '.css')); ?>', $style_url) ;
      wp_enqueue_style('<?php echo sanitize_title_with_dashes(basename($print_style->title, '.css')); ?>');
    }
<?php      } ?>
<?php foreach( $admin_print_scripts as $print_script ) { ?>
    $script_url = plugins_url('<?php echo $print_script->title; ?>', __FILE__);
    $script_file = dirname( __FILE__ ) . '/<?php echo $print_script->title; ?>';
    if( file_exists( $script_file ) ) {
      wp_register_script('<?php echo sanitize_title_with_dashes(basename($print_script->title, '.js')); ?>', $script_url) ;
      wp_enqueue_script('<?php echo sanitize_title_with_dashes(basename($print_script->title, '.js')); ?>');
    }
<?php      } ?>
  }

<?php endif; ?>
}

<?php $items = $plugin->get_action_items();
      foreach( $items as $item ) {
        echo 'add_' . $item->param_type . "( '{$item->pluginitem_name}', array('$classname', '{$item->hook_method}'), {$item->hook_priority}, {$item->hook_args} );\n" ;
      }
      unset($items);
      $items = $plugin->get_filter_items();
      foreach( $items as $item ) {
        echo 'add_' . $item->param_type . "( '{$item->pluginitem_name}', array('$classname', '{$item->hook_method}'), {$item->hook_priority}, {$item->hook_args} );\n" ;
      }
      unset($items);
?>

<?php if( !empty( $print_styles ) || !empty( $print_scripts) ) : ?>
add_action( 'wp_enqueue_scripts', array( '<?php echo $classname; ?>', 'add_styles' ) );
<?php endif; ?>

<?php if( !empty( $admin_print_styles ) || !empty( $admin_print_scripts) ) : ?>
add_action( 'admin_enqueue_scripts', array( '<?php echo $classname; ?>', 'add_admin_styles' ) );
<?php endif; ?>

<?php
  $widgets = $plugin->get_widget_items();
  foreach( $widgets as $widget ) {
    echo "require_once dirname(__FILE__) . '/widgets/" . $widget->get_widget_file($plugin) . "';\n";
  }
?>

<?php
    $custom_plugin_items = apply_filters('pde_custom_plugin_items', array());
    foreach( $custom_plugin_items as $plugin_item ) {
      do_action( 'pde_custom_plugin_item_require_for_' . $plugin_item['value'], $plugin ) ;
    }
?>
@>

<@php
<?php $x_widget_name = $widget->get_name() . ($export_mode === 'test' ? ' - dev' : ''); 
?>
/**
 * <?php echo esc_comments($x_widget_name); ?> widget class
 */
class <?php echo $widget->get_classname($plugin); ?> extends WP_Widget {
  function __construct() {
    $widget_ops = array('classname' => '<?php echo $widget->get_classname($plugin); ?>', 'description' => __( <?php _pv( $widget->get_description() ); ?> ) );
    <?php 
          $w_width = $widget->get_width(); $w_height = $widget->get_height();
          if( !empty($w_width) || !empty($w_height))  {
            $vals = '';
            if (!empty($w_width))
              $vals = "'width' => '" . $w_width . "'";

            if (!empty($w_height)) {
              if( !empty ($vals) )
                $vals .= ", ";
              $vals .= "'height' => '" . $w_height . "'";
            }
            $control_ops = ", array( $vals )";
          }
    ?>
    parent::__construct('<?php echo $widget->get_classname($plugin); ?>', __(<?php _pv( $x_widget_name ); ?>), $widget_ops <?php if(isset($control_ops)) echo $control_ops; ?>);
  }

  function widget( $sidebar, $instance ) {
<?php if( $widget->do_wrap() ) echo '    echo $sidebar["before_widget"];'; echo "\n"; ?>
<?php $args = array ('walker' => new Walker_widget_widget());
echo Walker_PDE_Form::walk_tree($widget->get_form_field_items(), 0, (object) $args );
?>

    /* Display */
<?php
  $source = $widget->content;
  if( preg_match( '/^\/\*.*\*\//msU', $source ) )
      $source = preg_replace( '/^\/\*.*\*\//msU', '', $source );
  echo '    ' . addcslashes( str_replace("\n", "\n    ", $source . "\n"), '\\' ); ?>
/* Display */

<?php if( $widget->do_wrap()) echo '    echo $sidebar["after_widget"];'; echo "\n" ?>
  }

  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
<?php $args = array ('walker' => new Walker_widget_update());
echo Walker_PDE_Form::walk_tree($widget->get_form_field_items(), 0, (object) $args );
?>
    return $instance;
  }

  function form( $instance ) {
    $defaults = array(
<?php $args = array ('walker' => new Walker_form_defaults());
echo Walker_PDE_Form::walk_tree($widget->get_form_field_items(), 0, (object) $args );
?>
                );
    $defaults = apply_filters('pde-widget-defaults-<?php echo $widget->get_name(); ?>', $defaults);
    $instance = wp_parse_args( (array) $instance, $defaults);
    @>
    <div id='<@php echo $this->get_field_id("wp-pde-form"); @>' class="pde-widget <?php echo $widget->get_theme_value(); ?>">
    <@php
<?php $args = array ('walker' => new Walker_form());
echo Walker_PDE_Form::walk_tree($widget->get_form_field_items(), 0, (object) $args );
?>
    @>
    </div>
    <@php
  }

  static function __widgets_init() {
    register_widget( '<?php echo $widget->get_classname($plugin); ?>' );
  }

  static function __enqueue_css() {
     $file = '<?php echo basename($widget->get_theme_file()); ?>';
     $script_id = '<?php echo sanitize_title_with_dashes( basename( $widget->get_theme_file(), '.css' ) ) ; ?>' ;
     wp_enqueue_style( $script_id, plugins_url( $file, __FILE__ ) );
     do_action( '<?php echo sanitize_title_with_dashes( $widget->get_name() ); ?>_enqueue_css', null );
<?php
     $items = $widget->get_form_field_items() ;
     $param_types_processed = array() ;
     foreach( $items as $item ) {
       if( !in_array( $item->param_type, $param_types_processed ) ) {
         do_action( 'pde_custom_form_item_enqueue_css_for_' . $item->param_type, null ) ;
         $param_types_processed[] = $item->param_type ;
       }
     }
?>
  }
}

add_action("widgets_init", array('<?php echo $widget->get_classname($plugin); ?>', '__widgets_init'));
add_action("load-widgets.php", array('<?php echo $widget->get_classname($plugin); ?>', '__enqueue_css'));

<?php
     $items = $widget->get_form_field_items() ;
     $param_types_processed = array() ;
     foreach( $items as $item ) {
       if( !in_array( $item->param_type, $param_types_processed ) ) {
         do_action( 'pde_custom_form_item_additional_code_for_' . $item->param_type, null ) ;
         $param_types_processed[] = $item->param_type ;
       }
     }
?>
?>

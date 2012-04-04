<?php

class PDEForm extends PDEPluginItem {

  static function pde_form_items_meta_box($args) {

    global $pde_plugin_selected_id;

    extract ( $args );
    $item = PDEPluginItem::get ( $file_id );
    $enabled = false ;
    if ( $item && !is_wp_error( $item ) && PDEPluginItem::is_form( $item ) )
      $enabled = true ;
	?>
<form id="pde-plugin-meta" action="#" class="pde-plugin-meta" method="post" enctype="multipart/form-data">
<div class="addformitemdiv" id="addformitemdiv">

			<input type="hidden" name="plugin" id="pde-plugin-meta-object-id" value="<?php echo esc_attr( $pde_plugin_selected_id ); ?>" />
			<input type="hidden" name="action" value="add-form-item" />
			<?php wp_nonce_field( 'add_form_item_' . esc_attr($pde_plugin_selected_id), 'add_form_item_nonce' ); ?>
    <p id="form-item-param-type-wrap">
      <label class="metabox-side-label" for="form-item-param-type"><?php _e('Type:'); ?></label>
        <select id="form-item-param-type" name="form-item-param-type" class="widefat metabox-side-input input-with-default-title">
          <option value="label">Label</option>
          <option value="text" selected>Text Field</option>
          <option value="checkbox" >Checkbox Field</option>
          <option value="dropdown" >Dropdown List</option>
          <option value="radio" >Radio Button Group</option>
          <option value="textarea" >Text Area Field</option>
          <option value="password" >Password Field</option>
          <option value="markup">Markup</option>
  <?php
  $custom_form_items = apply_filters('pde_custom_form_items', array());
  foreach( $custom_form_items as $form_item ) {
          $value = $form_item['value'];
          $display = $form_item['display'];
          echo "          <option value='$value' >$display</option>\n";
  }
  ?>
        </select>
    </p>

    <p id="form-item-name-wrap">
      <label class="metabox-side-label" for="form-item-name"><?php _e('Label:'); ?></label>
        <input id="form-item-name" name="form-item-title" type="text" class=" widefat metabox-side-input input-with-default-title" title="<?php esc_attr_e('Display Label'); ?>" />
    </p>

    <p id="form-item-html-option-wrap">
      <label class="metabox-side-label" for="form-item-html-option"><?php _e('HTML:'); ?></label>
        <select id="form-item-html-option" name="form-item-html-option" class=" widefat metabox-side-input input-with-default-title">
          <option value="Yes">Accept</option>
          <option value="No" selected>Do not accept</option>
        </select>
    </p>

  <p class="button-controls">
    <span class="add-to-plugin">
      <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
      <input type="submit"<?php disabled( $enabled, false ); ?> class="button-secondary submit-add-to-plugin" value="<?php esc_attr_e('Add to Form'); ?>" name="add-form-item" id="submit-addformitemdiv" />
    </span>
  </p>

</div><!-- /.addformitemdiv -->
</form>
	<?php
  }

  function get_form_items() {
    return PDEFormItem::get_items($this->db_id);
  }

  function get_form_field_items() {
    return array_filter ($this->get_form_items(), array ('PDEFormItem', 'is_field')) ;
  }

  function get_form_info() {
    return current( array_filter ($this->get_form_items(), array( 'PDEFormItem', 'is_info' ) ) ) ;
  }

  function get_edit_markup_fixed() {
    $info = $this->get_form_info ();

    $walker = new Walker_PDE_Form ;

    $result = '<ul class="plugin">';
    $result .= Walker_PDE_Form::walk_tree( $info ? array($info) : array(), 0, (object) array('walker' => $walker ) );
    $result .= ' </ul> ';

    return $result ;
  }

  function get_edit_markup() {
    $result = $this->get_edit_markup_fixed();

    $form_items = $this->get_form_field_items();

    if ( empty($form_items) )
      return $result . ' <ul class="plugin" id="form-to-edit"> </ul>';

    $walker = new Walker_PDE_Form ;

    $result .= '<ul class="plugin" id="form-to-edit">';
    $result .= Walker_PDE_Form::walk_tree( $form_items, 0, (object) array('walker' => $walker ) );
    $result .= ' </ul> ';
    return $result;
  }

  static function max_depth($classes) {
    global $_ww_pde_plugin_max_depth;
    if( !isset($_ww_pde_plugin_max_depth) )
      $_ww_pde_plugin_max_depth = 11;
    return "$classes form-max-depth-$_ww_pde_plugin_max_depth";
  }

  function update_form_items ( $form_data, &$messages ) {
    $form_data   = array_map( 'stripslashes_deep', $form_data );
    $terms = wp_get_post_terms ( $this->db_id, 'pde_plugin' );
    if ( !$terms || empty ($terms) )
      return false ;

    $plugin = PDEPlugin::get( $terms[0]->term_id );
    if ( !$plugin || is_wp_error ( $plugin ) )
      return $plugin ;

    $form = PDEPluginItem::get( $this->db_id );
    if( !PDEPluginItem::is_form( $form ) )
      return new WP_Error( 'not-a-form', 'Internal Error: Got a non-form when expecting one') ;

    $form_items = $this->get_form_items();
    if ( !$form_items || is_wp_error ( $form_items ) )
      return $form_items ;

    wp_defer_term_counting(true);

    $updated_items = array();
    foreach( $form_items as $item) {
      $form_item = PDEFormItem::update( $this->db_id, $plugin->plugin_id, $item->db_id, $form_data['db-'.$item->db_id], $messages );

      if ( is_wp_error( $form_item ) )
        WpPDEPlugin::messages('error', $form_item->get_error_message(), $messages);
      else
        $updated_items[] = $form_item;
    }

    wp_defer_term_counting(false);

    $form->update_source_preface($updated_items, $messages);

    WpPDEPlugin::messages('updated fade', sprintf( __('The form has been updated.') ), $messages);
    unset( $form_items );
		return true ;
  }

  function update_source_preface( $items, &$messages ) {
  }

  function is_form_item() {
    return true ;
  }

  function get_can_add_items() {
    return true ;
  }

  function get_info( $n ) {
    $items = $this->get_form_items();
    foreach( $items as $item )
      if( $item->param_type == $n )
        return $item ;
    return false;
  }

}

class Walker_form_defaults extends Walker_PDE_Form {

	function start_el(&$output, $item, $depth, $args) {
    $php_var = $item->get_php_variable();
    $default_val = $item->get_default_value();
    if ( empty( $php_var ) )
      return;
    $form_default = "                 '$php_var' => '$default_val',\n" ;
    $form_default = apply_filters( 'pde_custom_form_item_form_defaults_for_' . $item->param_type, $form_default, $item, $this );
    $output .= $form_default;
  }

  function end_el(&$output, $item, $depth) {}
}

class Walker_form extends Walker_PDE_Form {

  var $last_item ;

  function start_lvl(&$output) {
    $last = $this->last_item;
    $php_var = $last->get_php_variable();
    if( !isset( $last->display_when ) )
      $last->display_when = 'display_always' ;
    $last_id = '<@php echo $this->get_field_id("'. $php_var . '") @>';
    $start_level = "@>\n";
    $cls = " group-for-" . $last->param_type ;
    if( !empty($php_var) )
      $cls .= " group-" . $php_var ;
    $start_level .= '    <div class="' . $last->display_when . $cls . '" ' . 'id="group-'.  $last_id . '">' . "\n" ;
    if( $last->param_type == 'checkbox' &&
        ( $last->display_when == 'display_when_selected' || $last->display_when == 'display_when_unselected' ) ) {
      ob_start();
      if( !isset( $this->checkbox_script ) ) {
        $this->checkbox_script = true ;
?>
<script type="text/javascript">
(function($) {
  $('#<@php echo $this->get_field_id("wp_pde_form"); @>').on('change', '.wp_pde_checkbox', function (e) {
    item = $(e.target);
    group = '#group-' + $(item).attr('id');
    if($(group).size() > 0 && !$(group).hasClass('display_always')) {
      if( ( $(item).attr('checked') != 'checked' && $(group).hasClass('display_when_unselected') )
            || ( $(item).attr('checked') == 'checked' && $(group).hasClass('display_when_selected') ) )
        d = 'block' ;
      else
        d = 'none';
      $(group).css('display', d);
		};
  });
})(jQuery);
</script>
<?php
    }
?>
<script type="text/javascript">
(function($) {
  $('.wp_pde_checkbox').trigger('change');
})(jQuery);
</script>
<?php
      $start_level .= ob_get_clean();
    }
    $start_level .= "<@php\n";
    $start_level = apply_filters( 'pde_custom_form_item_form_start_level_for_' . $last->param_type, $start_level, $last, $this );
    $output .= $start_level;
  }

	function start_el(&$output, $item, $depth, $args) {
    $this->last_item = $item;
		ob_start();
    $inc_file = dirname( __FILE__ ) . '/templates/form-item-' . sanitize_title_with_dashes( $item->param_type ) . '-form.php' ;
    if( is_readable( $inc_file ) )
      include $inc_file;
    $form_data = ob_get_clean();
    $form_data = apply_filters( 'pde_custom_form_item_form_for_' . $item->param_type, $form_data, $item, $this );
    $output .= $form_data;
  }

  function end_el(&$output, $item, $depth) {}

  function end_lvl(&$output) {
    $output .= "@>\n";
    $output .= "   </div>\n";
    $output .= "<@php\n";
  }

}

?>

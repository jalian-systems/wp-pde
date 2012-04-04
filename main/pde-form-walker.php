<?
class Walker_PDE_Form extends Walker {
	var $tree_type = array( 'custom' );

	var $db_fields = array( 'parent' => 'parent_id', 'id' => 'db_id' );

	function start_lvl(&$output) {}

	function end_lvl(&$output) {
	}

	function start_el(&$output, $item, $depth, $args) {
		global $_ww_pde_plugin_max_depth;
		$_ww_pde_plugin_max_depth = $depth > $_ww_pde_plugin_max_depth ? $depth : $_ww_pde_plugin_max_depth;

		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		ob_start();
		$item_id = esc_attr( $item->db_id );

		$original_title = '';

		$classes = array(
			'form-item form-item-depth-' . $depth,
			'form-item-edit-' . ( ( isset( $_GET['edit-form-item'] ) && $item_id == $_GET['edit-form-item'] ) ? 'active' : 'inactive'),
		);

		$title = $item->title;

		$title = empty( $item->label ) ? $title : $item->label;

		?>
		<li id="form-item-<?php echo $item_id; ?>" class="<?php echo implode(' ', $classes ); ?>">
			<dl class="form-item-bar">
				<dt class="form-item-handle">
					<span class="item-title"><?php echo esc_html( $title ); ?></span>
					<span class="item-controls">
						<span class="item-type"><?php echo esc_html( $item->type_label ); ?></span>
						<a class="item-edit" id="form-item-edit-<?php echo $item_id; ?>" title="<?php esc_attr_e('Edit Plugin Item'); ?>" href="#"><?php _e( 'Edit Plugin Item' ); ?></a>
					</span>
				</dt>
			</dl>

      <div class="form-item-settings" id="form-item-settings-<?php echo $item_id; ?>">
      <?php
        $form_item_file = dirname( __FILE__ ) . '/templates/form-item-' . sanitize_title_with_dashes( $item->param_type ) . '.php' ;
        if( is_readable( $form_item_file ) )
          include dirname( __FILE__ ) . '/templates/form-item-' . sanitize_title_with_dashes( $item->param_type ) . '.php' ;
        else {
          $action = 'pde_custom_form_item_get_markup_for_' . sanitize_title_with_dashes( $item->param_type ) ;
          if( has_action( $action ) )
            do_action( $action, $item );
          else {
?>
        <div class='error'>
          Trying to add unknown item. Action <?php echo $action; ?> or file <?php echo $form_item_file; ?> not found.
        </div>
<?php
          }
        }
      ?>
				<div class="form-item-actions description-wide submitbox">
          <?php $remove_url = wp_nonce_url(add_query_arg(array ('action' => 'wp-pde-delete-item', 'item_id' => $item_id), admin_url('admin-ajax.php')), 'delete-item-' . $item_id); ?>
          <?php if ( PDEFormItem::is_deletable( $item ) ) : ?>
					  <a class="item-delete submitdelete deletion" id="delete-<?php echo $item_id; ?>" href="<?php echo esc_url($remove_url); ?>"><?php _e('Remove'); ?></a> <span class="meta-sep"> | </span>
          <?php endif; ?>
          <a class="item-cancel submitcancel" id="cancel-<?php echo $item_id; ?>" href="#"><?php _e('Cancel'); ?></a>
				</div>

				<input class="form-item-data-db-id" type="hidden" name="db-<?php echo $item_id; ?>[db_id]" value="<?php echo $item_id; ?>" />
				<input class="form-item-data-parent-id" type="hidden" name="db-<?php echo $item_id; ?>[parent_id]" value="<?php echo esc_attr( $item->parent_id ); ?>" />
				<input class="form-item-data-position" type="hidden" name="db-<?php echo $item_id; ?>[position]" value="<?php echo esc_attr( $item->position ); ?>" />
			</div><!-- .form-item-settings-->
			<ul class="form-item-transport"></ul>
		<?php
		$output .= ob_get_clean();
	}

	/**
	 * @see Walker::end_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Page data object. Not used.
	 * @param int $depth Depth of page. Not Used.
	 */
	function end_el(&$output, $item, $depth) {
		$output .= "</li>\n";
	}

  static function walk_tree( $items, $depth, $r ) {
	  $walker = ( empty($r->walker) ) ? new Walker_PDE_Form : $r->walker;
	  $args = array( $items, $depth, $r );

	  return call_user_func_array( array(&$walker, 'walk'), $args );
  }

}
?>

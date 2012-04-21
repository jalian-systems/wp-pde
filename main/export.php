<?php

/**
 * Retrieve or display list of plugins as a dropdown (select list).
 *
 * @param array|string $args Optional. Override default arguments.
 * @return string HTML content, if not displaying.
 */
function wppde_dropdown_plugins() {
	$output = '';

	$plugins = PDEPlugin::get_all();
	if ( ! empty($plugins) ) {
		$output = "<select class=\"postform\" name=\"plugin_id\" id=\"plugin_id\">\\n" ;
	  $output .= "\t<option value=\"" . "0" . "\">- All - </option>\\n";
    foreach( $plugins as $plugin )
			$output .= "\t<option value=\"" . esc_attr($plugin->plugin_id) . "\">" . esc_html( $plugin->plugin_name ) . "</option>\\n";
		$output .= "</select>\\n";
	}

  echo $output ;
}

function wppde_add_export_js() {
?>
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function($){
 		var form = $('#export-filters');
        ourradio = form.find('input:radio[value=pde_plugin_item]');
        ourradio.closest('p').after('<ul class="u-export-filters" id="pde_plugin_item-filters" style="margin-left: 18px; display: none;">\n<li>\n<label>Plugins:</label>\n<?php wppde_dropdown_plugins(); ?> </li>\n</ul>\n');
 			filters = form.find('.export-filters');
 		form.find('input:radio').change(function() {
			switch ( $(this).val() ) {
        case 'pde_plugin_item': $('#pde_plugin_item-filters').slideDown(); break;
        default: $('#pde_plugin_item-filters').slideUp(); break;
			}
 		});
	});
//]]>
</script>
<?php
}
add_action( 'admin_head-export.php', 'wppde_add_export_js' );

function wppde_add_query_filter() {
  add_filter( 'query', 'wppde_query' );
}

function wppde_query( $query ) {
  if( !strpos( $query, 'pde_plugin_item' ) )
    return $query ;
  $plugin_id = $_REQUEST['plugin_id'] ;
  if( $plugin_id == 0 )
    return $query ;

  $items = get_objects_in_term( $plugin_id, 'pde_plugin' ) ;
  if ( count( $items ) > 1 )
    $include = implode( ',', $items );
  else
    $include = $items[0];

  global $wpdb;
  $query .= " AND {$wpdb->posts}.ID in ( $include )" ;
  return $query;
}
add_action( 'export_wp', 'wppde_add_query_filter' );

?>

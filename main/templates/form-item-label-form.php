<?php
  $s_style = $item->get_style();
  $e_style = '' ;
  if ( !empty( $s_style ) ) {
    $style = $s_style ;
    $e_style = '</' . $s_style . '>' ;
    $s_style = '<' . $s_style . '>' ;
  } else {
    $style = 'none';
  }
?>
@>
      <div class="pde_form_field pde_form_label label-style-<?php echo $style; ?>"><?php echo $s_style; ?><@php _e( <?php _pv( $item->get_title() ); ?> ); ?><?php echo $e_style; ?></div>
<@php 

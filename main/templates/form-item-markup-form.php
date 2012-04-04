<?php
  $style = $item->get_style();
  $markup = isset( $item->markup ) ? $item->markup : '' ;
  if( $style == 'markdown' )
    $markup = Markdown( $markup );
?>
@>
      <div class="pde_form_field pde_form_markup markup-style-<?php echo $style; ?>">
        <?php echo $markup; ?>
      </div>
<@php 

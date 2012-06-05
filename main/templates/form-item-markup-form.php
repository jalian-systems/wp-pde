<?php
  $style = $item->get_style();
  $markup = isset( $item->markup ) ? $item->markup : '' ;
  if( $style == 'markdown' )
    $markup = Markdown( $markup );
?>
@>
      <div class="pde-form-field pde-form-markup markup-style-<?php echo $style; ?>">
        <?php echo $markup; ?>
      </div>
<@php 

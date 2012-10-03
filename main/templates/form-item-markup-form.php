<?php
  $style = $item->get_style();
  $markup = isset( $item->markup ) ? $item->markup : '' ;
  if( $style == 'markdown' )
    $markup = Markdown( $markup );
  $raw = isset( $item->raw_markup ) && $item->raw_markup == 'raw_markup' ;
?>
@>
<?php if($raw) : ?> 
      <div class="pde-form-field pde-form-markup markup-style-<?php echo $style; ?>">
<?php endif; ?>
        <?php echo $markup; ?>
<?php if($raw) : ?> 
      </div>
<?php endif; ?>

<@php 

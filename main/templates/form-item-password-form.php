<?php
  $var = $item->get_php_variable();
  if( empty( $item->description_html_escape ) )
    $description = $item->get_description() ;
  else
    $description = '<@php _e( \'' . esc_html( $item->get_description() ) . '\' ); @>' ;
?>
    $<?php echo $var; ?> = esc_attr( $instance['<?php echo $var; ?>'] );
@>
    <div class="pde_form_field pde_form_password <?php echo $var; ?>">
      <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
        <span class="pde_form_title"><@php esc_html_e( __(<?php _pv( $item->get_title() ); ?>) ); @></span>
      <input type="password" value="<@php echo $<?php echo $var; ?>; ?>" name="<@php echo $this->get_field_name('<?php echo $var; ?>'); ?>" id="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>" />
<?php if( !empty( $description ) ): ?>
        <span class="description-small"><?php echo $description; ?></span>
<?php endif; ?>
      </label>
    </div> <!-- <?php echo $var; ?> -->
<@php 

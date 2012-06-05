<?php
  $var = $item->get_php_variable();
  $description = $item->get_description() ;
  if( !empty( $description ) && !empty( $item->description_html_escape ) )
    $description = '<@php _e( \'' . esc_html( $item->get_description() ) . '\' ); @>' ;
?>
    $<?php echo $var; ?> = esc_attr( $instance['<?php echo $var; ?>'] );
@>
    <div class="pde-form-field pde-form-text <?php echo $var; ?>">
      <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
      <span class="pde-form-title"><@php esc_html_e( __(<?php _pv( $item->get_title() ); ?>) ); @></span>
      <input type="text" value="<@php echo $<?php echo $var; ?>; ?>" name="<@php echo $this->get_field_name('<?php echo $var; ?>'); ?>" id="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>" />
<?php if( !empty( $description ) ): ?>
        <span class="description-small"><?php echo $description; ?></span>
<?php endif; ?>
      </label>
    </div> <!-- <?php echo $var; ?> -->
<@php 

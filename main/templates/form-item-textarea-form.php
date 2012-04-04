<?php
  $var = $item->get_php_variable();
  if( empty( $item->description_html_escape ) )
    $description = $item->get_description() ;
  else
    $description = '<@php _e( \'' . esc_html( $item->get_description() ) . '\' ); @>' ;
  $rows = $item->get_rows_attr();
?>
    $<?php echo $var; ?> = esc_textarea( $instance['<?php echo $var; ?>'] );
@>
    <div class="pde_form_field pde_form_textarea <?php echo $var; ?>">
      <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
        <div class="pde_form_title"><@php esc_html_e( __(<?php echo _pv( $item->get_title() ); ?>) ); @></div>
        <textarea <?php echo $rows; ?> id="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>" name="<@php echo $this->get_field_name('<?php echo $var; ?>'); ?>"><@php echo $<?php echo $var; ?>; ?></textarea> 
<?php if( !empty( $description ) ): ?>
        <div class="description-small"><?php echo $description; ?></div>
<?php endif; ?>
      </label>
    </div> <!-- <?php echo $var; ?> -->
<@php 

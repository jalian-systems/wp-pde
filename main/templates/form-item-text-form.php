<?php
  $var = $item->get_php_variable();
  $title = $item->get_title();
  if( empty( $item->description_html_escape ) )
    $description = $item->get_description() ;
  else
    $description = '<@php _e( \'' . esc_html( $item->get_description() ) . '\' ); @>' ;
?>
    $<?php echo $var; ?> = esc_attr( $instance['<?php echo $var; ?>'] );
@>
    <div class="pde_form_field pde_form_text <?php echo $var; ?>">
      <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
      <div class="pde_form_title"><@php esc_html_e( __('<?php echo $title; ?>') ); @></div>
      <input type="text" value="<@php echo $<?php echo $var; ?>; ?>" name="<@php echo $this->get_field_name('<?php echo $var; ?>'); ?>" id="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>" />
<?php if( !empty( $description ) ): ?>
        <div class="description-small"><?php echo $description; ?></div>
<?php endif; ?>
      </label>
    </div> <!-- <?php echo $var; ?> -->
<@php 

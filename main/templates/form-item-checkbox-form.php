<?php
  $var = $item->get_php_variable();
  $value = esc_attr($item->get_value());
  if( empty( $item->description_html_escape ) )
    $description = $item->get_description() ;
  else
    $description = '<@php _e( \'' . esc_html( $item->get_description() ) . '\' ); @>' ;
?>
@>
    <div class="pde_form_field pde_form_checkbox <?php echo $var; ?>">
      <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
        <input class="wp_pde_checkbox" id="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>"
           value="<?php echo $value; ?>"
           name="<@php echo $this->get_field_name('<?php echo $var; ?>'); @>"
           type="checkbox"<@php checked(isset($instance['<?php echo $var; ?>']) ? $instance['<?php echo $var; ?>'] : '', '<?php echo $value; ?>'); @> />
      <span class="pde_form_title"><@php esc_html_e( __(<?php _pv( $item->get_title() ); ?>) ); @></span>
<?php if( !empty( $description ) ): ?>
      <span class="description-small"><?php echo $description; ?></span>
<?php endif; ?>
      </label>
    </div> <!-- <?php echo $var; ?> -->
<@php 

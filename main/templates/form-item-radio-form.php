<?php
  $var = $item->get_php_variable();
  $title = $item->get_title();
  if( empty( $item->description_html_escape ) )
    $description = $item->get_description() ;
  else
    $description = '<@php _e( \'' . esc_html( $item->get_description() ) . '\' ); @>' ;
  $single_line = $item->get_display_on_single_line();
  $options = $item->get_option_values();
?>
    $<?php echo $var; ?> = esc_textarea( $instance['<?php echo $var; ?>'] );
?>
    <div class="pde_form_field pde_form_radio <?php echo $var; ?>">
        <div class="pde_form_title"><@php esc_html_e( __('<?php echo $title; ?>') ); @></div>
<?php foreach( $options as $key => $value ) { ?>
      <label for="<@php echo $this->get_field_id('<?php echo $key; ?>'); @>">
        <input id="<@php echo $this->get_field_id('<?php echo $key; ?>'); ?>"
               name="<@php echo $this->get_field_name('<?php echo $var; ?>'); ?>"
               type="radio"<@php checked(isset($instance['<?php echo $var; ?>']) ? $instance['<?php echo $var; ?>'] : '', '<?php echo $value;?>'); @>
               value="<?php echo esc_attr($value);?>" />
        <div class="pde_form_radio_option"><@php esc_html_e( __('<?php echo $value; ?>') ); @></div>
      </label>
<?php } ?>
<?php if( !empty( $description ) ): ?>
      <div class="description-small"><?php echo $description; ?></div>
<?php endif; ?>
    </div> <!-- <?php echo $var; ?> -->
<@php 

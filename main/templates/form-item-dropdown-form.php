<?php
  $var = $item->get_php_variable();
  $title = $item->get_title();
  if( empty( $item->description_html_escape ) )
    $description = $item->get_description() ;
  else
    $description = '<@php _e( \'' . esc_html( $item->get_description() ) . '\' ); @>' ;
  $options = $item->get_option_values();
?>
    $<?php echo $var; ?> = esc_textarea( $instance['<?php echo $var; ?>'] );
?>
    <div class="pde_form_field pde_form_dropdown <?php echo $var; ?>">
      <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
      <div class="pde_form_title"><@php esc_html_e( __('<?php echo $title; ?>') ); @></div>
        <select name="<@php echo $this->get_field_name('<?php echo $var; ?>'); ?>" id="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
<?php foreach( $options as $key => $value ) {
        $value = esc_attr($value);
        $esc_value = esc_html($value); ?>
          <option value="<?php echo $value; ?>"<@php selected( $instance['<?php echo $var; ?>'], '<?php echo $value; ?>' ); ?>><@php _e('<?php echo $esc_value; ?>'); ?></option>
<?php } ?>
        </select>
<?php if( !empty( $description ) ): ?>
        <div class="description-small"><?php echo $description; ?></div>
<?php endif; ?>
      </label>
    </div> <!-- <?php echo $var; ?> -->
<@php 

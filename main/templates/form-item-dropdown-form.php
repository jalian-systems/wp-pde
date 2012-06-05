<?php
  $var = $item->get_php_variable();
  $description = $item->get_description() ;
  if( !empty( $description ) && !empty( $item->description_html_escape ) )
    $description = '<@php _e( \'' . esc_html( $item->get_description() ) . '\' ); @>' ;
  $options = $item->get_option_values();
?>
    $<?php echo $var; ?> = esc_textarea( $instance['<?php echo $var; ?>'] );
?>
    <div class="pde-form-field pde-form-dropdown <?php echo $var; ?>">
      <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
      <span class="pde-form-title"><@php esc_html_e( __(<?php _pv( $item->get_title() ); ?>) ); @></span>
        <select name="<@php echo $this->get_field_name('<?php echo $var; ?>'); ?>" id="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
<?php foreach( $options as $key => $value ) {
        $value = esc_attr($value);
        $esc_value = esc_html($value); ?>
          <option value="<?php echo $value; ?>"<@php selected( $instance['<?php echo $var; ?>'], <?php _pv( $value ); ?> ); ?>><@php _e('<?php echo $esc_value; ?>'); ?></option>
<?php } ?>
        </select>
<?php if( !empty( $description ) ): ?>
        <span class="description-small"><?php echo $description; ?></span>
<?php endif; ?>
      </label>
    </div> <!-- <?php echo $var; ?> -->
<@php 

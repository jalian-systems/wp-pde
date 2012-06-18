<?php
  $var = $item->get_php_variable();
  $description = $item->get_description() ;
  if( !empty( $description ) && !empty( $item->description_html_escape ) )
    $description = '<@php _e( \'' . esc_html( $item->get_description() ) . '\' ); @>' ;
  $single_line = $item->get_display_on_single_line();
  $radio_class = "pde-form-radio-ml" ;
  if( $single_line ) $radio_class = "pde-form-radio-sl";
  $options = $item->get_option_values();
?>
    $<?php echo $var; ?> = esc_textarea( $instance['<?php echo $var; ?>'] );
?>
    <div class="pde-form-field <?php echo $radio_class; ?> <?php echo $var; ?>">
        <div class="pde-form-title"><@php esc_html_e( __(<?php echo _pv( $item->get_title() ); ?>) ); @></div>
<?php if( !$single_line ) : ?>
      <div class="pde-form-radio-ml-options">
<?php endif; ?>
<?php foreach( $options as $key => $value ) { ?>
      <label for="<@php echo $this->get_field_id('<?php echo $key; ?>'); @>">
        <input id="<@php echo $this->get_field_id('<?php echo $key; ?>'); ?>"
               name="<@php echo $this->get_field_name('<?php echo $var; ?>'); ?>"
               type="radio"<@php checked(isset($instance['<?php echo $var; ?>']) ? $instance['<?php echo $var; ?>'] : '', <?php _pv( $value );?>); @>
               value="<?php echo esc_attr($value);?>" />
        <span class="pde-form-radio-option"><@php esc_html_e( __(<?php _pv( $value ); ?>) ); @></span>
      </label>
<?php if( !$single_line ) : ?>
      <br/>
<?php endif; ?>

<?php } ?>
<?php if( !$single_line ) : ?>
      </div>
<?php endif; ?>
<?php if( !empty( $description ) ): ?>
      <span class="description-small"><?php echo $description; ?></span>
<?php endif; ?>
    </div> <!-- <?php echo $var; ?> -->
<@php 

<?php
  $var = $item->get_php_variable();
  $value = esc_attr($item->get_value());
  $description = $item->get_description() ;
  if( !empty( $description ) && !empty( $item->description_html_escape ) )
    $description = '<@php _e( \'' . esc_html( $item->get_description() ) . '\' ); @>' ;
?>
@>
    <div class="pde-form-field pde-form-checkbox <?php echo $var; ?>">
        <div class="pde-form-title">
          <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
            <span><@php esc_html_e( __(<?php _pv( $item->get_title() ); ?>) ); @></span>
          </label>
        </div>
        <div class="pde-form-input">
          <input class="wp-pde-checkbox" id="<@php echo $this->get_field_id('<?php echo $var; ?>'); @>"
            value="<?php echo $value; ?>"
            name="cb-<@php echo $this->get_field_name('<?php echo $var; ?>'); @>"
            type="checkbox"<@php checked(isset($instance['<?php echo $var; ?>']) ? $instance['<?php echo $var; ?>'] : '', '<?php echo $value; ?>'); @> />
          <input id="txtcb-<@php echo $this->get_field_id('<?php echo $var; ?>'); @>"
            value="<?php echo $value; ?>"
            name="<@php echo $this->get_field_name('<?php echo $var; ?>'); @>"
            type="hidden" />
<script type="text/javascript">
(function($) {
  $('#<@php echo $this->get_field_id('<?php echo $var; ?>'); @>').change(function(e) {
    if($(this).attr('checked'))
      $('#txtcb-<@php echo $this->get_field_id('<?php echo $var; ?>'); @>').val($(this).val());
    else
      $('#txtcb-<@php echo $this->get_field_id('<?php echo $var; ?>'); @>').val('');
  });
})(jQuery);
</script>
          <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
          	<span class="pde-form-cb-label"><@php esc_html_e( __(<?php _pv( $item->get_label() ); ?>) ); @></span>
          </label>
        </div>
<?php if( !empty( $description ) ): ?>
        <div class="pde-form-description">
          <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
            <span><?php echo $description; ?></span>
          </label>
        </div>
<?php endif; ?>
    </div> <!-- <?php echo $var; ?> -->

<@php 

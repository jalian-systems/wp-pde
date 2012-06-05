<?php
  $var = $item->get_php_variable();
  $description = $item->get_description() ;
  if( !empty( $description ) && !empty( $item->description_html_escape ) )
    $description = '<@php _e( \'' . esc_html( $item->get_description() ) . '\' ); @>' ;
?>
    $<?php echo $var; ?> = esc_attr( $instance['<?php echo $var; ?>'] );
@>
    <div class="pde-form-field pde-form-date <?php echo $var; ?>">
      <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
        <span class="pde-form-title"><@php esc_html_e( __(<?php _pv( $item->get_title() ); ?>) ); @></span>
      </label>
      <div id="<@php echo $this->get_field_id('<?php echo $var; ?>_date_div'); @>"></div>
<?php if( empty( $item->display_style ) || $item->display_style == 'inline' ) : ?>
      <input id="<@php echo $this->get_field_id('<?php echo $var; ?>'); @>" type="hidden" value="<@php echo $<?php echo $var; ?>; @>" name="<@php echo $this->get_field_name('<?php echo $var; ?>'); @>" />
<?php else: ?>
      <input id="<@php echo $this->get_field_id('<?php echo $var; ?>'); @>" type="text" value="<@php echo $<?php echo $var; ?>; @>" name="<@php echo $this->get_field_name('<?php echo $var; ?>'); @>" />
<?php endif; ?>
<?php if( !empty( $description ) ): ?>
        <span class="description-small"><?php echo $description; ?></span>
<?php endif; ?>
    </div> <!-- <?php echo $var; ?> -->
<script type="text/javascript">
(function($) {
  $(document).ready(function() {
<?php if( empty( $item->display_style ) || $item->display_style == 'inline' ) : ?>
    dval = $('#<@php echo $this->get_field_id('<?php echo $var; ?>'); @>').val();
    $('#<@php echo $this->get_field_id('<?php echo $var; ?>_date_div'); @>').datepicker({
      altField: '#<@php echo $this->get_field_id('<?php echo $var; ?>'); @>',
    });
    $('#<@php echo $this->get_field_id('<?php echo $var; ?>_date_div'); @>').datepicker('setDate', dval);
<?php else: ?>
    $('#<@php echo $this->get_field_id('<?php echo $var; ?>'); @>').datepicker();
<?php endif; ?>
  });
})(jQuery);
</script>
<@php 

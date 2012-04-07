<?php
  $var = $item->get_php_variable();
  if( empty( $item->description_html_escape ) )
    $description = $item->get_description() ;
  else
    $description = '<@php _e( \'' . esc_html( $item->get_description() ) . '\' ); @>' ;
?>
    $<?php echo $var; ?> = esc_attr( $instance['<?php echo $var; ?>'] );
@>
    <div class="pde_form_field pde_form_date <?php echo $var; ?>">
      <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
      <div class="pde_form_title"><@php esc_html_e( __(<?php _pv( $item->get_title() ); ?>) ); @></div>
      <div id="<@php echo $this->get_field_id('<?php echo $var; ?>_date_div'); @>"></div>
<?php if( empty( $item->display_style ) || $item->display_style == 'inline' ) : ?>
      <input id="<@php echo $this->get_field_id('<?php echo $var; ?>'); @>" type="hidden" value="<@php echo $<?php echo $var; ?>; @>" name="<@php echo $this->get_field_name('<?php echo $var; ?>'); @>" />
<?php else: ?>
      <input id="<@php echo $this->get_field_id('<?php echo $var; ?>'); @>" type="text" value="<@php echo $<?php echo $var; ?>; @>" name="<@php echo $this->get_field_name('<?php echo $var; ?>'); @>" />
<?php endif; ?>
<?php if( !empty( $description ) ): ?>
        <div class="description-small"><?php echo $description; ?></div>
<?php endif; ?>
      </label>
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
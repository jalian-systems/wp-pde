<?php
  $var = $item->get_php_variable();
  if( empty( $item->description_html_escape ) )
    $description = $item->get_description() ;
  else
    $description = '<@php _e( \'' . esc_html( $item->get_description() ) . '\' ); @>' ;
?>
    $<?php echo $var; ?> = esc_attr( $instance['<?php echo $var; ?>'] );
@>
    <div class="pde_form_field pde_form_text <?php echo $var; ?>">
      <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
      <div class="pde_form_title"><@php esc_html_e( __(<?php _pv( $item->get_title() ); ?>) ); @></div>
      <div class="color-picker-div">
        <input type="text" value="<@php echo $<?php echo $var; ?>; ?>" name="<@php echo $this->get_field_name('<?php echo $var; ?>'); ?>" id="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>" class="pde-plugin-pickcolor-text"/>
        <a href="#" class="pde-plugin-pickcolor-example hide-if-no-js" id="<@php echo $this->get_field_id('<?php echo $var; ?>'); @>-example"></a>
        <input id="<@php echo $this->get_field_id('<?php echo $var; ?>'); @>-button" type="button" class="pde-plugin-pickcolor button hide-if-no-js" value="<?php esc_attr_e( 'Select' ); ?>" />
        <div id="<@php echo $this->get_field_id('<?php echo $var; ?>'); @>-colorPickerDiv" class="pde-plugin-pickcolor-popup"></div>
      </div>
<?php if( !empty( $description ) ): ?>
        <div class="description-small"><?php echo $description; ?></div>
<?php endif; ?>
      </label>
    </div> <!-- <?php echo $var; ?> -->
<script type="text/javascript">
(function($){
  var <?php echo $var; ?>_farbtastic = undefined;
	var pickColor = function(a) {
		$('#<@php echo $this->get_field_id('<?php echo $var; ?>'); @>').val(a);
		$('#<@php echo $this->get_field_id('<?php echo $var; ?>'); @>-example').css('background-color', a);
	};

  $('#wpbody-content').off( 'click', ".pde-plugin-pickcolor, .pde-plugin-pickcolor-example");
  $('#wpbody-content').on( 'click', ".pde-plugin-pickcolor, .pde-plugin-pickcolor-example", function(e) {
    e.preventDefault();
    id = $(e.target).attr('id').replace(/-button$|-example$/, '')
		if ( <?php echo $var; ?>_farbtastic == undefined ) {
      pickColor = function(a) {
        <?php echo $var; ?>_farbtastic.setColor(a);
        $('#' + id).val(a);
        $('#' + id + '-example').css('background-color', a);
      };
		  <?php echo $var; ?>_farbtastic = $.farbtastic('#' + id + '-colorPickerDiv', pickColor);
      $(document).mousedown( function() {
        $('#' + id + '-colorPickerDiv').hide();
      });
    }

    $('#' + id + '-colorPickerDiv').show();
  });

  $('#wpbody-content').off( 'keyup', '.pde-plugin-pickcolor-text');
  $('#wpbody-content').on( 'keyup', '.pde-plugin-pickcolor-text',  function() {
    var a = $('#<@php echo $this->get_field_id('<?php echo $var; ?>'); @>').val(),
      b = a;

    a = a.replace(/[^a-fA-F0-9]/, '');
    if ( '#' + a !== b )
      $('#<@php echo $this->get_field_id('<?php echo $var; ?>'); @>').val(a);
    if ( a.length === 3 || a.length === 6 )
      pickColor( '#' + a );
  });

	$(document).ready( function() {

		pickColor( $('#<@php echo $this->get_field_id('<?php echo $var; ?>'); @>').val() );

		$(document).mousedown( function() {
			$('#<@php echo $this->get_field_id('<?php echo $var; ?>'); @>-colorPickerDiv').hide();
		});

	});
})(jQuery);
</script>
<@php 

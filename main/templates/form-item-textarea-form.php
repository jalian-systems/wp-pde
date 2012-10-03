<?php
  $var = $item->get_php_variable();
  $description = $item->get_description() ;
  if( !empty( $description ) && !empty( $item->description_html_escape ) )
    $description = '<@php _e( \'' . esc_html( $item->get_description() ) . '\' ); @>' ;
  $rows = $item->get_rows_attr();
?>
    $<?php echo $var; ?> = esc_textarea( $instance['<?php echo $var; ?>'] );
@>
    <div class="pde-form-field pde-form-textarea <?php echo $var; ?>">
<?php if(!empty($item->full_width)): ?>
      <div class="pde-form-title" style="width:100%">
<?php else: ?>
      <div class="pde-form-title">
<?php endif; ?>
        <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
          <span><@php esc_html_e( __(<?php _pv( $item->get_title() ); ?>) ); @></span>
        </label>
      </div>
<?php if(!empty($item->full_width)): ?>
      <div class="pde-form-input" style="width: 100%">
<?php else: ?>
      <div class="pde-form-input">
<?php endif; ?>
        <textarea <?php echo $rows; ?> id="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>" name="<@php echo $this->get_field_name('<?php echo $var; ?>'); ?>"><@php echo $<?php echo $var; ?>; ?></textarea> 
      </div>
<?php if( !empty( $description ) ): ?>
<?php if(!empty($item->full_width)): ?>
      <div class="pde-form-description" style="width:100%" >
<?php else: ?>
      <div class="pde-form-description">
<?php endif; ?>
        <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
          <span><?php echo $description; ?></span>
        </label>
      </div>
<?php endif; ?>
    </div> <!-- <?php echo $var; ?> -->

<@php 

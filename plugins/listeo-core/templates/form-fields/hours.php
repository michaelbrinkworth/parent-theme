<!-- Section -->
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
	$field = $data->field;
	$key = $data->key;
 
$days = listeo_get_days();

?>
<!-- Day -->
<?php

 foreach ($days as $id => $dayname) { 
 	
 		$opening_val = (isset($field['value'][$id.'_opening'])) ? $field['value'][$id.'_opening'] : false;
 		$closing_val = (isset($field['value'][$id.'_closing'])) ? $field['value'][$id.'_closing'] : false;


 		?>
		<div class="row opening-day">
			<div class="col-md-2"><h5><?php echo esc_html($dayname) ?></h5><span class='day_hours_reset'><?php esc_html_e('Clear time','listeo_core') ?></span></div>
			<div class="col-md-5">
				<input type="text" class="listeo-flatpickr" name="_<?php echo esc_attr($id); ?>_opening_hour" placeholder="<?php esc_html_e('Opening Time','listeo_core'); ?>" value="<?php echo esc_attr($opening_val); ?>">
					
			</div>
			<div class="col-md-5">
				<input type="text" class="listeo-flatpickr" name="_<?php echo esc_attr($id); ?>_closing_hour" placeholder="<?php esc_html_e('Closing Time','listeo_core'); ?>" value="<?php echo esc_attr($closing_val); ?>">
				
			</div>
		</div>
		<!-- Day / End -->
<?php } ?>
							
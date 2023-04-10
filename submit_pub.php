<?php
require('includes/application_top.php');

require(DIR_WS_INCLUDES . 'template_top.php');
?>

<div class="main_content_wide">
	<div class="content_box">
		<h1 class="<?php echo $color_scheme; ?>_gradient">Add a Pub</h1>
		<div class="box_content_line" id="pub_creation">
			<?php
			echo tep_draw_form('create_discount', tep_href_link(FILENAME_ZAJAX_FORM_SUBMISIONS), 'post', 'id="pub_create_form" class="current_form"', true);
			?>
			<div class="long_container">
				<div class="long_label">
					<b>Requiered Information</b>
				</div>
			</div>
			<div class="long_container">
				<div class="long_label">
					Pokies Pub Name <span class="red_text">*</span> <span class="error" id="error_pub_name"> </span> <a title="Enter a NZ Pub Name." class="tooltip"> ? </a>
				</div>
				<div class="long_input">
					<?php echo tep_draw_input_field('pub_name', '', 'id="pub_name" class="long_text"'); ?>
				</div>
			</div>
			<div class="long_container">
				<div class="long_label">
					Postcode of the Pub <span class="red_text">*</span> <span class="error" id="error_location_postcode"></span> <a title="Please enter a valid NZ Post Code.<br/> Based on this the Region and City will be autodetected." class="tooltip"> ? </a>
				</div>
				<div class="long_input">
					<?php echo tep_draw_input_field('location_postcode', '', 'id="location_postcode" class="long_text"'); ?>
				</div>
			</div>
			<div class="long_container">
				<div class="long_label">
					<b>Optional Information</b>
				</div>
			</div>
			<div class="long_container">
				<div class="long_label">
					Pub Description <span class="error" id="error_pub_description"> </span> <a title="It can have maximum 20000 characters.<br> Please use propper grammar and spelling." class="tooltip"> ? </a>
				</div>
				<div class="long_input">
					<?php echo tep_draw_textarea_field('pub_description', 'soft', '70', '15', '', 'id="pub_description" class="long_textarea"'); ?>
				</div>
			</div>
			<div class="long_container">
				<div class="long_label">
					Pub Photo <span class="error" id="error_photo"> </span> <a title="Photos will be automatically cropped when uploaded." class="tooltip"> ? </a>
				</div>
				<div class="long_input">
					<?php echo get_image_upload_container($pictures, 1, 'pub'); ?>
				</div>
			</div>
			<div class="long_container">
				<div class="long_label">
					Pub Address <span class="error" id="error_pub_address"> </span> <a title="Maximum 100 characters." class="tooltip"> ? </a>
				</div>
				<div class="long_input">
					<?php echo tep_draw_input_field('pub_address', '', 'id="pub_address" class="long_text"'); ?>
				</div>
			</div>
			<div class="long_container">
				<div class="long_label">
					Pub Phone <span class="error" id="error_pub_phone"> </span> <a title="Maximum 100 characters." class="tooltip"> ? </a>
				</div>
				<div class="long_input">
					<?php echo tep_draw_input_field('pub_phone', '', 'id="pub_phone" class="long_text"'); ?>
				</div>
			</div>
			<div class="long_container">
				<div class="long_label">
					Pub Website <span class="error" id="error_pub_website"> </span> <a title="Maximum 100 characters." class="tooltip"> ? </a>
				</div>
				<div class="long_input">
					<?php echo tep_draw_input_field('pub_website', '', 'id="pub_website" class="long_text"'); ?>
				</div>
			</div>
			<div class="long_container">
				<div class="long_label">
					Your Email Address <span class="error" id="error_author_email"> </span> <a title="Maximum 100 characters." class="tooltip"> ? </a>
				</div>
				<div class="long_input">
					<?php echo tep_draw_input_field('author_email', '', 'id="author_email" class="long_text"'); ?>
				</div>
			</div>
			<div class="long_container">
				<div class="long_label">
					By submiting the above content I agree to the <a href="<?php echo tep_href_link('pages/terms-and-conditions'); ?>" target="_blank">terms and conditions</a> of the site.* <span class="error" id="error_terms"> </span>
				</div>
			</div>
			<div class="long_container">
				<div class="long_label extra_bottom_margin">
					<?php echo tep_draw_input_field('address', '', 'id="address" class="long_text address_style"'); ?>
					<input type="submit" name="submit_pub" id="submit_pub" value="Submit Pub" class="button <?php echo $color_scheme; ?>_button" />
				</div>
			</div>
			</form>
		</div>
	</div>
</div>

<?php
require(DIR_WS_INCLUDES . 'template_bottom.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
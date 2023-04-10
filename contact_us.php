<?php
    require('includes/application_top.php');

	// To do error checking and set up a way of displaying errors when JS is disabled


    require(DIR_WS_INCLUDES . 'template_top.php');
?>

	<!-- Contact Us -->
	<div class="main_content_wide">
		<div class="content_box">
			<h1 class="<?php echo $color_scheme; ?>_gradient">Contact Us</h1>
			<div class="box_content_line" id="contact_creation">
            <p><strong>Do you have a question?</strong></p>
<p>It may already be in our <a href="<?php echo HTTP_SERVER; ?>/pages/faqs">Answers page</a>. </p>
<?php
				echo tep_draw_form('contact_us', tep_href_link(FILENAME_ZAJAX_CONTACT_US), 'post', 'id="contact_us_form"', true);
				?>
					<div class="long_container">
						<div class="long_label">
							Subject * <span class="error" id="error_title"></span>
						</div>
						<div class="long_input">
							<?php echo tep_draw_input_field('contact_us_title', '', 'id="contact_us_title" class="long_text"');?>
						</div>
					</div>
					<div class="long_container">
						<div class="long_label">
							Email * <span class="error" id="error_email"></span>
						</div>
						<div class="long_input">
							<?php echo tep_draw_input_field('contact_us_email', '', 'id="contact_us_email" class="long_text"');?>
						</div>
					</div>
					<div class="long_container">
						<div class="long_label">
							Message * <span class="error" id="error_description"> </span>
						</div>
						<div class="long_input">
							<?php echo tep_draw_textarea_field('contact_us_description', 'soft', '70', '15', '', 'id="contact_us_description" class="long_textarea  ckeditor"' ); ?>
						</div>
					</div>
					<div class="long_container">
						<div class="long_label">
							<?php echo tep_draw_input_field('address', '', 'id="address" class="long_text address_style"'); ?>
							<input type="submit" name="contact_us" id="contact_us" value="Submit Message" class="button <?php echo $color_scheme; ?>_button"/>
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

<div class="main_content_wide">
	<div class="content_box">
		<h2 class="<?php echo $color_scheme; ?>_gradient">Other 10 Pubs Close By</h2>
		<?php
		$listing_query = tep_db_query("select p.pub_id, p.pub_name, p.pub_description, p.pub_pictures, p.pub_address, p.pub_phone, p.pub_website, p.likes, p.pub_url, l.location_city, l.location_postcode, l.location_url, lz.location_zone_name, lz.location_zone_url from ". TABLE_PUBS." p left join ".TABLE_LOCATIONS." l on p.location_id = l.location_id left join ".TABLE_LOCATIONS_ZONES." lz on l.location_zone_id = lz.location_zone_id where p.status = '1' and (".$location_query.") and p.pub_id != '".$_GET['pub_id']."' order by p.likes DESC, p.number_of_visits DESC limit 10");
		$row = 1;
		while ($listing = tep_db_fetch_array($listing_query)) {
			
			if ($listing['pub_pictures']) { 
				$image = '<img src="images/pub_thumb/'. $listing['pub_pictures'] .'" width="107" height="87" alt="'. $listing['pub_name'] .'">';
			} else {
				$image = '<img src="images/small_no_image.jpg" width="107" height="87" alt="No Image">';
			}	
							
			?>
			<div class="listing_container">
				<div class="listing_image_container">
					<?php echo $image; ?>
				</div>
				<div class="listing_content_container_title">
					<h3>						
						<a href="<?php echo tep_href_link($listing['location_zone_url'].'/'.$listing['location_url'].'/'.$listing['pub_url'].'/'); ?>"><?php echo $listing['pub_name']; ?></a>
						<?php echo get_like_button('pub', $listing['pub_id'] , $listing['likes']);?>
					</h3>
				</div>
				<div class="listing_content_container_text">
					<p>
						<?php
						if (strlen($listing['pub_address'])) {
							echo 'Address: '. $listing['pub_address'].'<br/>';
						}
						if (strlen($listing['pub_phone'])) {
							echo 'Phone: '. $listing['pub_phone'].'<br/>';
						}
						if (strlen($listing['pub_website'])) {
							echo 'Website: '. $listing['pub_website'].'<br/>';
						}
						?>
					</p>
				</div>
			</div>
			<?php 
			$row++;
		}
		?>
		<?php
		if ($row == 1) {
		?>
			<p>It seems there are no pubs nearby the one you currently selected.</p>
		<?php	
		}
		?>
	</div>
</div>
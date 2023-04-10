<div class="main_content_wide">
	<div class="content_box">
		<h2 class="<?php echo $color_scheme; ?>_gradient">Search Results For: "<?php echo $_GET['keywords']; ?>" </h2>
		<?php
		// No pagination on the search listing
		$listing_split = new splitPageResults($listing_sql, 500000 , 'p.pub_id');
		if ($listing_split->number_of_rows > 0) {
			
			$row = 1;
			$listing_query = tep_db_query($listing_split->sql_query);
			while ($listing = tep_db_fetch_array($listing_query)) {
				
				if ($listing['pub_pictures']) { 
					$image = '<img src="images/pub_thumb/'. $listing['pub_pictures'] .'" width="107" height="87" alt="'. $listing['pub_name'] .'">';
				} else {
					$image = '<img src="images/small_no_image.jpg" width="107" height="87"  alt="No Image">';
				}	
				
				?>
				<div class="listing_container">
					<div class="listing_image_container">
						<?php echo $image; ?>
					</div>
					<div class="listing_content_container_title">
						<h3>						
							<a href="<?php echo tep_href_link($listing['location_zone_url'].'/'.$listing['location_url'].'/'.$listing['pub_url'].'/'); ?>"><?php echo $listing['pub_name']; ?></a>
							<?php echo get_like_button('pub', $listing['pub_id'] , $listing['likes']);?></h1>
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
		} else {
		?>
		<p>It seems no pubs here :) !</p>
		<?php	
		}
		?>
	</div>
</div>	
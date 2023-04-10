<div class="content_box">
	<h2 class="<?php echo $color_scheme; ?>_gradient">Most Visited Pubs</h2>
	<ul class="column_list">
		<?php
		$listing_query = tep_db_query("select p.pub_name, p.pub_url, l.location_city, l.location_url, lz.location_zone_name, lz.location_zone_url from ". TABLE_PUBS." p left join ".TABLE_LOCATIONS." l on p.location_id = l.location_id left join ".TABLE_LOCATIONS_ZONES." lz on l.location_zone_id = lz.location_zone_id where p.status = '1' order by p.number_of_visits DESC limit 15");
		$row = 1;
		while ($listing = tep_db_fetch_array($listing_query)) {			
			?>
			<li>
				<a href="<?php echo tep_href_link($listing['location_zone_url'].'/'.$listing['location_url'].'/'.$listing['pub_url'].'/'); ?>"><span class="<?php echo $color_scheme; ?>_color"><?php echo $listing['pub_name'].'</span> - '.strtolower($listing['location_city']).', '.$listing['location_zone_name']; ?></a>
			</li>							
			<?php
			$row++;
		}
		?>
		<?php
		if ($row == 1) {
			?>
			<li>
				<a>It seems there are no games on the site</a>
			</li>
			<?php	
		}
		?>
	</ul>
</div>
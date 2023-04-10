<div class="main_content_wide">
	<div class="content_box">
		<h2 class="<?php echo $color_scheme; ?>_gradient">Select a specific Town/City from <?php echo $current_page_results['location_zone_name']; ?> to see the Pubs</h2>
		<ul class="zones_listing">
			<?php
			$listing_query = tep_db_query("select distinct l.location_city, l.location_url from " . TABLE_PUBS . " p left join " . TABLE_LOCATIONS . " l on p.location_id = l.location_id where l.location_zone_id='" . (int)$_GET['location_zone_id'] . "' order by l.location_city");
			$row = 1;
			while ($listing = tep_db_fetch_array($listing_query)) {
			?>
				<li>
					<a href="<?php echo tep_href_link($current_location_zone_url . $listing['location_url'] . '/'); ?>"><?php echo strtolower($listing['location_city']); ?></a>
				</li>
			<?php
				$row++;
			}
			?>
			<?php
			if ($row == 1) {
			?>
				<li>
					<a>No Zones</a>
				</li>
			<?php
			}
			?>
		</ul>
	</div>
</div>
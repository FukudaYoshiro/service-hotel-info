<div class="main_content_wide">
	<div class="content_box">
		<h2 class="<?php echo $color_scheme; ?>_gradient">Pubs by Region</h2>
		<p>Select the region to view pubs from that area. You can then further filter the pubs by specific towns or
			cities.</p>
		<ul class="zones_listing" id="zones_fixer">
			<?php
			$listing_query = tep_db_query("select location_zone_name, location_zone_url from " . TABLE_LOCATIONS_ZONES . "");
			$row = 1;
			while ($listing = tep_db_fetch_array($listing_query)) {
			?>
				<li<?php echo $row_class; ?>>
					<a href="<?php echo tep_href_link($listing['location_zone_url'] . '/'); ?>"><?php echo $listing['location_zone_name']; ?></a>
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
</div>

<div class="right_column">
	<div class="right_column_wide"> <?php echo tep_display_banner('static', 4); ?>
		<!-- <img src="images/add_right.png" alt="Right Column Pubs NZ" width="300" height="355"> -->
	</div>
	<div class="right_column_wide">
		<?php
		// Most Popular Pubs on the directory
		include(DIR_WS_MODULES . 'box_pubs_most_popular_alltime.php');
		?>
	</div>
	<div class="right_column_wide">
		<div class="social-wrap"><?php echo tep_display_banner('static', 5); ?>
			<!-- <img src="images/add_right_2.png" alt="Right Column Pubs 2" width="300" height="464"> -->
		</div>
	</div>
</div>
</div>
</div>

<!-- Footer -->
<div class="footer_container">
	<div class="footer">
		<div class="footer_wide">
			<div class="company_manifesto">
				<h3>See More</h3>
				<div class="footer_bottom_list_links">
					<ul>
						<li><a href="<?php echo tep_href_link('pages/about-us'); ?>">About Us</a></li>
						<li><a href="<?php echo tep_href_link('articles/'); ?>">Latest Articles</a></li>
						<li><a href="<?php echo tep_href_link('pages/terms-and-conditions'); ?>">Terms of Use</a></li>
						<li><a href="<?php echo tep_href_link('pages/privacy-policy'); ?>">Privacy Policy</a></li>
						<li><a href="<?php echo tep_href_link('pages/faqs'); ?>">Frequent Questions</a></li>
						<li><a href="<?php echo tep_href_link(FILENAME_CONTACT_US); ?>">Contact Us </a></li>
					</ul>
				</div>
			</div>
			<?php
			// Top 24 NZ Pokies go in the footer
			$listing_query = tep_db_query("select p.pub_name, p.pub_url, l.location_url, lz.location_zone_url from " . TABLE_PUBS . " p left join " . TABLE_LOCATIONS . " l on p.location_id = l.location_id left join " . TABLE_LOCATIONS_ZONES . " lz on l.location_zone_id = lz.location_zone_id where p.status = '1' order by p.number_of_visits DESC limit 24");
			$row = 1;
			if (tep_db_num_rows($listing_query)) {
				while ($listing = tep_db_fetch_array($listing_query)) {
					if ($row % 8 == 1) {
			?>
						<div class="footer_list_links">
							<h3>
								<?php if ($row == 1) {
									echo 'Top New Kiwis Pokies';
								} else {
									echo '&nbsp;';
								} ?>
							</h3>
							<ul>
							<?php
						}
							?>
							<li> <a href="<?php echo tep_href_link($listing['location_zone_url'] . '/' . $listing['location_url'] . '/' . $listing['pub_url'] . '/'); ?>"><?php echo $listing['pub_name']; ?></a> </li>
							<?php
							$row++;
							if ($row % 8 == 1) {
							?>
							</ul>
						</div>
				<?php
							}
						}
				?>
				<?php
				if ($row % 8 != 1) {
				?>
					</ul>
		</div>
<?php
				}
			}
?>

	</div>
	<div class="social">
		<a href="#" target="_blank" class="facebook"></a>
		<!--<a href="#" class="twitter"></a>
			<a href="#" class="google_plus"></a>-->
	</div>
	<div class="copyright">Copyright <?php echo date('Y'); ?> LocalPokies.co.nz </div>
</div>
</div>
<div id="dialog_holder" style="display:none;"> </div>
<div id="loading" style="display:none;">
	<div class="popup_form_width"> <img src="images/large.gif" width="32" height="32" alt="Please Wait While We Process Your Request ..."> <br />
		<br />
		Please Wait While We Process Your Request ...
	</div>
</div>
<?php
if (($PHP_SELF == 'pubs.php') || ($PHP_SELF == 'articles_details.php')) {
?>
	<div id="fb-root"></div>
	<script>
		(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s);
			js.id = id;
			js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
			fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));
	</script>
<?php
}
?>
<script type="text/javascript" src="jquery/javascripts1.4.js"></script>
<script>
	$(function() {
		var availableTags = [
			<?php
			$listing_query = tep_db_query("select pub_name from " . TABLE_PUBS . " where status = '1' order by pub_name");
			while ($listing = tep_db_fetch_array($listing_query)) {
				$list .= '"' . addcslashes($listing['pub_name'], "\\\'\"&\n\r<>") . '",';
			}
			$list = substr($list, 0, -1);
			echo $list;
			?>
		];
		$("#search_box").autocomplete({
			source: function(request, response) {
				var results = $.ui.autocomplete.filter(availableTags, request.term);
				if ((results.length) > 12) {
					results = results.slice(0, 12);
				}
				response(results);
			},
			select: function(event, ui) {
				$(event.target).val(ui.item.value);
				$('#search_form').submit();
				return false;
			},
			open: function(request) {
				var count = $('ul.ui-autocomplete').children().length;
				var searched_value = $('#search_box').val();
				// Adding the Show more results link
				if (count == 12) {
					$('<li id="ac-add-venue"><a href="<?php echo HTTP_SERVER . DIR_WS_HTTP_CATALOG; ?>/advanced_search_result.php?keywords=' + searched_value + '" id="more_results">Show more results</a></li>').appendTo('ul.ui-autocomplete');
				}
			},
			delay: 100,
			minLength: 2

		});
		// $('#zones_fixer').on('click', 'a', function(event) {
		// 	// Prevent the default behavior of the <a> tag
		// 	event.preventDefault();

		// 	// Get the href attribute value of the clicked <a> tag
		// 	var href = $(this).attr('href');

		// 	// Do something with the href value
		// 	console.log('The link was clicked: ' + href);
		// });
	});
</script>
<?php if ($PHP_SELF == 'submit_pub.php') { ?>
	<script src="jquery/redactor.min.js"></script>
	<!-- Redactor's plugin -->
	<script src="jquery/fontsize.js"></script>
	<script src="jquery/fontfamily.js"></script>
	<script type="text/javascript">
		$(function() {
			$('#pub_description').redactor({
				focus: false, // If redactor gets focused on load or not
				linebreaks: false, // If true, then it uses <br>, otherwise it uses <p>
				boldTag: 'b', // If this is not set, then for bold <strong> is used
				italicTag: 'i', // If this is not set, then for bold <em> is used
				buttons: ['html', '|', 'bold', 'italic', 'underline', 'fontcolor'], // these are all the default buttons, remove what is not necessary
				formattingTags: ['p', 'h2', 'h3', '<br>'], // List what tags we allow for formating
				buttonSource: false, // Show or not show the source button (for editing HTML)
				cleanup: true, // Auto cleanup of copy/pasted text
				paragraphy: false, // Not sure what this is
				convertLinks: true, // automatically convert links to hyperlinks
				convertDivs: false, // convert divs to p
				minHeight: 250, // pixels
				removeEmptyTags: false, //remove empty tags?
				pastePlaintText: false
			});
		});
	</script>
<?php } ?>

<!-- Google Webfont Loader -->
<script type="text/javascript">
	WebFontConfig = {
		google: {
			families: ['Open+Sans:400,300,600,700', 'Roboto+Condensed:400,700']
		}
	};
	(function() {
		var wf = document.createElement('script');
		wf.src = 'https://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
		wf.type = 'text/javascript';
		wf.async = 'true';
		var s = document.getElementsByTagName('script')[0];
		s.parentNode.insertBefore(wf, s);
	})();
</script>
<!-- Google Webfont Loader -->
</body>

</html>
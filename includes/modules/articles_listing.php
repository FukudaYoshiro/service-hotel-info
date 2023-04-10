<?php
	// Limit the amount of posts on the homepage
	$listing_split = new splitPageResults($listing_sql, 30, 'a.article_id');

	if ($listing_split->number_of_rows > 0) {
		$rows = 0;
		$listing_query = tep_db_query($listing_split->sql_query);
		while ($listing = tep_db_fetch_array($listing_query)) {
			$rows++;
			$pictures = explode (',',$listing['article_pictures']);
			$article_name =	$listing['article_name'];
			if (strlen($article_name) > 50) {
				$article_name = preg_replace('/\s+?(\S+)?$/', '', substr($article_name, 0, 75)).' ...';	
			}
			$article_description =	$listing['article_description'];
			if (strlen($article_description) > 300) {
				$article_description = strip_tags(preg_replace('/\s+?(\S+)?$/', '', substr($article_description, 0, 300))).' ... ';
			}

			?>
			<div class="article_preview">
				<div class="article_preview_title">
					<h2><a href="<?php echo tep_href_link('articles/'.$listing['article_url'].'/'); ?>"><?php echo $article_name ;?></a></h2>
					<?php echo get_like_button('article',$listing['article_id'],$listing['likes']);?>
				</div>
				<div class="article_preview_container">
					<div class="article_preview_picture">
						<a href="<?php echo tep_href_link('articles/'.$listing['article_url'].'/'); ?>">
							<img src="images/article_small/<?php echo str_replace(' ','%20',$pictures[0]); ?>" alt="<?php echo $article_name ;?>" width="90" height="124"/>
						</a>
					</div>
					<div class="article_preview_story">
						<p><?php echo $article_description;?></p>
						<p><span class="italic_text">Added on <?php echo tep_date_long($listing['date_added']);?></span></p>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		<div class="bottom_filters">
			<div class="filter_right">
				<div class="filter_label">
					Page:
				</div>
				<div class="page">
					<?php echo $listing_split->display_links(30, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?>
				</div>
			</div>
		</div>
	<?php
	} else {
		echo '<div class="details"><p>Currently, there are no blog articles to list on the site.</p></div>';
	}
?>

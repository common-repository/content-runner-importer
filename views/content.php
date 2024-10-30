<?php
	include( plugin_dir_path( __FILE__ ) . "content-header.php" );

	if ( get_option('crpi_valid_auth') == '1' && $_SESSION['crpi_password'] ) { // if authenticated and password is in session ?>

		<p class="instructions">
			Browse your content and choose articles to import.
			Articles are grouped by order number. Click the plus and minus
			icons in the first column to view information about individual
			articles. Click the checkboxes associated with each article to
			select them for import. Once you have selected all the articles
			you would like imported, choose whether you would like them to be
			imported as posts, pages, or a custom post type. Then click 'Import.'
		</p>

		<?php
		//message if user did not choose any articles or options
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['submit'] == 'Import'
					&& ( empty($checkedArticles) || $postType == '' ) ) {
			echo '<div class="error"><p>
					Make sure you select the articles you would like to import!
					</p></div>';
		}

		if ( isset($authMsg) && $authMsg != '' ) {
			echo $authMsg;
			exit();
		}

		//unset the session 'nonce' var when the user visits this page
		if ( isset( $_SESSION['crpi_nonce'] ) ) {
			unset( $_SESSION['crpi_nonce'] );
		}

		//$curPage = isset($_GET['cr-page-num']) ? $_GET['cr-page-num'] : '1';

		//for pagination
		$recordsPerPage = $data['pagination']['per_page'];
		$totalRecords = $data['pagination']['total'];
		$totalPages = $data['pagination']['last'];
		$currentPage = $data['pagination']['page'];
		$isGap = false;
		$lastPageCount = $totalRecords % $recordsPerPage;

		if ( $totalPages > 1 ) { //if there is more than 1 page of pagination
			echo '<div>';
				echo '<ul class="pagination-list">';
					if ($currentPage != 1) {
						echo '';
					} else {
						echo '<li class="previous-page"><a href="' . $_SERVER['PHP_SELF'] . '?page=view-content' . '&cr-page-num=' . ($currentPage - 1) . '">&#171; Previous</a></li>';
					}

					for ($i = 1; $i <= $totalPages; $i++) {

						if ( $totalPages > 14 ) {

							//are we at a gap?
							if ( $i < 3 || ($i >= $currentPage - 2 && $i <= $currentPage + 2) || $i > $totalPages - 2 ) {

								$liClass = $currentPage == $i ?  ' class="current-page"' : '';
								echo '<li' . $liClass . '><a href="' . $_SERVER['PHP_SELF'] . '?page=view-content' . '&cr-page-num=' . $i . '">'. $i . '</a></li>';
								$isGap = false;

							} else {

								if ( ! $isGap ) {
									echo '<li class="pagination-no-style">.....</li>';
									$isGap = true;
								}
							}
						} else { // $totalPages of 10 or less
							$liClass = $currentPage == $i ?  ' class="current-page"' : '';
							echo '<li' . $liClass . '><a href="' . $_SERVER['PHP_SELF'] . '?page=view-content' . '&cr-page-num=' . $i . '">'. $i . '</a></li>';
						}
					} //end for $totalPages

					if ($currentPage == $totalPages) {
						echo '';
					} else {
						echo '<li class="next-page"><a href="' . $_SERVER['PHP_SELF'] . '?page=view-content' . '&cr-page-num=' . ($currentPage + 1) . '">Next &#187;</a></li>';
					}

				echo '</ul>';
			echo '</div>';
		} //end if $data['pagination']['last'] > 1

		//pagination status ?>
		<div class="pagination-status">
			<?php if ($currentPage == $totalPages) {
						$recordsPerPage = $lastPageCount;
				  } ?>
			<p>Showing <?php echo $totalRecords < $recordsPerPage ? $totalRecords : $recordsPerPage; ?> of <?php echo $totalRecords; ?> orders</p>
		</div><!-- /pagination-status -->

		<div class="clear"></div>

		<form method="post" action="">

			<input name="submit" type="submit" value="Import" />
			<br />

			<table class="crpi-table">
				<thead>
					<th class="header-center"><a href="#" class="show-hide-all">Show All Articles</a></th>
					<th>Order#</th>
					<th>Notes</th>
					<th>Date</th>
					<th>
						<input type="checkbox" id="check-all" name="check-all" label="check all" />
						<label for="check-all">Select All</label>
					</th>
				</thead>

				<?php
					$counter = 1; //set up counter variable

					//loop through returned cURL data and display it in a table
					foreach ($data['articles'] as $orderNum => $item) { ?>

					<tr class="<?php echo ($counter % 2 == 0) ? 'even' : 'odd'; ?>">
						<td class="col-1"><a href="#details-<?php echo $counter; ?>" class="show-hide"></a></td>
						<td class="col-2"><?php echo $orderNum; ?></td>
						<td class="col-3"><?php echo $item['notes']; ?></td>
						<td class="col-4"><?php echo $item['created_at']; ?></td>
						<td class="col-5"></td>
					</tr>

					<tr id="details-<?php echo $counter; ?>" class="details">
						<td class="col-1"></td>
						<td colspan="4">
						<table class="crpi-inner-table">
							<thead>
								<th style="background: #fff;" class="inner-col-2"><div>Article ID</div></th>
								<th style="background: #fff;" class="inner-col-3-4"><div>Title</div></th>
								<th style="background: #fff;" class="inner-col-5"><div>Import</div></th>
								<th style="background: #fff;" class="inner-col-5"><div>Previously Imported</div></th>
							</thead>

						<?php
							//loop through the articles and display them in a sub-table
							if ( !empty($item['articles']) ) { // if articles is not empty
								foreach ($item['articles'] as $articleId => $article) { ?>
								<tr>
									<td class="inner-col-2"><div><?php echo $articleId; ?></div></td>
									<td class="inner-col-3-4"><div <?php echo $article['status_id'] != '6' ? 'class="greyout"' : '' ; ?>><?php echo $article['title']; ?></div></td>
									<td class="inner-col-5">
										<div>
											<?php if ($article['status_id'] == '6') { ?>
												<input type="checkbox"
													id="<?php echo $articleId; ?>" class="checkbox"
													name="checked_articles[]" value="<?php echo $articleId; ?>"
													<?php if(!empty($_POST['checked_articles'])
														&& in_array($articleId, $checkedArticles)) {
															echo ' checked="checked"';
													} //end if !empty($_POST['checked_articles'] ?> />
												</div>
											<?php } //end if $article['status_id'] == '6' ?>
									</td>
									<td class="inner-col-6">
										<div>
											<?php
											foreach ($dbResults as $row) {
												if ($articleId == $row->article_id) {
													echo date("n/j/Y", strtotime($row->datetime)) . '&nbsp;';
												}
											} ?>
										</div>
									</td>
								</tr>
						<?php  } //end foreach ($item['articles'])
							} //endif ?>

						</table><!-- /.crpi-inner-table -->
						</td>
					</tr>

			<?php
						$counter++; //increment the counter
					} // end foreach ($data) ?>

			</table><!-- /.crpi-table -->

			<br />
			<fieldset>
				<p>
					Import as:&nbsp;

					<input type="radio" name="crpi_post_type" id="post-radio-btn"
						value="post" <?php
								if ( $postType == 'post' || $postType == '' ) {
									echo 'checked="checked"';
								} ?> />
						<label for="post-radio-btn">Posts</label>

					&nbsp;

					<input type="radio" name="crpi_post_type"
						id="page-radio-btn" value="page" <?php
								if ( $postType == 'page' ) {
									echo 'checked="checked"';
								} ?> />
						<label for="page-radio-btn">Pages</label>

					<?php if ($custPostTypes) { //if custom post types exist in the wp database ?>

					&nbsp;

					<input type="radio" name="crpi_post_type"
						id="custom-radio-btn" value="custom" <?php
								if ( $postType == 'custom' ) {
									echo 'checked="checked"';
								} ?> />
						<label for="page-radio-btn">Custom Post Type</label>

					<?php } //end if ($custPostTypes) ?>
				</p>
				<div id="cust-post-types-dropdown" class="hidden">
					Choose a Post Type:&nbsp;
					<div class="styled-select">
						<div class="styled-select-top">
							<div class="text"><?php echo $custPostTypes[0]; ?></div>
							<div class="arrow"></div>
						</div>
						<input type="hidden" onchange="$(this).closest('form').submit()" value="<?php echo $custPostTypes[0]; ?>" name="custom_post_type">
						<ul class="dir-order-by-list">
							<?php
							foreach ($custPostTypes as $type) {
								echo '<li data-val="' . $type . '">' . $type . '</li>';
							}
							?>
						</ul>
					</div><!-- end .filter-order-by -->
				</div><!-- end cust-post-types-dropdown -->

			</fieldset>

			<p>
				<input name="submit" type="submit" value="Import" />
			</p>

		</form>

<?php	} else {

		echo '<div class="error"><p>You have not authenticated yet.
					Please visit the Settings page and enter your username and
						api key.</p></div>';

?>		</div><!-- /.wrap -->
	<div class="clear"></div>

<?php } //end if authenticated
/*END OF FILE*/
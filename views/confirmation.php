<?php
	include( plugin_dir_path( __FILE__ ) . "content-header.php" );

	if ( isset($authMsg) && $authMsg != '' ) {
		echo $authMsg;
		exit();
	}
?>

<div class="updated highlight">
	<p>
		You have imported the following articles into WordPress!<br />
		Post Type:&nbsp;<?php echo $postType; ?><br />
		Post Status: draft
	</p>
</div>
<table class="crpi-table crpi-confirm-table">
	<thead><th class="check-mark">Imported</th><th>Article ID</th><th>Title</th></thead>

	<?php 
		foreach ($data['article_contents'] as $articleId => $value) {
			
			//build an array for inserting articles into wp database
			$args = array(
				'post_title'    => $value['title'],
				'post_content'  => $value['content'],
				'post_status'   => 'draft',
				'post_type'		=> $postType
				// 'post_author'   => 1,
				// 'post_category' => array(8,39)
			);
			
			//if the session 'nonce' var is not set insert stuff into db
			if ( ! isset($_SESSION['crpi_nonce']) ) {
				
    			$postInsertId = wp_insert_post( $args ); //insert post/page into wp db
				
				if ($postInsertId != 0) {
					//insert article data into our wp db table for tracking purposes
					$inserted = crpi_insert_data($articleId, $value['title'], $postInsertId);
				}
			}
			
			?>
			<tr>
				<td class="check-mark"><img src="<?php echo PLUGIN_FOLDER; ?>images/green-check.png" alt="Success" /></td>
				<td><?php echo $articleId; ?></td>
				<td><?php echo $value['title']; ?></td>
			</tr>
	<?php
		} // end foreach ($data)
		
		//set a session 'nonce' var to prevent re-insertion into the db
		if ( ! isset($_SESSION['crpi_nonce']) ) {
			$_SESSION['crpi_nonce'] = '987654321';
		}
		?>
</table><!-- /.main-table -->
</div><!-- /.wrap -->
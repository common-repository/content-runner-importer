<div class="wrap">
	<?php if (HEADER_LOGO_EXTENSION != '::extension::') { ?>
				<img src="<?php echo PLUGIN_FOLDER; ?>images/header-logo.<?php echo HEADER_LOGO_EXTENSION; ?>" alt="Logo"/>
	<?php 
		  } //end if HEADER_LOGO_EXTENSION
	
		  if ( get_option('crpi_username') ) {
				$crpiUsername = get_option('crpi_username'); ?>
			<div class="alignright name-box">
				<p><strong>Hello, <?php echo $crpiUsername; ?>!</strong></p>
			</div>
	<?php } else { ?>
			<div class="alignright name-box">
				<p>
					Visit the
					<a href="<?php echo admin_url( 'admin.php?page=content-settings' ); ?>">
						Settings page
					</a>
					to authenticate!
				</p>
			</div>
	<?php } ?>
	
	<h2>Import your content to your site, let&rsquo;s get started!</h2>
	<p class="instructions">
		Welcome! To use this plugin you must:
		<br /><br />
		<strong>1.</strong> Obtain an API username and API key. If you have not yet obtained a username
		and api key you can obtain one <a href="https://www.contentrunner.com" target="_blank">here</a>.
		<br />
		<strong>2.</strong> Visit the Settings page to authenticate your API username and API key.
		<br />
		<strong>3.</strong> After authentication, visit the View Content page and browse articles you would like to import.
	</p>
	
	<?php
	
	if ( get_option('crpi_valid_auth') != '1' ) {
		echo '<div class="error"><p>You have not authenticated yet. 
					Please visit the Settings page and enter your username and
						api key.</p></div>';
	}
	?>
</div>
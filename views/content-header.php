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
				<p>Visit the <a href="<?php echo admin_url( 'admin.php?page=content-settings' ); ?>">Settings page</a> to authenticate!</p>
			</div>
	<?php } ?>
	
	<h2>View Content</h2>
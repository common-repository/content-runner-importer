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
	
	<h2>Settings</h2>
	
	<?php
	
	if ( $authMsg != '' ) {
		echo $authMsg;
	}
	
	?>
		<br />
		<form action="" method="post">
			<div id="crpi-plugin-settings-form">
				<div id="settings-form-header">
						<h4>Enter API username and key to authenticate</h4>
						<div class="clear"></div>
				</div>
				<?php
					// register the name of our options group
					settings_fields('crpi_options');
				?>
				<p><label for="crpi-username">Username:</label><br />
					<input type="text" id="crpi-username" name="crpi_username" maxlength="60"
						value="<?php echo get_option('crpi_username') ? get_option('crpi_username') : $username; ?>" /></p>
				<p><label for="crpi-api-key">API Key:</label><br />
					<input type="text" id="crpi-api-key" name="crpi_api_key" maxlength="60"
						value="<?php echo get_option('crpi_api_key') ? get_option('crpi_api_key') : $apiKey; ?>" /></p>
				<p>
					<input name="submit" type="submit" value="Authenticate" />
				</p>
			</div>
		</form>
</div>
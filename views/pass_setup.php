<div class="wrap">
	<?php if (HEADER_LOGO_EXTENSION != '::extension::') { ?>
				<img src="<?php echo PLUGIN_FOLDER; ?>images/header-logo.<?php echo HEADER_LOGO_EXTENSION; ?>" alt="Logo"/>
	<?php } ?>
	<h2>Set Up Your Password</h2>
	<p class="instructions">
		This is to keep another WordPress user from viewing your content.
	</p>
	<?php
	
	if ( isset($passMsg) && $passMsg != '' ) {
		echo $passMsg;
	}
	
	?>
	<form action="" method="post" autocomplete="off">
		<div id="crpi-plugin-settings-form">
			<div id="settings-form-header">
				<h4>Choose a password for this plugin</h4>
				<div class="clear"></div>
			</div>
			<p>
				<label for="crpi-password">
					Enter the password you'd like to use:
				</label><br />
				<input type="password" id="crpi-password" name="crpi_password" maxlength="12" autocomplete="off" />
			</p>
			<p>
				<label for="crpi-2nd-password">
					Re-enter your password:
				</label><br />
				<input type="password" id="crpi-2nd-password" name="crpi_2nd_pass" maxlength="12" autocomplete="off" />
			</p>
			<p>
				<label for="crpi-email">
					Your email address:
				</label><br />
				<input type="text" id="crpi-email" name="crpi_email" maxlength="50" autocomplete="off" />
			</p>
			<p>
				<input name="submit" type="submit" value="Set Password" />
			</p>
		</div>
	</form>
</div>
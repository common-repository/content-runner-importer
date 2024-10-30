<div class="wrap">
	<?php if (HEADER_LOGO_EXTENSION != '::extension::') { ?>
			<img src="<?php echo PLUGIN_FOLDER; ?>images/header-logo.<?php echo HEADER_LOGO_EXTENSION; ?>" alt="Logo"/>
	<?php } ?>
	<h2>Confirm Your Password</h2>
	<br />
	<?php
	
	if ( isset($passMsg) && $passMsg != '' ) {
		echo $passMsg;
	}
	
	?>
	<form action="" method="post" autocomplete="off">
		<div id="crpi-plugin-settings-form">
			<div id="settings-form-header">
				<h4>Plugin secured via password</h4>
				<div class="clear"></div>
			</div>
			<p>
				<label for="crpi-password">Enter password:</label><br />
				<input type="password" id="crpi-password" name="crpi_pass_check" maxlength="60" autocomplete="off"
					value="<?php echo isset($crpiPassCheck) ? $crpiPassCheck : ''; ?>" />
			</p>
			<p class="confirm-form-buttons">
				<input type="submit" id="pass-confirm-submit" name="submit" value="Go!" />
				<input type="submit" id="forgot-pass" name="crpi_forgot_pass" value="Forgot Password?" />
			</p>
			<div class="clear"></div>
		</div>
	</form>
</div>
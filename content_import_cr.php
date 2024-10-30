<?php
/*
Plugin Name: Content Runner Importer
Description: Import quality content to your website
Version: 1.0.2
Author: Content Runner
License: GPLv2
*/

/*
Copyright 2013 Damien Smith, Matt Peters, and Larry Fiedler

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



require_once __DIR__.'/config.php'; //require the config file

//constant for the path to the plugin folder
define('PLUGIN_FOLDER', plugin_dir_url(__FILE__));

//function to start a session
function crpi_start_session() {
    if( !session_id() ) {
        session_start();
    }
}

///function to end the session
function crpi_end_session() {
    session_destroy();
}

//hooks for session
add_action('init', 'crpi_start_session', 1);
add_action('wp_logout', 'crpi_end_session');
add_action('wp_login', 'crpi_end_session');

//function to initialize fields in the wp options table
function crpi_init()
{
	register_setting('crpi_options', 'crpi_username');
	register_setting('crpi_options', 'crpi_api_key');
	register_setting('crpi_options', 'crpi_password');
	register_setting('crpi_options', 'crpi_email');
	register_setting('crpi_options', 'crpi_token');
}
//hook for init
add_action('admin_init', 'crpi_init');


//function to do stuff when the plugin is activated
function crpi_activate()
{
	crpi_create_db_table(); //create the db table for tracking imports if not exists
}

register_activation_hook(__FILE__, 'crpi_activate');

//function to do stuff when the plugin is deactivated
function crpi_deactivate()
{
	//do stuff when the plugin is deactivated
}

//register_deactivation_hook(__FILE__, 'crpi_deactivate');

//function to create our custom database table
function crpi_create_db_table()
{
	global $wpdb;

	$tableName = $wpdb->prefix . "crpi_imported_articles";

	$sql = "CREATE TABLE IF NOT EXISTS {$tableName} (
			import_id	INT UNSIGNED NOT NULL AUTO_INCREMENT,
			article_id	INT,
			title		varchar(240),
			post_id		INT,
			datetime 	datetime DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (import_id)
	);";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

//function for inserting data into crpi_imported_articles wp db table
function crpi_insert_data($articleId, $title, $postId)
{
	global $wpdb;
	$tableName = $wpdb->prefix . "crpi_imported_articles";

	$rowsAffected = $wpdb->insert( $tableName, array(
	  					'article_id' => $articleId,
	  					'title' => $title,
	  					'post_id' => $postId,
	  					'datetime' => current_time('mysql')
					)
				);

	return $rowsAffected;
}

// for encrypting plugin password
function crpi_retrieve_data()
{
	global $wpdb;
	$tableName = $wpdb->prefix . "crpi_imported_articles";

	//$sql = "SELECT article_id, datetime FROM {$tableName}";

	$results = $wpdb->get_results(
		"
		SELECT article_id, datetime
		FROM {$tableName}
		"
	);

	//$results = $wpdb->query($sql);

	return $results;
}

//for pulling custom post types from the wp database
function crpi_retrieve_post_types()
{
	$args = array(
	   'public'   => true,
	   '_builtin' => false
	);

	$output = 'names'; // names or objects, note names is the default
	$operator = 'and'; // 'and' or 'or'

	$postTypes = get_post_types( $args, $output, $operator );

	return $postTypes;
}

//function to add the plugin's pages to the admin dashboard menu
function crpi_plugin_menu()
{
	add_menu_page(
		"Content Runner Importer",
		PROVIDER_NAME,
		"manage_options",
		"content-plugin",
		"crpi_splash_page"
		//path_join( WP_PLUGIN_URL, basename( dirname( __FILE__ ) ) . "/images/cr_icon.png")
	);

	add_submenu_page(
		"content-plugin",
		"Settings",
		"Settings",
		"manage_options",
		"content-settings",
		"crpi_settings_page"
	);

	add_submenu_page(
		"content-plugin",
		"View Content",
		"View Content",
		"manage_options",
		"view-content",
		"crpi_content_page"
	);
}

//add the plugin menu to the admin menu
add_action('admin_menu', 'crpi_plugin_menu');


//add styles to admin pages
function crpi_admin_styles()
{
	wp_register_style( 'admin', PLUGIN_FOLDER . 'css/admin.css' );
	wp_enqueue_style( 'admin' );
}

//hook the styles
add_action( 'admin_menu', 'crpi_admin_styles' );

//add javascript to admin pages
function crpi_plugin_script()
{
    wp_register_script( 'plugin-script', PLUGIN_FOLDER . 'js/plugin.js' );
    wp_enqueue_script( 'plugin-script', array('jquery'), NULL, NULL, TRUE );
}

//hook the javascript
add_action( 'admin_menu', 'crpi_plugin_script' );

/*
 * function for api/curl request
 * @params: username (string), api key (string), data (array), action (string), page number (string)
 * @return: curl response in the form of an associative array
 */
function crpi_api_request($username, $apiKey, $data, $action, $pageNum = '1')
{
	//username is public key, api key is private key
	$publicKey = $username;
	$privateKey = $apiKey;

	//hash the data passed in using the private key
	$hash = hash_hmac('sha256', json_encode($data), $privateKey);

	//headers containing the public key and hash... for authentication purposes
	$headers = array(
		"X-Auth: $publicKey",
		"X-Auth-Hash: $hash",
		'X-API-Version: 1.0.2'
	);

	//format the passed in data for posting via curl
	$postData = array(
		'data' => $data
	);

	$curlObj = curl_init(); //init curl obj

	$curl_options =  array(
		CURLOPT_RETURNTRANSFER => 1, //return the raw output
		
		CURLOPT_HTTPHEADER => $headers,
		CURLOPT_URL => "https://www.contentrunner.com/api/" . $action . '?page=' . $pageNum, //where the data is coming from		
		CURLOPT_POST => true, //do a regular HTTP POST		
		CURLOPT_POSTFIELDS => http_build_query($postData) //The full data to post in a HTTP "POST" operation
	);

	curl_setopt_array($curlObj, $curl_options); //set the options

	$response = curl_exec($curlObj); //get the response

	$httpStatus = curl_getinfo($curlObj, CURLINFO_HTTP_CODE); //get the returned HTTP code

	//put the response body and response HTTP code into an array
	$statusAndBody = array(
		'HTTP_Status' => $httpStatus,
		'Body' => $response
	);

	return $statusAndBody;
}

//function for creating splash/home page
function crpi_splash_page()
{
	if (CRPI_PASSWORD_REQUIRED) { //if password is required (for white-labeled version of plugin)

		$passMsg = ''; //var for password message

		if ( isset($_SESSION['crpi_password']) ) { //if a password is in the session

			include( plugin_dir_path( __FILE__ ) . 'views/splash.php' );

		} else { // there's no password in the session

			if ( get_option('crpi_password') ) { //if a password exists in the options table

				//if the password confirm form is submitted via the 'Go!' button
				if ( $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit']) ) {

					$crpiPassCheck = isset($_POST['crpi_pass_check']) ? $_POST['crpi_pass_check'] : '';

					//compare the password entered by the user to the stored password
					if ( wp_check_password( $crpiPassCheck, get_option('crpi_password') ) ) { //match!!

						$_SESSION['crpi_password'] = get_option('crpi_password'); //put password in the session
						include( plugin_dir_path( __FILE__ ) . 'views/splash.php' );

					} elseif ( empty($crpiPassCheck) ) { //submitted password confirm form without entering anything

						$passMsg = '<div class="error"><p>Please enter your password.</p></div>';
						include( plugin_dir_path( __FILE__ ) . 'views/pass_confirm.php' );

					} else { // didn't match the password in wp options table

						$passMsg = '<div class="error"><p>Does not match the password on file!
									Please make sure you have the right password and try again.</p></div>';
						include( plugin_dir_path( __FILE__ ) . 'views/pass_confirm.php' );
					}
				} elseif ( $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crpi_forgot_pass']) ) { //if the forgot password button was hit

					//generate a token and put it in the database
					$crpiToken = md5( get_option('crpi_email') . date('Y-m-d') );
					$updateToken = update_option( 'crpi_token', $crpiToken );

					//set the url that the link inside the email will go to
					function getCurrentUrl() {
					    $protocol = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
					    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
					}

					$currentUrl = getCurrentUrl();
					$url = $currentUrl . "&crpi-token={$crpiToken}";

					//send a reset password email
					$userEmail = get_option( 'crpi_email' );

					$fromPerson = 'Content Runner Importer';
					//$fromEmail = $email; //leaving 'from email' blank should default to wp email
					$toEmail = $userEmail;

					$subject = 'Password reset';

					$emailMessage = "You requested a reset of your Content Runner Importer password.
								You can reset your plugin password <a href='{$url}'>here</a>.";

					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-Type: text/html; charset=iso-8859-1' . "\r\n";
					$headers .= "From: $fromPerson\r\n";

					$message = $emailMessage . '<br />' . $fromPerson;

					$mailit = mail($toEmail, $subject, $message, $headers);

					if ($mailit) {
						$passMsg = '<div class="error"><p>An email has been sent to you with a link to
									reset your password!</p></div>';

						include( plugin_dir_path( __FILE__ ) . 'views/pass_confirm.php' );
					} else {
						$passMsg = '<div class="error"><p>An error was encountered sending password reset email.</p></div>';

						include( plugin_dir_path( __FILE__ ) . 'views/pass_confirm.php' );
					}
				} elseif ( $_GET['crpi-token'] && $_GET['crpi-token'] == get_option('crpi_token') ) { //if user is trying to reset their password

					//wipe the current password from the database
					$deleted = delete_option('crpi_password');
					//send them to the password setup form
					include ( plugin_dir_path( __FILE__ ) . "views/redirect.php" );

				} else { //show the password confirm form by default
					include( plugin_dir_path( __FILE__ ) . 'views/pass_confirm.php' );
				} // end if password confirm submitted

			} else { //no password in the options table

				if ( $_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['submit'] == 'Set Password' ) { //if pass_setup form is submitted

					$crpiPassword = isset($_POST['crpi_password']) ? $_POST['crpi_password'] : '';
					$crpi2ndPass = isset($_POST['crpi_2nd_pass']) ? $_POST['crpi_2nd_pass'] : '';
					$crpiEmail = isset($_POST['crpi_email']) ? $_POST['crpi_email'] : '';

					if ( !empty($crpiPassword) && !empty($crpi2ndPass) && !empty($crpiEmail) && filter_var($crpiEmail, FILTER_VALIDATE_EMAIL) && $crpiPassword == $crpi2ndPass) { //if everything's peachy

						$passUpdate = update_option( 'crpi_password', wp_hash_password($crpiPassword) );
						$emailUpdate = update_option( 'crpi_email', $crpiEmail );
						$_SESSION['crpi_password'] = get_option('crpi_password'); //put password into the session
						include( plugin_dir_path( __FILE__ ) . 'views/splash.php' );

					} elseif ( empty($crpiPassword) || empty($crpi2ndPass) ) { //one of the inputs is blank

						$passMsg = '<div class="error"><p>Please fill out both password fields.</p></div>';
						include( plugin_dir_path( __FILE__ ) . 'views/pass_setup.php' );

					} elseif ( empty($crpiEmail) || ! filter_var($crpiEmail, FILTER_VALIDATE_EMAIL) ) { //if email is ok

						$passMsg = '<div class="error"><p>Please enter a valid email address.</p></div>';
						include( plugin_dir_path( __FILE__ ) . 'views/pass_setup.php' );

					} elseif ($crpiPassword != $crpi2ndPass) { //user's password entries don't match
						$passMsg = '<div class="error"><p>Passwords do not match! Make sure you
									enter the same password twice.</p></div>';
						include( plugin_dir_path( __FILE__ ) . 'views/pass_setup.php' );
					}
				} else { //show the password setup form by default
					include( plugin_dir_path( __FILE__ ) . 'views/pass_setup.php' );
				} //end if pass_setup form is submitted
			} //end if password is in wp database
		} //end if password is in the session
	} else { //no password required
		$_SESSION['crpi_password'] = 'password'; //put a password in the session
		include( plugin_dir_path( __FILE__ ) . 'views/splash.php' );
	}//end if password required
} // end crpi_splash_page()

//function for creating the settings page
function crpi_settings_page()
{
	if ( isset($_SESSION['crpi_password']) ) { //if password is in session

		//variables for form values
		$username = isset($_POST['crpi_username']) ? $_POST['crpi_username'] : '';
		$apiKey = isset($_POST['crpi_api_key']) ? $_POST['crpi_api_key'] : '';
		$authMsg = ''; //var for authentication message

		//if the 'Authenticate' button was pressed
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['submit'] == 'Authenticate'
				&& $username != '' && $apiKey != '' ) {

			$action = 'authenticate'; //function to route the request to

			$data = array('username' => $username); //the body data

			//call the curl function and pass our info
			$response = crpi_api_request($username, $apiKey, $data, $action);

			//decide what to do based on http status code being returned from curl
			switch($response['HTTP_Status']) {
				case '204': //api request action was successful
				case '200': //api request action was successful
					$usernameAdded = update_option('crpi_username', $username);
					$apiKeyAdded = update_option('crpi_api_key', $apiKey);
					$valid_auth = update_option('crpi_valid_auth', '1');

					$authMsg = '<div class="updated highlight"><p>You are authenticated! Visit
						the View Content page and choose articles to import</p></div>';

					include( plugin_dir_path( __FILE__ ) . "views/settings.php" );
					break;
				case '401': //api auth failed
					$authMsg = '<div class="error"><p>Error: ' . $response['HTTP_Status'] .
						'. Authentication failed! Please make sure you have the correct
						username and api key and try again.</p></div>';

					include( plugin_dir_path( __FILE__ ) . "views/settings.php" );
					break;
				case '404': //api action was not found
					$authMsg = '<div class="error"><p>Error: ' . $response['HTTP_Status'] .
						'. Resource not found. Please make sure you have the correct
						username and api key and try again.</p></div>';

				 	include( plugin_dir_path( __FILE__ ) . "views/settings.php" );
					break;
				case '500': //there was a server error
					$authMsg = '<div class="error"><p>Error: ' . $response['HTTP_Status'] .
						'. There is a problem with the server. Please make sure you have
						the correct username and api key and try again.</p></div>';

					include( plugin_dir_path( __FILE__ ) . "views/settings.php" );
					break;
				default:
					$authMsg = '<div class="error"><p>Error: Please make sure you have
						the correct username and api key and try again.</p></div>';

					include( plugin_dir_path( __FILE__ ) . "views/settings.php" );
					break;
			}
		} else {
			include( plugin_dir_path( __FILE__ ) . "views/settings.php" );
		} //end if authenticated
	} else { // no password in session
		include ( plugin_dir_path( __FILE__ ) . "views/redirect.php" );
	}//end if valid password
}

//function for creating the content page
function crpi_content_page()
{
	if ( isset($_SESSION['crpi_password']) ) { //if password is in the session

		//array for the user selected articles to import
		$checkedArticles = !empty($_POST['checked_articles']) ? $_POST['checked_articles'] : array();

		//variable for whether the user wants to import articles as a post or a page
		$postType = isset($_POST['crpi_post_type']) ? $_POST['crpi_post_type'] : '';

		if ($postType == 'custom') { //if the 'Custom Post Type' radio btn is checked
			//post type is now a custom type that has been selected from the dropdown menu
			if (isset($_POST['custom_post_type'])) {
				$postType = $_POST['custom_post_type'];
			} else {
				$postType = '';
			}
		}

		//if the 'Import' submit button was pressed
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['submit'] == 'Import'
					&& !empty($checkedArticles) && $postType != '' ) {

			//get user's info from wp options database
			$username = get_option('crpi_username');
			$apiKey = get_option('crpi_api_key');

			$action = 'pull_content'; //function to route the request to

			$data = array( //data to be posted to the contentrunner api
				'data' => $checkedArticles
			);

			//call the curl function and pass our info
			$response = crpi_api_request($username, $apiKey, $data, $action);
			

			//decode json-- pass 'true' as second param to decode as regular array, not as object
			$data = json_decode($response['Body'], true);
			

			//decide what to do based on http status code being returned from curl
			switch($response['HTTP_Status']) {
				case '200': //api request action was successful, include confirmation view
					include( plugin_dir_path( __FILE__ ) . "views/confirmation.php" );
					break;
				case '401': //api auth failed
					$authMsg = '<div class="error"><p>Error: ' . $response['HTTP_Status'] .
						'. Authentication failed! Please make sure you have the correct
						username and api key and try again.</p></div>';

					include( plugin_dir_path( __FILE__ ) . "views/confirmation.php" );
					break;
				case '404': //api action was not found
					$authMsg = '<div class="error"><p>Error: ' . $response['HTTP_Status'] .
						'. Resource not found. Please make sure you have the correct
						username and api key and try again.</p></div>';

					include( plugin_dir_path( __FILE__ ) . "views/confirmation.php" );
					break;
				case '500': //there was a server error
					$authMsg = '<div class="error"><p>Error: ' . $response['HTTP_Status'] .
						'. There is a problem with the server. Please make sure you have
						the correct username and api key and try again.</p></div>';

					include( plugin_dir_path( __FILE__ ) . "views/confirmation.php" );
					break;
				default:
					$authMsg = '<div class="error"><p>Error: Please make sure you have
						the correct username and api key and try again.</p></div>';

					include( plugin_dir_path( __FILE__ ) . "views/confirmation.php" );
			}
		} else { // 'Import' submit button not pressed yet
			//get user's info from wp options database
			$username = get_option('crpi_username');
			$apiKey = get_option('crpi_api_key');

			/* pull all article ids from our custom wp db table so we
			  can show the user which ones have already been imported */
			$dbResults = crpi_retrieve_data();

			/* pull any custom post types that exist in the wp database so we
			  can give the user the option of importing articles as a custom type */
			$custPostTypes = crpi_retrieve_post_types();
			//turn the associative array into a regular array
			$custPostTypes = array_values($custPostTypes);

			$action = 'list_articles'; //function to route the request to

			$data = array('username' => $username); //data to be posted to the contentrunner api

			$pageNum = isset($_GET['cr-page-num']) ? $_GET['cr-page-num'] : '1';

			//call the curl function and pass our info
			$response = crpi_api_request($username, $apiKey, $data, $action, $pageNum);			

			//decode json-- pass 'true' as second param to decode as regular array, not as object
			$data = json_decode($response['Body'], true);			

			//decide what to do based on http status code being returned from curl
			switch($response['HTTP_Status']) {
				case '200': //api request action was successful, show content view
					include( plugin_dir_path( __FILE__ ) . "views/content.php" );
					break;
				case '401': //api auth failed
					$authMsg = '<div class="error"><p>Error: ' . $response['HTTP_Status'] .
								'. Authentication failed! Please make sure you have the correct
								username and api key and try again.</p></div>';
					include( plugin_dir_path( __FILE__ ) . "views/content.php" );
					break;
				case '404': //api action was not found
					$authMsg = '<div class="error"><p>Error: ' . $response['HTTP_Status'] .
								'. Resource not found. Please make sure you have the correct
								username and api key and try again.</p></div>';
					include( plugin_dir_path( __FILE__ ) . "views/content.php" );
					break;
				case '500': //there was a server error
					$authMsg = '<div class="error"><p>Error: ' . $response['HTTP_Status'] .
								'. There is a problem with the server. Please make sure you have
								the correct username and api key and try again.</p></div>';
					include( plugin_dir_path( __FILE__ ) . "views/content.php" );
					break;
				default:
					$authMsg = '<div class="error"><p>Error: Please make sure you have
								the correct username and api key and try again.</p></div>';
					include( plugin_dir_path( __FILE__ ) . "views/content.php" );
			}
		} //end if $_SERVER['REQUEST_METHOD'] == 'POST'
	} else {
		include ( plugin_dir_path( __FILE__ ) . "views/redirect.php" );
	}//end if password is in session
} //end crpi_content_page function

/*END OF FILE*/
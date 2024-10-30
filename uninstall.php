<?php

if ( !defined('WP_UNINSTALL_PLUGIN') ) {
	exit();
}

delete_option('crpi_username');
delete_option('crpi_api_key');
delete_option('crpi_valid_auth');
delete_option('crpi_password');
delete_option('crpi_email');
delete_option('crpi_token');

global $wpdb;

$tableName = $wpdb->prefix . "crpi_imported_articles";

$sql = "DROP TABLE IF EXISTS {$tableName};";

$wpdb->query( $sql );

/*END OF FILE*/
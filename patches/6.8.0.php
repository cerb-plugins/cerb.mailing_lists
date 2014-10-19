<?php
$db = DevblocksPlatform::getDatabaseService();
$logger = DevblocksPlatform::getConsoleLog();
$tables = $db->metaTables();

// ===========================================================================
// Create initial tables

if(!isset($tables['mailing_list'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS mailing_list (
			id INT UNSIGNED AUTO_INCREMENT,
			name VARCHAR(255) DEFAULT '',
			created_at INT UNSIGNED NOT NULL DEFAULT 0,
			updated_at INT UNSIGNED NOT NULL DEFAULT 0,
			num_members INT UNSIGNED NOT NULL DEFAULT 0,
			PRIMARY KEY (id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);

	$tables['mailing_list'] = 'mailing_list';
}

if(!isset($tables['mailing_list_member'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS mailing_list_member (
			id INT UNSIGNED AUTO_INCREMENT,
			list_id INT UNSIGNED NOT NULL DEFAULT 0,
			address_id INT UNSIGNED NOT NULL DEFAULT 0,
			created_at INT UNSIGNED NOT NULL DEFAULT 0,
			updated_at INT UNSIGNED NOT NULL DEFAULT 0,
			status CHAR(1) NOT NULL DEFAULT '',
			PRIMARY KEY (id),
			UNIQUE list_and_address (list_id, address_id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);

	$tables['mailing_list_member'] = 'mailing_list_member';
}

if(!isset($tables['mailing_list_broadcast'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS mailing_list_broadcast (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) DEFAULT '',
			list_id INT UNSIGNED NOT NULL DEFAULT 0,
			created_at INT UNSIGNED NOT NULL DEFAULT 0,
			updated_at INT UNSIGNED NOT NULL DEFAULT 0,
			PRIMARY KEY (id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);

	$tables['mailing_list_broadcast'] = 'mailing_list_broadcast';
}

if(!isset($tables['mailing_list_broadcast_to_member'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS mailing_list_broadcast_to_member (
			broadcast_id INT UNSIGNED NOT NULL DEFAULT 0,
			address_id INT UNSIGNED NOT NULL DEFAULT 0,
			sent_at INT UNSIGNED NOT NULL DEFAULT 0,
			PRIMARY KEY (broadcast_id, address_id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);

	$tables['mailing_list_broadcast_to_member'] = 'mailing_list_broadcast_to_member';
}
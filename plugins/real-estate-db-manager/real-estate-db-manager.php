<?php
/*
Plugin Name: Real Estate DB Manager
Description: Creates custom database tables for Realtor, Client, Property, Saved Properties, and Inquiries.
Version: 1.0
Author: Md. Kamruzzaman
*/

if (!defined('ABSPATH')) exit;

/**
 * On plugin activation create tables
 */
register_activation_hook(__FILE__, 'redbm_create_tables');

function redbm_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Table definitions
    $tables = [];

    // Realtors table
    $tables[] = "CREATE TABLE {$wpdb->prefix}realtors (
        realtor_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        agency_name VARCHAR(255),
        license_number VARCHAR(100),
        rating_avg FLOAT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (realtor_id)
    ) $charset_collate;";

   // Clients table
    $tables[] = "CREATE TABLE {$wpdb->prefix}clients (
        client_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NULL,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        budget DECIMAL(12,2),
        preferred_location VARCHAR(255),
        note TEXT,
        status VARCHAR(50),
        lead_status ENUM('hot', 'warm', 'cold') DEFAULT 'cold',
        profile_picture VARCHAR(500) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_by BIGINT(20) UNSIGNED NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by BIGINT(20) UNSIGNED NULL,
        deleted_at DATETIME NULL DEFAULT NULL,
        deleted_by BIGINT(20) UNSIGNED NULL,
        PRIMARY KEY (client_id)
    ) $charset_collate;";

    // Properties table
    $tables[] = "CREATE TABLE {$wpdb->prefix}properties (
        property_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        realtor_id BIGINT(20) UNSIGNED NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(12,2) NOT NULL,
        address VARCHAR(255),
        city VARCHAR(100),
        state VARCHAR(100),
        zip VARCHAR(20),
        status ENUM('available','pending','sold') DEFAULT 'available',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (property_id),
        KEY realtor_id (realtor_id)
    ) $charset_collate;";

    // Saved Properties table (client ↔ property relation)
    $tables[] = "CREATE TABLE {$wpdb->prefix}saved_properties (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        client_id BIGINT(20) UNSIGNED NOT NULL,
        property_id BIGINT(20) UNSIGNED NOT NULL,
        saved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY client_id (client_id),
        KEY property_id (property_id)
    ) $charset_collate;";

    // Inquiries table (client ↔ realtor ↔ property)
    $tables[] = "CREATE TABLE {$wpdb->prefix}inquiries (
        inquiry_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        client_id BIGINT(20) UNSIGNED NOT NULL,
        realtor_id BIGINT(20) UNSIGNED NOT NULL,
        property_id BIGINT(20) UNSIGNED NOT NULL,
        message TEXT NOT NULL,
        reply TEXT,
        status ENUM('open','closed') DEFAULT 'open',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (inquiry_id),
        KEY client_id (client_id),
        KEY realtor_id (realtor_id),
        KEY property_id (property_id)
    ) $charset_collate;";

    // Document Types Table
    $tables[] = "CREATE TABLE {$wpdb->prefix}document_types (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        type_name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_by BIGINT(20) UNSIGNED NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by BIGINT(20) UNSIGNED NULL,
        deleted_at DATETIME NULL DEFAULT NULL,
        deleted_by BIGINT(20) UNSIGNED NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Run queries
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    foreach ($tables as $sql) {
        dbDelta($sql);
    }
}
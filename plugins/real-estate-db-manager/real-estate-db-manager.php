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
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50) DEFAULT NULL,
        agency_name VARCHAR(255) DEFAULT NULL,
        license_number VARCHAR(100) DEFAULT NULL,
        rating_avg FLOAT DEFAULT 0,
        profile_picture VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_by BIGINT(20) UNSIGNED DEFAULT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by BIGINT(20) UNSIGNED DEFAULT NULL,
        deleted_at DATETIME DEFAULT NULL,
        deleted_by BIGINT(20) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (realtor_id)
    ) $charset_collate;";

   // Clients table
    $tables[] = "CREATE TABLE {$wpdb->prefix}clients (
        client_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NULL,
        properties_id BIGINT(20) UNSIGNED NULL,
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

    // Rentcast Properties table
    $tables[] = "CREATE TABLE {$wpdb->prefix}rentcast_properties (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        listing_id VARCHAR(255) NOT NULL,
        address VARCHAR(255) NULL,
        city VARCHAR(100) NULL,
        state VARCHAR(50) NULL,
        zip VARCHAR(20) NULL,
        bedrooms INT(11) NULL,
        bathrooms INT(11) NULL,
        sqft INT(11) NULL,
        price VARCHAR(50) NULL,
        image_url VARCHAR(255) NULL,
        PRIMARY KEY (id),
        KEY listing_id (listing_id)
    ) $charset_collate;";

    // Saved Properties table (client ↔ property relation)
    $tables[] = "CREATE TABLE {$wpdb->prefix}saved_properties (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        client_id BIGINT(20) UNSIGNED NOT NULL,
        properties_id BIGINT(20) UNSIGNED NOT NULL,
        saved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY client_id (client_id),
        KEY properties_id (properties_id)
    ) $charset_collate;";

    // Inquiries table (client ↔ realtor ↔ property)
    $tables[] = "CREATE TABLE {$wpdb->prefix}inquiries (
        inquiry_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        client_id BIGINT(20) UNSIGNED NOT NULL,
        realtor_id BIGINT(20) UNSIGNED NOT NULL,
        properties_id BIGINT(20) UNSIGNED NOT NULL,
        message TEXT NOT NULL,
        reply TEXT,
        status ENUM('open','closed') DEFAULT 'open',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (inquiry_id),
        KEY client_id (client_id),
        KEY realtor_id (realtor_id),
        KEY properties_id (properties_id)
    ) $charset_collate;";

    // Document Types Table
    $tables[] = "CREATE TABLE {$wpdb->prefix}document_types (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        type_name VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_by BIGINT(20) UNSIGNED NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by BIGINT(20) UNSIGNED NULL,
        deleted_at DATETIME NULL DEFAULT NULL,
        deleted_by BIGINT(20) UNSIGNED NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Documents Table
    $tables[] = "CREATE TABLE {$wpdb->prefix}documents (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        type_id BIGINT(20) UNSIGNED NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_by BIGINT(20) UNSIGNED NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by BIGINT(20) UNSIGNED NULL,
        deleted_at DATETIME NULL DEFAULT NULL,
        deleted_by BIGINT(20) UNSIGNED NULL,
        PRIMARY KEY (id),
        KEY type_id (type_id),
        CONSTRAINT fk_document_type FOREIGN KEY (type_id) REFERENCES {$wpdb->prefix}document_types(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Assigned Property table
    $tables[] = "CREATE TABLE {$wpdb->prefix}assigned_property (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        client_id BIGINT(20) UNSIGNED NOT NULL,
        properties_id BIGINT(20) UNSIGNED NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_by BIGINT(20) UNSIGNED DEFAULT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by BIGINT(20) UNSIGNED DEFAULT NULL,
        deleted_at DATETIME DEFAULT NULL,
        deleted_by BIGINT(20) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $tables[] = "CREATE TABLE {$wpdb->prefix}assigned_tasks (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        client_id BIGINT(20) UNSIGNED NOT NULL,
        properties_id BIGINT(20) UNSIGNED NOT NULL,
        document_id BIGINT(20) UNSIGNED DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_by BIGINT(20) UNSIGNED DEFAULT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by BIGINT(20) UNSIGNED DEFAULT NULL,
        deleted_at DATETIME DEFAULT NULL,
        deleted_by BIGINT(20) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Run queries
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    foreach ($tables as $sql) {
        dbDelta($sql);
    }
}
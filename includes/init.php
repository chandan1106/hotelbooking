<?php
/**
 * Initialization file
 * Include this file at the beginning of every page
 */

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Include database class
require_once __DIR__ . '/database.php';

// Include helper functions
require_once __DIR__ . '/functions.php';

// Create database instance
$db = new Database();

// Set default timezone
date_default_timezone_set('UTC');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants for user roles
define('ROLE_USER', 'user');
define('ROLE_HOTEL_OWNER', 'hotel_owner');
define('ROLE_ADMIN', 'admin');

// Check if user is logged in and set global variables
$currentUser = null;
$currentHotelOwner = null;
$currentAdmin = null;

if (isset($_SESSION['user_id'])) {
    // Get user data
    $db->query("SELECT * FROM users WHERE id = :id");
    $db->bind(':id', $_SESSION['user_id']);
    $currentUser = $db->single();
}

if (isset($_SESSION['hotel_owner_id'])) {
    // Get hotel owner data
    $db->query("SELECT * FROM hotel_owners WHERE id = :id");
    $db->bind(':id', $_SESSION['hotel_owner_id']);
    $currentHotelOwner = $db->single();
}

if (isset($_SESSION['admin_id'])) {
    // Get admin data
    $db->query("SELECT * FROM admins WHERE id = :id");
    $db->bind(':id', $_SESSION['admin_id']);
    $currentAdmin = $db->single();
}
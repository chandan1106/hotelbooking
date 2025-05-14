<?php
/**
 * Configuration file for Hotel Booking System
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hotel_booking');

// Application configuration
define('SITE_NAME', 'Hotel Booking System');
define('SITE_URL', 'http://localhost/HotelBooking');
define('ADMIN_URL', SITE_URL . '/admin');
define('HOTEL_URL', SITE_URL . '/hotel');
define('USER_URL', SITE_URL . '/user');

// File upload paths
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/HotelBooking/uploads/');
define('HOTEL_IMAGES', UPLOAD_DIR . 'hotels/');
define('ROOM_IMAGES', UPLOAD_DIR . 'rooms/');
define('USER_IMAGES', UPLOAD_DIR . 'users/');

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
    mkdir(HOTEL_IMAGES, 0755, true);
    mkdir(ROOM_IMAGES, 0755, true);
    mkdir(USER_IMAGES, 0755, true);
}

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time zone
date_default_timezone_set('UTC');

// Payment gateway configuration (replace with your actual credentials)
define('STRIPE_PUBLIC_KEY', 'pk_test_your_stripe_public_key');
define('STRIPE_SECRET_KEY', 'sk_test_your_stripe_secret_key');
define('PAYPAL_CLIENT_ID', 'your_paypal_client_id');
define('PAYPAL_SECRET', 'your_paypal_secret');

// Email configuration
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@example.com');
define('SMTP_PASSWORD', 'your_email_password');
define('SMTP_FROM_EMAIL', 'noreply@hotelbooking.com');
define('SMTP_FROM_NAME', SITE_NAME);
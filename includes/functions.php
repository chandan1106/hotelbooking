<?php
/**
 * Helper functions for the application
 */

/**
 * Clean data to prevent XSS attacks
 * @param string $data - Input data
 * @return string - Cleaned data
 */
function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Redirect to a specific page
 * @param string $location - URL to redirect to
 */
function redirect($location) {
    header("Location: {$location}");
    exit;
}

/**
 * Check if user is logged in
 * @return boolean
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if hotel owner is logged in
 * @return boolean
 */
function isHotelOwnerLoggedIn() {
    return isset($_SESSION['hotel_owner_id']);
}

/**
 * Check if admin is logged in
 * @return boolean
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

/**
 * Generate a random string
 * @param int $length - Length of the string
 * @return string - Random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Generate a unique booking number
 * @return string - Booking number
 */
function generateBookingNumber() {
    return 'BK' . date('Ymd') . strtoupper(generateRandomString(6));
}

/**
 * Format date to display
 * @param string $date - Date in Y-m-d format
 * @return string - Formatted date
 */
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Calculate number of nights between two dates
 * @param string $checkIn - Check-in date
 * @param string $checkOut - Check-out date
 * @return int - Number of nights
 */
function calculateNights($checkIn, $checkOut) {
    $checkInDate = new DateTime($checkIn);
    $checkOutDate = new DateTime($checkOut);
    $interval = $checkInDate->diff($checkOutDate);
    return $interval->days;
}

/**
 * Calculate total price for a booking
 * @param float $pricePerNight - Price per night
 * @param int $nights - Number of nights
 * @return float - Total price
 */
function calculateTotalPrice($pricePerNight, $nights) {
    return $pricePerNight * $nights;
}

/**
 * Check if a room is available for the given dates
 * @param int $roomId - Room ID
 * @param string $checkIn - Check-in date
 * @param string $checkOut - Check-out date
 * @param object $db - Database connection
 * @return boolean - True if available, false otherwise
 */
function isRoomAvailable($roomId, $checkIn, $checkOut, $db) {
    $db->query("SELECT COUNT(*) as count FROM bookings 
                WHERE room_id = :room_id 
                AND status != 'cancelled'
                AND ((check_in_date <= :check_in AND check_out_date > :check_in)
                OR (check_in_date < :check_out AND check_out_date >= :check_out)
                OR (check_in_date >= :check_in AND check_out_date <= :check_out))");
    
    $db->bind(':room_id', $roomId);
    $db->bind(':check_in', $checkIn);
    $db->bind(':check_out', $checkOut);
    
    $result = $db->single();
    return $result['count'] == 0;
}

/**
 * Upload an image file
 * @param array $file - $_FILES array element
 * @param string $destination - Destination directory
 * @return string|boolean - File path if successful, false otherwise
 */
function uploadImage($file, $destination) {
    // Check if file was uploaded without errors
    if ($file['error'] == 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Validate file type and size
        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
            // Generate unique filename
            $filename = uniqid() . '_' . basename($file['name']);
            $targetPath = $destination . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                return $filename;
            }
        }
    }
    return false;
}

/**
 * Send email using PHPMailer
 * @param string $to - Recipient email
 * @param string $subject - Email subject
 * @param string $body - Email body
 * @return boolean - True if sent, false otherwise
 */
function sendEmail($to, $subject, $body) {
    // This is a placeholder. In a real application, you would use PHPMailer or similar library
    // to send emails. For now, we'll just return true.
    return true;
}

/**
 * Get subscription status for a hotel owner
 * @param int $ownerId - Hotel owner ID
 * @param object $db - Database connection
 * @return string - Subscription status
 */
function getSubscriptionStatus($ownerId, $db) {
    $db->query("SELECT subscription_end_date FROM hotel_owners WHERE id = :id");
    $db->bind(':id', $ownerId);
    $result = $db->single();
    
    if (!$result || empty($result['subscription_end_date'])) {
        return 'inactive';
    }
    
    $endDate = new DateTime($result['subscription_end_date']);
    $today = new DateTime();
    
    if ($today > $endDate) {
        return 'expired';
    }
    
    return 'active';
}

/**
 * Format currency
 * @param float $amount - Amount to format
 * @return string - Formatted amount
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Get user-friendly status label
 * @param string $status - Status code
 * @return string - Status label
 */
function getStatusLabel($status) {
    $labels = [
        'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
        'confirmed' => '<span class="badge bg-success">Confirmed</span>',
        'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
        'completed' => '<span class="badge bg-info">Completed</span>',
        'active' => '<span class="badge bg-success">Active</span>',
        'inactive' => '<span class="badge bg-secondary">Inactive</span>',
        'expired' => '<span class="badge bg-danger">Expired</span>'
    ];
    
    return isset($labels[$status]) ? $labels[$status] : $status;
}

/**
 * Check if a string is a valid JSON
 * @param string $string - String to check
 * @return boolean - True if valid JSON, false otherwise
 */
function isValidJson($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Debug function to print variables in a readable format
 * @param mixed $data - Data to debug
 * @param boolean $die - Whether to stop execution after printing
 */
function debug($data, $die = true) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    if ($die) {
        die();
    }
}
<?php
/**
 * Add Hotel Page for Hotel Owners
 */
$pageTitle = 'Add New Hotel';
require_once '../includes/init.php';

// Check if hotel owner is logged in
if (!isHotelOwnerLoggedIn()) {
    $_SESSION['error_message'] = 'You must be logged in to access this page.';
    redirect(HOTEL_URL . '/login.php');
}

// Check subscription status
$subscriptionStatus = getSubscriptionStatus($_SESSION['hotel_owner_id'], $db);
if ($subscriptionStatus != 'active') {
    $_SESSION['error_message'] = 'Your subscription is not active. Please renew your subscription to add hotels.';
    redirect(HOTEL_URL . '/subscription.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize POST data
    $name = clean($_POST['name']);
    $description = clean($_POST['description']);
    $address = clean($_POST['address']);
    $city = clean($_POST['city']);
    $country = clean($_POST['country']);
    $postalCode = clean($_POST['postal_code']);
    $latitude = clean($_POST['latitude']);
    $longitude = clean($_POST['longitude']);
    $phone = clean($_POST['phone']);
    $email = clean($_POST['email']);
    $website = clean($_POST['website']);
    $checkInTime = clean($_POST['check_in_time']);
    $checkOutTime = clean($_POST['check_out_time']);
    $starRating = clean($_POST['star_rating']);
    $amenities = isset($_POST['amenities']) ? $_POST['amenities'] : [];
    $policies = clean($_POST['policies']);
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Hotel name is required';
    }
    
    if (empty($address)) {
        $errors[] = 'Address is required';
    }
    
    if (empty($city)) {
        $errors[] = 'City is required';
    }
    
    if (empty($country)) {
        $errors[] = 'Country is required';
    }
    
    // If no errors, proceed with adding hotel
    if (empty($errors)) {
        // Convert amenities array to JSON
        $amenitiesJson = json_encode($amenities);
        
        // Begin transaction
        $db->beginTransaction();
        
        try {
            // Insert hotel into database
            $db->query("INSERT INTO hotels (owner_id, name, description, address, city, country, postal_code, latitude, longitude, phone, email, website, check_in_time, check_out_time, star_rating, amenities, policies) 
                        VALUES (:owner_id, :name, :description, :address, :city, :country, :postal_code, :latitude, :longitude, :phone, :email, :website, :check_in_time, :check_out_time, :star_rating, :amenities, :policies)");
            $db->bind(':owner_id', $_SESSION['hotel_owner_id']);
            $db->bind(':name', $name);
            $db->bind(':description', $description);
            $db->bind(':address', $address);
            $db->bind(':city', $city);
            $db->bind(':country', $country);
            $db->bind(':postal_code', $postalCode);
            $db->bind(':latitude', $latitude);
            $db->bind(':longitude', $longitude);
            $db->bind(':phone', $phone);
            $db->bind(':email', $email);
            $db->bind(':website', $website);
            $db->bind(':check_in_time', $checkInTime);
            $db->bind(':check_out_time', $checkOutTime);
            $db->bind(':star_rating', $starRating);
            $db->bind(':amenities', $amenitiesJson);
            $db->bind(':policies', $policies);
            $db->execute();
            
            $hotelId = $db->lastInsertId();
            
            // Handle hotel images
            if (isset($_FILES['hotel_images']) && !empty($_FILES['hotel_images']['name'][0])) {
                $images = $_FILES['hotel_images'];
                $totalImages = count($images['name']);
                
                for ($i = 0; $i < $totalImages; $i++) {
                    if ($images['error'][$i] == 0) {
                        $file = [
                            'name' => $images['name'][$i],
                            'type' => $images['type'][$i],
                            'tmp_name' => $images['tmp_name'][$i],
                            'error' => $images['error'][$i],
                            'size' => $images['size'][$i]
                        ];
                        
                        $imagePath = uploadImage($file, HOTEL_IMAGES);
                        
                        if ($imagePath) {
                            // Set first image as primary
                            $isPrimary = ($i == 0) ? 1 : 0;
                            
                            $db->query("INSERT INTO hotel_images (hotel_id, image_path, is_primary) VALUES (:hotel_id, :image_path, :is_primary)");
                            $db->bind(':hotel_id', $hotelId);
                            $db->bind(':image_path', $imagePath);
                            $db->bind(':is_primary', $isPrimary);
                            $db->execute();
                        }
                    }
                }
            }
            
            // Commit transaction
            $db->endTransaction();
            
            // Set success message and redirect
            $_SESSION['success_message'] = 'Hotel added successfully! Now you can add room types and rooms.';
            redirect(HOTEL_URL . '/edit_hotel.php?id=' . $hotelId . '&action=add_rooms');
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->cancelTransaction();
            $errors[] = 'Something went wrong. Please try again.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold mb-6">Add New Hotel</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul class="list-disc pl-4">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-4">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-gray-700 font-medium mb-2">Hotel Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo isset($name) ? $name : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <label for="star_rating" class="block text-gray-700 font-medium mb-2">Star Rating</label>
                    <select id="star_rating" name="star_rating" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Rating</option>
                        <option value="1" <?php echo (isset($starRating) && $starRating == 1) ? 'selected' : ''; ?>>1 Star</option>
                        <option value="2" <?php echo (isset($starRating) && $starRating == 2) ? 'selected' : ''; ?>>2 Stars</option>
                        <option value="3" <?php echo (isset($starRating) && $starRating == 3) ? 'selected' : ''; ?>>3 Stars</option>
                        <option value="4" <?php echo (isset($starRating) && $starRating == 4) ? 'selected' : ''; ?>>4 Stars</option>
                        <option value="5" <?php echo (isset($starRating) && $starRating == 5) ? 'selected' : ''; ?>>5 Stars</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4">
                <label for="description" class="block text-gray-700 font-medium mb-2">Description</label>
                <textarea id="description" name="description" rows="5" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo isset($description) ? $description : ''; ?></textarea>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-4">Location</h3>
            
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label for="address" class="block text-gray-700 font-medium mb-2">Address *</label>
                    <input type="text" id="address" name="address" value="<?php echo isset($address) ? $address : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="city" class="block text-gray-700 font-medium mb-2">City *</label>
                        <input type="text" id="city" name="city" value="<?php echo isset($city) ? $city : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div>
                        <label for="country" class="block text-gray-700 font-medium mb-2">Country *</label>
                        <input type="text" id="country" name="country" value="<?php echo isset($country) ? $country : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div>
                        <label for="postal_code" class="block text-gray-700 font-medium mb-2">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" value="<?php echo isset($postalCode) ? $postalCode : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="latitude" class="block text-gray-700 font-medium mb-2">Latitude</label>
                        <input type="text" id="latitude" name="latitude" value="<?php echo isset($latitude) ? $latitude : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="longitude" class="block text-gray-700 font-medium mb-2">Longitude</label>
                        <input type="text" id="longitude" name="longitude" value="<?php echo isset($longitude) ? $longitude : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-4">Contact Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="phone" class="block text-gray-700 font-medium mb-2">Phone</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo isset($phone) ? $phone : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="website" class="block text-gray-700 font-medium mb-2">Website</label>
                    <input type="url" id="website" name="website" value="<?php echo isset($website) ? $website : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-4">Check-in/Check-out</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="check_in_time" class="block text-gray-700 font-medium mb-2">Check-in Time</label>
                    <input type="time" id="check_in_time" name="check_in_time" value="<?php echo isset($checkInTime) ? $checkInTime : '14:00'; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="check_out_time" class="block text-gray-700 font-medium mb-2">Check-out Time</label>
                    <input type="time" id="check_out_time" name="check_out_time" value="<?php echo isset($checkOutTime) ? $checkOutTime : '11:00'; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-4">Amenities</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="wifi" name="amenities[]" value="wifi" <?php echo (isset($amenities) && in_array('wifi', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="wifi" class="text-gray-700">Free WiFi</label>
                    </div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="parking" name="amenities[]" value="parking" <?php echo (isset($amenities) && in_array('parking', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="parking" class="text-gray-700">Parking</label>
                    </div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="breakfast" name="amenities[]" value="breakfast" <?php echo (isset($amenities) && in_array('breakfast', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="breakfast" class="text-gray-700">Breakfast</label>
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="pool" name="amenities[]" value="pool" <?php echo (isset($amenities) && in_array('pool', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="pool" class="text-gray-700">Swimming Pool</label>
                    </div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="gym" name="amenities[]" value="gym" <?php echo (isset($amenities) && in_array('gym', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="gym" class="text-gray-700">Fitness Center</label>
                    </div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="spa" name="amenities[]" value="spa" <?php echo (isset($amenities) && in_array('spa', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="spa" class="text-gray-700">Spa</label>
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="restaurant" name="amenities[]" value="restaurant" <?php echo (isset($amenities) && in_array('restaurant', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="restaurant" class="text-gray-700">Restaurant</label>
                    </div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="bar" name="amenities[]" value="bar" <?php echo (isset($amenities) && in_array('bar', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="bar" class="text-gray-700">Bar</label>
                    </div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="room_service" name="amenities[]" value="room_service" <?php echo (isset($amenities) && in_array('room_service', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="room_service" class="text-gray-700">Room Service</label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-4">Hotel Policies</h3>
            
            <div>
                <label for="policies" class="block text-gray-700 font-medium mb-2">Policies</label>
                <textarea id="policies" name="policies" rows="5" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo isset($policies) ? $policies : ''; ?></textarea>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-4">Hotel Images</h3>
            
            <div>
                <label for="hotel_images" class="block text-gray-700 font-medium mb-2">Upload Images</label>
                <input type="file" id="hotel_images" name="hotel_images[]" multiple accept="image/*" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-sm text-gray-500 mt-1">You can upload multiple images. The first image will be used as the main image.</p>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-lg font-medium hover:bg-blue-700 transition duration-200">Add Hotel</button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
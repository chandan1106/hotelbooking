<?php
/**
 * Add Room Type Page for Hotel Owners
 */
$pageTitle = 'Add Room Type';
require_once '../includes/init.php';

// Check if hotel owner is logged in
if (!isHotelOwnerLoggedIn()) {
    $_SESSION['error_message'] = 'You must be logged in to access this page.';
    redirect(HOTEL_URL . '/login.php');
}

// Check if hotel ID is provided
if (!isset($_GET['hotel_id']) || empty($_GET['hotel_id'])) {
    $_SESSION['error_message'] = 'Hotel ID is required.';
    redirect(HOTEL_URL . '/hotels.php');
}

$hotelId = clean($_GET['hotel_id']);

// Check if hotel belongs to the logged-in owner
$db->query("SELECT * FROM hotels WHERE id = :id AND owner_id = :owner_id");
$db->bind(':id', $hotelId);
$db->bind(':owner_id', $_SESSION['hotel_owner_id']);
$hotel = $db->single();

if (!$hotel) {
    $_SESSION['error_message'] = 'You do not have permission to add room types to this hotel.';
    redirect(HOTEL_URL . '/hotels.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize POST data
    $name = clean($_POST['name']);
    $description = clean($_POST['description']);
    $capacity = clean($_POST['capacity']);
    $pricePerNight = clean($_POST['price_per_night']);
    $sizeSqm = clean($_POST['size_sqm']);
    $amenities = isset($_POST['amenities']) ? $_POST['amenities'] : [];
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Room type name is required';
    }
    
    if (empty($capacity)) {
        $errors[] = 'Capacity is required';
    }
    
    if (empty($pricePerNight)) {
        $errors[] = 'Price per night is required';
    }
    
    // If no errors, proceed with adding room type
    if (empty($errors)) {
        // Convert amenities array to JSON
        $amenitiesJson = json_encode($amenities);
        
        // Begin transaction
        $db->beginTransaction();
        
        try {
            // Insert room type into database
            $db->query("INSERT INTO room_types (hotel_id, name, description, capacity, price_per_night, size_sqm, amenities) 
                        VALUES (:hotel_id, :name, :description, :capacity, :price_per_night, :size_sqm, :amenities)");
            $db->bind(':hotel_id', $hotelId);
            $db->bind(':name', $name);
            $db->bind(':description', $description);
            $db->bind(':capacity', $capacity);
            $db->bind(':price_per_night', $pricePerNight);
            $db->bind(':size_sqm', $sizeSqm);
            $db->bind(':amenities', $amenitiesJson);
            $db->execute();
            
            $roomTypeId = $db->lastInsertId();
            
            // Handle room images
            if (isset($_FILES['room_images']) && !empty($_FILES['room_images']['name'][0])) {
                $images = $_FILES['room_images'];
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
                        
                        $imagePath = uploadImage($file, ROOM_IMAGES);
                        
                        if ($imagePath) {
                            // Set first image as primary
                            $isPrimary = ($i == 0) ? 1 : 0;
                            
                            $db->query("INSERT INTO room_images (room_type_id, image_path, is_primary) VALUES (:room_type_id, :image_path, :is_primary)");
                            $db->bind(':room_type_id', $roomTypeId);
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
            $_SESSION['success_message'] = 'Room type added successfully! Now you can add rooms.';
            redirect(HOTEL_URL . '/add_rooms.php?room_type_id=' . $roomTypeId);
            
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
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Add Room Type for <?php echo $hotel['name']; ?></h2>
        <a href="<?php echo HOTEL_URL; ?>/edit_hotel.php?id=<?php echo $hotelId; ?>" class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600 transition duration-200">Back to Hotel</a>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul class="list-disc pl-4">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?hotel_id=' . $hotelId; ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-4">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-gray-700 font-medium mb-2">Room Type Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo isset($name) ? $name : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <p class="text-sm text-gray-500 mt-1">E.g., Standard Double, Deluxe Suite, etc.</p>
                </div>
                
                <div>
                    <label for="capacity" class="block text-gray-700 font-medium mb-2">Capacity (Max Guests) *</label>
                    <input type="number" id="capacity" name="capacity" min="1" value="<?php echo isset($capacity) ? $capacity : '2'; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>
            
            <div class="mt-4">
                <label for="description" class="block text-gray-700 font-medium mb-2">Description</label>
                <textarea id="description" name="description" rows="4" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo isset($description) ? $description : ''; ?></textarea>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-4">Pricing & Size</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="price_per_night" class="block text-gray-700 font-medium mb-2">Price Per Night ($) *</label>
                    <input type="number" id="price_per_night" name="price_per_night" min="0" step="0.01" value="<?php echo isset($pricePerNight) ? $pricePerNight : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <label for="size_sqm" class="block text-gray-700 font-medium mb-2">Room Size (sq.m)</label>
                    <input type="number" id="size_sqm" name="size_sqm" min="0" step="0.01" value="<?php echo isset($sizeSqm) ? $sizeSqm : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-4">Room Amenities</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="wifi" name="amenities[]" value="wifi" <?php echo (isset($amenities) && in_array('wifi', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="wifi" class="text-gray-700">Free WiFi</label>
                    </div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="tv" name="amenities[]" value="tv" <?php echo (isset($amenities) && in_array('tv', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="tv" class="text-gray-700">TV</label>
                    </div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="air_conditioning" name="amenities[]" value="air_conditioning" <?php echo (isset($amenities) && in_array('air_conditioning', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="air_conditioning" class="text-gray-700">Air Conditioning</label>
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="minibar" name="amenities[]" value="minibar" <?php echo (isset($amenities) && in_array('minibar', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="minibar" class="text-gray-700">Minibar</label>
                    </div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="safe" name="amenities[]" value="safe" <?php echo (isset($amenities) && in_array('safe', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="safe" class="text-gray-700">Safe</label>
                    </div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="coffee_maker" name="amenities[]" value="coffee_maker" <?php echo (isset($amenities) && in_array('coffee_maker', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="coffee_maker" class="text-gray-700">Coffee Maker</label>
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="bathtub" name="amenities[]" value="bathtub" <?php echo (isset($amenities) && in_array('bathtub', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="bathtub" class="text-gray-700">Bathtub</label>
                    </div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="shower" name="amenities[]" value="shower" <?php echo (isset($amenities) && in_array('shower', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="shower" class="text-gray-700">Shower</label>
                    </div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="balcony" name="amenities[]" value="balcony" <?php echo (isset($amenities) && in_array('balcony', $amenities)) ? 'checked' : ''; ?> class="mr-2">
                        <label for="balcony" class="text-gray-700">Balcony</label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-4">Room Images</h3>
            
            <div>
                <label for="room_images" class="block text-gray-700 font-medium mb-2">Upload Images</label>
                <input type="file" id="room_images" name="room_images[]" multiple accept="image/*" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-sm text-gray-500 mt-1">You can upload multiple images. The first image will be used as the main image.</p>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-lg font-medium hover:bg-blue-700 transition duration-200">Add Room Type</button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
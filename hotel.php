<?php
/**
 * Hotel Details Page
 */
require_once 'includes/init.php';

// Check if hotel ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'Hotel ID is required.';
    redirect('index.php');
}

$hotelId = clean($_GET['id']);

// Get hotel details
$db->query("SELECT * FROM hotels WHERE id = :id");
$db->bind(':id', $hotelId);
$hotel = $db->single();

if (!$hotel) {
    $_SESSION['error_message'] = 'Hotel not found.';
    redirect('index.php');
}

// Get hotel images
$db->query("SELECT * FROM hotel_images WHERE hotel_id = :hotel_id ORDER BY is_primary DESC");
$db->bind(':hotel_id', $hotelId);
$hotelImages = $db->resultSet();

// Get room types
$db->query("SELECT rt.*, 
           (SELECT image_path FROM room_images WHERE room_type_id = rt.id AND is_primary = 1 LIMIT 1) as image 
           FROM room_types rt 
           WHERE rt.hotel_id = :hotel_id 
           ORDER BY rt.price_per_night ASC");
$db->bind(':hotel_id', $hotelId);
$roomTypes = $db->resultSet();

// Get search parameters from URL
$dates = isset($_GET['dates']) ? clean($_GET['dates']) : '';
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 2;

// Parse dates
$checkIn = '';
$checkOut = '';
if (!empty($dates)) {
    $dateRange = explode(' - ', $dates);
    if (count($dateRange) == 2) {
        $checkIn = $dateRange[0];
        $checkOut = $dateRange[1];
    }
}

// Calculate number of nights if dates are provided
$nights = 0;
if (!empty($checkIn) && !empty($checkOut)) {
    $nights = calculateNights($checkIn, $checkOut);
}

$pageTitle = $hotel['name'];
require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Hotel Images Slider -->
        <div class="relative h-96">
            <?php if (!empty($hotelImages)): ?>
                <div class="hotel-slider h-full">
                    <?php foreach ($hotelImages as $image): ?>
                        <div class="h-full">
                            <img src="uploads/hotels/<?php echo $image['image_path']; ?>" alt="<?php echo $hotel['name']; ?>" class="w-full h-full object-cover">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="flex items-center justify-center h-full bg-gray-200">
                    <i class="fas fa-hotel text-6xl text-gray-400"></i>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Hotel Details -->
        <div class="p-6">
            <div class="flex flex-col md:flex-row justify-between items-start mb-6">
                <div>
                    <h1 class="text-3xl font-bold mb-2"><?php echo $hotel['name']; ?></h1>
                    <div class="flex items-center mb-2">
                        <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                        <span class="text-gray-600"><?php echo $hotel['address'] . ', ' . $hotel['city'] . ', ' . $hotel['country']; ?></span>
                    </div>
                    <?php if (!empty($hotel['star_rating'])): ?>
                        <div class="flex items-center mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $hotel['star_rating']): ?>
                                    <i class="fas fa-star text-yellow-400"></i>
                                <?php else: ?>
                                    <i class="far fa-star text-yellow-400"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-4 md:mt-0">
                    <?php if (!empty($roomTypes)): ?>
                        <div class="text-gray-600 mb-1">Starting from</div>
                        <div class="text-3xl font-bold text-blue-600"><?php echo formatCurrency($roomTypes[0]['price_per_night']); ?></div>
                        <div class="text-gray-600 text-sm">per night</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="border-t border-b py-4 my-4">
                <h2 class="text-xl font-semibold mb-4">About This Hotel</h2>
                <p class="text-gray-700"><?php echo nl2br($hotel['description']); ?></p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-6">
                <div>
                    <h2 class="text-xl font-semibold mb-4">Amenities</h2>
                    <?php 
                    $amenities = json_decode($hotel['amenities'], true);
                    if ($amenities && is_array($amenities)):
                    ?>
                        <div class="grid grid-cols-2 gap-2">
                            <?php foreach ($amenities as $amenity): ?>
                                <div class="flex items-center">
                                    <i class="fas fa-check text-green-500 mr-2"></i>
                                    <span><?php echo ucfirst(str_replace('_', ' ', $amenity)); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600">No amenities listed.</p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <h2 class="text-xl font-semibold mb-4">Hotel Policies</h2>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <div class="font-medium">Check-in Time</div>
                            <div class="text-gray-600"><?php echo date('h:i A', strtotime($hotel['check_in_time'])); ?></div>
                        </div>
                        <div>
                            <div class="font-medium">Check-out Time</div>
                            <div class="text-gray-600"><?php echo date('h:i A', strtotime($hotel['check_out_time'])); ?></div>
                        </div>
                    </div>
                    <?php if (!empty($hotel['policies'])): ?>
                        <div class="text-gray-700"><?php echo nl2br($hotel['policies']); ?></div>
                    <?php else: ?>
                        <p class="text-gray-600">No specific policies listed.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Search Box -->
    <div class="bg-white rounded-lg shadow-md p-6 my-8">
        <h2 class="text-xl font-semibold mb-4">Check Availability</h2>
        <form action="hotel.php" method="GET" class="space-y-4">
            <input type="hidden" name="id" value="<?php echo $hotelId; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="dates" class="block text-gray-700 font-medium mb-2">Check-in / Check-out</label>
                    <input type="text" id="dates" name="dates" value="<?php echo $dates; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label for="guests" class="block text-gray-700 font-medium mb-2">Guests</label>
                    <select id="guests" name="guests" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="1" <?php echo $guests == 1 ? 'selected' : ''; ?>>1 Guest</option>
                        <option value="2" <?php echo $guests == 2 ? 'selected' : ''; ?>>2 Guests</option>
                        <option value="3" <?php echo $guests == 3 ? 'selected' : ''; ?>>3 Guests</option>
                        <option value="4" <?php echo $guests == 4 ? 'selected' : ''; ?>>4 Guests</option>
                        <option value="5" <?php echo $guests == 5 ? 'selected' : ''; ?>>5+ Guests</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition duration-200">Check Availability</button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Available Room Types -->
    <div class="my-8">
        <h2 class="text-2xl font-bold mb-6">Available Room Types</h2>
        
        <?php if (empty($roomTypes)): ?>
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <p class="text-gray-600">No room types available for this hotel.</p>
            </div>
        <?php else: ?>
            <?php foreach ($roomTypes as $roomType): ?>
                <?php
                // Check if room is available for the selected dates
                $isAvailable = true;
                if (!empty($checkIn) && !empty($checkOut)) {
                    // Get available rooms of this type
                    $db->query("SELECT r.id FROM rooms r 
                                WHERE r.room_type_id = :room_type_id 
                                AND r.status = 'available'
                                AND NOT EXISTS (
                                    SELECT 1 FROM bookings b 
                                    WHERE b.room_id = r.id 
                                    AND b.status != 'cancelled'
                                    AND ((b.check_in_date <= :check_in AND b.check_out_date > :check_in)
                                    OR (b.check_in_date < :check_out AND b.check_out_date >= :check_out)
                                    OR (b.check_in_date >= :check_in AND b.check_out_date <= :check_out))
                                )");
                    $db->bind(':room_type_id', $roomType['id']);
                    $db->bind(':check_in', $checkIn);
                    $db->bind(':check_out', $checkOut);
                    $availableRooms = $db->resultSet();
                    
                    $isAvailable = !empty($availableRooms);
                }
                
                // Calculate total price if dates are provided
                $totalPrice = 0;
                if ($nights > 0) {
                    $totalPrice = $roomType['price_per_night'] * $nights;
                }
                ?>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3">
                        <div class="h-48 md:h-auto bg-gray-300 relative">
                            <?php if (!empty($roomType['image'])): ?>
                                <img src="uploads/rooms/<?php echo $roomType['image']; ?>" alt="<?php echo $roomType['name']; ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="flex items-center justify-center h-full bg-gray-200">
                                    <i class="fas fa-bed text-4xl text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-4">
                            <h3 class="text-xl font-bold mb-2"><?php echo $roomType['name']; ?></h3>
                            <div class="flex items-center mb-2">
                                <i class="fas fa-user text-gray-500 mr-2"></i>
                                <span>Max <?php echo $roomType['capacity']; ?> <?php echo $roomType['capacity'] > 1 ? 'guests' : 'guest'; ?></span>
                            </div>
                            <?php if (!empty($roomType['size_sqm'])): ?>
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-expand-arrows-alt text-gray-500 mr-2"></i>
                                    <span><?php echo $roomType['size_sqm']; ?> sq.m</span>
                                </div>
                            <?php endif; ?>
                            <p class="text-gray-600 mt-2"><?php echo substr($roomType['description'], 0, 100) . (strlen($roomType['description']) > 100 ? '...' : ''); ?></p>
                            
                            <?php 
                            $amenities = json_decode($roomType['amenities'], true);
                            if ($amenities && is_array($amenities) && count($amenities) > 0):
                            ?>
                                <div class="mt-3">
                                    <div class="font-medium mb-1">Room Amenities:</div>
                                    <div class="flex flex-wrap">
                                        <?php foreach (array_slice($amenities, 0, 3) as $amenity): ?>
                                            <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded mr-1 mb-1"><?php echo ucfirst(str_replace('_', ' ', $amenity)); ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($amenities) > 3): ?>
                                            <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">+<?php echo count($amenities) - 3; ?> more</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-4 bg-gray-50 flex flex-col justify-between">
                            <div>
                                <div class="text-xl font-bold text-blue-600 mb-1"><?php echo formatCurrency($roomType['price_per_night']); ?></div>
                                <div class="text-gray-600 text-sm">per night</div>
                                
                                <?php if ($nights > 0): ?>
                                    <div class="mt-3 p-2 bg-blue-50 rounded">
                                        <div class="flex justify-between text-sm mb-1">
                                            <span><?php echo formatCurrency($roomType['price_per_night']); ?> x <?php echo $nights; ?> nights</span>
                                            <span><?php echo formatCurrency($totalPrice); ?></span>
                                        </div>
                                        <div class="flex justify-between font-bold">
                                            <span>Total</span>
                                            <span><?php echo formatCurrency($totalPrice); ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mt-4">
                                <?php if (!empty($checkIn) && !empty($checkOut)): ?>
                                    <?php if ($isAvailable): ?>
                                        <a href="booking.php?room_type_id=<?php echo $roomType['id']; ?>&check_in=<?php echo $checkIn; ?>&check_out=<?php echo $checkOut; ?>&guests=<?php echo $guests; ?>" class="block w-full bg-blue-600 text-white text-center py-2 px-4 rounded font-medium hover:bg-blue-700 transition duration-200">Book Now</a>
                                    <?php else: ?>
                                        <button class="block w-full bg-gray-400 text-white text-center py-2 px-4 rounded font-medium cursor-not-allowed" disabled>Not Available</button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="block w-full bg-blue-600 text-white text-center py-2 px-4 rounded font-medium hover:bg-blue-700 transition duration-200" onclick="document.getElementById('dates').focus()">Select Dates</button>
                                <?php endif; ?>
                                
                                <button class="block w-full text-blue-600 text-center py-2 px-4 mt-2 hover:underline" onclick="showRoomDetails(<?php echo $roomType['id']; ?>)">View Details</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Map Section -->
    <div class="bg-white rounded-lg shadow-md p-6 my-8">
        <h2 class="text-xl font-semibold mb-4">Location</h2>
        <?php if (!empty($hotel['latitude']) && !empty($hotel['longitude'])): ?>
            <div id="map" class="h-96 rounded-lg"></div>
        <?php else: ?>
            <div class="bg-gray-100 h-96 rounded-lg flex items-center justify-center">
                <p class="text-gray-500">Map location not available</p>
            </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <h3 class="font-medium mb-2">Address</h3>
            <p class="text-gray-600"><?php echo $hotel['address']; ?>, <?php echo $hotel['city']; ?>, <?php echo $hotel['country']; ?> <?php echo $hotel['postal_code']; ?></p>
        </div>
    </div>
</div>

<!-- Room Details Modal -->
<div id="roomDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold" id="modalRoomTitle">Room Details</h2>
                <button onclick="closeRoomDetails()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="modalRoomContent" class="space-y-4">
                <!-- Content will be loaded dynamically -->
                <div class="loader"></div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize date range picker
        $('#dates').daterangepicker({
            opens: 'left',
            minDate: moment(),
            locale: {
                format: 'YYYY-MM-DD'
            }
        });
        
        // Initialize hotel image slider
        $('.hotel-slider').slick({
            dots: true,
            infinite: true,
            speed: 500,
            fade: true,
            cssEase: 'linear',
            autoplay: true,
            autoplaySpeed: 5000
        });
    });
    
    // Room details modal functions
    function showRoomDetails(roomTypeId) {
        document.getElementById('roomDetailsModal').classList.remove('hidden');
        document.getElementById('modalRoomContent').innerHTML = '<div class="loader"></div>';
        
        // Fetch room details via AJAX
        $.ajax({
            url: 'ajax/get_room_details.php',
            type: 'GET',
            data: { room_type_id: roomTypeId },
            success: function(response) {
                document.getElementById('modalRoomContent').innerHTML = response;
            },
            error: function() {
                document.getElementById('modalRoomContent').innerHTML = '<p class="text-red-500">Error loading room details. Please try again.</p>';
            }
        });
    }
    
    function closeRoomDetails() {
        document.getElementById('roomDetailsModal').classList.add('hidden');
    }
    
    <?php if (!empty($hotel['latitude']) && !empty($hotel['longitude'])): ?>
    // Initialize Google Maps
    function initMap() {
        const hotelLocation = { 
            lat: <?php echo $hotel['latitude']; ?>, 
            lng: <?php echo $hotel['longitude']; ?> 
        };
        
        const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 15,
            center: hotelLocation,
        });
        
        const marker = new google.maps.Marker({
            position: hotelLocation,
            map: map,
            title: "<?php echo $hotel['name']; ?>"
        });
    }
    <?php endif; ?>
</script>

<?php if (!empty($hotel['latitude']) && !empty($hotel['longitude'])): ?>
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
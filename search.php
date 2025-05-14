<?php
/**
 * Search Hotels Page
 */
$pageTitle = 'Search Hotels';
require_once 'includes/init.php';

// Get search parameters
$location = isset($_GET['location']) ? clean($_GET['location']) : '';
$dates = isset($_GET['dates']) ? clean($_GET['dates']) : '';
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 2;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

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

// Build search query
$searchConditions = [];
$params = [];

if (!empty($location)) {
    $searchConditions[] = "(h.city LIKE :location OR h.country LIKE :location)";
    $params[':location'] = '%' . $location . '%';
}

if (!empty($checkIn) && !empty($checkOut)) {
    // This is a simplified version. In a real application, you would need to check room availability
    // based on existing bookings for the selected dates.
    $searchConditions[] = "1=1"; // Placeholder for date filtering
}

if (!empty($guests)) {
    $searchConditions[] = "EXISTS (SELECT 1 FROM room_types rt WHERE rt.hotel_id = h.id AND rt.capacity >= :guests)";
    $params[':guests'] = $guests;
}

// Construct the WHERE clause
$whereClause = !empty($searchConditions) ? "WHERE " . implode(" AND ", $searchConditions) : "";

// Count total results
$countQuery = "SELECT COUNT(DISTINCT h.id) as total FROM hotels h $whereClause";
$db->query($countQuery);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$totalResults = $db->single()['total'];
$totalPages = ceil($totalResults / $perPage);

// Get hotels
$query = "SELECT h.*, 
          (SELECT image_path FROM hotel_images WHERE hotel_id = h.id AND is_primary = 1 LIMIT 1) as image,
          (SELECT MIN(price_per_night) FROM room_types WHERE hotel_id = h.id) as min_price
          FROM hotels h
          $whereClause
          GROUP BY h.id
          ORDER BY h.name
          LIMIT :offset, :per_page";

$db->query($query);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$db->bind(':offset', $offset);
$db->bind(':per_page', $perPage);
$hotels = $db->resultSet();

require_once 'includes/header.php';
?>

<div class="bg-blue-600 text-white py-6">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-6">Find Your Perfect Hotel</h1>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <form action="search.php" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="location" class="block text-gray-700 font-medium mb-2">Destination</label>
                        <input type="text" id="location" name="location" placeholder="Where are you going?" value="<?php echo $location; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="dates" class="block text-gray-700 font-medium mb-2">Check-in / Check-out</label>
                        <input type="text" id="dates" name="dates" value="<?php echo $dates; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                </div>
                <div class="text-center">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition duration-200">Search Hotels</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row">
        <!-- Filters -->
        <div class="w-full md:w-1/4 mb-6 md:mb-0 md:pr-6">
            <div class="bg-white rounded-lg shadow-md p-4 mb-4">
                <h3 class="text-lg font-semibold mb-4">Filter Results</h3>
                
                <form action="search.php" method="GET" id="filter-form">
                    <input type="hidden" name="location" value="<?php echo $location; ?>">
                    <input type="hidden" name="dates" value="<?php echo $dates; ?>">
                    <input type="hidden" name="guests" value="<?php echo $guests; ?>">
                    
                    <div class="mb-4">
                        <h4 class="font-medium mb-2">Price Range</h4>
                        <div class="flex items-center">
                            <input type="number" name="min_price" placeholder="Min" class="w-1/2 px-3 py-2 border rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <input type="number" name="max_price" placeholder="Max" class="w-1/2 px-3 py-2 border rounded-r-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h4 class="font-medium mb-2">Star Rating</h4>
                        <div class="space-y-2">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <div class="flex items-center">
                                    <input type="checkbox" id="star-<?php echo $i; ?>" name="star_rating[]" value="<?php echo $i; ?>" class="mr-2">
                                    <label for="star-<?php echo $i; ?>" class="flex">
                                        <?php for ($j = 1; $j <= $i; $j++): ?>
                                            <i class="fas fa-star text-yellow-400"></i>
                                        <?php endfor; ?>
                                    </label>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h4 class="font-medium mb-2">Amenities</h4>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="checkbox" id="wifi" name="amenities[]" value="wifi" class="mr-2">
                                <label for="wifi">Free WiFi</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="parking" name="amenities[]" value="parking" class="mr-2">
                                <label for="parking">Parking</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="pool" name="amenities[]" value="pool" class="mr-2">
                                <label for="pool">Swimming Pool</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="breakfast" name="amenities[]" value="breakfast" class="mr-2">
                                <label for="breakfast">Breakfast</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="gym" name="amenities[]" value="gym" class="mr-2">
                                <label for="gym">Fitness Center</label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded font-medium hover:bg-blue-700 transition duration-200">Apply Filters</button>
                </form>
            </div>
        </div>
        
        <!-- Results -->
        <div class="w-full md:w-3/4">
            <div class="bg-white rounded-lg shadow-md p-4 mb-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold"><?php echo $totalResults; ?> Hotels Found</h2>
                    <div>
                        <label for="sort" class="mr-2">Sort by:</label>
                        <select id="sort" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="price_asc">Price: Low to High</option>
                            <option value="price_desc">Price: High to Low</option>
                            <option value="rating_desc">Rating: High to Low</option>
                            <option value="name_asc">Name: A to Z</option>
                        </select>
                    </div>
                </div>
                
                <?php if (empty($hotels)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-search text-gray-400 text-5xl mb-4"></i>
                        <h3 class="text-xl font-semibold mb-2">No hotels found</h3>
                        <p class="text-gray-600">Try adjusting your search criteria or filters</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-6">
                        <?php foreach ($hotels as $hotel): ?>
                            <div class="border rounded-lg overflow-hidden flex flex-col md:flex-row">
                                <div class="w-full md:w-1/3 h-48 md:h-auto bg-gray-300 relative">
                                    <?php if (!empty($hotel['image'])): ?>
                                        <img src="uploads/hotels/<?php echo $hotel['image']; ?>" alt="<?php echo $hotel['name']; ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="flex items-center justify-center h-full bg-gray-200">
                                            <i class="fas fa-hotel text-4xl text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="w-full md:w-2/3 p-4 flex flex-col">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="text-xl font-bold mb-2"><?php echo $hotel['name']; ?></h3>
                                            <div class="flex items-center mb-2">
                                                <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                                                <span class="text-gray-600"><?php echo $hotel['city'] . ', ' . $hotel['country']; ?></span>
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
                                        <div class="text-right">
                                            <?php if (!empty($hotel['min_price'])): ?>
                                                <div class="text-gray-600 mb-1">From</div>
                                                <div class="text-2xl font-bold text-blue-600"><?php echo formatCurrency($hotel['min_price']); ?></div>
                                                <div class="text-gray-600 text-sm">per night</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <p class="text-gray-600 my-2 line-clamp-2"><?php echo substr($hotel['description'], 0, 150) . (strlen($hotel['description']) > 150 ? '...' : ''); ?></p>
                                    <div class="mt-auto flex justify-end">
                                        <a href="hotel.php?id=<?php echo $hotel['id']; ?><?php echo !empty($dates) ? '&dates=' . urlencode($dates) : ''; ?><?php echo !empty($guests) ? '&guests=' . $guests : ''; ?>" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition duration-200">View Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="flex justify-center mt-8">
                            <nav class="inline-flex rounded-md shadow">
                                <?php if ($page > 1): ?>
                                    <a href="?location=<?php echo urlencode($location); ?>&dates=<?php echo urlencode($dates); ?>&guests=<?php echo $guests; ?>&page=<?php echo $page - 1; ?>" class="px-3 py-2 bg-white border border-gray-300 rounded-l-md hover:bg-gray-100">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <a href="?location=<?php echo urlencode($location); ?>&dates=<?php echo urlencode($dates); ?>&guests=<?php echo $guests; ?>&page=<?php echo $i; ?>" class="px-3 py-2 <?php echo $i == $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?> border border-gray-300">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <a href="?location=<?php echo urlencode($location); ?>&dates=<?php echo urlencode($dates); ?>&guests=<?php echo $guests; ?>&page=<?php echo $page + 1; ?>" class="px-3 py-2 bg-white border border-gray-300 rounded-r-md hover:bg-gray-100">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Date Range Picker Script -->
<script>
    $(document).ready(function() {
        $('#dates').daterangepicker({
            opens: 'left',
            minDate: moment(),
            locale: {
                format: 'YYYY-MM-DD'
            }
        });
        
        // Sort functionality
        $('#sort').change(function() {
            const sortValue = $(this).val();
            const $hotels = $('.space-y-6 > div');
            
            $hotels.sort(function(a, b) {
                let aValue, bValue;
                
                if (sortValue === 'price_asc' || sortValue === 'price_desc') {
                    aValue = parseFloat($(a).find('.text-2xl.font-bold').text().replace('$', '').trim()) || 0;
                    bValue = parseFloat($(b).find('.text-2xl.font-bold').text().replace('$', '').trim()) || 0;
                    
                    return sortValue === 'price_asc' ? aValue - bValue : bValue - aValue;
                } else if (sortValue === 'rating_desc') {
                    aValue = $(a).find('.fas.fa-star').length || 0;
                    bValue = $(b).find('.fas.fa-star').length || 0;
                    
                    return bValue - aValue;
                } else if (sortValue === 'name_asc') {
                    aValue = $(a).find('h3').text().trim();
                    bValue = $(b).find('h3').text().trim();
                    
                    return aValue.localeCompare(bValue);
                }
                
                return 0;
            });
            
            const $parent = $('.space-y-6');
            $hotels.detach().appendTo($parent);
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>
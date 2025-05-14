<?php
/**
 * Homepage
 */
$pageTitle = 'Home';
require_once 'includes/init.php';
require_once 'includes/header.php';

// Get featured hotels
$db->query("SELECT h.*, 
           (SELECT image_path FROM hotel_images WHERE hotel_id = h.id AND is_primary = 1 LIMIT 1) as image 
           FROM hotels h 
           ORDER BY RAND() 
           LIMIT 6");
$featuredHotels = $db->resultSet();
?>

<!-- Hero Section -->
<section class="relative bg-blue-600 text-white">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'); opacity: 0.4;"></div>
    <div class="container mx-auto px-4 py-24 relative z-10">
        <div class="max-w-3xl">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">Find Your Perfect Stay</h1>
            <p class="text-xl mb-8">Discover and book accommodations worldwide with our secure hotel booking platform.</p>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <form action="search.php" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="location" class="block text-gray-700 font-medium mb-2">Destination</label>
                            <input type="text" id="location" name="location" placeholder="Where are you going?" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label for="dates" class="block text-gray-700 font-medium mb-2">Check-in / Check-out</label>
                            <input type="text" id="dates" name="dates" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label for="guests" class="block text-gray-700 font-medium mb-2">Guests</label>
                            <select id="guests" name="guests" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="1">1 Guest</option>
                                <option value="2" selected>2 Guests</option>
                                <option value="3">3 Guests</option>
                                <option value="4">4 Guests</option>
                                <option value="5">5+ Guests</option>
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
</section>

<!-- Featured Hotels -->
<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8 text-center">Featured Hotels</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($featuredHotels as $hotel): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="h-48 bg-gray-300 relative">
                        <?php if (!empty($hotel['image'])): ?>
                            <img src="uploads/hotels/<?php echo $hotel['image']; ?>" alt="<?php echo $hotel['name']; ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="flex items-center justify-center h-full bg-gray-200">
                                <i class="fas fa-hotel text-4xl text-gray-400"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2"><?php echo $hotel['name']; ?></h3>
                        <div class="flex items-center mb-2">
                            <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                            <span class="text-gray-600"><?php echo $hotel['city'] . ', ' . $hotel['country']; ?></span>
                        </div>
                        <?php if (!empty($hotel['star_rating'])): ?>
                            <div class="flex items-center mb-4">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $hotel['star_rating']): ?>
                                        <i class="fas fa-star text-yellow-400"></i>
                                    <?php else: ?>
                                        <i class="far fa-star text-yellow-400"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                        <p class="text-gray-600 mb-4 line-clamp-2"><?php echo substr($hotel['description'], 0, 100) . (strlen($hotel['description']) > 100 ? '...' : ''); ?></p>
                        <a href="hotel.php?id=<?php echo $hotel['id']; ?>" class="block text-center bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition duration-200">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-8">
            <a href="search.php" class="inline-block bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transition duration-200">View All Hotels</a>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8 text-center">Why Choose Our Platform</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center p-6">
                <div class="bg-blue-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-blue-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Easy Search & Booking</h3>
                <p class="text-gray-600">Find and book your perfect accommodation in just a few clicks with our user-friendly interface.</p>
            </div>
            
            <div class="text-center p-6">
                <div class="bg-blue-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-blue-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Secure Payments</h3>
                <p class="text-gray-600">Your payment information is always protected with our secure payment processing system.</p>
            </div>
            
            <div class="text-center p-6">
                <div class="bg-blue-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-headset text-blue-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">24/7 Customer Support</h3>
                <p class="text-gray-600">Our dedicated support team is always available to assist you with any questions or issues.</p>
            </div>
        </div>
    </div>
</section>

<!-- For Hotel Owners -->
<section class="py-12 bg-gray-800 text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold mb-4 text-center">Are You a Hotel Owner?</h2>
            <p class="text-xl mb-8 text-center text-gray-300">Join our platform and reach millions of potential guests worldwide.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-gray-700 p-6 rounded-lg">
                    <h3 class="text-xl font-bold mb-4">Benefits for Hotel Owners</h3>
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-400 mt-1 mr-2"></i>
                            <span>Increase your hotel's visibility and bookings</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-400 mt-1 mr-2"></i>
                            <span>Manage your property listings with our easy-to-use dashboard</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-400 mt-1 mr-2"></i>
                            <span>Receive direct payments from guests</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-400 mt-1 mr-2"></i>
                            <span>Access detailed analytics and booking reports</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-400 mt-1 mr-2"></i>
                            <span>24/7 support for hotel partners</span>
                        </li>
                    </ul>
                </div>
                
                <div class="bg-gray-700 p-6 rounded-lg">
                    <h3 class="text-xl font-bold mb-4">Subscription Plans</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center pb-2 border-b border-gray-600">
                            <span class="font-medium">Basic Plan</span>
                            <span class="font-bold">$29.99/month</span>
                        </div>
                        <div class="flex justify-between items-center pb-2 border-b border-gray-600">
                            <span class="font-medium">Standard Plan</span>
                            <span class="font-bold">$59.99/month</span>
                        </div>
                        <div class="flex justify-between items-center pb-2 border-b border-gray-600">
                            <span class="font-medium">Premium Plan</span>
                            <span class="font-bold">$99.99/month</span>
                        </div>
                    </div>
                    <div class="mt-6 text-center">
                        <a href="<?php echo HOTEL_URL; ?>/register.php" class="inline-block bg-blue-600 text-white py-2 px-6 rounded font-medium hover:bg-blue-700 transition duration-200">Register Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8 text-center">What Our Users Say</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-blue-600 font-bold">JD</span>
                    </div>
                    <div>
                        <h4 class="font-bold">John Doe</h4>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600">"I found the perfect hotel for my vacation through this platform. The booking process was smooth and hassle-free. Highly recommended!"</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-blue-600 font-bold">JS</span>
                    </div>
                    <div>
                        <h4 class="font-bold">Jane Smith</h4>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600">"As a frequent traveler, I've tried many booking platforms, but this one stands out for its user-friendly interface and excellent customer service."</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-blue-600 font-bold">RJ</span>
                    </div>
                    <div>
                        <h4 class="font-bold">Robert Johnson</h4>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600">"I own a small boutique hotel and joining this platform has significantly increased our bookings. The management tools are excellent!"</p>
            </div>
        </div>
    </div>
</section>

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
    });
</script>

<?php require_once 'includes/footer.php'; ?>
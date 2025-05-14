<?php
/**
 * Hotel Owner Dashboard
 */
$pageTitle = 'Dashboard';
require_once '../includes/init.php';

// Check if hotel owner is logged in
if (!isHotelOwnerLoggedIn()) {
    redirect(HOTEL_URL . '/login.php');
}

// Get hotel owner details
$db->query("SELECT * FROM hotel_owners WHERE id = :id");
$db->bind(':id', $_SESSION['hotel_owner_id']);
$hotelOwner = $db->single();

// Get subscription details
$db->query("SELECT sp.* FROM subscription_plans sp 
            JOIN hotel_owners ho ON sp.id = ho.subscription_id 
            WHERE ho.id = :id");
$db->bind(':id', $_SESSION['hotel_owner_id']);
$subscription = $db->single();

// Get subscription status
$subscriptionStatus = getSubscriptionStatus($_SESSION['hotel_owner_id'], $db);

// Get hotels count
$db->query("SELECT COUNT(*) as count FROM hotels WHERE owner_id = :owner_id");
$db->bind(':owner_id', $_SESSION['hotel_owner_id']);
$hotelsCount = $db->single()['count'];

// Get rooms count
$db->query("SELECT COUNT(r.id) as count 
            FROM rooms r 
            JOIN room_types rt ON r.room_type_id = rt.id 
            JOIN hotels h ON rt.hotel_id = h.id 
            WHERE h.owner_id = :owner_id");
$db->bind(':owner_id', $_SESSION['hotel_owner_id']);
$roomsCount = $db->single()['count'];

// Get bookings count
$db->query("SELECT COUNT(b.id) as count 
            FROM bookings b 
            JOIN rooms r ON b.room_id = r.id 
            JOIN room_types rt ON r.room_type_id = rt.id 
            JOIN hotels h ON rt.hotel_id = h.id 
            WHERE h.owner_id = :owner_id");
$db->bind(':owner_id', $_SESSION['hotel_owner_id']);
$bookingsCount = $db->single()['count'];

// Get total revenue
$db->query("SELECT SUM(b.total_price) as total 
            FROM bookings b 
            JOIN rooms r ON b.room_id = r.id 
            JOIN room_types rt ON r.room_type_id = rt.id 
            JOIN hotels h ON rt.hotel_id = h.id 
            WHERE h.owner_id = :owner_id 
            AND b.status = 'completed'");
$db->bind(':owner_id', $_SESSION['hotel_owner_id']);
$totalRevenue = $db->single()['total'] ?? 0;

// Get recent bookings
$db->query("SELECT b.*, u.first_name, u.last_name, h.name as hotel_name, rt.name as room_type 
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            JOIN rooms r ON b.room_id = r.id 
            JOIN room_types rt ON r.room_type_id = rt.id 
            JOIN hotels h ON rt.hotel_id = h.id 
            WHERE h.owner_id = :owner_id 
            ORDER BY b.created_at DESC 
            LIMIT 5");
$db->bind(':owner_id', $_SESSION['hotel_owner_id']);
$recentBookings = $db->resultSet();

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start mb-6">
        <div>
            <h1 class="text-3xl font-bold mb-2">Welcome, <?php echo $hotelOwner['first_name']; ?>!</h1>
            <p class="text-gray-600">Here's an overview of your hotel business</p>
        </div>
        
        <div class="mt-4 md:mt-0">
            <div class="bg-white rounded-lg shadow-md p-4">
                <h2 class="font-semibold mb-2">Subscription Status</h2>
                <div class="flex items-center">
                    <div class="mr-4">
                        <?php echo getStatusLabel($subscriptionStatus); ?>
                    </div>
                    <div>
                        <?php if ($subscriptionStatus == 'active'): ?>
                            <p class="text-sm text-gray-600">Expires: <?php echo formatDate($hotelOwner['subscription_end_date']); ?></p>
                        <?php elseif ($subscriptionStatus == 'expired'): ?>
                            <a href="<?php echo HOTEL_URL; ?>/subscription.php" class="text-blue-600 hover:underline text-sm">Renew Now</a>
                        <?php else: ?>
                            <a href="<?php echo HOTEL_URL; ?>/subscription.php" class="text-blue-600 hover:underline text-sm">Subscribe Now</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-blue-100 rounded-full p-3 mr-4">
                    <i class="fas fa-hotel text-blue-600 text-xl"></i>
                </div>
                <div>
                    <div class="text-gray-500">Hotels</div>
                    <div class="text-2xl font-bold"><?php echo $hotelsCount; ?></div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-green-100 rounded-full p-3 mr-4">
                    <i class="fas fa-bed text-green-600 text-xl"></i>
                </div>
                <div>
                    <div class="text-gray-500">Rooms</div>
                    <div class="text-2xl font-bold"><?php echo $roomsCount; ?></div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-purple-100 rounded-full p-3 mr-4">
                    <i class="fas fa-calendar-check text-purple-600 text-xl"></i>
                </div>
                <div>
                    <div class="text-gray-500">Bookings</div>
                    <div class="text-2xl font-bold"><?php echo $bookingsCount; ?></div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-red-100 rounded-full p-3 mr-4">
                    <i class="fas fa-dollar-sign text-red-600 text-xl"></i>
                </div>
                <div>
                    <div class="text-gray-500">Revenue</div>
                    <div class="text-2xl font-bold"><?php echo formatCurrency($totalRevenue); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="<?php echo HOTEL_URL; ?>/add_hotel.php" class="bg-blue-600 text-white text-center py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                <i class="fas fa-plus-circle mr-2"></i> Add New Hotel
            </a>
            <a href="<?php echo HOTEL_URL; ?>/bookings.php" class="bg-green-600 text-white text-center py-3 px-4 rounded-lg hover:bg-green-700 transition duration-200">
                <i class="fas fa-calendar-alt mr-2"></i> Manage Bookings
            </a>
            <a href="<?php echo HOTEL_URL; ?>/reports.php" class="bg-purple-600 text-white text-center py-3 px-4 rounded-lg hover:bg-purple-700 transition duration-200">
                <i class="fas fa-chart-bar mr-2"></i> View Reports
            </a>
        </div>
    </div>
    
    <!-- Recent Bookings -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Recent Bookings</h2>
            <a href="<?php echo HOTEL_URL; ?>/bookings.php" class="text-blue-600 hover:underline">View All</a>
        </div>
        
        <?php if (empty($recentBookings)): ?>
            <p class="text-gray-500">No bookings found.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-3 text-left">Booking #</th>
                            <th class="py-2 px-3 text-left">Guest</th>
                            <th class="py-2 px-3 text-left">Hotel</th>
                            <th class="py-2 px-3 text-left">Room Type</th>
                            <th class="py-2 px-3 text-left">Dates</th>
                            <th class="py-2 px-3 text-left">Status</th>
                            <th class="py-2 px-3 text-left">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBookings as $booking): ?>
                            <tr class="border-t">
                                <td class="py-2 px-3"><?php echo $booking['booking_number']; ?></td>
                                <td class="py-2 px-3"><?php echo $booking['first_name'] . ' ' . $booking['last_name']; ?></td>
                                <td class="py-2 px-3"><?php echo $booking['hotel_name']; ?></td>
                                <td class="py-2 px-3"><?php echo $booking['room_type']; ?></td>
                                <td class="py-2 px-3"><?php echo formatDate($booking['check_in_date']) . ' - ' . formatDate($booking['check_out_date']); ?></td>
                                <td class="py-2 px-3"><?php echo getStatusLabel($booking['status']); ?></td>
                                <td class="py-2 px-3"><?php echo formatCurrency($booking['total_price']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Bookings by Month</h2>
            <canvas id="bookingsChart" height="300"></canvas>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Revenue by Month</h2>
            <canvas id="revenueChart" height="300"></canvas>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sample data for charts - in a real application, this would come from the database
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    // Bookings chart
    const bookingsCtx = document.getElementById('bookingsChart').getContext('2d');
    const bookingsChart = new Chart(bookingsCtx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Bookings',
                data: [12, 19, 15, 17, 14, 23, 25, 27, 28, 26, 20, 18],
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Revenue chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Revenue ($)',
                data: [1200, 1900, 1500, 1700, 1400, 2300, 2500, 2700, 2800, 2600, 2000, 1800],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                tension: 0.3
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>
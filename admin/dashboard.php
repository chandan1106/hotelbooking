<?php
/**
 * Admin Dashboard
 */
$pageTitle = 'Admin Dashboard';
require_once '../includes/init.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect(ADMIN_URL . '/login.php');
}

// Get statistics
// Total users
$db->query("SELECT COUNT(*) as count FROM users");
$totalUsers = $db->single()['count'];

// Total hotel owners
$db->query("SELECT COUNT(*) as count FROM hotel_owners");
$totalHotelOwners = $db->single()['count'];

// Total hotels
$db->query("SELECT COUNT(*) as count FROM hotels");
$totalHotels = $db->single()['count'];

// Total bookings
$db->query("SELECT COUNT(*) as count FROM bookings");
$totalBookings = $db->single()['count'];

// Total revenue
$db->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
$totalRevenue = $db->single()['total'] ?? 0;

// Recent bookings
$db->query("SELECT b.*, u.first_name, u.last_name, h.name as hotel_name, rt.name as room_type 
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            JOIN rooms r ON b.room_id = r.id 
            JOIN room_types rt ON r.room_type_id = rt.id 
            JOIN hotels h ON rt.hotel_id = h.id 
            ORDER BY b.created_at DESC 
            LIMIT 5");
$recentBookings = $db->resultSet();

// Recent hotel owners
$db->query("SELECT * FROM hotel_owners ORDER BY created_at DESC LIMIT 5");
$recentHotelOwners = $db->resultSet();

// Include admin header
require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Admin Dashboard</h1>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-blue-100 rounded-full p-3 mr-4">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <div>
                    <div class="text-gray-500">Total Users</div>
                    <div class="text-2xl font-bold"><?php echo $totalUsers; ?></div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-green-100 rounded-full p-3 mr-4">
                    <i class="fas fa-user-tie text-green-600 text-xl"></i>
                </div>
                <div>
                    <div class="text-gray-500">Hotel Owners</div>
                    <div class="text-2xl font-bold"><?php echo $totalHotelOwners; ?></div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-purple-100 rounded-full p-3 mr-4">
                    <i class="fas fa-hotel text-purple-600 text-xl"></i>
                </div>
                <div>
                    <div class="text-gray-500">Total Hotels</div>
                    <div class="text-2xl font-bold"><?php echo $totalHotels; ?></div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-yellow-100 rounded-full p-3 mr-4">
                    <i class="fas fa-calendar-check text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <div class="text-gray-500">Bookings</div>
                    <div class="text-2xl font-bold"><?php echo $totalBookings; ?></div>
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
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Bookings -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Recent Bookings</h2>
                <a href="<?php echo ADMIN_URL; ?>/bookings.php" class="text-blue-600 hover:underline">View All</a>
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
                                <th class="py-2 px-3 text-left">Dates</th>
                                <th class="py-2 px-3 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $booking): ?>
                                <tr class="border-t">
                                    <td class="py-2 px-3"><?php echo $booking['booking_number']; ?></td>
                                    <td class="py-2 px-3"><?php echo $booking['first_name'] . ' ' . $booking['last_name']; ?></td>
                                    <td class="py-2 px-3"><?php echo $booking['hotel_name']; ?></td>
                                    <td class="py-2 px-3"><?php echo formatDate($booking['check_in_date']) . ' - ' . formatDate($booking['check_out_date']); ?></td>
                                    <td class="py-2 px-3"><?php echo getStatusLabel($booking['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Recent Hotel Owners -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Recent Hotel Owners</h2>
                <a href="<?php echo ADMIN_URL; ?>/hotel_owners.php" class="text-blue-600 hover:underline">View All</a>
            </div>
            
            <?php if (empty($recentHotelOwners)): ?>
                <p class="text-gray-500">No hotel owners found.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-2 px-3 text-left">Name</th>
                                <th class="py-2 px-3 text-left">Email</th>
                                <th class="py-2 px-3 text-left">Company</th>
                                <th class="py-2 px-3 text-left">Subscription</th>
                                <th class="py-2 px-3 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentHotelOwners as $owner): ?>
                                <tr class="border-t">
                                    <td class="py-2 px-3"><?php echo $owner['first_name'] . ' ' . $owner['last_name']; ?></td>
                                    <td class="py-2 px-3"><?php echo $owner['email']; ?></td>
                                    <td class="py-2 px-3"><?php echo $owner['company_name']; ?></td>
                                    <td class="py-2 px-3">
                                        <?php 
                                        $subscriptionStatus = getSubscriptionStatus($owner['id'], $db);
                                        echo getStatusLabel($subscriptionStatus);
                                        ?>
                                    </td>
                                    <td class="py-2 px-3"><?php echo $owner['is_verified'] ? '<span class="text-green-600">Verified</span>' : '<span class="text-yellow-600">Pending</span>'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
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
                data: [65, 59, 80, 81, 56, 55, 40, 45, 60, 70, 85, 90],
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
                data: [12000, 19000, 15000, 17000, 16000, 23000, 25000, 27000, 28000, 26000, 30000, 32000],
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

<?php require_once 'includes/footer.php'; ?>
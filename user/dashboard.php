<?php
/**
 * User Dashboard
 */
$pageTitle = 'Dashboard';
require_once '../includes/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect(USER_URL . '/login.php');
}

// Get user details
$db->query("SELECT * FROM users WHERE id = :id");
$db->bind(':id', $_SESSION['user_id']);
$user = $db->single();

// Get user's bookings
$db->query("SELECT b.*, h.name as hotel_name, h.city, h.country, rt.name as room_type 
            FROM bookings b 
            JOIN rooms r ON b.room_id = r.id 
            JOIN room_types rt ON r.room_type_id = rt.id 
            JOIN hotels h ON rt.hotel_id = h.id 
            WHERE b.user_id = :user_id 
            ORDER BY b.check_in_date DESC");
$db->bind(':user_id', $_SESSION['user_id']);
$bookings = $db->resultSet();

// Get upcoming bookings
$upcomingBookings = [];
$pastBookings = [];
$today = date('Y-m-d');

foreach ($bookings as $booking) {
    if ($booking['check_in_date'] >= $today) {
        $upcomingBookings[] = $booking;
    } else {
        $pastBookings[] = $booking;
    }
}

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Welcome, <?php echo $user['first_name']; ?>!</h1>
    
    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="<?php echo SITE_URL; ?>/search.php" class="bg-blue-600 text-white text-center py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                <i class="fas fa-search mr-2"></i> Find Hotels
            </a>
            <a href="<?php echo USER_URL; ?>/bookings.php" class="bg-green-600 text-white text-center py-3 px-4 rounded-lg hover:bg-green-700 transition duration-200">
                <i class="fas fa-calendar-alt mr-2"></i> My Bookings
            </a>
            <a href="<?php echo USER_URL; ?>/profile.php" class="bg-purple-600 text-white text-center py-3 px-4 rounded-lg hover:bg-purple-700 transition duration-200">
                <i class="fas fa-user mr-2"></i> Edit Profile
            </a>
        </div>
    </div>
    
    <!-- Upcoming Bookings -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Upcoming Bookings</h2>
            <a href="<?php echo USER_URL; ?>/bookings.php" class="text-blue-600 hover:underline">View All</a>
        </div>
        
        <?php if (empty($upcomingBookings)): ?>
            <div class="text-center py-8">
                <i class="fas fa-calendar-alt text-gray-400 text-5xl mb-4"></i>
                <h3 class="text-xl font-semibold mb-2">No upcoming bookings</h3>
                <p class="text-gray-600 mb-4">You don't have any upcoming hotel reservations.</p>
                <a href="<?php echo SITE_URL; ?>/search.php" class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 transition duration-200">Find Hotels</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach (array_slice($upcomingBookings, 0, 2) as $booking): ?>
                    <div class="border rounded-lg overflow-hidden">
                        <div class="bg-blue-50 p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-lg"><?php echo $booking['hotel_name']; ?></h3>
                                    <p class="text-gray-600"><?php echo $booking['city'] . ', ' . $booking['country']; ?></p>
                                </div>
                                <div>
                                    <?php echo getStatusLabel($booking['status']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <div class="text-sm text-gray-500">Check-in</div>
                                    <div class="font-medium"><?php echo formatDate($booking['check_in_date']); ?></div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Check-out</div>
                                    <div class="font-medium"><?php echo formatDate($booking['check_out_date']); ?></div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="text-sm text-gray-500">Room Type</div>
                                <div class="font-medium"><?php echo $booking['room_type']; ?></div>
                            </div>
                            <div class="mb-4">
                                <div class="text-sm text-gray-500">Booking Number</div>
                                <div class="font-medium"><?php echo $booking['booking_number']; ?></div>
                            </div>
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="text-sm text-gray-500">Total</div>
                                    <div class="font-bold text-lg"><?php echo formatCurrency($booking['total_price']); ?></div>
                                </div>
                                <a href="<?php echo USER_URL; ?>/booking_details.php?id=<?php echo $booking['id']; ?>" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition duration-200">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Recent Bookings -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Recent Bookings</h2>
        </div>
        
        <?php if (empty($pastBookings)): ?>
            <p class="text-gray-500">No past bookings found.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-3 text-left">Booking #</th>
                            <th class="py-2 px-3 text-left">Hotel</th>
                            <th class="py-2 px-3 text-left">Room Type</th>
                            <th class="py-2 px-3 text-left">Dates</th>
                            <th class="py-2 px-3 text-left">Status</th>
                            <th class="py-2 px-3 text-left">Amount</th>
                            <th class="py-2 px-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($pastBookings, 0, 5) as $booking): ?>
                            <tr class="border-t">
                                <td class="py-2 px-3"><?php echo $booking['booking_number']; ?></td>
                                <td class="py-2 px-3"><?php echo $booking['hotel_name']; ?></td>
                                <td class="py-2 px-3"><?php echo $booking['room_type']; ?></td>
                                <td class="py-2 px-3"><?php echo formatDate($booking['check_in_date']) . ' - ' . formatDate($booking['check_out_date']); ?></td>
                                <td class="py-2 px-3"><?php echo getStatusLabel($booking['status']); ?></td>
                                <td class="py-2 px-3"><?php echo formatCurrency($booking['total_price']); ?></td>
                                <td class="py-2 px-3">
                                    <a href="<?php echo USER_URL; ?>/booking_details.php?id=<?php echo $booking['id']; ?>" class="text-blue-600 hover:underline">Details</a>
                                    <?php if ($booking['status'] == 'completed'): ?>
                                        <a href="<?php echo USER_URL; ?>/add_review.php?booking_id=<?php echo $booking['id']; ?>" class="text-green-600 hover:underline ml-2">Review</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
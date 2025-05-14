<?php
/**
 * Booking Page
 */
$pageTitle = 'Book Room';
require_once 'includes/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = 'Please login to book a room.';
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    redirect(USER_URL . '/login.php');
}

// Check if required parameters are provided
if (!isset($_GET['room_type_id']) || empty($_GET['room_type_id']) || 
    !isset($_GET['check_in']) || empty($_GET['check_in']) || 
    !isset($_GET['check_out']) || empty($_GET['check_out'])) {
    $_SESSION['error_message'] = 'Missing required booking information.';
    redirect('index.php');
}

$roomTypeId = clean($_GET['room_type_id']);
$checkIn = clean($_GET['check_in']);
$checkOut = clean($_GET['check_out']);
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 1;

// Validate dates
$today = date('Y-m-d');
if ($checkIn < $today) {
    $_SESSION['error_message'] = 'Check-in date cannot be in the past.';
    redirect('index.php');
}

if ($checkOut <= $checkIn) {
    $_SESSION['error_message'] = 'Check-out date must be after check-in date.';
    redirect('index.php');
}

// Get room type details
$db->query("SELECT rt.*, h.name as hotel_name, h.id as hotel_id, h.city, h.country 
            FROM room_types rt 
            JOIN hotels h ON rt.hotel_id = h.id 
            WHERE rt.id = :id");
$db->bind(':id', $roomTypeId);
$roomType = $db->single();

if (!$roomType) {
    $_SESSION['error_message'] = 'Room type not found.';
    redirect('index.php');
}

// Check if room capacity is sufficient
if ($guests > $roomType['capacity']) {
    $_SESSION['error_message'] = 'This room type cannot accommodate ' . $guests . ' guests. Maximum capacity is ' . $roomType['capacity'] . '.';
    redirect('hotel.php?id=' . $roomType['hotel_id']);
}

// Find available rooms of this type
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
            )
            LIMIT 1");
$db->bind(':room_type_id', $roomTypeId);
$db->bind(':check_in', $checkIn);
$db->bind(':check_out', $checkOut);
$availableRoom = $db->single();

if (!$availableRoom) {
    $_SESSION['error_message'] = 'No rooms available for the selected dates.';
    redirect('hotel.php?id=' . $roomType['hotel_id']);
}

// Calculate booking details
$nights = calculateNights($checkIn, $checkOut);
$totalPrice = calculateTotalPrice($roomType['price_per_night'], $nights);

// Process booking form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize POST data
    $firstName = clean($_POST['first_name']);
    $lastName = clean($_POST['last_name']);
    $email = clean($_POST['email']);
    $phone = clean($_POST['phone']);
    $specialRequests = clean($_POST['special_requests']);
    $paymentMethod = clean($_POST['payment_method']);
    
    // Validation
    $errors = [];
    
    if (empty($firstName)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($lastName)) {
        $errors[] = 'Last name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($paymentMethod)) {
        $errors[] = 'Payment method is required';
    }
    
    // If no errors, proceed with booking
    if (empty($errors)) {
        // Generate booking number
        $bookingNumber = generateBookingNumber();
        
        // Begin transaction
        $db->beginTransaction();
        
        try {
            // Insert booking into database
            $db->query("INSERT INTO bookings (booking_number, user_id, room_id, check_in_date, check_out_date, adults, children, total_price, special_requests, status) 
                        VALUES (:booking_number, :user_id, :room_id, :check_in_date, :check_out_date, :adults, :children, :total_price, :special_requests, :status)");
            $db->bind(':booking_number', $bookingNumber);
            $db->bind(':user_id', $_SESSION['user_id']);
            $db->bind(':room_id', $availableRoom['id']);
            $db->bind(':check_in_date', $checkIn);
            $db->bind(':check_out_date', $checkOut);
            $db->bind(':adults', $guests);
            $db->bind(':children', 0);
            $db->bind(':total_price', $totalPrice);
            $db->bind(':special_requests', $specialRequests);
            $db->bind(':status', 'pending');
            $db->execute();
            
            $bookingId = $db->lastInsertId();
            
            // Process payment
            $transactionId = 'TXN' . time() . rand(1000, 9999); // In a real app, this would come from payment gateway
            
            // Insert payment record
            $db->query("INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status) 
                        VALUES (:booking_id, :amount, :payment_method, :transaction_id, :status)");
            $db->bind(':booking_id', $bookingId);
            $db->bind(':amount', $totalPrice);
            $db->bind(':payment_method', $paymentMethod);
            $db->bind(':transaction_id', $transactionId);
            $db->bind(':status', 'completed'); // In a real app, this would depend on payment gateway response
            $db->execute();
            
            // Update booking status
            $db->query("UPDATE bookings SET status = 'confirmed' WHERE id = :id");
            $db->bind(':id', $bookingId);
            $db->execute();
            
            // Commit transaction
            $db->endTransaction();
            
            // Set success message and redirect
            $_SESSION['success_message'] = 'Booking confirmed! Your booking number is ' . $bookingNumber;
            redirect(USER_URL . '/booking_confirmation.php?id=' . $bookingId);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->cancelTransaction();
            $errors[] = 'Something went wrong. Please try again.';
        }
    }
}

// Get user details for pre-filling the form
$db->query("SELECT * FROM users WHERE id = :id");
$db->bind(':id', $_SESSION['user_id']);
$user = $db->single();

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Complete Your Booking</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul class="list-disc pl-4">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Booking Form -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Guest Information</h2>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="first_name" class="block text-gray-700 font-medium mb-2">First Name *</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            
                            <div>
                                <label for="last_name" class="block text-gray-700 font-medium mb-2">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="email" class="block text-gray-700 font-medium mb-2">Email *</label>
                                <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-gray-700 font-medium mb-2">Phone</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo $user['phone']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div>
                            <label for="special_requests" class="block text-gray-700 font-medium mb-2">Special Requests</label>
                            <textarea id="special_requests" name="special_requests" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            <p class="text-sm text-gray-500 mt-1">We'll do our best to accommodate your requests, but they cannot be guaranteed.</p>
                        </div>
                        
                        <hr class="my-6">
                        
                        <h2 class="text-xl font-semibold mb-4">Payment Information</h2>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Payment Method *</label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input type="radio" id="credit_card" name="payment_method" value="credit_card" class="mr-2" checked>
                                    <label for="credit_card" class="flex items-center">
                                        <span class="mr-2">Credit Card</span>
                                        <i class="fab fa-cc-visa text-blue-700 text-xl mr-1"></i>
                                        <i class="fab fa-cc-mastercard text-red-600 text-xl mr-1"></i>
                                        <i class="fab fa-cc-amex text-blue-500 text-xl"></i>
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" id="paypal" name="payment_method" value="paypal" class="mr-2">
                                    <label for="paypal" class="flex items-center">
                                        <span class="mr-2">PayPal</span>
                                        <i class="fab fa-paypal text-blue-800 text-xl"></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div id="credit_card_form" class="space-y-4">
                            <div>
                                <label for="card_number" class="block text-gray-700 font-medium mb-2">Card Number</label>
                                <input type="text" id="card_number" placeholder="•••• •••• •••• ••••" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="expiry_date" class="block text-gray-700 font-medium mb-2">Expiry Date</label>
                                    <input type="text" id="expiry_date" placeholder="MM/YY" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                
                                <div>
                                    <label for="cvv" class="block text-gray-700 font-medium mb-2">CVV</label>
                                    <input type="text" id="cvv" placeholder="•••" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div>
                                <label for="card_name" class="block text-gray-700 font-medium mb-2">Name on Card</label>
                                <input type="text" id="card_name" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div class="flex items-center mt-4">
                            <input type="checkbox" id="terms" name="terms" class="mr-2" required>
                            <label for="terms" class="text-gray-700">I agree to the <a href="terms.php" class="text-blue-600 hover:underline">Terms of Service</a> and <a href="privacy.php" class="text-blue-600 hover:underline">Privacy Policy</a></label>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition duration-200">Complete Booking</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Booking Summary -->
            <div>
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                    <h2 class="text-xl font-semibold mb-4">Booking Summary</h2>
                    
                    <div class="border-b pb-4 mb-4">
                        <h3 class="font-medium mb-2"><?php echo $roomType['hotel_name']; ?></h3>
                        <div class="text-gray-600 mb-1"><?php echo $roomType['city'] . ', ' . $roomType['country']; ?></div>
                        <div class="font-medium"><?php echo $roomType['name']; ?></div>
                    </div>
                    
                    <div class="space-y-2 border-b pb-4 mb-4">
                        <div class="flex justify-between">
                            <div>
                                <div class="font-medium">Check-in</div>
                                <div class="text-gray-600"><?php echo formatDate($checkIn); ?></div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">Check-out</div>
                                <div class="text-gray-600"><?php echo formatDate($checkOut); ?></div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between">
                            <div>
                                <div class="font-medium">Guests</div>
                                <div class="text-gray-600"><?php echo $guests; ?> <?php echo $guests > 1 ? 'guests' : 'guest'; ?></div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">Duration</div>
                                <div class="text-gray-600"><?php echo $nights; ?> <?php echo $nights > 1 ? 'nights' : 'night'; ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Room Rate</span>
                            <span><?php echo formatCurrency($roomType['price_per_night']); ?> x <?php echo $nights; ?></span>
                        </div>
                        
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span><?php echo formatCurrency($totalPrice); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Toggle payment form based on selected payment method
        $('input[name="payment_method"]').change(function() {
            if ($(this).val() === 'credit_card') {
                $('#credit_card_form').show();
            } else {
                $('#credit_card_form').hide();
            }
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>
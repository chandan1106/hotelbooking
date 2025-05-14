<?php
/**
 * Hotel Owner Registration Page
 */
$pageTitle = 'Register as Hotel Owner';
require_once '../includes/init.php';

// Redirect if already logged in
if (isHotelOwnerLoggedIn()) {
    redirect(HOTEL_URL . '/dashboard.php');
}

// Get subscription plans
$db->query("SELECT * FROM subscription_plans ORDER BY price ASC");
$subscriptionPlans = $db->resultSet();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize POST data
    $firstName = clean($_POST['first_name']);
    $lastName = clean($_POST['last_name']);
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $phone = clean($_POST['phone']);
    $companyName = clean($_POST['company_name']);
    $taxId = clean($_POST['tax_id']);
    $address = clean($_POST['address']);
    $city = clean($_POST['city']);
    $country = clean($_POST['country']);
    $subscriptionId = clean($_POST['subscription_id']);
    
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
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    if (empty($companyName)) {
        $errors[] = 'Company name is required';
    }
    
    if (empty($subscriptionId)) {
        $errors[] = 'Please select a subscription plan';
    }
    
    // Check if email already exists
    $db->query("SELECT id FROM hotel_owners WHERE email = :email");
    $db->bind(':email', $email);
    $existingOwner = $db->single();
    
    if ($existingOwner) {
        $errors[] = 'Email already exists. Please use a different email or login.';
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Get subscription details
        $db->query("SELECT * FROM subscription_plans WHERE id = :id");
        $db->bind(':id', $subscriptionId);
        $subscription = $db->single();
        
        if (!$subscription) {
            $errors[] = 'Invalid subscription plan';
        } else {
            // Calculate subscription dates
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d', strtotime("+{$subscription['duration']} days"));
            
            // Begin transaction
            $db->beginTransaction();
            
            try {
                // Insert hotel owner into database
                $db->query("INSERT INTO hotel_owners (first_name, last_name, email, password, phone, company_name, tax_id, address, city, country, subscription_id, subscription_start_date, subscription_end_date) 
                            VALUES (:first_name, :last_name, :email, :password, :phone, :company_name, :tax_id, :address, :city, :country, :subscription_id, :subscription_start_date, :subscription_end_date)");
                $db->bind(':first_name', $firstName);
                $db->bind(':last_name', $lastName);
                $db->bind(':email', $email);
                $db->bind(':password', $hashedPassword);
                $db->bind(':phone', $phone);
                $db->bind(':company_name', $companyName);
                $db->bind(':tax_id', $taxId);
                $db->bind(':address', $address);
                $db->bind(':city', $city);
                $db->bind(':country', $country);
                $db->bind(':subscription_id', $subscriptionId);
                $db->bind(':subscription_start_date', $startDate);
                $db->bind(':subscription_end_date', $endDate);
                $db->execute();
                
                $hotelOwnerId = $db->lastInsertId();
                
                // Create subscription transaction
                $db->query("INSERT INTO subscription_transactions (hotel_owner_id, subscription_id, amount, payment_method, status) 
                            VALUES (:hotel_owner_id, :subscription_id, :amount, :payment_method, :status)");
                $db->bind(':hotel_owner_id', $hotelOwnerId);
                $db->bind(':subscription_id', $subscriptionId);
                $db->bind(':amount', $subscription['price']);
                $db->bind(':payment_method', 'credit_card'); // This would be replaced with actual payment method
                $db->bind(':status', 'pending'); // This would be updated after payment processing
                $db->execute();
                
                // Commit transaction
                $db->endTransaction();
                
                // Set success message and redirect to payment page
                $_SESSION['success_message'] = 'Registration successful! Please complete your payment to activate your account.';
                $_SESSION['hotel_owner_id'] = $hotelOwnerId; // Temporarily set session for payment
                redirect(HOTEL_URL . '/payment.php?transaction_type=subscription');
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $db->cancelTransaction();
                $errors[] = 'Something went wrong. Please try again.';
            }
        }
    }
}

require_once '../includes/header.php';
?>

<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6 mt-8">
    <h2 class="text-2xl font-bold mb-6 text-center">Register as a Hotel Owner</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul class="list-disc pl-4">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="space-y-4">
        <h3 class="text-lg font-semibold mb-2">Personal Information</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="first_name" class="block text-gray-700 font-medium mb-2">First Name</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo isset($firstName) ? $firstName : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <div>
                <label for="last_name" class="block text-gray-700 font-medium mb-2">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo isset($lastName) ? $lastName : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
        </div>
        
        <div>
            <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        
        <div>
            <label for="phone" class="block text-gray-700 font-medium mb-2">Phone Number</label>
            <input type="tel" id="phone" name="phone" value="<?php echo isset($phone) ? $phone : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <p class="text-sm text-gray-500 mt-1">Password must be at least 6 characters long</p>
            </div>
            
            <div>
                <label for="confirm_password" class="block text-gray-700 font-medium mb-2">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
        </div>
        
        <hr class="my-6">
        
        <h3 class="text-lg font-semibold mb-2">Business Information</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="company_name" class="block text-gray-700 font-medium mb-2">Company/Business Name</label>
                <input type="text" id="company_name" name="company_name" value="<?php echo isset($companyName) ? $companyName : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <div>
                <label for="tax_id" class="block text-gray-700 font-medium mb-2">Tax ID/Business Registration Number</label>
                <input type="text" id="tax_id" name="tax_id" value="<?php echo isset($taxId) ? $taxId : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        
        <div>
            <label for="address" class="block text-gray-700 font-medium mb-2">Business Address</label>
            <input type="text" id="address" name="address" value="<?php echo isset($address) ? $address : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="city" class="block text-gray-700 font-medium mb-2">City</label>
                <input type="text" id="city" name="city" value="<?php echo isset($city) ? $city : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label for="country" class="block text-gray-700 font-medium mb-2">Country</label>
                <input type="text" id="country" name="country" value="<?php echo isset($country) ? $country : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        
        <hr class="my-6">
        
        <h3 class="text-lg font-semibold mb-2">Subscription Plan</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php foreach ($subscriptionPlans as $plan): ?>
                <div class="border rounded-lg p-4 <?php echo (isset($subscriptionId) && $subscriptionId == $plan['id']) ? 'border-blue-500 bg-blue-50' : ''; ?>">
                    <div class="flex items-center mb-2">
                        <input type="radio" id="plan_<?php echo $plan['id']; ?>" name="subscription_id" value="<?php echo $plan['id']; ?>" <?php echo (isset($subscriptionId) && $subscriptionId == $plan['id']) ? 'checked' : ''; ?> class="mr-2">
                        <label for="plan_<?php echo $plan['id']; ?>" class="font-bold"><?php echo $plan['name']; ?></label>
                    </div>
                    <p class="text-gray-600 mb-2"><?php echo $plan['description']; ?></p>
                    <p class="font-bold text-lg"><?php echo formatCurrency($plan['price']); ?>/month</p>
                    <?php 
                    $features = json_decode($plan['features'], true);
                    if ($features && isset($features['features'])):
                    ?>
                        <ul class="text-sm text-gray-600 mt-2 list-disc pl-4">
                            <?php foreach ($features['features'] as $feature): ?>
                                <li><?php echo $feature; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="flex items-center mt-4">
            <input type="checkbox" id="terms" name="terms" class="mr-2" required>
            <label for="terms" class="text-gray-700">I agree to the <a href="../terms.php" class="text-blue-600 hover:underline">Terms of Service</a> and <a href="../privacy.php" class="text-blue-600 hover:underline">Privacy Policy</a></label>
        </div>
        
        <div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition duration-200">Register & Continue to Payment</button>
        </div>
    </form>
    
    <div class="mt-6 text-center">
        <p class="text-gray-600">Already have an account? <a href="login.php" class="text-blue-600 hover:underline">Login</a></p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
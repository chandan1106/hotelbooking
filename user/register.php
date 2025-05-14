<?php
/**
 * User Registration Page
 */
$pageTitle = 'Register';
require_once '../includes/init.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(USER_URL . '/dashboard.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize POST data
    $firstName = clean($_POST['first_name']);
    $lastName = clean($_POST['last_name']);
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $phone = clean($_POST['phone']);
    
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
    
    // Check if email already exists
    $db->query("SELECT id FROM users WHERE email = :email");
    $db->bind(':email', $email);
    $existingUser = $db->single();
    
    if ($existingUser) {
        $errors[] = 'Email already exists. Please use a different email or login.';
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user into database
        $db->query("INSERT INTO users (first_name, last_name, email, password, phone) 
                    VALUES (:first_name, :last_name, :email, :password, :phone)");
        $db->bind(':first_name', $firstName);
        $db->bind(':last_name', $lastName);
        $db->bind(':email', $email);
        $db->bind(':password', $hashedPassword);
        $db->bind(':phone', $phone);
        
        if ($db->execute()) {
            // Set success message and redirect to login
            $_SESSION['success_message'] = 'Registration successful! You can now log in.';
            redirect(USER_URL . '/login.php');
        } else {
            $errors[] = 'Something went wrong. Please try again.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6 mt-8">
    <h2 class="text-2xl font-bold mb-6 text-center">Create an Account</h2>
    
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
        
        <div>
            <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
            <input type="password" id="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <p class="text-sm text-gray-500 mt-1">Password must be at least 6 characters long</p>
        </div>
        
        <div>
            <label for="confirm_password" class="block text-gray-700 font-medium mb-2">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        
        <div class="flex items-center">
            <input type="checkbox" id="terms" name="terms" class="mr-2" required>
            <label for="terms" class="text-gray-700">I agree to the <a href="../terms.php" class="text-blue-600 hover:underline">Terms of Service</a> and <a href="../privacy.php" class="text-blue-600 hover:underline">Privacy Policy</a></label>
        </div>
        
        <div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition duration-200">Register</button>
        </div>
    </form>
    
    <div class="mt-6 text-center">
        <p class="text-gray-600">Already have an account? <a href="login.php" class="text-blue-600 hover:underline">Login</a></p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
<?php
/**
 * User Login Page
 */
$pageTitle = 'Login';
require_once '../includes/init.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(USER_URL . '/dashboard.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize POST data
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $rememberMe = isset($_POST['remember_me']);
    
    // Validation
    $errors = [];
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    // If no errors, proceed with login
    if (empty($errors)) {
        // Check if user exists
        $db->query("SELECT * FROM users WHERE email = :email");
        $db->bind(':email', $email);
        $user = $db->single();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            
            // Update last login time
            $db->query("UPDATE users SET last_login = NOW() WHERE id = :id");
            $db->bind(':id', $user['id']);
            $db->execute();
            
            // Set remember me cookie if checked
            if ($rememberMe) {
                $token = generateRandomString(32);
                $expiry = time() + (30 * 24 * 60 * 60); // 30 days
                
                // Store token in database
                $db->query("UPDATE users SET remember_token = :token, remember_expiry = :expiry WHERE id = :id");
                $db->bind(':token', $token);
                $db->bind(':expiry', date('Y-m-d H:i:s', $expiry));
                $db->bind(':id', $user['id']);
                $db->execute();
                
                // Set cookie
                setcookie('remember_user', $user['id'] . ':' . $token, $expiry, '/');
            }
            
            // Redirect to dashboard
            redirect(USER_URL . '/dashboard.php');
        } else {
            $errors[] = 'Invalid email or password';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6 mt-8">
    <h2 class="text-2xl font-bold mb-6 text-center">Login to Your Account</h2>
    
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
        <div>
            <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        
        <div>
            <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
            <input type="password" id="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input type="checkbox" id="remember_me" name="remember_me" class="mr-2">
                <label for="remember_me" class="text-gray-700">Remember me</label>
            </div>
            <a href="forgot_password.php" class="text-blue-600 hover:underline">Forgot Password?</a>
        </div>
        
        <div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition duration-200">Login</button>
        </div>
    </form>
    
    <div class="mt-6 text-center">
        <p class="text-gray-600">Don't have an account? <a href="register.php" class="text-blue-600 hover:underline">Register</a></p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
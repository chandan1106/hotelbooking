<?php
/**
 * Admin Login Page
 */
$pageTitle = 'Admin Login';
require_once '../includes/init.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    redirect(ADMIN_URL . '/dashboard.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize POST data
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    
    // Validation
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Username is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    // If no errors, proceed with login
    if (empty($errors)) {
        // Check if admin exists
        $db->query("SELECT * FROM admins WHERE username = :username");
        $db->bind(':username', $username);
        $admin = $db->single();
        
        if ($admin && password_verify($password, $admin['password'])) {
            // Set session variables
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];
            
            // Update last login time
            $db->query("UPDATE admins SET last_login = NOW() WHERE id = :id");
            $db->bind(':id', $admin['id']);
            $db->execute();
            
            // Redirect to dashboard
            redirect(ADMIN_URL . '/dashboard.php');
        } else {
            $errors[] = 'Invalid username or password';
        }
    }
}

// Custom header for admin login
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-6">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800"><?php echo SITE_NAME; ?></h1>
            <h2 class="text-xl font-semibold text-gray-600">Admin Login</h2>
        </div>
        
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
                <label for="username" class="block text-gray-700 font-medium mb-2">Username</label>
                <input type="text" id="username" name="username" value="<?php echo isset($username) ? $username : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <div>
                <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition duration-200">Login</button>
            </div>
        </form>
        
        <div class="mt-6 text-center">
            <a href="../index.php" class="text-blue-600 hover:underline">Back to Website</a>
        </div>
    </div>
</body>
</html>
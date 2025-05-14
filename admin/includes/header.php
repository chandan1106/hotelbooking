<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME . ' Admin' : SITE_NAME . ' Admin'; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom styles -->
    <style>
        .sidebar {
            width: 250px;
            transition: all 0.3s;
        }
        
        .content {
            margin-left: 250px;
            transition: all 0.3s;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .content {
                margin-left: 0;
            }
            
            .content.active {
                margin-left: 250px;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">
    <!-- Sidebar -->
    <div class="sidebar bg-gray-800 text-white fixed h-full z-10">
        <div class="p-4 border-b border-gray-700">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-bold"><?php echo SITE_NAME; ?> Admin</h1>
                <button id="sidebarToggle" class="md:hidden text-white">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        
        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/dashboard.php" class="block py-2 px-4 rounded hover:bg-gray-700 <?php echo strpos($_SERVER['PHP_SELF'], 'dashboard.php') !== false ? 'bg-gray-700' : ''; ?>">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/users.php" class="block py-2 px-4 rounded hover:bg-gray-700 <?php echo strpos($_SERVER['PHP_SELF'], 'users.php') !== false ? 'bg-gray-700' : ''; ?>">
                        <i class="fas fa-users mr-2"></i> Users
                    </a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/hotel_owners.php" class="block py-2 px-4 rounded hover:bg-gray-700 <?php echo strpos($_SERVER['PHP_SELF'], 'hotel_owners.php') !== false ? 'bg-gray-700' : ''; ?>">
                        <i class="fas fa-user-tie mr-2"></i> Hotel Owners
                    </a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/hotels.php" class="block py-2 px-4 rounded hover:bg-gray-700 <?php echo strpos($_SERVER['PHP_SELF'], 'hotels.php') !== false ? 'bg-gray-700' : ''; ?>">
                        <i class="fas fa-hotel mr-2"></i> Hotels
                    </a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/bookings.php" class="block py-2 px-4 rounded hover:bg-gray-700 <?php echo strpos($_SERVER['PHP_SELF'], 'bookings.php') !== false ? 'bg-gray-700' : ''; ?>">
                        <i class="fas fa-calendar-check mr-2"></i> Bookings
                    </a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/payments.php" class="block py-2 px-4 rounded hover:bg-gray-700 <?php echo strpos($_SERVER['PHP_SELF'], 'payments.php') !== false ? 'bg-gray-700' : ''; ?>">
                        <i class="fas fa-credit-card mr-2"></i> Payments
                    </a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/subscriptions.php" class="block py-2 px-4 rounded hover:bg-gray-700 <?php echo strpos($_SERVER['PHP_SELF'], 'subscriptions.php') !== false ? 'bg-gray-700' : ''; ?>">
                        <i class="fas fa-tags mr-2"></i> Subscription Plans
                    </a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/reviews.php" class="block py-2 px-4 rounded hover:bg-gray-700 <?php echo strpos($_SERVER['PHP_SELF'], 'reviews.php') !== false ? 'bg-gray-700' : ''; ?>">
                        <i class="fas fa-star mr-2"></i> Reviews
                    </a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/settings.php" class="block py-2 px-4 rounded hover:bg-gray-700 <?php echo strpos($_SERVER['PHP_SELF'], 'settings.php') !== false ? 'bg-gray-700' : ''; ?>">
                        <i class="fas fa-cog mr-2"></i> Settings
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="content flex-1">
        <!-- Top Navigation -->
        <header class="bg-white shadow-sm">
            <div class="flex justify-between items-center p-4">
                <button id="mobileMenuToggle" class="md:hidden text-gray-700">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="flex items-center">
                    <div class="relative group">
                        <button class="flex items-center text-gray-700 focus:outline-none">
                            <span class="mr-2"><?php echo $_SESSION['admin_name']; ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg hidden group-hover:block z-10">
                            <a href="<?php echo ADMIN_URL; ?>/profile.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Profile</a>
                            <a href="<?php echo ADMIN_URL; ?>/logout.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="p-4">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['success_message']; ?></span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <title>Close</title>
                            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                        </svg>
                    </span>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['error_message']; ?></span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <title>Close</title>
                            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                        </svg>
                    </span>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
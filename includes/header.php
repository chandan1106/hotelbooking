<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Date Range Picker -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    
    <!-- Custom styles -->
    <style>
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 2s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="<?php echo SITE_URL; ?>" class="text-xl font-bold">
                        <?php echo SITE_NAME; ?>
                    </a>
                </div>
                
                <div class="hidden md:flex items-center space-x-4">
                    <a href="<?php echo SITE_URL; ?>" class="px-3 py-2 rounded hover:bg-blue-700">Home</a>
                    <a href="<?php echo SITE_URL; ?>/search.php" class="px-3 py-2 rounded hover:bg-blue-700">Find Hotels</a>
                    <a href="<?php echo SITE_URL; ?>/about.php" class="px-3 py-2 rounded hover:bg-blue-700">About</a>
                    <a href="<?php echo SITE_URL; ?>/contact.php" class="px-3 py-2 rounded hover:bg-blue-700">Contact</a>
                    
                    <?php if (isset($currentUser)): ?>
                        <div class="relative group">
                            <button class="flex items-center px-3 py-2 rounded hover:bg-blue-700">
                                <span class="mr-2"><?php echo $currentUser['first_name']; ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg hidden group-hover:block z-10">
                                <a href="<?php echo USER_URL; ?>/dashboard.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Dashboard</a>
                                <a href="<?php echo USER_URL; ?>/bookings.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">My Bookings</a>
                                <a href="<?php echo USER_URL; ?>/profile.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Profile</a>
                                <a href="<?php echo USER_URL; ?>/logout.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>
                    <?php elseif (isset($currentHotelOwner)): ?>
                        <div class="relative group">
                            <button class="flex items-center px-3 py-2 rounded hover:bg-blue-700">
                                <span class="mr-2"><?php echo $currentHotelOwner['first_name']; ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg hidden group-hover:block z-10">
                                <a href="<?php echo HOTEL_URL; ?>/dashboard.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Dashboard</a>
                                <a href="<?php echo HOTEL_URL; ?>/hotels.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">My Hotels</a>
                                <a href="<?php echo HOTEL_URL; ?>/bookings.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Bookings</a>
                                <a href="<?php echo HOTEL_URL; ?>/subscription.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Subscription</a>
                                <a href="<?php echo HOTEL_URL; ?>/profile.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Profile</a>
                                <a href="<?php echo HOTEL_URL; ?>/logout.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo USER_URL; ?>/login.php" class="px-3 py-2 rounded hover:bg-blue-700">Login</a>
                        <a href="<?php echo USER_URL; ?>/register.php" class="px-3 py-2 rounded hover:bg-blue-700">Register</a>
                        <a href="<?php echo HOTEL_URL; ?>/login.php" class="px-3 py-2 bg-blue-800 rounded hover:bg-blue-900">Hotel Owners</a>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button class="mobile-menu-button">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile menu -->
            <div class="mobile-menu hidden md:hidden pb-4">
                <a href="<?php echo SITE_URL; ?>" class="block px-3 py-2 rounded hover:bg-blue-700">Home</a>
                <a href="<?php echo SITE_URL; ?>/search.php" class="block px-3 py-2 rounded hover:bg-blue-700">Find Hotels</a>
                <a href="<?php echo SITE_URL; ?>/about.php" class="block px-3 py-2 rounded hover:bg-blue-700">About</a>
                <a href="<?php echo SITE_URL; ?>/contact.php" class="block px-3 py-2 rounded hover:bg-blue-700">Contact</a>
                
                <?php if (isset($currentUser)): ?>
                    <a href="<?php echo USER_URL; ?>/dashboard.php" class="block px-3 py-2 rounded hover:bg-blue-700">Dashboard</a>
                    <a href="<?php echo USER_URL; ?>/bookings.php" class="block px-3 py-2 rounded hover:bg-blue-700">My Bookings</a>
                    <a href="<?php echo USER_URL; ?>/profile.php" class="block px-3 py-2 rounded hover:bg-blue-700">Profile</a>
                    <a href="<?php echo USER_URL; ?>/logout.php" class="block px-3 py-2 rounded hover:bg-blue-700">Logout</a>
                <?php elseif (isset($currentHotelOwner)): ?>
                    <a href="<?php echo HOTEL_URL; ?>/dashboard.php" class="block px-3 py-2 rounded hover:bg-blue-700">Dashboard</a>
                    <a href="<?php echo HOTEL_URL; ?>/hotels.php" class="block px-3 py-2 rounded hover:bg-blue-700">My Hotels</a>
                    <a href="<?php echo HOTEL_URL; ?>/bookings.php" class="block px-3 py-2 rounded hover:bg-blue-700">Bookings</a>
                    <a href="<?php echo HOTEL_URL; ?>/subscription.php" class="block px-3 py-2 rounded hover:bg-blue-700">Subscription</a>
                    <a href="<?php echo HOTEL_URL; ?>/profile.php" class="block px-3 py-2 rounded hover:bg-blue-700">Profile</a>
                    <a href="<?php echo HOTEL_URL; ?>/logout.php" class="block px-3 py-2 rounded hover:bg-blue-700">Logout</a>
                <?php else: ?>
                    <a href="<?php echo USER_URL; ?>/login.php" class="block px-3 py-2 rounded hover:bg-blue-700">Login</a>
                    <a href="<?php echo USER_URL; ?>/register.php" class="block px-3 py-2 rounded hover:bg-blue-700">Register</a>
                    <a href="<?php echo HOTEL_URL; ?>/login.php" class="block px-3 py-2 rounded hover:bg-blue-700">Hotel Owners</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Main content -->
    <main class="flex-grow">
        <div class="container mx-auto px-4 py-6">
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
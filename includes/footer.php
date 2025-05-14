        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4"><?php echo SITE_NAME; ?></h3>
                    <p class="text-gray-400">Find and book the perfect accommodation for your next trip. Our platform connects travelers with hotel owners worldwide.</p>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="<?php echo SITE_URL; ?>" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/search.php" class="text-gray-400 hover:text-white">Find Hotels</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/about.php" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contact.php" class="text-gray-400 hover:text-white">Contact Us</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">For Hotel Owners</h3>
                    <ul class="space-y-2">
                        <li><a href="<?php echo HOTEL_URL; ?>/register.php" class="text-gray-400 hover:text-white">Register Your Hotel</a></li>
                        <li><a href="<?php echo HOTEL_URL; ?>/login.php" class="text-gray-400 hover:text-white">Hotel Owner Login</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pricing.php" class="text-gray-400 hover:text-white">Subscription Plans</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/faq.php" class="text-gray-400 hover:text-white">FAQs</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-envelope mr-2"></i> contact@hotelbooking.com</li>
                        <li><i class="fas fa-phone mr-2"></i> +1 (555) 123-4567</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i> 123 Main Street, City, Country</li>
                    </ul>
                    <div class="mt-4 flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-6 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                <div class="mt-4 md:mt-0 flex space-x-4">
                    <a href="<?php echo SITE_URL; ?>/terms.php" class="text-gray-400 hover:text-white">Terms of Service</a>
                    <a href="<?php echo SITE_URL; ?>/privacy.php" class="text-gray-400 hover:text-white">Privacy Policy</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Mobile menu script -->
    <script>
        $(document).ready(function() {
            // Mobile menu toggle
            $('.mobile-menu-button').click(function() {
                $('.mobile-menu').toggleClass('hidden');
            });
            
            // Close alert messages
            $('[role="alert"] svg').click(function() {
                $(this).closest('[role="alert"]').remove();
            });
        });
    </script>
</body>
</html>
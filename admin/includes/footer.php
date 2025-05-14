        </main>
    </div>
    
    <!-- Scripts -->
    <script>
        $(document).ready(function() {
            // Mobile menu toggle
            $('#mobileMenuToggle, #sidebarToggle').click(function() {
                $('.sidebar').toggleClass('active');
                $('.content').toggleClass('active');
            });
            
            // Close alert messages
            $('[role="alert"] svg').click(function() {
                $(this).closest('[role="alert"]').remove();
            });
        });
    </script>
</body>
</html>
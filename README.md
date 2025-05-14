# Hotel Booking System

A comprehensive SaaS application for hotel booking management, connecting hotel owners with customers.

## Features

### For End Users
- Search and filter hotels by location, dates, and amenities
- View detailed hotel and room information
- Book rooms with secure payment processing
- Manage bookings (view, cancel, modify)
- Write reviews for past stays
- User account management

### For Hotel Owners
- Subscription-based hotel registration
- Add and manage hotels, room types, and rooms
- Upload hotel and room images
- Set room pricing and availability
- Manage bookings and view reports
- Receive direct payments from customers

### For Administrators
- Manage users, hotel owners, and hotels
- Monitor bookings and payments
- Manage subscription plans
- View system statistics and reports

## Technology Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS (Tailwind CSS), JavaScript
- **Libraries**: jQuery, Chart.js
- **Additional**: AJAX for dynamic content loading

## Installation

1. Clone the repository:
```
git clone https://github.com/yourusername/hotel-booking.git
```

2. Import the database schema:
```
mysql -u username -p database_name < database/hotel_booking.sql
```

3. Configure the database connection in `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'hotel_booking');
```

4. Set up your web server (Apache/Nginx) to point to the project directory.

5. Make sure the upload directories are writable:
```
chmod -R 755 uploads/
```

## Directory Structure

```
/
├── admin/              # Admin panel files
├── assets/             # CSS, JS, and image files
├── config/             # Configuration files
├── database/           # Database schema and migrations
├── hotel/              # Hotel owner panel files
├── includes/           # Shared PHP files and functions
├── uploads/            # Uploaded images
│   ├── hotels/         # Hotel images
│   ├── rooms/          # Room images
│   └── users/          # User profile images
├── user/               # User account files
├── index.php           # Homepage
├── search.php          # Hotel search page
├── hotel.php           # Hotel details page
├── booking.php         # Booking page
└── README.md           # Project documentation
```

## Default Admin Login

- Username: admin
- Password: admin123

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Credits

- [Tailwind CSS](https://tailwindcss.com/)
- [Font Awesome](https://fontawesome.com/)
- [Chart.js](https://www.chartjs.org/)
- [jQuery](https://jquery.com/)
- [DateRangePicker](https://www.daterangepicker.com/)
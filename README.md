# QR Table Ordering System

A real-time restaurant ordering system built with PHP and MySQL. Guests scan QR codes at their tables to browse the menu, select items with options, and place orders. Staff can manage orders, track sales, and analyze performance through an admin dashboard.

## Features

### Guest Ordering
- **QR Code Entry**: Scan QR code to access ordering system for specific table
- **Order Type Selection**: Choose between Eat In or Take Away
- **Interactive Menu**: Browse menu items by category with images
- **Item Options**: Select customizations (spicy level, vegetables, meat, etc.)
- **Real-time Quantity Selection**: Adjust quantities with stepper controls
- **Order Review**: Review order before submission
- **Multi-language Support**: English, Burmese, and Thai menu item names

### Admin Dashboard
- **Order Management**: View pending orders in FIFO queue
- **Order Completion**: Mark orders as done with one click
- **Menu Management**: Add, edit, delete menu items
- **Image Upload**: Upload and manage menu item images
- **Options Management**: Create and manage item options with pricing
- **Sales Reports**: Daily and monthly revenue reports
- **Popular Items**: Track best-selling items with option details
- **QR Code Generation**: Generate QR codes for each table
- **Data Reset**: Clear all order and report data when needed
- **Demo Menu Preview**: Preview menu as guests see it

### Technical Features
- **Session Management**: Persistent order type selection per table
- **Local Storage**: Client-side caching for improved UX
- **Database Foreign Keys**: Cascading deletes for data integrity
- **Responsive Design**: Mobile-friendly interface
- **High Contrast UI**: Accessible design with clear visibility
- **Bangkok Timezone**: All timestamps in Asia/Bangkok timezone

## Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or PHP built-in server

### Installation

1. **Clone or download the project to your web server**

2. **Create a MySQL database**
   ```sql
   CREATE DATABASE qr_table CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Import the database schema**
   ```bash
   mysql -u username -p qr_table < db/schema.sql
   ```

4. **Import report tables schema**
   ```bash
   mysql -u username -p qr_table < db/reports_schema.sql
   ```

5. **Seed menu data (optional)**
   ```bash
   mysql -u username -p qr_table < db/seed_menu.sql
   mysql -u username -p qr_table < db/seed_menu_options.sql
   ```

6. **Configure database credentials**
   Edit `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'qr_table');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

7. **Set admin password**
   Default admin credentials:
   - Username: `admin`
   - Password: `morningstarhuamak`
   
   To change, edit `lib/auth.php` and update the password hash.

8. **Run the server**
   ```bash
   php -S localhost:8000
   ```

9. **Access the application**
   - Guest ordering: `http://localhost:8000/index.php?table=1`
   - Admin panel: `http://localhost:8000/admin/dashboard.php`

## File Structure

```
QR Table Project/
├── admin/                    # Admin panel
│   ├── dashboard.php        # Main admin dashboard
│   ├── mark_done.php        # Mark order as complete
│   ├── menu.php             # Menu management (legacy)
│   ├── qr_tables.php        # QR code generation
│   ├── reset.php            # Reset all data
│   ├── partials/            # Admin tab components
│   │   ├── orders_tab.php
│   │   ├── menu_tab.php
│   │   ├── options_tab.php
│   │   ├── reports_tab.php
│   │   ├── qr_tab.php
│   │   └── demo_tab.php
│   └── reset_reports.php    # Reset report data
├── assets/                   # Static assets
│   └── styles.css          # Custom styles
├── db/                      # Database files
│   ├── schema.sql          # Main schema
│   ├── reports_schema.sql  # Reports tables
│   ├── seed_menu.sql       # Menu items seed
│   └── seed_menu_options.sql # Options seed
├── lib/                     # Core libraries
│   ├── auth.php            # Authentication
│   ├── db.php              # Database connection
│   ├── helpers.php         # Helper functions
│   └── reports.php         # Report generation
├── partials/                # Shared templates
│   ├── head.php            # HTML head
│   └── foot.php            # HTML footer
├── scripts/                 # Utility scripts
│   ├── image_organizer.php # Organize menu images
│   └── replace_menu_items.php # Bulk menu update
├── config.php              # Configuration
├── index.php               # QR entry point
├── menu.php                # Guest menu
├── place_order.php         # Order submission
├── review_order.php        # Order review
├── set_order_type.php      # Order type selection
└── reset.php               # Reset session data
```

## Database Schema

### Core Tables
- **menu_items**: Menu items with multilingual names, pricing, categories
- **orders**: Customer orders with table number, type, status
- **order_items**: Individual items in an order with quantities and options
- **menu_item_options**: Customization options for menu items

### Report Tables
- **daily_reports**: Aggregated daily sales data
- **monthly_reports**: Aggregated monthly sales data
- **popular_items_monthly**: Detailed popular items tracking

## Guest Flow

1. **Scan QR Code**: Guest scans QR code at table (e.g., `index.php?table=12`)
2. **Choose Order Type**: Select Eat In or Take Away (remembered for session)
3. **Browse Menu**: View menu items by category with images
4. **Select Items**: Click items to view details and options
5. **Customize**: Choose options (spicy level, vegetables, etc.)
6. **Set Quantity**: Adjust quantity using stepper controls
7. **Review Order**: Review selected items and total price
8. **Submit Order**: Send order to kitchen
9. **Wait**: Order appears in admin queue for staff

## Admin Flow

1. **Login**: Access admin panel with credentials
2. **View Orders**: See pending orders in FIFO queue
3. **Complete Orders**: Mark orders as done when ready
4. **Manage Menu**: Add/edit/delete menu items
5. **Upload Images**: Add photos to menu items
6. **Create Options**: Define customization options
7. **View Reports**: Check daily/monthly sales
8. **Generate QR Codes**: Create QR codes for tables
9. **Reset Data**: Clear all order/report data when needed

## Configuration

### Database Settings
Edit `config.php` to set database connection parameters.

### Timezone
The system uses Asia/Bangkok timezone. To change, edit `admin/dashboard.php`:
```php
date_default_timezone_set('Asia/Bangkok');
```

### Admin Password
To change admin password, edit `lib/auth.php` and update the password hash using:
```php
password_hash('your_new_password', PASSWORD_DEFAULT)
```

## Troubleshooting

### Orders not appearing in admin
- Check database connection in `config.php`
- Verify orders table has data
- Check admin authentication

### QR scan not showing selection page
- Clear browser cache and localStorage
- Check `index.php` session clearing logic
- Verify table parameter is passed correctly

### Quantity stepper not working
- Clear browser cache
- Check JavaScript console for errors
- Verify `step` attributes in HTML

### Images not displaying
- Check image file paths in database
- Verify images exist in correct folders
- Check file permissions

## Security Notes

- Admin panel requires authentication
- Passwords are hashed using PHP's password_hash()
- SQL injection protection through prepared statements
- XSS protection through output escaping
- Session-based authentication

## License & Copyright

This project is provided as-is for restaurant use.

**Copyright © 2026 Han Su Yin**

All credits and the whole project are written and copyright to Han Su Yin. 
Unauthorized reproduction, distribution, or modification of this project without explicit permission from the copyright holder is prohibited.

## Support

For issues or questions, refer to the code comments or contact development team.

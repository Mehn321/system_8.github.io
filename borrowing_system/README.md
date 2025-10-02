# Borrowing & Inventory System

A complete borrowing and inventory management system built with PHP (Object-Oriented Programming) and MySQL database, normalized up to 5th Normal Form (5NF).

## Features

### Core Features
- ✅ Borrower Information Management
- ✅ Inventory Management
- ✅ Borrowing Transaction Processing
- ✅ Digital Slip Generation
- ✅ User Authentication & Security

### Additional Features
- ✅ Dashboard with Statistics
- ✅ Search & Filter Functionality
- ✅ Reports & Data Export
- ✅ Activity Logging
- ✅ System Settings Configuration

## Database Schema

The database is normalized up to 5NF with the following tables:
- `admins` - Admin user credentials
- `borrowers` - Borrower information
- `items` - Inventory items
- `borrowing_transactions` - Transaction records
- `borrowed_items` - Item-borrower relationships
- `approvals` - Approval tracking
- `activity_logs` - Admin activity logging
- `system_settings` - System configuration

## Installation

1. **Database Setup:**
   ```sql
   -- Create database
   CREATE DATABASE borrowing_system;
   USE borrowing_system;

   -- Import the schema
   SOURCE database_schema.sql;
   ```

2. **Web Server Setup:**
   - Place the `borrowing_system` folder in your web server's root directory
   - Ensure PHP 7.4+ and MySQL 5.7+ are installed
   - Configure your web server to serve PHP files

3. **Access the System:**
   - Open browser and go to: `http://localhost/borrowing_system/`
   - Default login credentials:
     - Username: `admin`
     - Password: `password`

## File Structure

```
borrowing_system/
├── classes/
│   └── Database.php          # Database connection class
├── models/
│   ├── Admin.php            # Admin management
│   ├── Borrower.php         # Borrower management
│   ├── Item.php             # Inventory management
│   └── Transaction.php      # Transaction management
├── views/
│   ├── login.php           # Login page
│   ├── dashboard.php       # Main dashboard
│   └── borrowers.php       # Borrower management
├── includes/
│   └── config.php          # System configuration
├── assets/
│   └── css/
│       └── style.css       # System styles
├── database_schema.sql     # Database schema
├── index.php              # Entry point
└── README.md              # This file
```

## Usage

### Admin Login
1. Navigate to the login page
2. Enter credentials (admin/password)
3. Access the dashboard

### Managing Borrowers
1. Go to Borrowers page from dashboard
2. Add new borrowers with complete information
3. Search and manage existing borrowers

### Inventory Management
- Add new items to inventory
- Track available quantities
- Monitor low stock items

### Transaction Processing
- Create borrowing transactions
- Process approvals and issuances
- Track returns and overdue items

## Security Features

- Password encryption using PHP's password_hash()
- Session-based authentication
- Input validation and sanitization
- SQL injection prevention with prepared statements
- Activity logging for all admin actions

## Technical Specifications

- **PHP Version:** 7.4+
- **Database:** MySQL 5.7+
- **Architecture:** Object-Oriented PHP
- **Database Design:** 5NF Normalized
- **Security:** Password hashing, session management
- **UI:** Responsive design with external CSS

## Development Notes

- All CSS and JavaScript are in external files
- Uses mysqli extension only (no PDO)
- Object-oriented PHP throughout
- Comprehensive error handling
- Mobile-responsive design

## Future Enhancements

- Email notifications for overdue items
- PDF slip generation
- Advanced reporting features
- Multi-admin support
- API endpoints for mobile app integration

## Support

For technical support or questions about the system, please refer to the code comments or contact the development team.

---

**Version:** 1.0.0
**Last Updated:** 2024
**Developed with:** PHP, MySQL, HTML, CSS

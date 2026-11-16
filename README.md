# SMEIC - School Management for Educational Institution Canteen

## University Canteen Management System

**SMEIC** is a comprehensive University Canteen Management System built with PHP, MySQL, JavaScript, and Tailwind CSS. It streamlines the entire canteen operations workflow for students, parents, canteen staff, and administrators.

### System Overview

SMEIC follows a **PHP MVC-lite architecture** with a single front controller that routes all requests. The system supports four user roles with distinct permissions and workflows:

- **Admin** — Full system management, reports, and exports
- **Student** — Browse menu, place orders, track status, view pickup tokens
- **Parent** — Top up student wallets, view orders, set meal preferences
- **Canteen Staff** — Accept orders, mark readiness, verify pickup tokens

### Key Features

- **Role-based access control** with session-based authentication
- **Wallet system** for parent-to-student fund transfers
- **Real-time order tracking** with status timeline
- **6-digit QR code pickup verification** for contactless pickup
- **Notification system** for order status and wallet updates
- **CSV/PDF report exports** for administrative reporting
- **Daily specials** and meal preference suggestions
- **Cart management** with real-time updates via AJAX API

### Architecture

```
SMEIC/
├── config/                   # PHP configuration constants and connections
├── controllers/              # Role-specific controllers (Auth, Student, Canteen, Admin, Parent)
├── public/                   # Document root
│   ├── index.php             # Front controller / router
│   ├── api/                  # JSON API endpoints
│   ├── assets/css/           # Tailwind CSS custom styles
│   ├── assets/js/            # Vanilla JS + Alpine.js interactivity
│   └── views/                # PHP templates with shared layout
└── database.sql              # MySQL schema + seed data
```

### Technical Stack

- **Backend:** PHP 7.4+ with PDO MySQL extensions
- **Frontend:** HTML5, Tailwind CSS (via CDN), vanilla JavaScript, Alpine.js
- **Database:** MySQL 5.7+ with InnoDB engine
- **Authentication:** bcrypt password hashing, session-based with CSRF protection
- **API:** JSON responses via `public/api/` endpoints
- **Template Engine:** PHP includes with shared layout (header/sidebar/footer)

### Getting Started

1. **Database Setup:** Import `database.sql` into MySQL
2. **Configuration:** Edit `config/constants.php` for currency and token settings
3. **Web Server:** Place in web server document root (Apache/Nginx with PHP support)
4. **Access:** Navigate to `index.php?role={admin|student|parent|canteen}`
5. **Default Login:** Use seed credentials from `database.sql` (passwords use known hash pattern for initial setup)

### User Roles

| Role | Primary Actions |
|------|----------------|
| **Admin** | Manage users, export reports, view system-wide analytics |
| **Student** | Browse menu, add to cart, checkout, track orders, view pickup token |
| **Parent** | Top up wallet, view children's orders, set preferences, transfer funds |
| **Canteen** | Accept orders, mark preparing/ready, verify pickup tokens, manage staff |

### API Endpoints

| Endpoint | Auth | Description |
|----------|------|-------------|
| `public/api/update-cart.php` | student | Add/remove items from cart |
| `public/api/transfer-funds.php` | parent | Transfer funds to student wallet |
| `public/api/check-notifications.php` | any logged-in user | Check unread notifications |
| `public/api/get-order-status.php` | scoped | Get order status by ID |
| `public/api/check-pickup-reminders.php` | any logged-in user | Dispatch pickup reminders |

### Security

- **CSRF protection** on all state-changing requests
- **Prepared statements** for all SQL queries (prevents injection)
- **Role-based access control** at both UI and API level
- **Session fixation protection** with `session_regenerate_id()`
- **Password hashing** with bcrypt format
- **Input validation** and output escaping with `e()` helper

### Development

- Add new menu items via database `menu_items` table
- Extend roles by modifying `require_role()` checks in controllers
- New API endpoints follow the `_bootstrap.php` pattern
- Views use shared layout: `header.php` + content + `sidebar.php` + `footer.php`
- CSRF tokens embedded via `csrf_field()` helper in all forms

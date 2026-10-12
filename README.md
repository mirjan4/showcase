# Digital Project Showcase & Portfolio Management System

A high-performance, modern, and responsive **Project Showcase & Portfolio Management Web Application** built with **PHP 8+ (PDO)** and **MySQL**, designed to run locally on **XAMPP** without third-party dependencies or external hosting costs.

---

## 1. System Overview & Features

### 🌟 Public Showcase & Discovery
- **Hero & Brand Identity**: Atmospheric radial mesh, dynamic typography, subtitle, and instant CTA navigation.
- **Dynamic Statistics Counter**: Real-time KPI counters (Total Projects, Live Deployments, In Development, Categories) queried dynamically from MySQL.
- **Spotlight Featured Showcase**: High-profile hero card highlighting the premier active project with technology tags and direct case study / live links.
- **Instant Client-Side & Server Filter Engine**:
  - Live fuzzy search across project title, summary, and technology stack.
  - Category selector with live count badges.
  - Deployment status filters (`Live`, `Development`).
  - Multi-criteria sorting (Recently Updated, Recently Added, Alphabetical A–Z, Alphabetical Z–A).
  - Instant reset button and dynamic "Showing X of Y projects" counter.
- **Interactive Project Cards**:
  - 16:9 thumbnail previews with graceful SVG fallback.
  - Glowing status pills (pulsing green for `Live`, amber for `Development`, slate for `Archived`).
  - Micro-interactions: subtle card lift, smooth image zoom (`scale(1.05)`), border glow, and interactive view button.
- **Dedicated Project Details Pages (`project.php?slug=...`)**:
  - Full-width hero banner with click-to-enlarge Lightbox preview.
  - Breadcrumb navigation (`Home > Category > Project`).
  - Architectural overview narrative and formatted paragraphs.
  - **Key Features & Capabilities Checklist** with checkmark icons.
  - Technology stack pills (PHP 8, MySQL, CSS Grid, Chart.js, etc.).
  - **Interactive Gallery Showcase** with thumbnail grid and fullscreen Lightbox modal.
  - Prominent "Visit Live Website" button (with secure `target="_blank" rel="noopener noreferrer"`) and GitHub repository links.
  - Automated view counter tracking.
  - **Related Projects Showcase** displaying up to 3 similar systems.
- **Category Archive Pages (`category.php?slug=...`)**:
  - Filterable archives displaying all projects belonging to a specific taxonomy.
- **Developer Biography Page (`about.php`)**:
  - Developer profile, narrative, domain competencies (Backend, Database, Frontend, DevOps), and configurable social channels.
- **Dual Theme Support**:
  - Default refined dark navy mode (`#0a0f1d`).
  - Crisp light mode toggle with smooth CSS transitions and `localStorage` persistence.

### 🛡️ Admin Control Center & CMS
- **Secure Authentication**:
  - Passwords hashed using bcrypt via `password_hash()` and verified with `password_verify()`.
  - Session regeneration on login (`session_regenerate_id(true)`) to prevent session fixation.
  - Strict CSRF token validation on all state-changing POST actions.
  - Automated brute-force rate limiting (locks out IP after 5 consecutive failures for 15 minutes).
- **Interactive Dashboard (`admin/dashboard.php`)**:
  - KPI summary metric cards (Total, Live, Development, Total Views).
  - **Chart.js Visual Analytics**: Interactive Doughnut chart for Category Distribution and Bar chart for Project Statuses.
  - Recently updated projects summary table.
  - Quick-action shortcuts to add projects, manage categories, and configure settings.
- **Full Project CRUD (`admin/projects.php`, `add-project.php`, `edit-project.php`, `delete-project.php`)**:
  - Real-time automatic slug generator from project title with manual override.
  - Drag-and-drop thumbnail file upload with instant client-side image preview.
  - Multiple gallery screenshot uploads with live thumbnail previews.
  - In-place gallery screenshot management (delete individual screenshots with a single click).
  - Quick "Set to Today" button for maintenance dates.
  - Safe deletion modal with automatic disk cleanup (`unlink`) for thumbnails and gallery images.
  - Filterable, searchable, and paginated inventory table.
- **Taxonomy Management (`admin/categories.php`)**:
  - Add, edit, and organize project categories with custom icons.
  - Deletion safeguard: prevents deleting categories that have projects currently assigned.
- **Portfolio Settings (`admin/settings.php`)**:
  - Configure website title, tagline, hero text, developer bio, email, location, and social links (GitHub, LinkedIn, Twitter/X).

---

## 2. Technology Stack & Requirements

- **PHP**: 8.0, 8.1, 8.2, or 8.3 (with `pdo_mysql` and `fileinfo` extensions enabled — default in XAMPP).
- **Database**: MySQL 8.0+ or MariaDB 10.4+.
- **Web Server**: Apache 2.4 (via XAMPP).
- **Frontend**: HTML5, Vanilla CSS3 (Custom Design System with CSS variables), JavaScript (ES6+).
- **Icons**: Inlined Lucide SVG icons (Zero CDN latency or render-blocking).
- **Charts**: Chart.js 4.4 (Admin analytics).

---

## 3. Directory Structure

```
showcase/
├── index.php                 # Public Homepage (Hero, Stats, Filter, Grid, Categories)
├── project.php               # Dedicated Project Details & Gallery Page
├── category.php              # Category Archive & Filter Page
├── about.php                 # Developer Profile & Skills Page
├── config/
│   ├── app.php               # Global constants, URLs, and upload settings
│   └── database.php          # Secure PDO MySQL connection
├── includes/
│   ├── header.php            # Public site header, navigation & theme switcher
│   ├── footer.php            # Public footer, social links & Lightbox container
│   ├── functions.php         # Shared helper library (XSS escape, dates, icons, uploads)
│   └── project-card.php      # Reusable project card component
├── admin/
│   ├── auth.php              # Session security, CSRF protection, and rate limiting
│   ├── header.php            # Admin sidebar and top navigation layout
│   ├── footer.php            # Admin footer, script bindings, and delete modal
│   ├── index.php             # Redirects to dashboard
│   ├── login.php             # Admin sign-in with lockout prevention
│   ├── logout.php            # Secure session termination
│   ├── dashboard.php         # Analytics dashboard with Chart.js
│   ├── projects.php          # Inventory table with search, filter & pagination
│   ├── add-project.php       # Full project creation form with multi-image upload
│   ├── edit-project.php      # Full project edit form with gallery management
│   ├── delete-project.php    # Safe deletion handler with media unlinking
│   ├── categories.php        # Taxonomy CRUD with foreign key protection
│   ├── settings.php          # Portfolio identity, hero, bio & social links editor
│   └── create-admin.php      # Setup utility to create or reset administrator credentials
├── assets/
│   ├── css/
│   │   ├── style.css         # Master CSS design system (Dark & Light themes)
│   │   └── admin.css         # Admin control center stylesheet
│   ├── js/
│   │   ├── app.js            # Frontend live search, filtering, lightbox & theme switcher
│   │   └── admin.js          # Admin auto-slug, upload previews & Chart.js init
│   └── images/
│       └── placeholder.svg   # Vector fallback image
├── uploads/
│   ├── .htaccess             # Security rules blocking PHP script execution
│   ├── projects/             # Project cover thumbnails
│   └── gallery/              # Additional project screenshots
├── database/
│   ├── schema.sql            # MySQL schema (tables, foreign keys, indexes)
│   └── sample_data.sql       # Seed data: 5 realistic projects, categories & settings
└── README.md                 # Complete documentation & installation guide
```

---

## 4. Local Installation Guide on XAMPP

### Step 1: Place the Project in XAMPP
Ensure this folder is located in your XAMPP web root:
```
C:\xampp\htdocs\showcase
```

### Step 2: Start Apache and MySQL
1. Launch the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.
4. Ensure both services show green status indicators.

### Step 3: Import the MySQL Database
You can import the database using either phpMyAdmin or the MySQL command line:

#### Option A: Using phpMyAdmin (Web Interface)
1. Open your browser and navigate to `http://localhost/phpmyadmin`.
2. Click on the **Import** tab at the top.
3. Click **Choose File** and select `database/schema.sql` from the project folder. Click **Import**.
4. Next, click **Choose File** and select `database/sample_data.sql`. Click **Import**.

#### Option B: Using Windows PowerShell / Terminal
```powershell
# Open PowerShell in the project directory:
cd C:\xampp\htdocs\showcase

# Run schema and seed scripts:
Get-Content database/schema.sql | & "C:\xampp\mysql\bin\mysql.exe" -u root
Get-Content database/sample_data.sql | & "C:\xampp\mysql\bin\mysql.exe" -u root
```

### Step 4: Verify Database Connection
Open `config/database.php` and confirm credentials match your local MySQL setup:
```php
$host = 'localhost';
$dbname = 'project_portfolio';
$username = 'root';
$password = ''; // Default in XAMPP is empty
```

### Step 5: Launch the Application
- **Public Showcase**: Open `http://localhost/showcase/`
- **Admin Control Center**: Open `http://localhost/showcase/admin/login.php`

---

## 5. Default Administrator Credentials

| Username | Password | Email | Role |
| :--- | :--- | :--- | :--- |
| `admin` | `Admin@123456` | `admin@example.com` | Super Administrator |

> **Note**: To create a custom administrator or reset password locally, visit `http://localhost/showcase/admin/create-admin.php`.

---

## 6. Pre-Loaded Sample Projects

The database includes 5 comprehensive, realistic software projects matching common real-world domains:

1. **Ramalan Da'wa** — *Receipt Book-Based Collection and Settlement Management System* (Management Systems)
2. **Financial Ledger** — *Double-Entry Accounting and Financial Statement System* (Finance & Accounting)
3. **Markaz Alumni Portal** — *Global Alumni Network, Directory Search & Event Platform* (Web Applications)
4. **Darain** — *Modern High-Conversion E-Commerce Platform* (E-Commerce)
5. **Confab Registration** — *Symposium Delegate Credentialing & Live Attendance Suite* (Internal Tools)

All sample projects come with vector UI mockups and screenshot gallery assets in `uploads/projects/` and `uploads/gallery/`.

---

## 7. Security Best Practices Implemented

1. **SQL Injection Prevention**: All database queries use PDO prepared statements with parameterized inputs. Direct string concatenation into SQL queries is strictly avoided.
2. **Cross-Site Scripting (XSS) Mitigation**: All user-controlled and database-derived outputs are sanitized using `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')` via the `e()` helper function.
3. **Cross-Site Request Forgery (CSRF)**: All state-changing POST forms (login, create, update, delete) include cryptographically generated 256-bit CSRF tokens verified with `hash_equals()`.
4. **Session Security**: Session cookies enforce `HttpOnly`, `SameSite=Lax`, and secure session ID regeneration upon login (`session_regenerate_id(true)`).
5. **Brute-Force Rate Limiting**: The `login_attempts` table tracks failed logins by client IP. More than 5 consecutive failed attempts trigger an automated 15-minute temporary lockout.
6. **Strict Image Upload Sanitization**:
   - File extensions are validated against an allowlist (`jpg`, `jpeg`, `png`, `webp`, `svg`).
   - MIME types are verified using PHP `finfo` (Fileinfo) and `getimagesize()`.
   - Filenames are randomized with `bin2hex(random_bytes(16))` to prevent path traversal and file collision.
   - An `.htaccess` configuration inside `uploads/` completely disables script execution (`php_flag engine off` and `SetHandler default-handler`).

---

## 8. Backup & Maintenance Instructions

### Database Backup
Export the MySQL database using `mysqldump`:
```powershell
& "C:\xampp\mysql\bin\mysqldump.exe" -u root project_portfolio > backup_project_portfolio.sql
```

### Media Assets Backup
To preserve all uploaded project thumbnails and gallery images, backup the `uploads/` folder:
- `uploads/projects/`
- `uploads/gallery/`

---

## 9. Troubleshooting Common Issues

### Issue 1: "Database connection failed" error
- Ensure the MySQL service is started in the XAMPP Control Panel.
- Ensure the database `project_portfolio` was created and imported.
- Check `config/database.php` to ensure the host is `localhost`, user is `root`, and password matches your local MySQL setup.

### Issue 2: Uploaded image exceeds file size limit
- By default, the application permits uploads up to 5MB. If you receive a PHP upload limit error, check your `C:\xampp\php\php.ini`:
  ```ini
  upload_max_filesize = 10M
  post_max_size = 12M
  ```
  Restart Apache after editing `php.ini`.

### Issue 3: Fileinfo extension not enabled
- In `C:\xampp\php\php.ini`, verify that `extension=fileinfo` is uncommented (no leading semicolon).

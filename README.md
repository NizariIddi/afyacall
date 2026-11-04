# AfyaCall — Hospital Management System

**AfyaCall** is a bilingual (Swahili / English) hospital management web application built with vanilla **PHP** and **MariaDB**. It lets clinics and small hospitals manage patients, medical records, appointments, doctor shifts, health broadcasts, USSD services, and analytics — all from a secure staff dashboard.

> 🩺 "Afya" means *health* in Swahili. The system is designed for low-connectivity environments, with a USSD gateway so patients can check records and receive health tips on any mobile phone — no internet required.

## ✨ Features

| Module | What it does |
|--------|--------------|
| **Authentication** | Secure login with CSRF protection, bcrypt password hashing, brute-force lockout (5 attempts / 10 min), session timeout (30 min), and session-fixation regeneration. |
| **Dashboard** | Live counts for patients, appointments, records, USSD sessions; pending/overdue alerts; Chart.js trends (patient growth, appointment outcomes, 6-month stacked bar). |
| **Patients** | Register, view, edit, delete, and print patient cards/prescriptions. Patient codes (e.g. `AC-12345`) for USSD lookup. |
| **Medical Records** | Record diagnoses, medications, allergies, and clinical notes per visit. Print prescriptions directly. |
| **Appointments** | Schedule, track (Pending / Completed / Missed), calendar view, and overdue-appointment alerts. |
| **Doctor Scheduling** | Assign morning / afternoon / night / custom shifts per department with a weekly rota. |
| **Health Broadcasts** | Bilingual health-tip library (nutrition, exercise, malaria, diabetes, HIV, hygiene, maternal, mental, general) with SMS/USSD broadcast logging. |
| **USSD Gateway** | `*384*7#` menu in Swahili and English. Patients check medical records, appointments, and health tips via USSD simulator + real logs. |
| **Reports & Analytics** | Monthly reports, patient statistics, and USSD success-rate metrics. |
| **Admin** | User management (admin/doctor/nurse roles), activity-log audit trail, and system settings. |
| **Exports** | Export appointments, patients, and medical records. |

## 🛠 Tech Stack

| Layer | Technology |
|-------|------------|
| Language | **PHP 8.5** (CLI + built-in server; `mysqli` extension) |
| Database | **MariaDB 11.8** (InnoDB, `utf8mb4`) |
| Frontend | Vanilla HTML/CSS/JS, Chart.js 4.4 (via CDN), Google Fonts |
| Web server | PHP built-in server (dev) / Apache httpd (production) |
| Auth | PHP sessions (`httponly`, `SameSite=Strict`), `password_hash`/`password_verify` |

## 📁 Project Structure

```
afyacall/
├── README.md
├── index.php
├── database.sql
├── .htaccess
├── config/
├── auth/
├── dashboard/
├── includes/
├── admin/
├── appointments/
├── analytics/, reports/
├── exports/
├── medical_records/
├── patients/
├── scheduling/
├── settings/
├── telecom/
├── ussd/
└── assets/
```

## 🗄 Database

The schema lives in `database.sql` and creates **13 tables**:

`activity_logs`, `admins`, `appointments`, `broadcast_logs`, `doctor_shifts`,
`emergency_reports`, `health_tips`, `medical_records`, `notifications`,
`patients`, `settings`, `ussd_logs`, `ussd_menus`

Seed data includes 2 staff accounts:
- `admin@afyacall.com` (admin)
- `john@afyacall.com` (doctor)

## 🚀 Quick Start

### Prerequisites
- **PHP 8.1+** with `mysqli`, `pdo_mysql` extensions
- **MariaDB 10.6+** or MySQL 5.7+
- (Optional) **Apache httpd** for production deployment

### 1. Clone the repository
```bash
git clone https://github.com/NizariIddi/afyacall.git
cd afyacall
```

### 2. Create the database
```bash
mysql -u root -p -e "CREATE DATABASE afyacall CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;"
mysql -u root -p afyacall < database.sql
```

> The dump contains `DROP TABLE IF EXISTS` statements, so it is safe to re-import.

### 3. Configure the connection
Edit `config/database.php` to match your environment:

```php
$host     = "localhost";   // or 127.0.0.1
$user     = "root";
$password = "your_db_password";
$database = "afyacall";
$port     = 3306;

$conn = mysqli_connect($host, $user, $password, $database, $port);
```

### 4. Run the app
**Option A — PHP built-in server (fastest for local dev):**
```bash
php -S 127.0.0.1:8000
```
Then open <http://127.0.0.1:8000>

**Option B — Apache httpd:**
Point your document root at this folder and ensure `mod_php` / `php-fpm` is enabled.

## 🔐 Test Credentials (seed data)

After importing `database.sql`:

| Email | Password (set manually) | Role |
|-------|-------------------------|------|
| `admin@afyacall.com` | *(set your own)* | admin |
| `john@afyacall.com` | *(set your own)* | doctor |

To set a password for the admin account:
```bash
php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"
```
Copy the hash and update the `admins` table:
```sql
UPDATE admins SET password='$2y$12$...' WHERE email='admin@afyacall.com';
```

Then log in at <http://127.0.0.1:8000/auth/login.php>.

## 🛡 Security Highlights

- **CSRF protection** on every state-changing form (`csrf_field()` / `csrf_verify()`).
- **Brute-force lockout** — 5 failed attempts per IP triggers a 10-minute lockout.
- **Session hardening** — `httponly`, `SameSite=Strict`, strict mode, 30-minute inactivity timeout, periodic session-id regeneration.
- **Role-based access control** — `require_role(['admin'])`, `can('add_patient')`, etc.
- **Input sanitization** — `clean()`, `db_clean()` (mysqli real_escape), `clean_email()`, `clean_date()`.
- **SQL injection mitigation** — all user input is escaped before query interpolation.

## 📱 USSD (`*384*7#`)

Patients dial the USSD code and navigate bilingual (Swahili / English) menus:

1. Medical Records — enter patient code → view diagnosis, medication, next appointment
2. Appointments — enter patient code → view date, status, remarks
3. Health Tips — nutrition, exercise, malaria, diabetes, HIV/AIDS
4. Emergency Alerts

The USSD flow is simulated in `ussd/ussd_simulator.php` and logged to `ussd_logs` / `ussd_menus`.

## 🧪 Continuous Integration

This project uses **GitHub Actions** CI. On every push/PR it:
1. Spins up a MariaDB 11.8 service container
2. Installs PHP 8.x with `mysqli`
3. Creates the `afyacall` database and imports `database.sql`
4. Runs `php -l` syntax checks on all PHP files
5. Starts the PHP built-in server and smoke-tests key endpoints

See `.github/workflows/ci.yml`.

## 🤝 Contributing

1. Fork the repo
2. Create a feature branch (`git checkout -b feature/your-feature`)
3. Commit your changes
4. Push to the branch (`git push origin feature/your-feature`)
5. Open a Pull Request

Please ensure `php -l` passes on any changed PHP files and that CI is green.

## 📄 License

This project is open-source. See the repository for license details.
# Intervention System for Teacher Performance

A web-based intervention tracking and analytics system built for **Holy Cross of Davao College (HCDC)**. It helps academic administrators monitor teacher performance, manage exam results, and generate insights across departments and semesters — all through a clean, role-based interface.

Built with **Laravel 13**, **Tailwind CSS**, and **Vite**.

---

## Features

- **Role-based access** — separate dashboards for Admin and Assistant users
- **Teacher management** — assign subjects, sections, and semesters per teacher
- **Exam result tracking** — upload, parse, and manage exam results per subject
- **PDF parsing** — item analysis extraction via `smalol/pdfparser`
- **PDF export** — generate reports using `barryvdh/laravel-dompdf`
- **Intervention notes** — admins can write and track notes per teacher per semester
- **Analytics module** — pass/fail trends, department breakdowns, teacher performance views
- **Analytics cache** — computed analytics stored and invalidated on demand
- **CSV export** — filtered intervention data exportable as CSV
- **Grading methods** — supports Base 50, Base 20, and Base 0 grading per exam

---

## Tech Stack

| Layer      | Technology                        |
|------------|-----------------------------------|
| Framework  | Laravel 13 (PHP ^8.3)             |
| Frontend   | Blade + Tailwind CSS + Vite       |
| Auth       | Laravel Breeze                    |
| Database   | MySQL                             |
| PDF Parse  | smalot/pdfparser                  |
| PDF Export | barryvdh/laravel-dompdf           |
| Dev Tools  | Laravel Pint, Pail, PHPUnit       |

---

## Requirements

- PHP 8.3+
- Composer
- Node.js 18+ and npm
- MySQL 5.7+ or MariaDB 10.3+

---

## Getting Started

### 1. Clone the repository

```bash
git clone https://github.com/UserReji/Intervention_System_For_TeacherPerfomance.git
cd Intervention_System_For_TeacherPerfomance
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install JS dependencies

```bash
npm install
```

### 4. Set up environment

```bash
cp .env.example .env
php artisan key:generate
```

Then open `.env` and configure your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=intervention_system
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Run migrations and seed

```bash
php artisan migrate --seed
```

### 6. Build frontend assets

```bash
npm run build
```

### 7. Start the development server

```bash
php artisan serve
```

The app will be available at **http://localhost:8000**.

Or use the all-in-one dev command (server + queue + logs + Vite hot reload):

```bash
composer run dev
```

---

## Default Credentials

> These are seeded automatically when you run `php artisan migrate --seed`.

| Role      | Email                      | Password   |
|-----------|----------------------------|------------|
| Admin     | admin@hcdc.edu.ph          | password   |
| Assistant | assistant@hcdc.edu.ph      | password   |

> **Important:** Change these credentials before deploying to any production or public environment.

---

## Running Locally with Laragon

[Laragon](https://laragon.org) is a fast, portable local development environment for Windows — and the easiest way to run this project locally without any manual server setup.

### Setup steps

1. **Download and install Laragon** from [laragon.org](https://laragon.org/download/)
   - Recommended: Laragon Full (includes PHP 8.x, MySQL, Apache/Nginx, and phpMyAdmin)

2. **Place the project in Laragon's `www` folder**
   ```
   D:\laragon\www\Intervention_System\
   ```
   *(or wherever your Laragon `www` directory is)*

3. **Start Laragon** — click **Start All** in the Laragon UI.

4. **Create the database**
   - Open phpMyAdmin via Laragon menu → **Database** → **phpMyAdmin**
   - Create a new database named `intervention_system`

5. **Configure `.env`**
   ```env
   APP_URL=http://intervention_system.test

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=intervention_system
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   > Laragon's default MySQL user is `root` with no password.

6. **Run migrations and seed**
   - Open a terminal inside the project folder (right-click in Laragon → Terminal)
   ```bash
   php artisan migrate --seed
   ```

7. **Build frontend assets**
   ```bash
   npm install && npm run build
   ```

8. **Access the app**
   - Laragon auto-generates a pretty URL: **http://intervention_system.test**
   - Make sure **Auto Virtual Hosts** is enabled in Laragon settings (it is by default)

---

## Database Structure

The system uses a single consolidated migration (`2025_01_01_000000_create_all_tables.php`) plus two additive migrations:

| Migration | Description |
|-----------|-------------|
| `create_all_tables` | Core schema — users, teachers, subjects, exams, results, notes |
| `add_grading_method_to_exams_table` | Adds `grading_method` column (base_50 / base_20 / base_0) |
| `create_analytics_cache_table` | Adds `analytics_cache` for computed analytics storage |

---

## Seeded Data

Running `php artisan migrate --seed` will populate:

- Department: **School of Information Technology**
- Course: **Bachelor of Science in Information Technology**
- Subject: **IT101 — Programming 1** (1st Year, Major)
- Teacher: **Juan dela Cruz**
- School Year: **2025–2026**, 2nd Semester (active)
- Section: **BSIT 1-A**
- Exam: Prelim
- Student: **Maria Santos** (2024-0001) — 70%, Fail

---

## User Roles

| Role      | Access |
|-----------|--------|
| **Admin** | Full access — manage users, teachers, subjects, departments, school years, interventions, analytics |
| **Assistant** | Upload exam results (PDF), view subjects, manage intervention data for assigned scope |

---

## Project Structure

```
app/
├── Http/
│   └── Controllers/
│       ├── Admin/         # Admin-side controllers
│       └── Assistant/     # Assistant-side controllers
├── Models/                # Eloquent models
├── Services/              # Business logic / analytics services
database/
├── migrations/
├── seeders/
resources/
├── views/                 # Blade templates
routes/
├── web.php                # All application routes
├── auth.php               # Breeze auth routes
```

---

## License

This project was developed as part of an academic capstone at **Holy Cross of Davao College**. All rights reserved.

---

## Credits

- **Laravel** — [laravel.com](https://laravel.com) — The PHP framework powering the backend
- **Laravel Breeze** — Authentication scaffolding
- **Tailwind CSS** — [tailwindcss.com](https://tailwindcss.com) — Utility-first CSS framework
- **barryvdh/laravel-dompdf** — PDF generation for reports
- **smalot/pdfparser** — PDF parsing for item analysis uploads
- **Laragon** — [laragon.org](https://laragon.org) — Local development environment for Windows
- **phpMyAdmin** — Database management (bundled with Laragon Full)

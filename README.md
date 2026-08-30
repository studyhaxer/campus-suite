# Campus Suite (MC-SMS)

**Multi-Campus School Management System** — a web-based platform that lets a school organization manage multiple campuses (branches) from one place: students, staff, attendance, fee collection, and payroll — with a consolidated, cross-campus view for the organization's Manager/Owner.

---

## 📋 Overview

Campus Suite is built for school organizations that operate several physical campuses under one umbrella. Each campus runs semi-independently — its own students, staff, attendance, fees, and payroll — while the organization's Manager gets a single dashboard to view any campus individually or all campuses consolidated, with drill-down into details.

**Core idea:** one shared application and database, with every campus-scoped record tagged by a `campus_id`, so data stays strictly isolated per campus except for Manager-level cross-campus reporting.

---

## ✨ Key Features

- **Multi-Campus Management** — create, configure, and manage unlimited campuses under one organization account
- **User & Role Management** — Manager, Campus Admin, Accountant, and Teacher roles, each scoped to their authorized campus(es); accounts are admin-provisioned, no public self-registration
- **Student Management** — admissions and enrollment, class/section assignment, Excel import/export with a downloadable template
- **Staff / HR Management** — employee records with login accounts, designation/department/salary, Excel import/export with a downloadable template
- **Attendance** — daily student & staff attendance, roster-style bulk marking (Present/Absent)
- **Fee Management** — fixed monthly fee per class, auto-generated monthly invoices, payment collection (supports partial payments), overdue tracking
- **Payroll** — flat base salary per month, manual bonus/deduction adjustments before finalizing, PDF payslip download
- **Cross-Campus Reporting** — consolidated Manager dashboard (org-wide KPIs + per-campus registry table), role-scoped single-campus dashboard for Campus Admin/Accountant/Teacher

> Out of scope for v1.0: parent/student self-service portal, email/in-app notifications, attendance-based payroll deductions, fee concessions/scholarships, activity/audit logging, campus comparison charts, SMS/WhatsApp notifications, biometric attendance devices, transport/hostel modules, native mobile apps, email verification (accounts are pre-verified on creation). Planned for a later phase where noted.

---

## 🛠 Tech Stack

| Layer | Technology |
|---|---|
| Backend Framework | Laravel |
| Frontend | Blade + Livewire 3 + Alpine.js |
| Styling | Tailwind CSS |
| Database | PostgreSQL |
| Auth | Laravel Breeze (Livewire stack) |
| Roles & Permissions | spatie/laravel-permission |
| PDF Generation | barryvdh/laravel-dompdf (payslips) |
| Excel Import/Export | maatwebsite/excel (Students, Staff, Classes) |

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.3+ and Composer (via [Laravel Herd](https://herd.laravel.com) on Windows/macOS, or manually)
- PostgreSQL 14+
- Node.js LTS + npm

### Installation

```bash
# Clone the repo
git clone https://github.com/YOUR_USERNAME/campus-suite.git
cd campus-suite

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Copy environment file and generate app key
cp .env.example .env
php artisan key:generate
```

### Configure the database

Edit `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=campus_suite
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password
```

Create the database in pgAdmin (or via `createdb campus_suite`), then run:

```bash
php artisan migrate --seed
```

### Create the first account

There's no public sign-up — the first Manager account (and its organization) is created via an interactive command:

```bash
php artisan app:create-manager
```

It prompts for an organization name, your name, email, and password, and creates the first Manager login. From there, log in and create your first campus from the Campuses page — everything else (Students, Staff, Fees, Payroll) is created from within the app by the Manager or Campus Admin.

### Run the app

```bash
npm run dev        # compiles Tailwind/CSS assets, run in one terminal
php artisan serve  # starts the app at http://127.0.0.1:8000, run in another
```

---

## 📁 Project Structure Notes

- All campus-scoped Eloquent models use a shared `BelongsToCampus` trait + global scope, enforced at the query layer — not just the UI — so a Campus Admin or Accountant can never see another campus's data.
- Role-based access control is enforced via Laravel Policies (backed by `spatie/laravel-permission` roles) on every Livewire component action, not just hidden in the UI.
- Campus Admins are explicitly prevented from creating or editing another Campus Admin or Manager account — enforced in the policy layer, not just the form.

---

## 🗺 Development Roadmap

- [x] Phase 1 — Foundation (auth, roles, campus scoping, base layout)
- [x] Phase 2 — Campus Management
- [x] Phase 3 — Student Management (+ Classes & Sections, Excel import/export)
- [x] Phase 4 — Staff Management (+ Excel import/export)
- [x] Phase 5 — Attendance (student & staff)
- [x] Phase 6 — Fee Management
- [x] Phase 7 — Payroll (+ PDF payslips)
- [x] Phase 8 — Reporting & Cross-Campus Dashboard, sidebar navigation
- [ ] Automated test coverage (currently only default Breeze auth tests)
- [ ] v2 candidates: email verification/notifications, activity/audit logging, fee concessions, campus comparison charts, PDF fee receipts

---

## 🔒 Security Notes

- All campus data isolation is enforced server-side via `campus_id` scoping — never relies on UI-level filtering alone.
- Passwords are hashed using bcrypt (Laravel default).
- Role-based access control is enforced in the policy layer on every Livewire component action.
- Public self-registration is disabled — all accounts are created by a Manager or Campus Admin from within the app, or via `php artisan app:create-manager` for the first account. See "Getting Started" below.
- Email verification is not enforced in v1 (no mail transport configured yet); accounts are marked verified at creation time.

---

## 📄 License

Proprietary — internal project. Not licensed for public redistribution.

---

## 📖 Reference

Built against the *Multi-Campus School Management System (MC-SMS) — Software Requirements Specification v1.0*.
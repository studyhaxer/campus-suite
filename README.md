# Campus Suite (MC-SMS)

**Multi-Campus School Management System** — a web-based platform that lets a school organization manage multiple campuses (branches) from one place: students, staff, attendance, fee collection, and payroll — with a consolidated, cross-campus view for the organization's Manager/Owner.

---

## 📋 Overview

Campus Suite is built for school organizations that operate several physical campuses under one umbrella. Each campus runs semi-independently — its own students, staff, attendance, fees, and payroll — while the organization's Manager gets a single dashboard to view any campus individually or all campuses consolidated, with drill-down into details.

**Core idea:** one shared application and database, with every campus-scoped record tagged by a `campus_id`, so data stays strictly isolated per campus except for Manager-level cross-campus reporting.

---

## ✨ Key Features

- **Multi-Campus Management** — create, configure, and manage unlimited campuses under one organization account
- **User & Role Management** — Manager, Campus Admin, Accountant, and Teacher roles, each scoped to their authorized campus(es)
- **Student Management** — admissions, enrollment, transfers between campuses, bulk CSV import
- **Staff / HR Management** — employee records, pay grades, inter-campus transfers with full history
- **Attendance** — daily student & staff attendance, bulk marking, audit-logged corrections, attendance-based payroll deductions
- **Fee Management** — fee structures, auto-generated invoices, payments, receipts, concessions/scholarships, overdue tracking
- **Payroll** — salary structures, monthly payroll runs with attendance-based adjustments, payslip generation (PDF)
- **Cross-Campus Reporting** — consolidated Manager dashboard, campus comparison charts, drill-down, PDF/Excel export
- **Notifications** — in-app and email alerts for due fees, low attendance, and payroll events

> Out of scope for v1.0: parent/student self-service portal, SMS/WhatsApp notifications, biometric attendance devices, transport/hostel modules, native mobile apps. (Planned for a later phase.)

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
| Audit Logging | spatie/laravel-activitylog |
| PDF Generation | barryvdh/laravel-dompdf |
| Excel Export | maatwebsite/excel |
| Charts | Chart.js |
| Background Jobs | Laravel Queues + Task Scheduler |

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
php artisan migrate
```

### Run the app

```bash
npm run dev        # compiles Tailwind/CSS assets, run in one terminal
php artisan serve  # starts the app at http://127.0.0.1:8000, run in another
```

---

## 📁 Project Structure Notes

- All campus-scoped Eloquent models use a shared `BelongsToCampus` trait + global scope, enforced at the query layer — not just the UI — so a Campus Admin or Accountant can never see another campus's data.
- Role-based access control is enforced via `spatie/laravel-permission` on every route/controller action, not just hidden in the UI.
- All financial actions (fee payments, payroll approvals) are recorded via `spatie/laravel-activitylog` with user, timestamp, and before/after values.

---

## 🗺 Development Roadmap

- [x] Phase 1 — Foundation (auth, roles, campus scoping, base layout)
- [ ] Phase 2 — Campus Management
- [ ] Phase 3 — User & Role Management
- [ ] Phase 4 — Student Management
- [ ] Phase 5 — Staff / HR Management
- [ ] Phase 6 — Attendance
- [ ] Phase 7 — Fee Management
- [ ] Phase 8 — Payroll
- [ ] Phase 9 — Reporting & Cross-Campus Dashboard
- [ ] Phase 10 — Notifications
- [ ] Phase 11 — Polish & Non-Functional Requirements (responsiveness, accessibility, performance)

---

## 🔒 Security Notes

- All campus data isolation is enforced server-side via `campus_id` scoping — never relies on UI-level filtering alone.
- Passwords are hashed using bcrypt (Laravel default).
- All financial and payroll actions are audit-logged.
- Role-based access control is enforced on every API/controller endpoint.

---

## 📄 License

Proprietary — internal project. Not licensed for public redistribution.

---

## 📖 Reference

Built against the *Multi-Campus School Management System (MC-SMS) — Software Requirements Specification v1.0*.
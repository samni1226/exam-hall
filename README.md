# Exam Hall Allocation System

A complete college exam hall allocation system built with **HTML, CSS, Bootstrap, JavaScript, PHP (PDO) and MySQL**.

It automatically allocates registered students to exam halls based on hall capacity, prevents duplicate allocations, and produces a printable/exportable hall-wise seating report.

---

## 1. Folder Structure

```
exam_hall_allocation/
│
├── config/
│   └── db.php                 # PDO database connection
│
├── includes/
│   ├── functions.php          # session, flash messages, validation helpers
│   ├── admin_header.php       # admin sidebar + page head
│   └── admin_footer.php       # admin page footer/scripts
│
├── css/
│   └── style.css              # all custom styling
│
├── js/
│   └── script.js              # client-side validation, delete confirm, live filter
│
├── sql/
│   └── database.sql           # full DB schema + sample data (import this!)
│
├── admin/
│   ├── dashboard.php          # stats + quick links
│   ├── students.php           # list/add/edit/delete students, search/filter
│   ├── student_delete.php
│   ├── exams.php              # list/add/edit/delete exams + register students for an exam
│   ├── exam_delete.php
│   ├── halls.php              # list/add/edit/delete halls
│   ├── hall_delete.php
│   ├── allocate.php           # THE ALLOCATION ALGORITHM (generate/regenerate)
│   ├── report.php             # searchable/filterable/printable allocation report
│   └── report_export.php      # CSV export of the report
│
├── student/
│   └── dashboard.php          # student's exam date/time, hall no, seat no
│
├── index.php                  # routes to the correct login
├── login.php                  # admin login form
├── login_process.php          # admin login handler
├── logout.php
├── student_login.php          # student login form (by Register Number)
├── student_login_process.php
├── student_logout.php
│
└── README.md                  # this file
```

---

## 2. Database Design

Database name: **`exam_hall_allocation`**

| Table | Purpose |
|---|---|
| `admins` | Admin login accounts (bcrypt-hashed passwords) |
| `students` | Student ID, Register No, Name, Department, Year, Section, password |
| `exams` | Exam ID, Subject Code, Subject Name, Date, Start/End Time |
| `halls` | Hall ID, Hall Number, Building, Floor, Capacity |
| `exam_registrations` | Which students are appearing for which exam (`UNIQUE(exam_id, student_id)`) |
| `allocations` | Final hall/seat allocation (`UNIQUE(exam_id, student_id)` — a student can never get two seats for the same exam) |

All foreign keys use `ON DELETE CASCADE`, so deleting a student/exam/hall automatically cleans up related registration/allocation rows. All queries in the PHP code use **prepared statements** (PDO) to prevent SQL injection.

---

## 3. How the Allocation Algorithm Works (Simple Explanation)

Location: `admin/allocate.php`

Think of it like filling water bottles from a tap, one bottle (hall) at a time:

1. **Get the list of students** registered for the selected exam, sorted by Register Number (so results are predictable).
2. **Get the list of halls**, sorted by hall number, each with its seat capacity.
3. **Add up total hall capacity.** If it's less than the number of students, **stop immediately** and show an error — nothing is allocated.
4. Otherwise, walk through the student list **in order**:
   - Fill Hall 1 seat 1, seat 2, seat 3... until Hall 1 is full.
   - Move to Hall 2, fill it the same way.
   - Continue until every student has a seat.
5. Every seat gets a **seat number** like `H101-01`, `H101-02`, formatted as `<HallNumber>-<SeatCount>`.
6. Before saving, any **previous allocation for that exam is deleted** — this is what lets the admin click **"Regenerate Allocation"** safely.
7. The insert happens inside a **database transaction**, and the `allocations` table has a `UNIQUE(exam_id, student_id)` constraint — so it is *structurally impossible* for a student to be allocated twice for the same exam, even if something goes wrong.

---

## 4. How to Run This Project on XAMPP (Step by Step)

1. **Install XAMPP** (if you don't have it) from https://www.apachefriends.org and open the **XAMPP Control Panel**.
2. Start the **Apache** and **MySQL** modules (click "Start" next to each).
3. Copy the whole `exam_hall_allocation` folder into your XAMPP `htdocs` directory:
   - Windows: `C:\xampp\htdocs\exam_hall_allocation`
   - Mac: `/Applications/XAMPP/htdocs/exam_hall_allocation`
   - Linux: `/opt/lampp/htdocs/exam_hall_allocation`
4. Open your browser and go to **http://localhost/phpmyadmin**.
5. Click **"Import"** in the top menu, choose the file `sql/database.sql`, then click **"Go"**.
   - This creates the `exam_hall_allocation` database, all 6 tables, and inserts sample data automatically.
6. Open `config/db.php` and double-check the credentials match your MySQL setup:
   ```php
   $DB_HOST = 'localhost';
   $DB_NAME = 'exam_hall_allocation';
   $DB_USER = 'root';
   $DB_PASS = ''; // default XAMPP password is empty
   ```
7. Open your browser and visit: **http://localhost/exam_hall_allocation/**
8. You will land on the login page. Log in as:
   - **Admin:** username `admin`, password `admin123`
   - **Student:** register number `CS2021001`, password `CS2021001`
9. As admin, go to **Manage Halls** / **Manage Students** / **Manage Exams** to add your own real data, or use the sample data already provided.
10. Go to **Generate Allocation**, pick an exam (the sample exam `CS301` already has 10 students registered), and click **Generate Allocation**.
11. Go to **Allocation Report** to search, filter, print, or export the results as CSV.
12. Log out and log back in as a student (`CS2021001` / `CS2021001`) to see that student's hall number, seat number, and exam timing.

---

## 5. Sample Test Data (already included in `sql/database.sql`)

**Admin login**
| Username | Password |
|---|---|
| admin | admin123 |

**Students** (password = their own Register Number)
| Register No | Name | Department | Year | Section |
|---|---|---|---|---|
| CS2021001 | Aarav Kumar | CSE | 3 | A |
| CS2021002 | Bhavya Sharma | CSE | 3 | A |
| CS2021003 | Chetan Rao | CSE | 3 | B |
| CS2021004 | Divya Patel | CSE | 3 | B |
| CS2021005 | Esha Nair | CSE | 3 | A |
| IT2021001 | Farhan Ali | IT | 3 | A |
| IT2021002 | Gayatri Iyer | IT | 3 | A |
| EC2021001 | Harish Menon | ECE | 2 | A |
| EC2021002 | Isha Verma | ECE | 2 | A |
| ME2021001 | Jatin Chawla | MECH | 2 | A |

**Exams**
| Subject Code | Subject Name | Date | Time |
|---|---|---|---|
| CS301 | Database Management Systems | 2026-10-05 | 09:30 AM - 12:30 PM |
| CS302 | Computer Networks | 2026-10-07 | 09:30 AM - 12:30 PM |
| IT301 | Operating Systems | 2026-10-05 | 09:30 AM - 12:30 PM |

**Halls**
| Hall No | Building | Floor | Capacity |
|---|---|---|---|
| H101 | Main Block | Ground | 4 |
| H102 | Main Block | Ground | 4 |
| H201 | Science Block | First | 3 |

Total capacity = 11 seats, and all 10 sample students are pre-registered for exam `CS301` — so you can generate an allocation immediately and see students spread across H101 → H102 → H201.

---

## 6. Security Notes

- Passwords are stored with `password_hash()` / verified with `password_verify()` (bcrypt) — never in plain text.
- All SQL queries use PDO **prepared statements** with bound parameters.
- Admin and student areas are protected by **PHP sessions** (`require_admin()` / `require_student()` guard every protected page).
- `session_regenerate_id(true)` is called on login to prevent session fixation.
- All dynamic output is passed through `htmlspecialchars()` (the `h()` helper) to prevent XSS.
- Change the default `admin123` password before using this in a real environment.

---

## 7. Notes / Possible Extensions

- This is a teaching-friendly, procedural PHP project — no framework required, easy to read top to bottom.
- To register students for an exam, use the **"Register Students"** button on the **Manage Exams** page (register all students, or just one department at a time).
- You can extend the allocation algorithm to shuffle/randomize students, mix departments per hall (to reduce cheating), or balance halls evenly instead of filling sequentially — the core logic lives entirely in `admin/allocate.php`.

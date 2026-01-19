# CN5006 - Web & Mobile Applications Development

## Assignment: CWRK CN5006 - Part A & Part B

Διαδικτυακή εφαρμογή συστήματος πανεπιστημίου με:

- Δημόσια αρχική σελίδα πανεπιστημίου
- Σύστημα εγγραφής και σύνδεσης χρηστών
- Role-Based Access Control (RBAC) με ρόλους: Student / Teacher
- Dashboard φοιτητή και καθηγητή
- Διαχείριση μαθημάτων, εργασιών και βαθμολογιών

## Technologies

- **HTML5 / CSS3** - Δομή της σελίδας & styling
- **JavaScript** - Dynamic UI
- **PHP** - Backend
- **MariaDB/MySQL** - Database
- **Bootstrap 5** - CSS Framework
- **Swiper.js** - Image Slider/Carousel
- **Leaflet.js** - Interactive Map
- **Prettier** - Code formatting

## Project Structure

```
prototype/
├── assets/
│   ├── css/
│   │   └── app.css
│   ├── images/
│   │   ├── archuni1.jpg
│   │   ├── archuni2.jpg
│   │   ├── archuni3.jpg
│   │   └── archuni4.jpg
│   └── js/
│       └── app.js
├── auth/
│   ├── login.php
│   ├── logout.php
│   └── register.php
├── config/
│   └── database.php
├── dashboard/
│   ├── index.php
│   ├── courses/
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── view.php
│   │   ├── edit.php
│   │   └── enroll.php
│   ├── assignments/
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── view.php
│   │   ├── edit.php
│   │   └── submit.php
│   └── grades/
│       ├── index.php
│       └── grade.php
├── includes/
│   ├── auth.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   ├── dashboard_header.php
│   ├── dashboard_footer.php
│   └── rbac.php
├── sql/
│   ├── archimedes_1_export.sql
│   └── init.sql
├── uploads/
└── index.php

screenshots/
├── homepage.png
├── loggedin_role_student.png
├── loggedin_role_teacher.png
├── login.png
└── registration.png
```

## Installation

### Requirements

- PHP 7.4+
- MariaDB/MySQL
- Web Server (Apache/Nginx)

### Database Setup

1. Δημιουργία χρήστη βάσης:

```sql
CREATE USER 'archimedes'@'localhost' IDENTIFIED BY 'archimedes123';
GRANT ALL PRIVILEGES ON archimedes_1.* TO 'archimedes'@'localhost';
FLUSH PRIVILEGES;
```

2. Εκτέλεση του schema:

```bash
mysql -u archimedes -p < prototype/sql/init.sql
```

### Registration Codes

| Role    | Code     |
| ------- | -------- |
| Student | STUD2025 |
| Teacher | PROF2025 |

### Test Credentials

| Email                       | Password | Role    |
| --------------------------- | -------- | ------- |
| student_1@archimedes.edu.gr | 123456   | Student |
| student_2@archimedes.edu.gr | 123456   | Student |
| teacher_1@archimedes.edu.gr | 123456   | Teacher |
| teacher_2@archimedes.edu.gr | 123456   | Teacher |

## Notes

Οι εικόνες `archuni1.jpg`, `archuni2.jpg`, `archuni3.jpg`, `archuni4.jpg` είναι AI-generated με το Nano Banana και απεικονίζουν φανταστικούς χώρους του πανεπιστημίου.

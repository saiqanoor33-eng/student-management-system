# Student Management System — Task 07

A database-driven CRUD web application built with PHP, MySQL, HTML, and CSS.

## Features
- Create student records
- Read and display records from MySQL
- Update existing records
- Delete records with confirmation
- Required-field, email-format, and length validation
- Unique email constraint to prevent duplicate records
- Prepared statements to reduce SQL injection risk
- HTML escaping to reduce cross-site scripting risk
- Basic database error handling and logging

## Requirements
- XAMPP (Apache and MySQL)
- PHP with PDO MySQL enabled (included in standard XAMPP)

## Run locally
1. Copy the `student-management-system` folder into `C:\\xampp\\htdocs\\`.
2. Open XAMPP Control Panel and start **Apache** and **MySQL**.
3. Open `http://localhost/phpmyadmin`.
4. Select **Import**, choose `database.sql`, and click **Import** / **Go**.
5. Open `http://localhost/student-management-system/`.

The default `db.php` configuration uses MySQL username `root` and a blank password, which is the usual local XAMPP default. If you set a MySQL password, update `db.php`.

## Database design
Database: `student_management`

Table: `students`
- `id`: auto-increment primary key
- `full_name`: required student name
- `email`: required, unique email
- `course`: required course
- `created_at`: timestamp created automatically

## Manual test checklist
1. Create: add a student with a valid name, unique email, and course.
2. Read: confirm the new student appears in the records table.
3. Update: click Edit, change the course, save, and verify the change.
4. Delete: delete a test record and verify it disappears.
5. Validation: submit empty fields; browser should require values.
6. Invalid email: try an invalid email format; it should be rejected.
7. Duplicate data: submit an email already in the database; duplicate should be rejected.
8. Long input: try text beyond the allowed limit; browser/database validation should prevent it.
9. Error handling: stop MySQL and reload; a helpful connection error should appear (restart MySQL afterward).

## Suggested GitHub repository description
A PHP and MySQL Student Management System demonstrating CRUD operations, input validation, and database error handling.

## Internship submission note
Completed a database-driven Student Management System using PHP and MySQL. Implemented database/table creation, insertion, retrieval, updating, deletion, input validation, duplicate-email handling, prepared statements, and database error handling. Tested the main CRUD operations locally using XAMPP.

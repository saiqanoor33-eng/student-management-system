<?php
require_once 'db.php';

$errors = [];
$success = '';
$editStudent = null;

function cleanInput(string $value): string {
    return trim($value);
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = cleanInput($_POST['full_name'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $course = cleanInput($_POST['course'] ?? '');

    if ($action === 'save') {
        if ($name === '' || mb_strlen($name) > 100) {
            $errors[] = 'Name is required and must be 100 characters or fewer.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $errors[] = 'Please enter a valid email address (maximum 150 characters).';
        }
        if ($course === '' || mb_strlen($course) > 100) {
            $errors[] = 'Course is required and must be 100 characters or fewer.';
        }

        if (!$errors) {
            try {
                $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
                if ($id) {
                    $stmt = $pdo->prepare(
                        'UPDATE students SET full_name = ?, email = ?, course = ? WHERE id = ?'
                    );
                    $stmt->execute([$name, $email, $course, $id]);
                    $success = 'Student record updated successfully.';
                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO students (full_name, email, course) VALUES (?, ?, ?)'
                    );
                    $stmt->execute([$name, $email, $course]);
                    $success = 'Student added successfully.';
                }
            } catch (PDOException $ex) {
                error_log($ex->getMessage());
                if ($ex->getCode() === '23000') {
                    $errors[] = 'This email already exists. Please use a different email.';
                } else {
                    $errors[] = 'Unable to save the record due to a database error.';
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id) {
            try {
                $stmt = $pdo->prepare('DELETE FROM students WHERE id = ?');
                $stmt->execute([$id]);
                $success = $stmt->rowCount() ? 'Student deleted successfully.' : 'Record was not found.';
            } catch (PDOException $ex) {
                error_log($ex->getMessage());
                $errors[] = 'Unable to delete the record due to a database error.';
            }
        } else {
            $errors[] = 'Invalid student ID.';
        }
    }
}

if (isset($_GET['edit'])) {
    $id = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
        $stmt->execute([$id]);
        $editStudent = $stmt->fetch() ?: null;
    }
}

try {
    $students = $pdo->query('SELECT * FROM students ORDER BY id DESC')->fetchAll();
} catch (PDOException $ex) {
    error_log($ex->getMessage());
    $students = [];
    $errors[] = 'Unable to retrieve student records right now.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Management System</title>
    <style>
        *{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif;background:#f3f6fb;color:#172033}
        header{background:#243b80;color:white;padding:28px 18px;text-align:center}
        header h1{margin:0 0 8px}header p{margin:0;color:#e2e8ff}
        main{max-width:1050px;margin:28px auto;padding:0 16px}
        .panel{background:white;padding:22px;border-radius:14px;box-shadow:0 5px 20px #1b2b4a12;margin-bottom:22px}
        h2{margin-top:0}.form-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
        label{display:block;font-weight:bold;font-size:14px;margin-bottom:7px}
        input{width:100%;padding:11px;border:1px solid #cbd5e1;border-radius:8px;font-size:15px}
        button,.button{border:0;border-radius:8px;padding:10px 14px;font-weight:bold;cursor:pointer;text-decoration:none;display:inline-block}
        .primary{background:#2948a5;color:white}.secondary{background:#e8edf9;color:#243b80}
        .danger{background:#fee2e2;color:#991b1b}.actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
        .notice{padding:12px 14px;border-radius:8px;margin-bottom:16px}.success{background:#dcfce7;color:#166534}
        .error{background:#fee2e2;color:#991b1b}.table-wrap{overflow-x:auto}
        table{width:100%;border-collapse:collapse;min-width:650px}th,td{text-align:left;padding:13px;border-bottom:1px solid #e5eaf2}
        th{background:#f8fafc;font-size:13px;text-transform:uppercase;color:#475569}
        .muted{color:#64748b;font-size:14px}.inline-form{display:inline}
        @media(max-width:700px){.form-grid{grid-template-columns:1fr}header h1{font-size:24px}}
    </style>
</head>
<body>
<header>
    <h1>Student Management System</h1>
    <p>A database-driven CRUD web application using PHP and MySQL</p>
</header>
<main>
    <?php if ($success): ?><div class="notice success"><?= e($success) ?></div><?php endif; ?>
    <?php foreach ($errors as $error): ?><div class="notice error"><?= e($error) ?></div><?php endforeach; ?>

    <section class="panel">
        <h2><?= $editStudent ? 'Update Student' : 'Add New Student' ?></h2>
        <form method="post" action="index.php" autocomplete="off">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= e((string)($editStudent['id'] ?? '')) ?>">
            <div class="form-grid">
                <div>
                    <label for="full_name">Full name</label>
                    <input id="full_name" name="full_name" maxlength="100" required
                           value="<?= e($editStudent['full_name'] ?? ($_POST['full_name'] ?? '')) ?>"
                           placeholder="e.g. Ayesha Khan">
                </div>
                <div>
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" maxlength="150" required
                           value="<?= e($editStudent['email'] ?? ($_POST['email'] ?? '')) ?>"
                           placeholder="student@example.com">
                </div>
                <div>
                    <label for="course">Course</label>
                    <input id="course" name="course" maxlength="100" required
                           value="<?= e($editStudent['course'] ?? ($_POST['course'] ?? '')) ?>"
                           placeholder="e.g. BS Computer Science">
                </div>
            </div>
            <div class="actions" style="margin-top:16px">
                <button class="primary" type="submit"><?= $editStudent ? 'Save Changes' : 'Add Student' ?></button>
                <?php if ($editStudent): ?><a class="button secondary" href="index.php">Cancel edit</a><?php endif; ?>
            </div>
        </form>
    </section>

    <section class="panel">
        <h2>Student Records <span class="muted">(<?= count($students) ?> total)</span></h2>
        <?php if (!$students): ?>
            <p class="muted">No records yet. Add your first student using the form above.</p>
        <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Course</th><th>Created</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= e((string)$student['id']) ?></td>
                    <td><?= e($student['full_name']) ?></td>
                    <td><?= e($student['email']) ?></td>
                    <td><?= e($student['course']) ?></td>
                    <td><?= e($student['created_at']) ?></td>
                    <td><div class="actions">
                        <a class="button secondary" href="?edit=<?= (int)$student['id'] ?>">Edit</a>
                        <form class="inline-form" method="post" action="index.php"
                              onsubmit="return confirm('Delete this student record?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$student['id'] ?>">
                            <button class="danger" type="submit">Delete</button>
                        </form>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </section>
    <p class="muted">Built for database CRUD practice. Records are stored in MySQL, not just in the browser.</p>
</main>
</body>
</html>

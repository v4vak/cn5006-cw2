<?php
// professor/courses.php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/rbac.php';

require_professor();

$professor_id = (int)$_SESSION['user_id'];

$errors = [];
$success = '';

// CREATE COURSE
if (is_post()) {
    $title = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));

    if ($title === '') {
        $errors[] = 'Title is required';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO courses (title, description, professor_id)
            VALUES (:title, :description, :professor_id)
        ");

        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'professor_id' => $professor_id
        ]);

        $success = 'Course created successfully!';
    }
}

// GET MY COURSES
$stmt = $pdo->prepare("
    SELECT * FROM courses
    WHERE professor_id = :pid
    ORDER BY created_at DESC
");
$stmt->execute(['pid' => $professor_id]);
$courses = $stmt->fetchAll();

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Courses</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
</head>

<body>
<div class="container">

<h1>Professor – Course Management</h1>

<a class="btn" href="../dashboard.php">Back to Dashboard</a>

<?php if ($errors): ?>
<div class="alert alert-error">
<ul>
<?php foreach ($errors as $e): ?>
<li><?= e($e) ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<div class="card">
<h3>Create New Course</h3>

<form method="post">
<label>
Title
<input type="text" name="title">
</label>

<label>
Description
<textarea name="description"></textarea>
</label>

<button class="btn" type="submit">Create Course</button>
</form>
</div>

<h2>My Courses</h2>

<table class="table table-striped table-bordered">
<tr>
<th>Title</th>
<th>Description</th>
<th>Actions</th>
</tr>

<?php foreach ($courses as $c): ?>
<tr>
<td><?= e($c['title']) ?></td>
<td><?= e($c['description']) ?></td>
<td style="display:flex; gap:8px; flex-wrap:wrap;">
  <a class="btn btn-secondary" href="assignments.php?course_id=<?= (int)$c['id'] ?>">Assignments</a>
  <a class="btn" href="course_edit.php?id=<?= (int)$c['id'] ?>">Edit</a>
</td>
</tr>
<?php endforeach; ?>

</table>

</div>
</body>
</html>

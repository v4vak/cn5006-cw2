<?php
// professor/course_edit.php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/rbac.php';

require_professor();

$professor_id = (int)$_SESSION['user_id'];
$course_id = (int)($_GET['id'] ?? 0);

if ($course_id <= 0) {
    redirect('courses.php');
}

/* Verify course belongs to this professor */
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = :cid AND professor_id = :pid LIMIT 1");
$stmt->execute(['cid' => $course_id, 'pid' => $professor_id]);
$course = $stmt->fetch();

if (!$course) {
    redirect('../forbidden.php');
}

$errors = [];
$success = '';

$title = (string)$course['title'];
$description = (string)($course['description'] ?? '');

if (is_post()) {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'update') {
        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));

        if ($title === '') {
            $errors[] = 'Title is required.';
        }

        if (!$errors) {
            $stmt = $pdo->prepare("
                UPDATE courses
                SET title = :title, description = :description
                WHERE id = :cid AND professor_id = :pid
            ");
            $stmt->execute([
                'title' => $title,
                'description' => $description,
                'cid' => $course_id,
                'pid' => $professor_id
            ]);

            $success = 'Course updated successfully!';

            // refresh course
            $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = :cid AND professor_id = :pid LIMIT 1");
            $stmt->execute(['cid' => $course_id, 'pid' => $professor_id]);
            $course = $stmt->fetch();
        }
    }

    if ($action === 'delete') {
        // Deleting course cascades:
        // assignments -> submissions -> grades, enrollments (because FK ON DELETE CASCADE)
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = :cid AND professor_id = :pid");
        $stmt->execute(['cid' => $course_id, 'pid' => $professor_id]);

        redirect('courses.php');
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Course</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <div class="container my-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
      <div>
        <h1 class="mb-1">Edit Course</h1>
        <p class="text-muted mb-0">Course ID: <?= (int)$course_id ?></p>
      </div>
      <div class="d-flex gap-2">
        <a class="btn btn-secondary" href="courses.php">Back to Courses</a>
        <a class="btn btn-primary" href="assignments.php?course_id=<?= (int)$course_id ?>">Assignments</a>
      </div>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-danger mt-3">
        <ul class="mb-0">
          <?php foreach ($errors as $er): ?>
            <li><?= e($er) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success mt-3"><?= e($success) ?></div>
    <?php endif; ?>

    <div class="card mt-3 p-3">
      <h3 class="h5 mb-3">Update Details</h3>

      <form method="post">
        <input type="hidden" name="action" value="update">

        <div class="mb-3">
          <label class="form-label">Title</label>
          <input class="form-control" type="text" name="title" value="<?= e($title) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea class="form-control" name="description" rows="4"><?= e($description) ?></textarea>
        </div>

        <button class="btn btn-success" type="submit">Save Changes</button>
      </form>
    </div>

    <div class="card mt-3 p-3 border border-danger">
      <h3 class="h5 text-danger mb-2">Danger Zone</h3>
      <p class="text-muted mb-3">
        Deleting this course will also delete its assignments, submissions, grades and enrollments (cascade).
      </p>

      <form method="post" onsubmit="return confirm('Are you sure you want to permanently delete this course?');">
        <input type="hidden" name="action" value="delete">
        <button class="btn btn-danger" type="submit">Delete Course</button>
      </form>
    </div>
  </div>
</body>
</html>

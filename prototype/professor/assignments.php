<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/rbac.php';

require_professor();

$professor_id = (int)$_SESSION['user_id'];
$course_id = (int)($_GET['course_id'] ?? 0);

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

if (is_post()) {
    $title = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $due_date = trim((string)($_POST['due_date'] ?? ''));

    if ($title === '') $errors[] = 'Assignment title is required.';

    // allow empty due_date, but if provided it should look like YYYY-MM-DDTHH:MM from input type datetime-local
    $due_for_db = null;
    if ($due_date !== '') {
        $due_for_db = str_replace('T', ' ', $due_date) . ':00';
    }

    if (!$errors) {
        $stmt = $pdo->prepare("
            INSERT INTO assignments (course_id, title, description, due_date)
            VALUES (:course_id, :title, :description, :due_date)
        ");
        $stmt->execute([
            'course_id' => $course_id,
            'title' => $title,
            'description' => $description,
            'due_date' => $due_for_db
        ]);
        $success = 'Assignment created successfully!';
    }
}

/* Fetch assignments */
$stmt = $pdo->prepare("SELECT * FROM assignments WHERE course_id = :cid ORDER BY created_at DESC");
$stmt->execute(['cid' => $course_id]);
$assignments = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Assignments</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <div class="container">
    <h1>Professor – Assignments</h1>
    <p class="muted"><strong>Course:</strong> <?= e($course['title']) ?></p>

    <a class="btn btn-secondary" href="courses.php">Back to Courses</a>
    <a class="btn" href="submissions.php?course_id=<?= $course_id ?>">View Submissions</a>

    <?php if ($errors): ?>
      <div class="alert alert-error">
        <ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <div class="card">
      <h3>Create New Assignment</h3>
      <form method="post" class="form">
        <label>
          Title
          <input type="text" name="title" required>
        </label>

        <label>
          Description
          <textarea name="description" rows="4"></textarea>
        </label>

        <label>
          Due Date (optional)
          <input type="datetime-local" name="due_date">
        </label>

        <button class="btn" type="submit">Create Assignment</button>
      </form>
    </div>

    <h2>Assignment List</h2>
    <div class="card">
      <?php if (!$assignments): ?>
        <p class="muted">No assignments yet.</p>
      <?php else: ?>
        <table class="table table-striped table-bordered">
          <tr>
            <th>Title</th>
            <th>Due</th>
            <th>Created</th>
          </tr>
          <?php foreach ($assignments as $a): ?>
            <tr>
              <td><?= e($a['title']) ?></td>
              <td><?= $a['due_date'] ? e((string)$a['due_date']) : '<span class="muted">—</span>' ?></td>
              <td><?= e((string)$a['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>

<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/rbac.php';

require_student();

$student_id = (int)$_SESSION['user_id'];

$errors = [];
$success = '';

if (is_post()) {
    $course_id = (int)($_POST['course_id'] ?? 0);
    if ($course_id <= 0) {
        $errors[] = 'Invalid course selected.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO enrollments (course_id, student_id) VALUES (:cid, :sid)");
            $stmt->execute(['cid' => $course_id, 'sid' => $student_id]);
            $success = 'Enrolled successfully!';
        } catch (PDOException $e) {
            // likely duplicate (unique constraint)
            $errors[] = 'You are already enrolled in this course.';
        }
    }
}

/* Courses I’m enrolled in */
$stmt = $pdo->prepare("
  SELECT c.*
  FROM courses c
  INNER JOIN enrollments e ON e.course_id = c.id
  WHERE e.student_id = :sid
  ORDER BY c.created_at DESC
");
$stmt->execute(['sid' => $student_id]);
$my_courses = $stmt->fetchAll();

/* All available courses to enroll */
$stmt = $pdo->query("
  SELECT c.id, c.title
  FROM courses c
  ORDER BY c.created_at DESC
");
$all_courses = $stmt->fetchAll();
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
    <h1>Student – My Courses</h1>
    <a class="btn btn-secondary" href="../dashboard.php">Back to Dashboard</a>
    <a class="btn" href="assignments.php">View Assignments</a>
    <a class="btn" href="grades.php">View Grades</a>

    <?php if ($errors): ?>
      <div class="alert alert-error"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <div class="card">
      <h3>Enroll in a Course</h3>
      <form method="post" class="form">
        <label>
          Select Course
          <select name="course_id" required>
            <option value="">Choose…</option>
            <?php foreach ($all_courses as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= e($c['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <button class="btn" type="submit">Enroll</button>
      </form>
    </div>

    <h2>Enrolled Courses</h2>
    <div class="card">
      <?php if (!$my_courses): ?>
        <p class="muted">You are not enrolled in any courses yet.</p>
      <?php else: ?>
        <table class="table table-striped table-bordered">
          <tr>
            <th>Title</th>
            <th>Description</th>
          </tr>
          <?php foreach ($my_courses as $c): ?>
            <tr>
              <td><?= e($c['title']) ?></td>
              <td><?= e((string)$c['description']) ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>

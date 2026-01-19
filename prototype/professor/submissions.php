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

/* verify course belongs to professor */
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = :cid AND professor_id = :pid LIMIT 1");
$stmt->execute(['cid' => $course_id, 'pid' => $professor_id]);
$course = $stmt->fetch();

if (!$course) {
    redirect('../forbidden.php');
}

/* fetch submissions for assignments of this course */
$stmt = $pdo->prepare("
  SELECT
    s.id AS submission_id,
    s.submitted_at,
    s.original_filename,
    s.file_path,
    s.comment,
    u.username AS student_name,
    u.email AS student_email,
    a.title AS assignment_title,
    g.grade,
    g.feedback,
    g.graded_at
  FROM submissions s
  INNER JOIN assignments a ON a.id = s.assignment_id
  INNER JOIN users u ON u.id = s.student_id
  LEFT JOIN grades g ON g.submission_id = s.id
  WHERE a.course_id = :cid
  ORDER BY s.submitted_at DESC
");
$stmt->execute(['cid' => $course_id]);
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Submissions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <div class="container">
    <h1>Professor – Submissions</h1>
    <p class="muted"><strong>Course:</strong> <?= e($course['title']) ?></p>

    <a class="btn btn-secondary" href="courses.php">Back to Courses</a>
    <a class="btn" href="assignments.php?course_id=<?= $course_id ?>">Back to Assignments</a>

    <div class="card">
      <?php if (!$rows): ?>
        <p class="muted">No submissions yet.</p>
      <?php else: ?>
        <table class="table table-striped table-bordered">
          <tr>
            <th>Assignment</th>
            <th>Student</th>
            <th>File</th>
            <th>Submitted</th>
            <th>Grade</th>
            <th>Action</th>
          </tr>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= e($r['assignment_title']) ?></td>
              <td><?= e($r['student_name']) ?> <span class="muted">(<?= e($r['student_email']) ?>)</span></td>
              <td><?= e($r['original_filename']) ?></td>
              <td><?= e((string)$r['submitted_at']) ?></td>
              <td><?= $r['grade'] !== null ? e((string)$r['grade']) : '<span class="muted">—</span>' ?></td>
              <td>
                <a class="btn" href="grade.php?submission_id=<?= (int)$r['submission_id'] ?>">Grade</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>

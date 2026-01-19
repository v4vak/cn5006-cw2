<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/rbac.php';

require_professor();

$professor_id = (int)$_SESSION['user_id'];
$submission_id = (int)($_GET['submission_id'] ?? 0);

if ($submission_id <= 0) {
    redirect('courses.php');
}

/* Verify this submission belongs to professor's course */
$stmt = $pdo->prepare("
  SELECT
    s.id AS submission_id,
    s.original_filename,
    s.file_path,
    s.submitted_at,
    s.comment,
    a.title AS assignment_title,
    c.id AS course_id,
    c.title AS course_title,
    u.username AS student_name,
    u.email AS student_email
  FROM submissions s
  INNER JOIN assignments a ON a.id = s.assignment_id
  INNER JOIN courses c ON c.id = a.course_id
  INNER JOIN users u ON u.id = s.student_id
  WHERE s.id = :sid AND c.professor_id = :pid
  LIMIT 1
");
$stmt->execute(['sid' => $submission_id, 'pid' => $professor_id]);
$info = $stmt->fetch();

if (!$info) {
    redirect('../forbidden.php');
}

/* existing grade */
$stmt = $pdo->prepare("SELECT * FROM grades WHERE submission_id = :sid LIMIT 1");
$stmt->execute(['sid' => $submission_id]);
$existing = $stmt->fetch();

$errors = [];
$success = '';

$grade_val = $existing ? (string)$existing['grade'] : '';
$feedback = $existing ? (string)$existing['feedback'] : '';

if (is_post()) {
    $grade_val = trim((string)($_POST['grade'] ?? ''));
    $feedback = trim((string)($_POST['feedback'] ?? ''));

    if ($grade_val === '' || !is_numeric($grade_val)) {
        $errors[] = 'Grade must be a number.';
    } else {
        $g = (float)$grade_val;
        if ($g < 0 || $g > 100) $errors[] = 'Grade must be between 0 and 100.';
    }

    if (!$errors) {
        if ($existing) {
            $stmt = $pdo->prepare("
              UPDATE grades
              SET grade = :grade, feedback = :feedback, professor_id = :pid, graded_at = CURRENT_TIMESTAMP
              WHERE submission_id = :sid
            ");
        } else {
            $stmt = $pdo->prepare("
              INSERT INTO grades (submission_id, professor_id, grade, feedback)
              VALUES (:sid, :pid, :grade, :feedback)
            ");
        }

        $stmt->execute([
            'sid' => $submission_id,
            'pid' => $professor_id,
            'grade' => $grade_val,
            'feedback' => $feedback
        ]);

        $success = 'Grade saved successfully!';
        // reload existing
        $stmt = $pdo->prepare("SELECT * FROM grades WHERE submission_id = :sid LIMIT 1");
        $stmt->execute(['sid' => $submission_id]);
        $existing = $stmt->fetch();
    }
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Grade</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <div class="container">
    <h1>Professor – Grade Submission</h1>

    <a class="btn btn-secondary" href="submissions.php?course_id=<?= (int)$info['course_id'] ?>">Back to Submissions</a>

    <?php if ($errors): ?>
      <div class="alert alert-error"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <div class="card">
      <p><strong>Course:</strong> <?= e($info['course_title']) ?></p>
      <p><strong>Assignment:</strong> <?= e($info['assignment_title']) ?></p>
      <p><strong>Student:</strong> <?= e($info['student_name']) ?> (<?= e($info['student_email']) ?>)</p>
      <p><strong>File:</strong> <?= e($info['original_filename']) ?></p>
      <p><strong>Submitted:</strong> <?= e((string)$info['submitted_at']) ?></p>
      <?php if (!empty($info['comment'])): ?>
        <p><strong>Student comment:</strong> <?= e((string)$info['comment']) ?></p>
      <?php endif; ?>
    </div>

    <div class="card">
      <h3>Enter Grade</h3>
      <form method="post" class="form">
        <label>
          Grade (0–100)
          <input type="number" name="grade" min="0" max="100" step="0.01" value="<?= e($grade_val) ?>" required>
        </label>

        <label>
          Feedback (optional)
          <textarea name="feedback" rows="4"><?= e($feedback) ?></textarea>
        </label>

        <button class="btn" type="submit">Save Grade</button>
      </form>
    </div>
  </div>
</body>
</html>

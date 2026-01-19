<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/rbac.php';

require_student();

$student_id = (int)$_SESSION['user_id'];
$assignment_id = (int)($_GET['assignment_id'] ?? 0);

if ($assignment_id <= 0) {
    redirect('assignments.php');
}

/* Verify student has access to this assignment (enrolled in that course) */
$stmt = $pdo->prepare("
  SELECT a.*, c.title AS course_title
  FROM assignments a
  INNER JOIN courses c ON c.id = a.course_id
  INNER JOIN enrollments e ON e.course_id = c.id AND e.student_id = :sid
  WHERE a.id = :aid
  LIMIT 1
");
$stmt->execute(['sid' => $student_id, 'aid' => $assignment_id]);
$assignment = $stmt->fetch();

if (!$assignment) {
    redirect('../forbidden.php');
}

$errors = [];
$success = '';

if (is_post()) {
    $comment = trim((string)($_POST['comment'] ?? ''));

    if (!isset($_FILES['submission_file']) || $_FILES['submission_file']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Please choose a file to upload.';
    } else {
        $file = $_FILES['submission_file'];

        // Basic file limits (safe for coursework)
        $maxBytes = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxBytes) {
            $errors[] = 'File is too large (max 5MB).';
        }

        $original = (string)$file['name'];
        $tmp = (string)$file['tmp_name'];
        $mime = (string)($file['type'] ?? '');
        $size = (int)$file['size'];

        // allow common formats
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $allowed = ['pdf','doc','docx','zip','rar','txt','png','jpg','jpeg'];
        if (!in_array($ext, $allowed, true)) {
            $errors[] = 'File type not allowed. Allowed: ' . implode(', ', $allowed);
        }

        if (!$errors) {
            $safeName = 'A' . $assignment_id . '_S' . $student_id . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $relativePath = 'uploads/submissions/' . $safeName;
            $target = __DIR__ . '/../' . $relativePath;

            if (!move_uploaded_file($tmp, $target)) {
                $errors[] = 'Upload failed. Please try again.';
            } else {
                try {
                    $stmt = $pdo->prepare("
                      INSERT INTO submissions (assignment_id, student_id, file_path, original_filename, mime_type, file_size, comment)
                      VALUES (:aid, :sid, :path, :orig, :mime, :size, :comment)
                    ");
                    $stmt->execute([
                        'aid' => $assignment_id,
                        'sid' => $student_id,
                        'path' => $relativePath,
                        'orig' => $original,
                        'mime' => $mime,
                        'size' => $size,
                        'comment' => $comment
                    ]);
                    $success = 'Submission uploaded successfully!';
                } catch (PDOException $e) {
                    // unique constraint: only one submission per assignment per student
                    $errors[] = 'You already submitted for this assignment.';
                }
            }
        }
    }
}

/* Fetch existing submission (if any) */
$stmt = $pdo->prepare("
  SELECT s.*
  FROM submissions s
  WHERE s.assignment_id = :aid AND s.student_id = :sid
  LIMIT 1
");
$stmt->execute(['aid' => $assignment_id, 'sid' => $student_id]);
$existing = $stmt->fetch();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Submit Assignment</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <div class="container">
    <h1>Submit Assignment</h1>
    <p class="muted"><strong>Course:</strong> <?= e($assignment['course_title']) ?> | <strong>Assignment:</strong> <?= e($assignment['title']) ?></p>

    <a class="btn btn-secondary" href="assignments.php">Back to Assignments</a>

    <?php if ($errors): ?>
      <div class="alert alert-error"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if ($existing): ?>
      <div class="card">
        <h3>Your current submission</h3>
        <p><strong>File:</strong> <?= e($existing['original_filename']) ?></p>
        <p><strong>Submitted at:</strong> <?= e((string)$existing['submitted_at']) ?></p>
        <p class="muted">Only one submission per assignment is allowed in this demo.</p>
      </div>
    <?php else: ?>
      <div class="card">
        <h3>Upload your file</h3>
        <form method="post" enctype="multipart/form-data" class="form">
          <label>
            File
            <input type="file" name="submission_file" required>
          </label>

          <label>
            Comment (optional)
            <textarea name="comment" rows="3"></textarea>
          </label>

          <button class="btn" type="submit">Submit</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>

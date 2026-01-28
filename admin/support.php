<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

$db   = new Database();
$conn = $db->getConnection();

$action = $_GET['action'] ?? 'list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Mark all questions as viewed when loading list
if ($action === 'list') {
    $conn->query('UPDATE support_questions SET admin_viewed = 1 WHERE admin_viewed = 0');
}

// Load categories for optional FAQ creation
$categories = $conn->query('SELECT id, name FROM categories WHERE is_archived = 0 ORDER BY name')->fetchAll();

// Handle answer submission
if ($action === 'answer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $questionId   = (int)($_POST['id'] ?? 0);
    $answerText   = trim($_POST['answer'] ?? '');
    $addToFaq     = isset($_POST['add_to_faq']);
    $faqCategory  = (int)($_POST['faq_category_id'] ?? 0);

    if ($questionId > 0 && $answerText !== '') {
        // Get question record
        $stmt = $conn->prepare('SELECT * FROM support_questions WHERE id = ?');
        $stmt->execute([$questionId]);
        $question = $stmt->fetch();

        if ($question) {
            $conn->beginTransaction();
            try {
                $faqId = null;
                if ($addToFaq && $faqCategory > 0) {
                    // Insert into FAQs and store link
                    $faqStmt = $conn->prepare('INSERT INTO faqs (question, answer, category_id) VALUES (?, ?, ?)');
                    $faqStmt->execute([$question['question'], $answerText, $faqCategory]);
                    $faqId = (int)$conn->lastInsertId();
                }

                // Update support question as answered
                $updateStmt = $conn->prepare('
                    UPDATE support_questions 
                    SET answered = 1, admin_viewed = 1, answered_by = ?, faq_id = COALESCE(?, faq_id)
                    WHERE id = ?
                ');
                $updateStmt->execute([$_SESSION['user_id'], $faqId, $questionId]);

                $conn->commit();

                // Send email to user (simple mail())
                $to      = $question['user_email'];
                $subject = 'Answer to your support question - ' . SITE_NAME;
                $body    = "Hello,\n\n"
                    . "You asked:\n"
                    . $question['question'] . "\n\n"
                    . "Our answer:\n"
                    . $answerText . "\n\n"
                    . "Thank you,\n"
                    . SITE_NAME . ' Support';

                @mail($to, $subject, $body);

                redirect('support.php', 'Answer sent and question updated successfully');
            } catch (Exception $e) {
                $conn->rollBack();
                redirect('support.php', 'Error saving answer. Please try again.');
            }
        }
    }
}

// Load question to answer form
if ($action === 'answer' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare('SELECT * FROM support_questions WHERE id = ?');
    $stmt->execute([$id]);
    $question = $stmt->fetch();
}

// Lists
$newQuestions = $conn->query('SELECT * FROM support_questions WHERE admin_viewed = 0 AND answered = 0 ORDER BY id DESC')->fetchAll();
$pending      = $conn->query('SELECT * FROM support_questions WHERE admin_viewed = 1 AND answered = 0 ORDER BY id DESC')->fetchAll();
$answered     = $conn->query('SELECT * FROM support_questions WHERE answered = 1 ORDER BY id DESC LIMIT 50')->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Questions - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .page-title {
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="page-header">
        <h3 class="page-title mb-0">
            <i class="fas fa-headset me-2 text-primary"></i>Support Questions
        </h3>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <?php if ($message = getFlashMessage()): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($action === 'answer' && !empty($question)): ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <strong>Answer Question</strong>
                    </div>
                    <div class="card-body">
                        <p><strong>From:</strong> <?php echo htmlspecialchars($question['user_email']); ?></p>
                        <p><strong>Question:</strong></p>
                        <p class="border rounded p-2 bg-light"><?php echo nl2br(htmlspecialchars($question['question'])); ?></p>

                        <form method="POST" action="support.php?action=answer">
                            <input type="hidden" name="id" value="<?php echo (int)$question['id']; ?>">
                            <div class="mb-3">
                                <label class="form-label">Your Answer</label>
                                <textarea name="answer" class="form-control" rows="6" required></textarea>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="1" id="add_to_faq" name="add_to_faq">
                                <label class="form-check-label" for="add_to_faq">
                                    Also add this question and answer to FAQs
                                </label>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">FAQ Category (if adding to FAQs)</label>
                                <select name="faq_category_id" class="form-select">
                                    <option value="">Select category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a href="support.php" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Send Answer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <strong>New Questions</strong>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($newQuestions) === 0): ?>
                            <p class="text-muted p-3 mb-0">No new questions.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($newQuestions as $q): ?>
                                    <a href="support.php?action=answer&id=<?php echo $q['id']; ?>" class="list-group-item list-group-item-action">
                                        <div class="small text-muted"><?php echo htmlspecialchars($q['user_email']); ?></div>
                                        <div><?php echo htmlspecialchars(getExcerpt($q['question'], 80)); ?></div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <strong>Pending (Viewed, Not Answered)</strong>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($pending) === 0): ?>
                            <p class="text-muted p-3 mb-0">No pending questions.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($pending as $q): ?>
                                    <a href="support.php?action=answer&id=<?php echo $q['id']; ?>" class="list-group-item list-group-item-action">
                                        <div class="small text-muted"><?php echo htmlspecialchars($q['user_email']); ?></div>
                                        <div><?php echo htmlspecialchars(getExcerpt($q['question'], 80)); ?></div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <strong>Recently Answered Questions</strong>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($answered) === 0): ?>
                            <p class="text-muted p-3 mb-0">No answered questions yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>User Email</th>
                                            <th>Question</th>
                                            <th>Linked FAQ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($answered as $q): ?>
                                            <tr>
                                                <td><?php echo (int)$q['id']; ?></td>
                                                <td><?php echo htmlspecialchars($q['user_email']); ?></td>
                                                <td><?php echo htmlspecialchars(getExcerpt($q['question'], 80)); ?></td>
                                                <td>
                                                    <?php if (!empty($q['faq_id'])): ?>
                                                        <a href="../faq.php?id=<?php echo $q['faq_id']; ?>" target="_blank">
                                                            View FAQ #<?php echo (int)$q['faq_id']; ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Not added</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



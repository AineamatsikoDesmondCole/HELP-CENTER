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

// Handle create / update / archive / restore
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question    = trim($_POST['question'] ?? '');
    $answer      = trim($_POST['answer'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $faq_id      = (int)($_POST['id'] ?? 0);

    if ($action === 'add') {
        if ($question && $answer && $category_id > 0) {
            $stmt = $conn->prepare('INSERT INTO faqs (question, answer, category_id) VALUES (?, ?, ?)');
            $stmt->execute([$question, $answer, $category_id]);
            redirect('faqs.php', 'FAQ added successfully');
        }
    } elseif ($action === 'edit' && $faq_id > 0) {
        if ($question && $answer && $category_id > 0) {
            $stmt = $conn->prepare('UPDATE faqs SET question = ?, answer = ?, category_id = ? WHERE id = ?');
            $stmt->execute([$question, $answer, $category_id, $faq_id]);
            redirect('faqs.php', 'FAQ updated successfully');
        }
    }
}

if ($action === 'archive' && $id > 0) {
    $stmt = $conn->prepare('UPDATE faqs SET is_archived = 1, archived_by = ? WHERE id = ?');
    $stmt->execute([$_SESSION['user_id'], $id]);
    redirect('faqs.php', 'FAQ archived');
}

if ($action === 'restore' && $id > 0) {
    $stmt = $conn->prepare('UPDATE faqs SET is_archived = 0, archived_by = NULL WHERE id = ?');
    $stmt->execute([$id]);
    redirect('faqs.php', 'FAQ restored');
}

// Load data for views
$categories = $conn->query('SELECT id, name FROM categories WHERE is_archived = 0 ORDER BY name')->fetchAll();

if ($action === 'edit' && $id > 0) {
    $stmt   = $conn->prepare('SELECT * FROM faqs WHERE id = ?');
    $stmt->execute([$id]);
    $faq = $stmt->fetch();
}

// Helpful FAQs for analysis
$helpfulFaqs = $conn->query("
    SELECT f.*, c.name AS category_name
    FROM faqs f
    LEFT JOIN categories c ON f.category_id = c.id
    WHERE f.is_archived = 0
    ORDER BY (f.upvotes - f.downvotes) DESC
    LIMIT 10
")->fetchAll();

// All FAQs list
$allFaqs = $conn->query("
    SELECT f.*, c.name AS category_name
    FROM faqs f
    LEFT JOIN categories c ON f.category_id = c.id
    ORDER BY f.is_archived ASC, f.id DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage FAQs - <?php echo SITE_NAME; ?></title>
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
        .faq-form-card .form-label {
            font-weight: 500;
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="page-header">
        <h3 class="page-title mb-0">
            <i class="fas fa-question-circle me-2 text-primary"></i>FAQ Management
        </h3>
        <div>
            <a href="index.php" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
            <a href="faqs.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add FAQ
            </a>
        </div>
    </div>

    <?php if ($message = getFlashMessage()): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($action === 'add' || $action === 'edit'): ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="faq-form-card card mb-4">
                    <div class="card-header bg-primary text-white">
                        <strong><?php echo $action === 'add' ? 'Add FAQ' : 'Edit FAQ'; ?></strong>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if (!empty($faq['id'])): ?>
                                <input type="hidden" name="id" value="<?php echo (int)$faq['id']; ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label">Question</label>
                                <textarea
                                    name="question"
                                    class="form-control"
                                    rows="2"
                                    required
                                ><?php echo htmlspecialchars($faq['question'] ?? ''); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Answer</label>
                                <textarea
                                    name="answer"
                                    class="form-control"
                                    rows="6"
                                    required
                                ><?php echo htmlspecialchars($faq['answer'] ?? ''); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select...</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo !empty($faq['category_id']) && $faq['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a href="faqs.php" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card analysis-card mb-4">
                    <div class="card-header">
                        <strong>Most Helpful FAQs (Analysis)</strong>
                    </div>
                    <div class="card-body">
                        <?php if (count($helpfulFaqs) === 0): ?>
                            <p class="text-muted mb-0">No FAQs yet.</p>
                        <?php else: ?>
                            <ul class="list-group small">
                                <?php foreach ($helpfulFaqs as $row): 
                                    $percent = calculateHelpfulness($row['upvotes'], $row['downvotes']);
                                ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($row['question']); ?></div>
                                            <div class="text-muted">
                                                <?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?>
                                            </div>
                                        </div>
                                        <span class="badge bg-success rounded-pill"><?php echo $percent; ?>%</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>All FAQs</strong>
                <span class="text-muted small">Green rows are active, gray rows are archived</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Question</th>
                                <th>Category</th>
                                <th>Votes (👍 / 👎)</th>
                                <th>Helpfulness</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allFaqs as $row): 
                                $percent = calculateHelpfulness($row['upvotes'], $row['downvotes']);
                            ?>
                                <tr class="<?php echo $row['is_archived'] ? 'table-secondary' : ''; ?>">
                                    <td><?php echo (int)$row['id']; ?></td>
                                    <td><?php echo htmlspecialchars(getExcerpt($row['question'], 80)); ?></td>
                                    <td><?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td><?php echo (int)$row['upvotes']; ?> / <?php echo (int)$row['downvotes']; ?></td>
                                    <td><?php echo $percent; ?>%</td>
                                    <td>
                                        <?php if ($row['is_archived']): ?>
                                            <span class="badge bg-secondary">Archived</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="faqs.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            Edit
                                        </a>
                                        <?php if ($row['is_archived']): ?>
                                            <a href="faqs.php?action=restore&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-success">
                                                Restore
                                            </a>
                                        <?php else: ?>
                                            <a href="faqs.php?action=archive&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-warning" onclick="return confirm('Archive this FAQ?');">
                                                Archive
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



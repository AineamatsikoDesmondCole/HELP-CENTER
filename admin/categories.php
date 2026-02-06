<<<<<<< HEAD
<?php
session_start();
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../helpers/functions.php';

requireAdmin();

$db   = new Database();
$conn = $db->getConnection();

$action = $_GET['action'] ?? 'list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $cat_id      = (int)($_POST['id'] ?? 0);

    if ($action === 'add') {
        if ($name !== '') {
            $stmt = $conn->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
            $stmt->execute([$name, $description]);
            redirect('categories.php', 'Category added successfully');
        }
    } elseif ($action === 'edit' && $cat_id > 0) {
        if ($name !== '') {
            $stmt = $conn->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?');
            $stmt->execute([$name, $description, $cat_id]);
            redirect('categories.php', 'Category updated successfully');
        }
    }
}

if ($action === 'archive' && $id > 0) {
    $stmt = $conn->prepare('UPDATE categories SET is_archived = 1 WHERE id = ?');
    $stmt->execute([$id]);
    redirect('categories.php', 'Category archived');
}

if ($action === 'restore' && $id > 0) {
    $stmt = $conn->prepare('UPDATE categories SET is_archived = 0 WHERE id = ?');
    $stmt->execute([$id]);
    redirect('categories.php', 'Category restored');
}

if ($action === 'edit' && $id > 0) {
    $stmt = $conn->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    $category = $stmt->fetch();
}

$allCategories = $conn->query('SELECT * FROM categories ORDER BY is_archived ASC, name ASC')->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - <?php echo SITE_NAME; ?></title>
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
        .category-form-card .form-label {
            font-weight: 500;
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="page-header">
        <h3 class="page-title mb-0">
            <i class="fas fa-folder me-2 text-primary"></i>FAQ Categories
        </h3>
        <div>
            <a href="index.php" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
            <a href="categories.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Category
            </a>
        </div>
    </div>

    <?php if ($message = getFlashMessage()): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($action === 'add' || $action === 'edit'): ?>
        <div class="category-form-card card mb-4">
            <div class="card-header bg-primary text-white">
                <strong><?php echo $action === 'add' ? 'Add Category' : 'Edit Category'; ?></strong>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php if (!empty($category['id'])): ?>
                        <input type="hidden" name="id" value="<?php echo (int)$category['id']; ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            required
                            value="<?php echo htmlspecialchars($category['name'] ?? ''); ?>"
                        >
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (optional)</label>
                        <textarea
                            name="description"
                            class="form-control"
                            rows="3"
                        ><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="categories.php" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>All Categories</strong>
            <span class="text-muted small">Gray rows are archived and hidden from the public help center.</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allCategories as $cat): ?>
                            <tr class="<?php echo $cat['is_archived'] ? 'table-secondary' : ''; ?>">
                                <td><?php echo (int)$cat['id']; ?></td>
                                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                <td><?php echo htmlspecialchars(getExcerpt($cat['description'] ?? '', 80)); ?></td>
                                <td>
                                    <?php if ($cat['is_archived']): ?>
                                        <span class="badge bg-secondary">Archived</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="categories.php?action=edit&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>
                                    <?php if ($cat['is_archived']): ?>
                                        <a href="categories.php?action=restore&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-success">
                                            Restore
                                        </a>
                                    <?php else: ?>
                                        <a href="categories.php?action=archive&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-warning" onclick="return confirm('Archive this category?');">
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
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


=======
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $cat_id      = (int)($_POST['id'] ?? 0);

    if ($action === 'add') {
        if ($name !== '') {
            $stmt = $conn->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
            $stmt->execute([$name, $description]);
            redirect('categories.php', 'Category added successfully');
        }
    } elseif ($action === 'edit' && $cat_id > 0) {
        if ($name !== '') {
            $stmt = $conn->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?');
            $stmt->execute([$name, $description, $cat_id]);
            redirect('categories.php', 'Category updated successfully');
        }
    }
}

if ($action === 'archive' && $id > 0) {
    $stmt = $conn->prepare('UPDATE categories SET is_archived = 1 WHERE id = ?');
    $stmt->execute([$id]);
    redirect('categories.php', 'Category archived');
}

if ($action === 'restore' && $id > 0) {
    $stmt = $conn->prepare('UPDATE categories SET is_archived = 0 WHERE id = ?');
    $stmt->execute([$id]);
    redirect('categories.php', 'Category restored');
}

if ($action === 'edit' && $id > 0) {
    $stmt = $conn->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    $category = $stmt->fetch();
}

$allCategories = $conn->query('SELECT * FROM categories ORDER BY is_archived ASC, name ASC')->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - <?php echo SITE_NAME; ?></title>
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
        .category-form-card .form-label {
            font-weight: 500;
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="page-header">
        <h3 class="page-title mb-0">
            <i class="fas fa-folder me-2 text-primary"></i>FAQ Categories
        </h3>
        <div>
            <a href="index.php" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
            <a href="categories.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Category
            </a>
        </div>
    </div>

    <?php if ($message = getFlashMessage()): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($action === 'add' || $action === 'edit'): ?>
        <div class="category-form-card card mb-4">
            <div class="card-header bg-primary text-white">
                <strong><?php echo $action === 'add' ? 'Add Category' : 'Edit Category'; ?></strong>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php if (!empty($category['id'])): ?>
                        <input type="hidden" name="id" value="<?php echo (int)$category['id']; ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            required
                            value="<?php echo htmlspecialchars($category['name'] ?? ''); ?>"
                        >
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (optional)</label>
                        <textarea
                            name="description"
                            class="form-control"
                            rows="3"
                        ><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="categories.php" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>All Categories</strong>
            <span class="text-muted small">Gray rows are archived and hidden from the public help center.</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allCategories as $cat): ?>
                            <tr class="<?php echo $cat['is_archived'] ? 'table-secondary' : ''; ?>">
                                <td><?php echo (int)$cat['id']; ?></td>
                                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                <td><?php echo htmlspecialchars(getExcerpt($cat['description'] ?? '', 80)); ?></td>
                                <td>
                                    <?php if ($cat['is_archived']): ?>
                                        <span class="badge bg-secondary">Archived</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="categories.php?action=edit&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>
                                    <?php if ($cat['is_archived']): ?>
                                        <a href="categories.php?action=restore&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-success">
                                            Restore
                                        </a>
                                    <?php else: ?>
                                        <a href="categories.php?action=archive&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-warning" onclick="return confirm('Archive this category?');">
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
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


>>>>>>> 36257f9ac8a2c27b93a4b73606d4a36660c330d7

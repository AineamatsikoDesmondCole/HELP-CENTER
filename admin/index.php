<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Enforce admin authentication
requireAdmin();

// Get stats for dashboard
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get FAQ count
    $faqCount = $conn->query("SELECT COUNT(*) as count FROM faqs WHERE is_archived = 0")->fetch()['count'];
    
    // Get pending support questions (not yet answered)
    $pendingCount = $conn->query("SELECT COUNT(*) as count FROM support_questions WHERE answered = 0")->fetch()['count'];

    // Get new/unseen questions for notification badge
    $newQuestions = $conn->query("SELECT COUNT(*) as count FROM support_questions WHERE admin_viewed = 0")->fetch()['count'];
    
    // Get categories count
    $categoryCount = $conn->query("SELECT COUNT(*) as count FROM categories WHERE is_archived = 0")->fetch()['count'];
    
} catch (Exception $e) {
    // Default values if error
    $faqCount = $pendingCount = $categoryCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            min-height: 100vh;
            color: white;
            width: 250px;
            position: fixed;
            left: 0;
            top: 0;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 1.5rem;
        }
        
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.9;
        }
        
        .card-primary { background: linear-gradient(45deg, #4e73df, #224abe); color: white; }
        .card-success { background: linear-gradient(45deg, #1cc88a, #13855c); color: white; }
        .card-warning { background: linear-gradient(45deg, #f6c23e, #dda20a); color: white; }
        .card-danger { background: linear-gradient(45deg, #e74a3b, #be2617); color: white; }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 15px 20px;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,.1);
            border-left: 4px solid #fff;
        }
        
        .sidebar-brand {
            padding: 1.5rem;
            font-size: 1.2rem;
            font-weight: 700;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        
        .user-info {
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        
        .welcome-text {
            font-size: 0.9rem;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-user-shield me-2"></i>
            <?php echo SITE_NAME; ?>
        </div>
        
        <div class="user-info text-center">
            <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
            <div class="welcome-text">Administrator</div>
        </div>
        
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link active" href="index.php">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="categories.php">
                    <i class="fas fa-folder me-2"></i> Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="faqs.php">
                    <i class="fas fa-question-circle me-2"></i> FAQs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="support.php">
                    <i class="fas fa-headset me-2"></i> Support Questions
                    <?php if (!empty($newQuestions)): ?>
                        <span class="badge bg-danger ms-2"><?php echo (int)$newQuestions; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Navbar -->
        <nav class="navbar">
            <div class="container-fluid">
                <h4 class="mb-0">Admin Dashboard</h4>
                <div class="d-flex align-items-center">
                    <span class="me-3 text-muted">
                        <i class="fas fa-calendar me-1"></i>
                        <?php echo date('F j, Y'); ?>
                    </span>
                </div>
            </div>
        </nav>

        <!-- Stats Cards -->
        <div class="row mt-4">
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card stat-card card-success">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-8">
                                <div class="h5 font-weight-bold mb-0"><?php echo $faqCount; ?></div>
                                <div class="text-uppercase mb-1">FAQs</div>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-question-circle stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card stat-card card-warning">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-8">
                                <div class="h5 font-weight-bold mb-0"><?php echo $pendingCount; ?></div>
                                <div class="text-uppercase mb-1">Pending Support</div>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-headset stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card stat-card card-danger">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-8">
                                <div class="h5 font-weight-bold mb-0"><?php echo $categoryCount; ?></div>
                                <div class="text-uppercase mb-1">Categories</div>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-folder stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="faqs.php?action=add" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> Add New FAQ
                            </a>
                            <a href="support.php" class="btn btn-warning">
                                <i class="fas fa-headset me-2"></i> View Support Questions
                            </a>
                            <a href="categories.php?action=add" class="btn btn-success">
                                <i class="fas fa-folder-plus me-2"></i> Add Category
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
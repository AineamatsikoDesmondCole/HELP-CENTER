<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { padding-top: 20px; }
        .navbar-brand { font-weight: bold; }
        .search-container { max-width: 600px; margin: 20px auto; }
    </style>
</head>
<body>
        <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <!-- Brand/Logo -->
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-question-circle me-2"></i>
                <?php echo SITE_NAME; ?>
            </a>
            
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navbar Content -->
            <div class="collapse navbar-collapse" id="navbarContent">
                <!-- Admin Login Button (Right Side) -->
                <div class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                        <!-- Admin is logged in -->
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-shield me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="admin/index.php">
                                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="admin/logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Admin Login Button -->
                        <a href="admin/login.php" class="btn btn-outline-light">
                            <i class="fas fa-sign-in-alt me-1"></i> Admin Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
        
           <!-- Search Bar -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            <!-- Search Form -->
            <div class="search-container position-relative">
                <form method="GET" action="index.php" class="d-flex">
                    <div class="input-group">
                        <input type="text" 
                               id="liveSearch"
                               name="search" 
                               class="form-control" 
                               placeholder="🔍 Search FAQs... (type 2+ characters)"
                               autocomplete="off"
                               aria-label="Search FAQs">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <!-- Live Results Dropdown -->
                <div id="searchResults" class="list-group position-absolute w-100 mt-1 shadow" 
                     style="display: none; z-index: 1000; max-height: 300px; overflow-y: auto;"></div>
            </div>
            
            <?php if (isset($_GET['search']) && strlen($_GET['search']) < 2): ?>
            <div class="text-danger small mt-1">
                Please enter at least 2 characters to search.
            </div>
            <?php endif; ?>
        </div>
    </div>
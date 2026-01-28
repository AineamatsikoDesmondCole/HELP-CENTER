<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $question = trim($_POST['question'] ?? '');
    
    // Validation
    if (empty($email) || empty($question)) {
        $error = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($question) < 10) {
        $error = 'Please provide more details in your question';
    } else {
        // Save to database
        try {
            $db = new Database();
            $stmt = $db->query(
                "INSERT INTO support_questions (user_email, question) VALUES (?, ?)",
                [$email, $question]
            );
            
            $success = 'Your question has been submitted! Our support team will contact you via email.';
            
            // Clear form
            $_POST = [];
            
        } catch (Exception $e) {
            $error = 'Sorry, there was an error submitting your question. Please try again.';
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-envelope me-2"></i>
                    Contact Support
                </h3>
            </div>
            
            <div class="card-body">
                <!-- Introduction -->
                <div class="mb-4">
                    <p class="lead">Can't find what you're looking for in our FAQs?</p>
                    <p class="text-muted">
                        Fill out the form below and our support team will get back to you via email.
                        We typically respond within 12 hours.
                    </p>
                </div>
                
                <!-- Success Message -->
                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <!-- Error Message -->
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <!-- Support Form -->
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1"></i> Email Address
                        </label>
                        <input type="email" 
                               class="form-control form-control-lg" 
                               id="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               placeholder="you@example.com"
                               required>
                        <div class="form-text">
                            We'll send our response to this address
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="question" class="form-label">
                            <i class="fas fa-question-circle me-1"></i> Your Question
                        </label>
                        <textarea class="form-control form-control-lg" 
                                  id="question" 
                                  name="question" 
                                  rows="5"
                                  placeholder="Please describe your issue or question in detail..."
                                  required><?php echo htmlspecialchars($_POST['question'] ?? ''); ?></textarea>
                        <div class="form-text">
                            Be as detailed as possible. Include error messages, steps to reproduce, etc.
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i> Submit Question
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-search me-2"></i> Back to Search FAQs
                        </a>
                    </div>
                </form>
                
                <!-- Support Information -->
                <div class="mt-5 pt-4 border-top">
                    <h5 class="mb-3">
                        <i class="fas fa-info-circle me-2"></i> What happens next?
                    </h5>
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-reply fa-2x text-primary mb-2"></i>
                                <h6>Email Response</h6>
                                <p class="small text-muted">We'll reply to your email with a solution</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-question fa-2x text-primary mb-2"></i>
                                <h6>FAQ Addition</h6>
                                <p class="small text-muted">Common questions get added to our FAQs</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-history fa-2x text-primary mb-2"></i>
                                <h6>12 Hour Response</h6>
                                <p class="small text-muted">Typical response time</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Before Contacting Tips -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-lightbulb me-2"></i> Before contacting support...
                </h5>
                <ul class="mb-0">
                    <li>Have you checked our <a href="index.php">FAQs</a>?</li>
                    <li>Did you try searching for your issue?</li>
                    <li>Include specific error messages if available</li>
                    <li>Provide steps to reproduce the issue</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
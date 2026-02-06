<?php
require_once 'config/config.php';
require_once 'helpers/functions.php';

// Load PHPMailer for admin notifications
require_once 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
            
            $lastInsertId = $db->getConnection()->lastInsertId();
            
            $success = 'Your question has been submitted! Our support team will contact you via email.';
            
            // Send email notification to admin
            sendAdminNotification($email, $question, $lastInsertId);
            
            // Clear form
            $_POST = [];
            
        } catch (Exception $e) {
            $error = 'Sorry, there was an error submitting your question. Please try again.';
            error_log("Support question error: " . $e->getMessage());
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

<?php
/**
 * Send email notification to admin about new support question
 */
function sendAdminNotification($userEmail, $question, $questionId) {
    try {
        $mail = new PHPMailer(true);
        
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->SMTPDebug = 0;
        
        // Sender
        $mail->setFrom(SMTP_USER, SITE_NAME . ' Support System');
        $mail->addReplyTo($userEmail, 'Support Question Author');
        
        // Recipient - Admin email
        $adminEmail = SMTP_USER;
        $mail->addAddress($adminEmail);
        
        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'New Support Question - ' . SITE_NAME;
        
        // Build the admin URL
        $adminUrl = SITE_URL . 'admin/support.php?action=answer&id=' . $questionId;
        $allQuestionsUrl = SITE_URL . 'admin/support.php';
        
        // HTML email body - SIMPLIFIED VERSION
        $htmlBody = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4a6bdf; color: white; padding: 15px; text-align: center; border-radius: 5px; margin-bottom: 20px; }
                .question-box { background: #f8f9fa; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px; margin: 20px 0; }
                .info { background: #e9f7fe; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; padding-top: 20px; border-top: 1px solid #e0e0e0; }
                .btn { background: #4a6bdf; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>New Support Question</h2>
                    <p>' . htmlspecialchars(SITE_NAME) . '</p>
                </div>
                
                <div class="info">
                    <strong>From:</strong> ' . htmlspecialchars($userEmail) . '<br>
                    <strong>Question ID:</strong> #' . $questionId . '<br>
                    <strong>Submitted:</strong> ' . date('F j, Y, g:i a') . '
                </div>
                
                <div class="question-box">
                    <h3>Question:</h3>
                    ' . nl2br(htmlspecialchars($question)) . '
                </div>
                
                <div style="text-align: center; margin: 25px 0;">
                    <a href="' . $adminUrl . '" class="btn">
                        Answer This Question
                    </a>
                    <p style="margin-top: 10px;">
                        <a href="' . $allQuestionsUrl . '" style="color: #4a6bdf;">
                            View All Questions
                        </a>
                    </p>
                </div>
                
                <div class="footer">
                    <p>This is an automated notification from ' . htmlspecialchars(SITE_NAME) . '</p>
                </div>
            </div>
        </body>
        </html>';
        
        $mail->Body = $htmlBody;
        
        // Plain text alternative
        $plainText = "NEW SUPPORT QUESTION\n" .
                    "====================\n\n" .
                    "From: " . $userEmail . "\n" .
                    "Question ID: #" . $questionId . "\n" .
                    "Time: " . date('F j, Y, g:i a') . "\n\n" .
                    "Question:\n" .
                    $question . "\n\n" .
                    "Answer this question:\n" .
                    $adminUrl . "\n\n" .
                    "View all questions:\n" .
                    $allQuestionsUrl;
        
        $mail->AltBody = $plainText;
        
        // Send email
        if ($mail->send()) {
            error_log("✅ Admin notification sent for question ID: " . $questionId);
            return true;
        } else {
            error_log("❌ Failed to send admin notification: " . $mail->ErrorInfo);
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Admin notification error: " . $e->getMessage());
        return false;
    }
}
?>
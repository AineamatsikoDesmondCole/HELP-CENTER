<?php
session_start();
require_once 'config/config.php';
require_once 'config/db.php';
require_once 'helpers/functions.php';
require_once 'models/CategoryModel.php';
require_once 'models/FAQModel.php';

// Initialize models
$categoryModel = new CategoryModel();
$faqModel = new FAQModel();

// Get category ID from URL
$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($categoryId <= 0) {
    header('Location: index.php');
    exit;
}

// Get category details
$category = $categoryModel->getCategory($categoryId);

if (!$category) {
    header('Location: index.php');
    exit;
}

// Get FAQs for this category
$faqs = $faqModel->getFAQsByCategory($categoryId);

// Get all categories for sidebar
$allCategories = $categoryModel->getAllCategories();
?>

<?php 
$pageTitle = $category['name'];
include 'includes/header.php'; 
?>

<style>
    .faq-item {
        transition: all 0.3s ease;
        border: 1px solid #dee2e6;
    }
    
    .faq-item:hover {
        border-color: #0d6efd;
    }
    
    .answer-hidden {
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: max-height 0.5s ease, opacity 0.5s ease;
    }
    
    .answer-visible {
        max-height: 1000px;
        opacity: 1;
        overflow: visible;
    }
    
    .read-more-btn {
        transition: all 0.3s;
    }
    
    .read-more-btn:hover {
        transform: translateY(-2px);
    }
    
    .rotate-icon {
        transition: transform 0.3s ease;
    }
    
    .rotate-icon.rotated {
        transform: rotate(180deg);
    }
</style>

<div class="row">
    <!-- Categories Sidebar -->
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-folder"></i> Categories
            </div>
            <div class="list-group list-group-flush">
                <?php while ($cat = $allCategories->fetch()): ?>
                <a href="category.php?id=<?php echo $cat['id']; ?>" 
                   class="list-group-item list-group-item-action <?php echo $cat['id'] == $categoryId ? 'active' : ''; ?>">
                    <i class="fas fa-folder me-1"></i>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
        
        <a href="index.php" class="btn btn-outline-secondary w-100">
            <i class="fas fa-home me-1"></i> Back to Home
        </a>
    </div>
    
    <!-- Main Content -->
    <div class="col-md-9">
        <!-- Category Header -->
        <div class="mb-4">
            <h1>
                <i class="fas fa-folder text-primary me-2"></i>
                <?php echo htmlspecialchars($category['name']); ?>
            </h1>
            <?php if (!empty($category['description'])): ?>
            <p class="text-muted"><?php echo htmlspecialchars($category['description']); ?></p>
            <?php endif; ?>
        </div>
        
        <!-- FAQs List -->
        <?php if ($faqs->rowCount() > 0): ?>
            <?php $counter = 0; while ($faq = $faqs->fetch()): $counter++; ?>
            <div class="card mb-3 faq-item" id="faq-<?php echo $faq['id']; ?>">
                <div class="card-body">
                    <!-- Question -->
                    <h5 class="card-title mb-2">
                        <i class="fas fa-question-circle text-primary me-2"></i>
                        <?php echo htmlspecialchars($faq['question']); ?>
                    </h5>
                    
                    <!-- Short Answer Preview -->
                    <div class="card-text mb-3 text-muted">
                        <?php echo truncateText($faq['answer'], 150); ?>
                    </div>
                    
                    <!-- Read More Button -->
                    <button class="btn btn-outline-primary read-more-btn mb-3" 
                            onclick="toggleAnswer(<?php echo $faq['id']; ?>)"
                            id="toggle-btn-<?php echo $faq['id']; ?>">
                        <i class="fas fa-chevron-down rotate-icon" id="icon-<?php echo $faq['id']; ?>"></i>
                        <span id="toggle-text-<?php echo $faq['id']; ?>">Read Full Answer</span>
                    </button>
                    
                    <!-- Full Answer (Hidden by default) -->
                    <div class="answer-hidden" id="answer-<?php echo $faq['id']; ?>">
                        <div class="border-top pt-3 mt-3">
                            <h6 class="mb-3"><i class="fas fa-align-left text-secondary me-2"></i>Full Answer:</h6>
                            <div class="card-text mb-4">
                                <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                            </div>
                            
                            <!-- Voting Buttons -->
                            <div class="border-top pt-3">
                                <p class="text-muted mb-2">
                                    <i class="fas fa-thumbs-up text-success me-1"></i>
                                    <i class="fas fa-thumbs-down text-danger me-1"></i>
                                    Was this answer helpful?
                                </p>
                                <div class="btn-group" role="group" id="vote-group-<?php echo $faq['id']; ?>">
                                    <button type="button" 
                                            class="btn btn-outline-success" 
                                            onclick="voteFAQ(<?php echo $faq['id']; ?>, 'up', this)">
                                        <i class="fas fa-thumbs-up"></i> Yes
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            onclick="voteFAQ(<?php echo $faq['id']; ?>, 'down', this)">
                                        <i class="fas fa-thumbs-down"></i> No
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <!-- Empty State -->
            <div class="alert alert-light border text-center py-5">
                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No questions in this category</h5>
                <p class="text-muted">Browse other categories for help.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Toggle FAQ answer visibility
function toggleAnswer(faqId) {
    const answerDiv = document.getElementById(`answer-${faqId}`);
    const toggleBtn = document.getElementById(`toggle-btn-${faqId}`);
    const toggleText = document.getElementById(`toggle-text-${faqId}`);
    const toggleIcon = document.getElementById(`icon-${faqId}`);
    
    if (answerDiv.classList.contains('answer-hidden')) {
        // Show answer
        answerDiv.classList.remove('answer-hidden');
        answerDiv.classList.add('answer-visible');
        toggleText.textContent = 'Show Less';
        toggleIcon.classList.add('rotated');
        toggleBtn.classList.remove('btn-outline-primary');
        toggleBtn.classList.add('btn-primary');
        
        // Smooth scroll to the expanded answer
        setTimeout(() => {
            answerDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 300);
    } else {
        // Hide answer
        answerDiv.classList.remove('answer-visible');
        answerDiv.classList.add('answer-hidden');
        toggleText.textContent = 'Read Full Answer';
        toggleIcon.classList.remove('rotated');
        toggleBtn.classList.remove('btn-primary');
        toggleBtn.classList.add('btn-outline-primary');
    }
}

// Voting via AJAX
async function voteFAQ(faqId, type, buttonEl) {
    const group = document.getElementById(`vote-group-${faqId}`);
    if (!group) return;

    group.querySelectorAll('button').forEach(btn => btn.disabled = true);

    try {
        const response = await fetch('<?php echo SITE_URL; ?>api/vote.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ faq_id: faqId, type })
        });

        const data = await response.json();
        if (!data.success || data.error) {
            throw new Error(data.error || 'Vote failed');
        }

        buttonEl.classList.remove('btn-outline-success', 'btn-outline-danger');
        buttonEl.classList.add(type === 'up' ? 'btn-success' : 'btn-danger');
        buttonEl.innerHTML = '<i class="fas fa-check"></i> Thank you!';
    } catch (e) {
        group.querySelectorAll('button').forEach(btn => btn.disabled = false);
        alert('Sorry, we could not record your vote. Please try again.');
    }
}

// Auto-expand if URL has hash (coming from search results)
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash;
    if (hash && hash.startsWith('#faq-')) {
        const faqId = hash.replace('#faq-', '');
        setTimeout(() => toggleAnswer(faqId), 500);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
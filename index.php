<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
?>

<?php include 'includes/header.php'; ?>

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
    
    .vote-btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
</style>

<div class="row">
    <!-- Categories Sidebar -->
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-folder"></i> Categories
            </div>
            <div class="list-group list-group-flush">
                <?php
                $db = new Database();
                $stmt = $db->query("SELECT * FROM categories WHERE is_archived = FALSE ORDER BY name");
                while ($category = $stmt->fetch()):
                ?>
                <a href="category.php?id=<?php echo $category['id']; ?>" 
                   class="list-group-item list-group-item-action">
                    <i class="fas fa-folder me-2"></i>
                    <?php echo htmlspecialchars($category['name']); ?>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- Contact Support -->
        <div class="card">
            <div class="card-body text-center">
                <h6 class="card-title">Need more help?</h6>
                <a href="support.php" class="btn btn-outline-primary mt-2 btn-sm">
                    <i class="fas fa-envelope me-1"></i> Contact Support
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-star text-warning me-2"></i>
                Most Helpful FAQs
            </h2>
            <div class="text-muted">
                <small>Click "Read Full Answer" to expand</small>
            </div>
        </div>
        
        <?php
        $stmt = $db->query("
            SELECT f.*, c.name as category_name 
            FROM faqs f 
            LEFT JOIN categories c ON f.category_id = c.id 
            WHERE f.is_archived = FALSE 
            ORDER BY (f.upvotes - f.downvotes) DESC 
            LIMIT 15
        ");
        
        if ($stmt->rowCount() > 0):
            while ($faq = $stmt->fetch()): 
        ?>
        <div class="card mb-3 faq-item" id="faq-<?php echo $faq['id']; ?>">
            <div class="card-body">
                <!-- Question -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title mb-0">
                        <?php echo htmlspecialchars($faq['question']); ?>
                    </h5>
                    <small class="text-muted">
                        <i class="fas fa-tag me-1"></i>
                        <?php echo htmlspecialchars($faq['category_name']); ?>
                    </small>
                </div>
                
                <!-- Short Answer Preview -->
                <div class="card-text mb-3 text-muted">
                    <?php echo truncateText($faq['answer'], 120); ?>
                </div>
                
                <!-- Read More Button -->
                <button class="btn btn-outline-primary btn-sm read-more-btn mb-3" 
                        onclick="toggleAnswer(<?php echo $faq['id']; ?>)"
                        id="toggle-btn-<?php echo $faq['id']; ?>">
                    <i class="fas fa-chevron-down rotate-icon" id="icon-<?php echo $faq['id']; ?>"></i>
                    <span id="toggle-text-<?php echo $faq['id']; ?>">Read Full Answer</span>
                </button>
                
                <!-- Full Answer (Hidden by default) -->
                <div class="answer-hidden" id="answer-<?php echo $faq['id']; ?>">
                    <div class="border-top pt-3 mt-3">
                        <!-- Full Answer Content -->
                        <h6 class="mb-3">
                            Complete Answer:
                        </h6>
                        <div class="card-text mb-4">
                            <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                        </div>
                        
                        <!-- Voting Section -->
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Was this helpful?
                                </small>
                                
                                <!-- Small Voting Buttons -->
                                <div class="btn-group" role="group" id="vote-group-<?php echo $faq['id']; ?>">
                                    <button type="button" 
                                            class="btn btn-outline-success btn-sm vote-btn-sm" 
                                            onclick="voteFAQ(<?php echo $faq['id']; ?>, 'up', this)">
                                        <i class="fas fa-thumbs-up"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-danger btn-sm vote-btn-sm" 
                                            onclick="voteFAQ(<?php echo $faq['id']; ?>, 'down', this)">
                                        <i class="fas fa-thumbs-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php 
            endwhile; 
        else: 
        ?>
        <!-- No FAQs Message -->
        <div class="text-center py-5">
            <i class="fas fa-question-circle fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">No FAQs available yet</h4>
            <p class="text-muted">Check back soon or contact support for help.</p>
            <a href="support.php" class="btn btn-primary">
                <i class="fas fa-envelope me-2"></i> Ask a Question
            </a>
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

    // Prevent double voting on this page view
    group.querySelectorAll('button').forEach(btn => btn.disabled = true);

    try {
        const response = await fetch('api/vote.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ faq_id: faqId, type })
        });

        const data = await response.json();
        if (!response.ok || data.error) {
            throw new Error(data.error || 'Vote failed');
        }

        // Visual feedback
        buttonEl.classList.remove('btn-outline-success', 'btn-outline-danger');
        buttonEl.classList.add(type === 'up' ? 'btn-success' : 'btn-danger');
        buttonEl.innerHTML = '<i class="fas fa-check"></i>';
    } catch (e) {
        // Re-enable buttons on error
        group.querySelectorAll('button').forEach(btn => btn.disabled = false);
        alert('Sorry, we could not record your vote. Please try again.');
    }
}

// Auto-expand if URL has hash
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash;
    if (hash && hash.startsWith('#faq-')) {
        const faqId = hash.replace('#faq-', '');
        setTimeout(() => toggleAnswer(faqId), 800);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
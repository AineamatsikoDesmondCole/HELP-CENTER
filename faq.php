<<<<<<< HEAD
<?php
require_once 'config/config.php';
require_once 'helpers/functions.php';

// Get FAQ ID from URL
$faqId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($faqId <= 0) {
    header('Location: index.php');
    exit;
}

// Get FAQ details
$db = new Database();
$faq = $db->query("
    SELECT f.*, c.name as category_name 
    FROM faqs f 
    LEFT JOIN categories c ON f.category_id = c.id 
    WHERE f.id = ? AND f.is_archived = FALSE
", [$faqId])->fetch();

if (!$faq) {
    // FAQ doesn't exist or is archived
    header('Location: index.php');
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<div class="row">
    <!-- Back Navigation -->
    <div class="col-12 mb-4">
        <a href="category.php?id=<?php echo $faq['category_id']; ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to <?php echo htmlspecialchars($faq['category_name']); ?>
        </a>
    </div>
    
    <!-- FAQ Content -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0"><?php echo htmlspecialchars($faq['question']); ?></h3>
            </div>
            <div class="card-body">
                <!-- Category Badge -->
                <div class="mb-3">
                    <span class="badge bg-secondary">
                        <i class="fas fa-folder"></i> <?php echo htmlspecialchars($faq['category_name']); ?>
                    </span>
                </div>
                
                <!-- Answer -->
                <div class="faq-answer mb-4">
                    <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                </div>
                
                <!-- Voting -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title">Was this helpful?</h6>
                        <div class="btn-group" role="group" id="vote-group-<?php echo $faq['id']; ?>">
                            <button type="button" class="btn btn-outline-success" onclick="voteFAQ(<?php echo $faq['id']; ?>, 'up', this)">
                                <i class="fas fa-thumbs-up"></i> Yes (<?php echo $faq['upvotes']; ?>)
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="voteFAQ(<?php echo $faq['id']; ?>, 'down', this)">
                                <i class="fas fa-thumbs-down"></i> No (<?php echo $faq['downvotes']; ?>)
                            </button>
                        </div>
                        <div class="mt-2 text-muted small" id="helpfulness-text">
                            <?php 
                            $totalVotes = $faq['upvotes'] + $faq['downvotes'];
                            if ($totalVotes > 0) {
                                $helpfulPercent = round(($faq['upvotes'] / $totalVotes) * 100);
                                echo "{$helpfulPercent}% of users found this helpful";
                            } else {
                                echo "Be the first to vote!";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Support Option -->
        <div class="card mt-4">
            <div class="card-body text-center">
                <h5 class="card-title">Still need help?</h5>
                <p class="card-text">If this FAQ didn't answer your question, our support team can help.</p>
                <a href="support.php" class="btn btn-primary">
                    <i class="fas fa-envelope"></i> Contact Support
                </a>
            </div>
        </div>
    </div>
    
    <!-- Related FAQs Sidebar -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-link"></i> Related FAQs
            </div>
            <div class="list-group list-group-flush">
                <?php
                $relatedFaqs = $db->query("
                    SELECT id, question 
                    FROM faqs 
                    WHERE category_id = ? 
                      AND id != ? 
                      AND is_archived = FALSE 
                    ORDER BY (upvotes - downvotes) DESC 
                    LIMIT 5
                ", [$faq['category_id'], $faqId]);
                
                while ($related = $relatedFaqs->fetch()):
                ?>
                <a href="faq.php?id=<?php echo $related['id']; ?>" class="list-group-item list-group-item-action">
                    <?php echo htmlspecialchars($related['question']); ?>
                </a>
                <?php endwhile; ?>
                
                <?php if ($relatedFaqs->rowCount() == 0): ?>
                <div class="list-group-item text-muted">
                    No other FAQs in this category yet.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
async function voteFAQ(faqId, type, buttonEl) {
    const group = document.getElementById(`vote-group-${faqId}`);
    if (!group) return;

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

        // Update helpfulness text if we have counts
        const up = data.upvotes;
        const down = data.downvotes;
        const total = up + down;
        const textEl = document.getElementById('helpfulness-text');
        if (textEl && total > 0) {
            const percent = Math.round((up / total) * 100);
            textEl.textContent = `${percent}% of users found this helpful`;
        }

        buttonEl.classList.remove('btn-outline-success', 'btn-outline-danger');
        buttonEl.classList.add(type === 'up' ? 'btn-success' : 'btn-danger');
        buttonEl.innerHTML = '<i class="fas fa-check"></i> Thank you!';
    } catch (e) {
        group.querySelectorAll('button').forEach(btn => btn.disabled = false);
        alert('Sorry, we could not record your vote. Please try again.');
    }
}
</script>

=======
<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get FAQ ID from URL
$faqId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($faqId <= 0) {
    header('Location: index.php');
    exit;
}

// Get FAQ details
$db = new Database();
$faq = $db->query("
    SELECT f.*, c.name as category_name 
    FROM faqs f 
    LEFT JOIN categories c ON f.category_id = c.id 
    WHERE f.id = ? AND f.is_archived = FALSE
", [$faqId])->fetch();

if (!$faq) {
    // FAQ doesn't exist or is archived
    header('Location: index.php');
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<div class="row">
    <!-- Back Navigation -->
    <div class="col-12 mb-4">
        <a href="category.php?id=<?php echo $faq['category_id']; ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to <?php echo htmlspecialchars($faq['category_name']); ?>
        </a>
    </div>
    
    <!-- FAQ Content -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0"><?php echo htmlspecialchars($faq['question']); ?></h3>
            </div>
            <div class="card-body">
                <!-- Category Badge -->
                <div class="mb-3">
                    <span class="badge bg-secondary">
                        <i class="fas fa-folder"></i> <?php echo htmlspecialchars($faq['category_name']); ?>
                    </span>
                </div>
                
                <!-- Answer -->
                <div class="faq-answer mb-4">
                    <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                </div>
                
                <!-- Voting -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title">Was this helpful?</h6>
                        <div class="btn-group" role="group" id="vote-group-<?php echo $faq['id']; ?>">
                            <button type="button" class="btn btn-outline-success" onclick="voteFAQ(<?php echo $faq['id']; ?>, 'up', this)">
                                <i class="fas fa-thumbs-up"></i> Yes (<?php echo $faq['upvotes']; ?>)
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="voteFAQ(<?php echo $faq['id']; ?>, 'down', this)">
                                <i class="fas fa-thumbs-down"></i> No (<?php echo $faq['downvotes']; ?>)
                            </button>
                        </div>
                        <div class="mt-2 text-muted small" id="helpfulness-text">
                            <?php 
                            $totalVotes = $faq['upvotes'] + $faq['downvotes'];
                            if ($totalVotes > 0) {
                                $helpfulPercent = round(($faq['upvotes'] / $totalVotes) * 100);
                                echo "{$helpfulPercent}% of users found this helpful";
                            } else {
                                echo "Be the first to vote!";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Support Option -->
        <div class="card mt-4">
            <div class="card-body text-center">
                <h5 class="card-title">Still need help?</h5>
                <p class="card-text">If this FAQ didn't answer your question, our support team can help.</p>
                <a href="support.php" class="btn btn-primary">
                    <i class="fas fa-envelope"></i> Contact Support
                </a>
            </div>
        </div>
    </div>
    
    <!-- Related FAQs Sidebar -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-link"></i> Related FAQs
            </div>
            <div class="list-group list-group-flush">
                <?php
                $relatedFaqs = $db->query("
                    SELECT id, question 
                    FROM faqs 
                    WHERE category_id = ? 
                      AND id != ? 
                      AND is_archived = FALSE 
                    ORDER BY (upvotes - downvotes) DESC 
                    LIMIT 5
                ", [$faq['category_id'], $faqId]);
                
                while ($related = $relatedFaqs->fetch()):
                ?>
                <a href="faq.php?id=<?php echo $related['id']; ?>" class="list-group-item list-group-item-action">
                    <?php echo htmlspecialchars($related['question']); ?>
                </a>
                <?php endwhile; ?>
                
                <?php if ($relatedFaqs->rowCount() == 0): ?>
                <div class="list-group-item text-muted">
                    No other FAQs in this category yet.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
async function voteFAQ(faqId, type, buttonEl) {
    const group = document.getElementById(`vote-group-${faqId}`);
    if (!group) return;

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

        // Update helpfulness text if we have counts
        const up = data.upvotes;
        const down = data.downvotes;
        const total = up + down;
        const textEl = document.getElementById('helpfulness-text');
        if (textEl && total > 0) {
            const percent = Math.round((up / total) * 100);
            textEl.textContent = `${percent}% of users found this helpful`;
        }

        buttonEl.classList.remove('btn-outline-success', 'btn-outline-danger');
        buttonEl.classList.add(type === 'up' ? 'btn-success' : 'btn-danger');
        buttonEl.innerHTML = '<i class="fas fa-check"></i> Thank you!';
    } catch (e) {
        group.querySelectorAll('button').forEach(btn => btn.disabled = false);
        alert('Sorry, we could not record your vote. Please try again.');
    }
}
</script>

>>>>>>> 36257f9ac8a2c27b93a4b73606d4a36660c330d7
<?php include 'includes/footer.php'; ?>
<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

echo "<h2>Adding Sample FAQs to Database</h2>";

$db = new Database();

// Sample FAQs data
$sampleFAQs = [
    // Category 1: Billing
    [
        'How do I view my invoices?',
        'To view your invoices, go to the Billing section in the main menu, then click on "Invoices". You can filter by date range and download PDF copies.',
        1, // category_id for Billing
        42, // upvotes
        3   // downvotes
    ],
    [
        'When will my payment be processed?',
        'Payments are typically processed within 24-48 hours. Credit card payments appear immediately, while bank transfers may take 2-3 business days.',
        1,
        35,
        2
    ],
    [
        'How do I update my payment method?',
        'Navigate to Settings > Payment Methods. Click "Add New Method" or edit existing ones. You can set a default payment method for automatic billing.',
        1,
        28,
        1
    ],
    
    // Category 2: Account
    [
        'How do I reset my password?',
        'Click "Forgot Password" on the login page. Enter your email address and follow the instructions sent to your inbox. The link expires in 24 hours.',
        2,
        56,
        4
    ],
    [
        'How do I update my profile information?',
        'Go to My Account > Profile Settings. You can update your name, email, phone number, and other personal details. Click Save when done.',
        2,
        31,
        1
    ],
    [
        'Why is my account locked?',
        'Accounts are locked after 5 failed login attempts for security. Contact support or wait 30 minutes for automatic unlock. You can also reset your password.',
        2,
        22,
        3
    ],
    
    // Category 3: Reports
    [
        'How do I export data to Excel?',
        'In any report view, click the Export button (📊 icon). Choose Excel format. Large exports may take a few minutes to generate.',
        3,
        39,
        2
    ],
    [
        'Can I schedule automated reports?',
        'Yes! Go to Reports > Scheduled Reports. Set frequency (daily, weekly, monthly) and choose recipients. Reports will be emailed automatically.',
        3,
        27,
        1
    ],
    [
        'Where are my saved report templates?',
        'Saved templates are in Reports > My Templates. You can edit, duplicate, or delete templates. Templates are user-specific.',
        3,
        18,
        0
    ]
];

// Insert FAQs
$inserted = 0;
foreach ($sampleFAQs as $faq) {
    list($question, $answer, $category_id, $upvotes, $downvotes) = $faq;
    
    $stmt = $db->query(
        "INSERT INTO faqs (question, answer, category_id, upvotes, downvotes) 
         VALUES (?, ?, ?, ?, ?)",
        [$question, $answer, $category_id, $upvotes, $downvotes]
    );
    
    $inserted++;
    echo "Added: $question<br>";
}

echo "<hr>";
echo "<h3>✅ Added $inserted sample FAQs!</h3>";
echo "<a href='index.php' class='btn btn-primary'>Go to Homepage</a><br>";
echo "<a href='category.php?id=1' class='btn btn-success mt-2'>Test Billing Category</a>";
?>
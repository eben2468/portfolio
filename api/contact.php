<?php
/**
 * Nadics Digital Solution — Contact Form API Endpoint
 * Handles AJAX contact form submissions
 */

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

require_once __DIR__ . '/../includes/functions.php';

// ─── Rate Limiting ──────────────────────────────────────────────────
if (isRateLimited('contact_form', 3, 300)) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => 'Too many submissions. Please wait a few minutes before trying again.'
    ]);
    exit;
}

// ─── CSRF Verification ─────────────────────────────────────────────
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCSRFToken($csrfToken)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Security validation failed. Please refresh the page and try again.'
    ]);
    exit;
}

// ─── Collect & Sanitize Input ───────────────────────────────────────
$name    = sanitize($_POST['name'] ?? '');
$email   = sanitize($_POST['email'] ?? '');
$phone   = sanitize($_POST['phone'] ?? '');
$subject = sanitize($_POST['subject'] ?? '');
$message = sanitize($_POST['message'] ?? '');

$isQuote = (isset($_POST['form_type']) && $_POST['form_type'] === 'quote') || isset($_POST['budget']) || isset($_POST['timeline']);
$budget = '';
$timeline = '';

// ─── Validation ─────────────────────────────────────────────────────
$errors = [];

if (empty($name) || strlen($name) < 2) {
    $errors['name'] = 'Full name is required (minimum 2 characters).';
}

if (empty($email) || !isValidEmail($email)) {
    $errors['email'] = 'A valid email address is required.';
}

if (!empty($phone) && !isValidPhone($phone)) {
    $errors['phone'] = 'Please enter a valid phone number.';
}

if (empty($subject)) {
    $errors['subject'] = 'Please select a subject/service.';
}

if ($isQuote) {
    $budget = sanitize($_POST['budget'] ?? '');
    $timeline = sanitize($_POST['timeline'] ?? '');

    if (empty($budget)) {
        $errors['budget'] = 'Please select an estimated budget range.';
    }
    if (empty($timeline)) {
        $errors['timeline'] = 'Please select an estimated timeline.';
    }
}

if (empty($message) || strlen($message) < 10) {
    $errors['message'] = 'Message details are required (minimum 10 characters).';
}

// Honeypot check (if a hidden field 'website' is filled, it's a bot)
if (!empty($_POST['website'] ?? '')) {
    // Silently reject — looks like success to bots
    echo json_encode(['success' => true, 'message' => 'Thank you for your message!']);
    exit;
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Please correct the errors below.',
        'errors'  => $errors
    ]);
    exit;
}

// Format message for database if this is a quote request
if ($isQuote) {
    $message .= "\n\n--- Quote Request Details ---\n";
    $message .= "Estimated Budget: " . $budget . "\n";
    $message .= "Estimated Timeline: " . $timeline;
}

// ─── Store in Database ──────────────────────────────────────────────
$db = getDB();

$successMessage = $isQuote 
    ? 'Thank you, ' . $name . '! Your quote request has been received. We\'ll get back to you with a proposal within 24 hours.'
    : 'Thank you, ' . $name . '! Your message has been received. We\'ll get back to you within 24 hours.';

if ($db) {
    try {
        $stmt = $db->prepare("
            INSERT INTO contacts (full_name, email, phone, subject, message, ip_address)
            VALUES (:name, :email, :phone, :subject, :message, :ip)
        ");

        $stmt->execute([
            ':name'    => $name,
            ':email'   => $email,
            ':phone'   => $phone,
            ':subject' => $subject,
            ':message' => $message,
            ':ip'      => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
        ]);

        echo json_encode([
            'success' => true,
            'message' => $successMessage
        ]);

    } catch (PDOException $e) {
        error_log("Contact form DB error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An internal error occurred. Please try again later or contact us directly at ' . SITE_EMAIL . '.'
        ]);
    }
} else {
    // Database not available — log the submission and still show success
    $logEntry = date('Y-m-d H:i:s') . " | Name: $name | Email: $email | Phone: $phone | Subject: $subject | Message: $message\n";
    $logFile  = __DIR__ . '/../database/contact_submissions.log';
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

    echo json_encode([
        'success' => true,
        'message' => $successMessage
    ]);
}

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// === CONFIGURATION ===
$to = 'info@hk-energieberatung.de';

// === GET FORM DATA ===
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? 'Kontaktformular');
$message = trim($_POST['message'] ?? '');
$honeypot = $_POST['website'] ?? ''; // Bot-trap field (must be empty)

// === VALIDATION & SECURITY ===

// 1. Check honeypot field for spam
if (!empty($honeypot)) {
  http_response_code(400);
  echo "Bot detection triggered.";
  exit;
}

// 2. Check for empty required fields
if (empty($name) || empty($email) || empty($message)) {
  http_response_code(400);
  echo "Please fill out all required fields.";
  exit;
}

// 3. Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo "Invalid email address format.";
  exit;
}

// 4. Prevent header injection
if (preg_match("/[\r\n]/", $name) || preg_match("/[\r\n]/", $email)) {
  http_response_code(400);
  echo "Invalid characters detected in form data.";
  exit;
}

// === COMPOSE AND SEND EMAIL ===

$email_subject = "Kontaktformular: " . $subject;
$email_body = "You have received a new message via the contact form:\n\n";
$email_body .= "Name: $name\n";
$email_body .= "Email: $email\n\n";
$email_body .= "Message:\n$message\n";

// --- CORRECTED HEADERS ---
// The 'From' header MUST be an email from your server's domain to avoid spam filters.
// The user's email goes into 'Reply-To'.
$headers = "From: " . $from_email . "\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();


// Send the email
if (mail($to, $email_subject, $email_body, $headers)) {
  http_response_code(200);
  // Optional: You can return a success message here too
  // echo "Message sent successfully!";
} else {
  http_response_code(500);
  echo "An error occurred while trying to send the message. Please check server logs.";
}
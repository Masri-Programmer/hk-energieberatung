<?php
// Activate error output (for development only – disable later)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// === CONFIGURATION ===
// The address emails will be sent TO.
$to = 'info@hk-energieberatung.de';
// The email address that emails will be sent FROM.
// Using your main contact email is perfectly fine.
$from_email = trim($_POST['email'] ?? '');

// === GET FORM DATA ===
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? 'Kontaktformular');
$message = trim($_POST['message'] ?? '');
$privacy = trim($_POST['privacy'] ?? ''); // Get privacy checkbox state
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
  echo "Bitte alle Pflichtfelder ausfüllen.";
  exit;
}

// 3. NEW: Check if the privacy policy checkbox was checked
if (empty($privacy)) {
  http_response_code(400);
  echo "Bitte akzeptieren Sie die Datenschutzerklärung.";
  exit;
}

// 4. Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo "Ungültige E-Mail-Adresse.";
  exit;
}

// 5. Prevent header injection
if (preg_match("/[\r\n]/", $name) || preg_match("/[\r\n]/", $email)) {
  http_response_code(400);
  echo "Ungültige Zeichen im Formular.";
  exit;
}

// === COMPOSE AND SEND EMAIL ===

$email_subject = "Kontaktformular: " . $subject;
$email_body = "Neue Nachricht über das Kontaktformular:\n\n";
$email_body .= "Name: $name\n";
$email_body .= "E-Mail: $email\n\n";
$email_body .= "Nachricht:\n$message\n\n";
$email_body .= "Datenschutzerklärung akzeptiert: Ja\n";

// --- CORRECTED HEADERS ---
$headers = "From: " . $from_email . "\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Send the email
if (mail($to, $email_subject, $email_body, $headers)) {
  http_response_code(200);
  echo "Ihre Nachricht wurde erfolgreich abgeschickt. Vielen Dank!";
} else {
  http_response_code(500);
  echo "Beim Senden ist ein Fehler aufgetreten. Bitte prüfen Sie die Server-Logs.";
}
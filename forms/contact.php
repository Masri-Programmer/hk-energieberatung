<?php
// Activate error output (for development only – disable later)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// === CONFIGURATION ===
$to = 'info@hk-energieberatung.de'; // Recipient
$from_email = 'no-reply@hk-energieberatung.de'; // Use your domain email to avoid spam filters

// === GET FORM DATA ===
$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$subject  = trim($_POST['subject'] ?? 'Kontaktformular');
$message  = trim($_POST['message'] ?? '');
$privacy  = trim($_POST['privacy'] ?? ''); // Privacy checkbox
$honeypot = $_POST['website'] ?? ''; // Bot trap

// === VALIDATION & SECURITY ===

// 1. Honeypot
if (!empty($honeypot)) {
  http_response_code(400);
  echo "Bot detection triggered.";
  exit;
}

// 2. Required fields
if (empty($name) || empty($email) || empty($message)) {
  http_response_code(400);
  echo "Bitte alle Pflichtfelder ausfüllen.";
  exit;
}

// 3. Privacy acceptance
if (empty($privacy)) {
  http_response_code(400);
  echo "Bitte akzeptieren Sie die Datenschutzerklärung.";
  exit;
}

// 4. Validate email
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

// === COMPOSE EMAIL ===
$email_subject = '=?UTF-8?B?' . base64_encode("Kontaktformular: " . $subject) . '?=';
$email_body  = "Neue Nachricht über das Kontaktformular:\n\n";
$email_body .= "Name: $name\n";
$email_body .= "E-Mail: $email\n\n";
$email_body .= "Nachricht:\n$message\n\n";
$email_body .= "Datenschutzerklärung akzeptiert: Ja\n";

// === HEADERS ===
$headers  = "From: HK Energieberatung <{$from_email}>\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// === SEND EMAIL ===
if (mail($to, $email_subject, $email_body, $headers)) {
  http_response_code(200);
  echo "OK"; // Important for many AJAX handlers
} else {
  http_response_code(500);
  echo "Beim Senden ist ein Fehler aufgetreten. Bitte prüfen Sie die Server-Logs.";
}

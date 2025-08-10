<?php
// contact.php

// Set the recipient email
$to = "info@hk-energieberatung.de";

// Allow only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  echo "Method Not Allowed";
  exit;
}

// Honeypot field (anti-spam) — if filled, it's a bot
if (!empty($_POST["website"])) {
  http_response_code(400);
  echo "Spam detected";
  exit;
}

// Validate required fields
$name    = trim($_POST["name"] ?? '');
$email   = trim($_POST["email"] ?? '');
$subject = trim($_POST["subject"] ?? '');
$message = trim($_POST["message"] ?? '');
$privacy = isset($_POST["privacy"]);

if (empty($name) || empty($email) || empty($subject) || empty($message) || !$privacy) {
  http_response_code(400);
  echo "Bitte füllen Sie alle erforderlichen Felder aus.";
  exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo "Ungültige E-Mail-Adresse.";
  exit;
}

// Prepare email content
$email_subject = "[Kontaktformular] $subject";
$email_body    = "Name: $name\n" .
  "E-Mail: $email\n" .
  "Nachricht:\n$message\n";

// Set headers
$headers = "From: $name <$email>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Send the email
if (mail($to, $email_subject, $email_body, $headers)) {
  http_response_code(200);
  echo "Ihre Nachricht wurde erfolgreich versendet.";
} else {
  http_response_code(500);
  echo "Beim Versenden der Nachricht ist ein Fehler aufgetreten.";
}

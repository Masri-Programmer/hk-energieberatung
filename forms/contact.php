<?php
// Fehlerausgabe aktivieren (nur für Entwicklung – später deaktivieren)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Zieladresse
$to = 'info@hk-energieberatung.de';

// Eingaben aus POST
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? 'Kontaktformular');
$message = trim($_POST['message'] ?? '');
$honeypot = $_POST['website'] ?? '';
// Prüfen, ob das Honeypot-Feld ausgefüllt ist → dann Spam
if (!empty($honeypot)) {
  http_response_code(400);
  echo "Bot-Verdacht erkannt.";
  exit;
}

// Eingaben validieren
if (empty($name) || empty($email) || empty($message)) {
  http_response_code(400);
  echo "Bitte alle Pflichtfelder ausfüllen.";
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo "Ungültige E-Mail-Adresse.";
  exit;
}

// Header-Injection verhindern
if (preg_match("/[\r\n]/", $name) || preg_match("/[\r\n]/", $email)) {
  http_response_code(400);
  echo "Ungültige Zeichen im Formular.";
  exit;
}

// Nachricht zusammenbauen
$email_subject = "Kontaktformular: " . $subject;
$email_body = "Neue Nachricht über das Kontaktformular:\n\n";
$email_body .= "Name: $name\n";
$email_body .= "E-Mail: $email\n\n";
$email_body .= "Nachricht:\n$message\n";

// Header setzen
$headers = "From: $name <$email>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// E-Mail senden
if (mail($to, $email_subject, $email_body, $headers)) {
  http_response_code(200);
  echo "Nachricht erfolgreich gesendet.";
} else {
  http_response_code(500);
  echo "Beim Senden ist ein Fehler aufgetreten.";
}
?>
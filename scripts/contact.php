<?php
declare(strict_types=1);

$TO      = 'info@consulting-cms.com';
$SUBJECT = '[CMS] Nuevo contacto desde Neo Fusion';
$FROM    = 'no-reply@consulting-cms.com';

header('Content-Type: application/json; charset=utf-8');

function fail(int $code, string $msg): void {
  http_response_code($code);
  echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
  exit;
}

function ok(): void {
  echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
  exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') fail(405, 'method_not_allowed');
if (!empty($_POST['website'])) ok();

$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

if ($name === '' || mb_strlen($name) < 2) fail(422, 'invalid_name');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail(422, 'invalid_email');
if ($message === '' || mb_strlen($message) < 10) fail(422, 'invalid_message');

$clean = fn(string $value): string => str_replace(["\r", "\n", "%0a", "%0d"], ' ', $value);
$name = $clean($name);
$email = $clean($email);
$phone = $clean($phone);

$body  = "Nombre: {$name}\n";
$body .= "Email: {$email}\n";
$body .= "Teléfono: {$phone}\n\n";
$body .= "Mensaje:\n{$message}\n\n";
$body .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? '') . "\n";
$body .= "UA: " . ($_SERVER['HTTP_USER_AGENT'] ?? '') . "\n";

$headers  = "From: CMS Web <{$FROM}>\r\n";
$headers .= "Reply-To: {$name} <{$email}>\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

if (!@mail($TO, $SUBJECT, $body, $headers)) fail(500, 'mail_failed');
ok();

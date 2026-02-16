<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

$docTypeId = (int)($_GET['document_type_id'] ?? 0);
if ($docTypeId <= 0) { exit; }

// kunin name/category para malaman anong form ang ilalabas
$stmt = $db->prepare("SELECT category, name FROM document_types WHERE id=? LIMIT 1");
$stmt->bind_param("i", $docTypeId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) exit;

$type = strtolower(trim(($row['category'] ?? '') . ' ' . ($row['name'] ?? '')));

// choose partial file
$view = null;

if (str_contains($type, 'guardian')) {
  $view = __DIR__ . '/../views/resident/forms/form_guardian.php';
} elseif (str_contains($type, 'cohabitation') || str_contains($type, 'live in')) {
  $view = __DIR__ . '/../views/resident/forms/form_livein.php';
} elseif (str_contains($type, 'solo parent')) {
  $view = __DIR__ . '/../views/resident/forms/form_soloparent.php';
} else {
  // walang extra form
  exit;
}

if (is_file($view)) {
  require $view; // echoes HTML
}

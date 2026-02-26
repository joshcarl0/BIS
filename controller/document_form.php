<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

$docTypeId = (int)($_GET['document_type_id'] ?? 0);
if ($docTypeId <= 0) { exit; }

// kunin template_key para malaman anong form ang ilalabas
$stmt = $db->prepare("SELECT template_key FROM document_types WHERE id=? LIMIT 1");
$stmt->bind_param("i", $docTypeId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) exit;

$templateKey = strtolower(trim((string)($row['template_key'] ?? '')));

// choose partial file
$view = null;

switch ($templateKey) {
  case 'guardian':
    $view = __DIR__ . '/../views/resident/forms/form_guardian.php';
    break;
  case 'livein':
    $view = __DIR__ . '/../views/resident/forms/form_livein.php';
    break;
  case 'soloparent':
    $view = __DIR__ . '/../views/resident/forms/form_soloparent.php';
    break;
  default:
    // walang extra form
    exit;
}

if (is_file($view)) {
  require $view; // echoes HTML
}

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

  case 'clearance':
  case 'barangay_clearance':
    $view = null; // handled separately in document_request.php (because of photo upload)
    break;

  case 'certification':
    $view = __DIR__ . '/../views/resident/forms/form_certification.php';
    break;

    case 'business_permit':
      case 'permit_business':
        case 'permit':
          $view = __DIR__ . '/../views/resident/forms/form_business_permit.php';
          break;

    case 'excavation_permit';
      case 'permit_excavation';
      case 'excavation';
          $view = __DIR__ . '/../views/resident/forms/form_excavation_permit.php';
          break;

      case 'construction_permit':
        case 'permit_construction':
        case 'construction':
          $view = __DIR__ . '/../views/resident/forms/form_construction_permit.php';
          break;

  default:
    $view = null;
    break;
    
    exit;
}

if (is_file($view)) {
  require $view; // echoes HTML
}

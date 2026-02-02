<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Household.php';
require_once __DIR__ . '/../models/Lookup.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /BIS/views/login.php"); exit;
}

$households = new Household($db);
$lookup     = new Lookup($db);

$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $r = $households->create($_POST);
        $_SESSION[$r['ok'] ? 'success' : 'error'] = $r['ok'] ? "Household added." : $r['error'];
        header("Location: /BIS/controller/households_manage.php"); exit;
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $r = $households->update($id, $_POST);
        $_SESSION[$r['ok'] ? 'success' : 'error'] = $r['ok'] ? "Household updated." : $r['error'];
        header("Location: /BIS/controller/households_manage.php"); exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $r = $households->delete($id);
        $_SESSION[$r['ok'] ? 'success' : 'error'] = $r['ok'] ? "Household deleted." : $r['error'];
        header("Location: /BIS/controller/households_manage.php"); exit;

    } 

}

$q = trim($_GET['q'] ?? '');
$page = (int)($_GET['page'] ?? 1);

$list = $households->list($q, $page, 10);

$data = [
  'list' => $list,
  'puroks' => $lookup->getAll('puroks','name'),
];

require_once __DIR__ . '/../views/admin/households_manage.php';


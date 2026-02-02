<?php

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Dashboard.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /BIS/views/login.php"); exit;
}

$dashboard = new Dashboard($db);

$data = [
  'total_population' => $dash->totalPopulation(),
  'total_households' => $dash->totalHouseholds(),
  'avg_age' => $dash->averageAge(),
  'by_purok' => $dash->populationByPurok(),
  'gender' => $dash->gender()
];

require_once __DIR__ . '/../views/admin/population_dashboard.php';
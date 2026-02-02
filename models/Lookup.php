<?php
class Lookup {
    // Add lookup methods here in the future

        private mysqli $conn;

    public function __construct(mysqli $db) {
        $this->conn = $db;
    
        }  
    
    public function getAll(string $table, string $orderBy = "name"): array
    {
        $allowed = ['puroks','civil_statuses','residency_types','education_levels','employment_statuses'];
        if (!in_array($table, $allowed, true)) return [];

        $sql = "SELECT id, name FROM {$table} ORDER BY {$orderBy}";
        $res = $this->conn->query($sql);

        $rows = [];
        if ($res) while ($row = $res->fetch_assoc()) $rows[] = $row;
        return $rows;
    }
}






    

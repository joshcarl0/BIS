<?php
class Dashboard
{
    private mysqli $conn;
    public function __construct(mysqli $db) { $this->conn = $db; }

    public function totalPopulation(): int {
        $r = $this->conn->query("SELECT COUNT(*) total FROM residents WHERE is_active=1");
        return (int)($r? $r->fetch_assoc()['total'] : 0);
    }

    public function totalHouseholds(): int {
        $r = $this->conn->query("SELECT COUNT(*) total FROM households");
        return (int)($r? $r->fetch_assoc()['total'] : 0);
    }

    public function averageAge(): float {
        $r = $this->conn->query("SELECT AVG(TIMESTAMPDIFF(YEAR,birthdate,CURDATE())) avg_age FROM residents WHERE is_active=1");
        return round((float)($r? $r->fetch_assoc()['avg_age'] : 0), 1);
    }

    public function populationByPurok(): array {
        $sql = "
          SELECT p.name purok,
                 COUNT(r.id) population,
                 COUNT(DISTINCT r.household_id) households
          FROM puroks p
          LEFT JOIN residents r ON r.purok_id=p.id AND r.is_active=1
          GROUP BY p.id
          ORDER BY population DESC
        ";
        $res = $this->conn->query($sql);
        $rows = [];
        if ($res) while ($row = $res->fetch_assoc()) $rows[] = $row;
        return $rows;
    }

    public function gender(): array {
        $res = $this->conn->query("SELECT sex, COUNT(*) total FROM residents WHERE is_active=1 GROUP BY sex");
        $rows = [];
        if ($res) while ($row = $res->fetch_assoc()) $rows[] = $row;
        return $rows;
    }
}

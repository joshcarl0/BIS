<?php
class Household
{
    private mysqli $conn;
    public function __construct(mysqli $db) { $this->conn = $db; }

    public function create(array $d): array
    {
        if (empty($d['household_code']) || empty($d['purok_id']) || empty($d['address_line'])) {
            return ['ok'=>false,'error'=>"Household code, purok, address required."];
        }

        $sql = "INSERT INTO households (household_code,purok_id,address_line,housing_type)
                VALUES (?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return ['ok'=>false,'error'=>"Prepare failed: ".$this->conn->error];

        $code = trim($d['household_code']);
        $purok_id = (int)$d['purok_id'];
        $addr = trim($d['address_line']);
        $housing = trim($d['housing_type'] ?? '');

        $stmt->bind_param("siss", $code, $purok_id, $addr, $housing);
        if (!$stmt->execute()) return ['ok'=>false,'error'=>"Insert failed: ".$stmt->error];
        return ['ok'=>true,'id'=>$stmt->insert_id];
    }

    public function update(int $id, array $d): array
    {
        if ($id <= 0) return ['ok'=>false,'error'=>"Invalid ID"];
        if (empty($d['household_code']) || empty($d['purok_id']) || empty($d['address_line'])) {
            return ['ok'=>false,'error'=>"Household code, purok, address required."];
        }

        $sql = "UPDATE households SET household_code=?, purok_id=?, address_line=?, housing_type=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return ['ok'=>false,'error'=>"Prepare failed: ".$this->conn->error];

        $code = trim($d['household_code']);
        $purok_id = (int)$d['purok_id'];
        $addr = trim($d['address_line']);
        $housing = trim($d['housing_type'] ?? '');

        $stmt->bind_param("sissi", $code, $purok_id, $addr, $housing, $id);
        if (!$stmt->execute()) return ['ok'=>false,'error'=>"Update failed: ".$stmt->error];
        return ['ok'=>true];
    }

    public function delete(int $id): array
    {
        // Only allow delete if no residents assigned (optional safe rule)
        $check = $this->conn->prepare("SELECT COUNT(*) c FROM residents WHERE household_id=?");
        $check->bind_param("i", $id);
        $check->execute();
        $c = (int)($check->get_result()->fetch_assoc()['c'] ?? 0);
        if ($c > 0) return ['ok'=>false,'error'=>"Cannot delete: household has residents assigned."];

        $stmt = $this->conn->prepare("DELETE FROM households WHERE id=?");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) return ['ok'=>false,'error'=>"Delete failed: ".$stmt->error];
        return ['ok'=>true];
    }

    public function list(string $q="", int $page=1, int $perPage=10): array
    {
        $page = max(1, $page);
        $perPage = max(5, min(50, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = "WHERE 1=1";
        $params = [];
        $types = "";

        if ($q !== "") {
            $where .= " AND (h.household_code LIKE ? OR h.address_line LIKE ?)";
            $like = "%{$q}%";
            $params = [$like, $like];
            $types = "ss";
        }

        $countSql = "SELECT COUNT(*) total FROM households h {$where}";
        $stmt = $this->conn->prepare($countSql);
        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $total = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);

        $sql = "
            SELECT h.id, h.household_code, h.address_line, h.housing_type,
                   p.name AS purok,
                   COUNT(r.id) AS members
            FROM households h
            JOIN puroks p ON p.id = h.purok_id
            LEFT JOIN residents r ON r.household_id = h.id AND r.is_active=1
            {$where}
            GROUP BY h.id
            ORDER BY h.id DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";
        $stmt = $this->conn->prepare($sql);
        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        $rows = [];
        while ($res && ($row = $res->fetch_assoc())) $rows[] = $row;

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'pages' => (int)ceil($total / $perPage),
        ];
    }

    public function allForSelect(): array
    {
        $res = $this->conn->query("SELECT id, household_code FROM households ORDER BY household_code");
        $rows = [];
        if ($res) while ($row = $res->fetch_assoc()) $rows[] = $row;
        return $rows;
    }
}



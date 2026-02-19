<?php

class Resident
{
    private mysqli $conn;
    private string $table = "residents";

    // Related tables
    private string $tblSpecialGroups = "special_groups";
    private string $tblResidentSpecialGroups = "resident_special_groups";

    public function __construct(mysqli $db)
    {
        $this->conn = $db;
    }

    /* =========================
       LIST + SEARCH + PAGINATION
    ========================= */
    public function getPaginated(string $q = '', int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = " WHERE is_active = 1 ";
        $params = [];
        $types = "";

        if ($q !== '') {
            $where .= " AND (
                CAST(id AS CHAR) LIKE ?
                OR last_name LIKE ?
                OR first_name LIKE ?
                OR email LIKE ?
                OR contact_number LIKE ?
            ) ";
            $like = "%{$q}%";
            $params = [$like, $like, $like, $like, $like];
            $types = "sssss";
        }

        // COUNT
        $sqlCount = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $stmt = $this->conn->prepare($sqlCount);
        if (!$stmt) return $this->emptyList();

        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $total = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
        $stmt->close();

        // ROWS
        $sql = "SELECT
                    id, household_id, purok_id, residency_type_id,
                    first_name, middle_name, last_name, suffix,
                    sex, birthdate, civil_status_id,
                    contact_number, email, occupation,
                    education_level_id, employment_status_id,
                    voter_status, is_head_of_household, is_active,
                    created_at, updated_at
                FROM {$this->table}
                {$where}
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return $this->emptyList();

        if ($types) {
            $types2 = $types . "ii";
            $params2 = array_merge($params, [$perPage, $offset]);
            $stmt->bind_param($types2, ...$params2);
        } else {
            $stmt->bind_param("ii", $perPage, $offset);
        }

        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $pages = (int)ceil($total / $perPage);

        return [
            "rows" => $rows,
            "total" => $total,
            "page" => $page,
            "perPage" => $perPage,
            "pages" => max(1, $pages)
        ];
    }

    /**
     * List residents + special_groups string
     */
    public function getPaginatedWithGroups(string $q = '', int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = " WHERE r.is_active = 1 ";
        $params = [];
        $types = "";

        if ($q !== '') {
            $where .= " AND (
                CAST(r.id AS CHAR) LIKE ?
                OR r.last_name LIKE ?
                OR r.first_name LIKE ?
                OR r.email LIKE ?
                OR r.contact_number LIKE ?
            ) ";
            $like = "%{$q}%";
            $params = [$like, $like, $like, $like, $like];
            $types = "sssss";
        }

        // COUNT
        $sqlCount = "SELECT COUNT(*) as total FROM {$this->table} r {$where}";
        $stmt = $this->conn->prepare($sqlCount);
        if (!$stmt) return $this->emptyList();
        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $total = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
        $stmt->close();

        // ROWS + groups
        $sql = "SELECT
                    r.*,
                    GROUP_CONCAT(sg.name ORDER BY sg.name SEPARATOR ', ') AS special_groups
                FROM {$this->table} r
                LEFT JOIN {$this->tblResidentSpecialGroups} rsg ON rsg.resident_id = r.id
                LEFT JOIN {$this->tblSpecialGroups} sg ON sg.id = rsg.group_id
                {$where}
                GROUP BY r.id
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return $this->emptyList();

        if ($types) {
            $types2 = $types . "ii";
            $params2 = array_merge($params, [$perPage, $offset]);
            $stmt->bind_param($types2, ...$params2);
        } else {
            $stmt->bind_param("ii", $perPage, $offset);
        }

        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $pages = (int)ceil($total / $perPage);

        return [
            "rows" => $rows,
            "total" => $total,
            "page" => $page,
            "perPage" => $perPage,
            "pages" => max(1, $pages)
        ];
    }

    private function emptyList(): array
    {
        return ["rows" => [], "total" => 0, "page" => 1, "perPage" => 10, "pages" => 1];
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }

    /* =========================
       CREATE
    ========================= */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (
                    household_id, purok_id, residency_type_id,
                    first_name, middle_name, last_name, suffix,
                    sex, birthdate, civil_status_id,
                    contact_number, email, occupation,
                    education_level_id, employment_status_id,
                    voter_status, is_head_of_household, is_active
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $get = fn($k, $def='') => trim((string)($data[$k] ?? $def));
        $getInt = function($k, $def = null) use ($data) {
            if (!isset($data[$k]) || $data[$k] === '') return $def;
            return (int)$data[$k];
        };

        $household_id = $getInt("household_id", null);
        $purok_id = $getInt("purok_id", 0);
        $residency_type_id = $getInt("residency_type_id", 0);

        $first_name = $get("first_name");
        $middle_name = $get("middle_name");
        $last_name = $get("last_name");
        $suffix = $get("suffix");

        $sex = $get("sex");
        $birthdate = $get("birthdate");

        $civil_status_id = $getInt("civil_status_id", 0);

        $contact_number = $get("contact_number");
        $email = $get("email");
        $occupation = $get("occupation");

        $education_level_id = $getInt("education_level_id", null);
        $employment_status_id = $getInt("employment_status_id", null);

        $voter_status = isset($data["voter_status"]) ? (int)$data["voter_status"] : 0;
        $is_head_of_household = !empty($data["is_head_of_household"]) ? 1 : 0;
        $is_active = isset($data["is_active"]) ? (int)$data["is_active"] : 1;

        $stmt->bind_param(
            "iiissssssisssiiiii",
            $household_id,
            $purok_id,
            $residency_type_id,
            $first_name,
            $middle_name,
            $last_name,
            $suffix,
            $sex,
            $birthdate,
            $civil_status_id,
            $contact_number,
            $email,
            $occupation,
            $education_level_id,
            $employment_status_id,
            $voter_status,
            $is_head_of_household,
            $is_active
        );

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function createReturnId(array $data)
    {
        $ok = $this->create($data);
        if (!$ok) return false;
        return (int)$this->conn->insert_id;
    }

    /* =========================
       UPDATE (FIXED types)
    ========================= */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET
                    household_id=?,
                    purok_id=?,
                    residency_type_id=?,
                    first_name=?,
                    middle_name=?,
                    last_name=?,
                    suffix=?,
                    sex=?,
                    birthdate=?,
                    civil_status_id=?,
                    contact_number=?,
                    email=?,
                    occupation=?,
                    education_level_id=?,
                    employment_status_id=?,
                    voter_status=?,
                    is_head_of_household=?,
                    is_active=?
                WHERE id=?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $get = fn($k, $def='') => trim((string)($data[$k] ?? $def));
        $getInt = function($k, $def = null) use ($data) {
            if (!isset($data[$k]) || $data[$k] === '') return $def;
            return (int)$data[$k];
        };

        $household_id = $getInt("household_id", null);

            if ($household_id !== null && $household_id <= 0) {
                $household_id = null;
            }

        $purok_id = $getInt("purok_id", 0);
        $residency_type_id = $getInt("residency_type_id", 0);

        $first_name = $get("first_name");
        $middle_name = $get("middle_name");
        $last_name = $get("last_name");
        $suffix = $get("suffix");

        $sex = $get("sex");
        $birthdate = $get("birthdate");

        $civil_status_id = $getInt("civil_status_id", 0);

        $contact_number = $get("contact_number");
        $email = $get("email");
        $occupation = $get("occupation");

        $education_level_id = $getInt("education_level_id", null);
        $employment_status_id = $getInt("employment_status_id", null);

        $voter_status = isset($data["voter_status"]) ? (int)$data["voter_status"] : 0;
        $is_head_of_household = !empty($data["is_head_of_household"]) ? 1 : 0;
        $is_active = isset($data["is_active"]) ? (int)$data["is_active"] : 1;

        //  19 params -> 19 types
        $stmt->bind_param(
            "iiissssssisssiiiiii",
            $household_id,
            $purok_id,
            $residency_type_id,
            $first_name,
            $middle_name,
            $last_name,
            $suffix,
            $sex,
            $birthdate,
            $civil_status_id,
            $contact_number,
            $email,
            $occupation,
            $education_level_id,
            $employment_status_id,
            $voter_status,
            $is_head_of_household,
            $is_active,
            $id
        );

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function deactivate(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET is_active = 0 WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    /* =========================
       SPECIAL GROUPS
    ========================= */
    public function getAllSpecialGroups(): array
    {
        $sql = "SELECT id, code, name FROM {$this->tblSpecialGroups} ORDER BY name ASC";
        $res = $this->conn->query($sql);
        if (!$res) return [];
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function getSpecialGroupIdsByResident(int $residentId): array
    {
        $sql = "SELECT group_id FROM {$this->tblResidentSpecialGroups} WHERE resident_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("i", $residentId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return array_map(fn($r) => (int)$r['group_id'], $rows);
    }

    public function updateSpecialGroups(int $residentId, array $groupIds, array $notesByGroupId = []): bool
    {
        $groupIds = array_values(array_unique(array_map('intval', $groupIds)));

        $this->conn->begin_transaction();

        try {
            $del = $this->conn->prepare("DELETE FROM {$this->tblResidentSpecialGroups} WHERE resident_id = ?");
            if (!$del) throw new Exception("Prepare failed (delete)");
            $del->bind_param("i", $residentId);
            if (!$del->execute()) {
                $del->close();
                throw new Exception("Delete failed");
            }
            $del->close();

            if (!empty($groupIds)) {
                $ins = $this->conn->prepare(
                    "INSERT INTO {$this->tblResidentSpecialGroups} (resident_id, group_id, notes) VALUES (?, ?, ?)"
                );
                if (!$ins) throw new Exception("Prepare failed (insert)");

                foreach ($groupIds as $gid) {
                    $note = trim((string)($notesByGroupId[$gid] ?? ''));
                    $ins->bind_param("iis", $residentId, $gid, $note);
                    if (!$ins->execute()) {
                        $ins->close();
                        throw new Exception("Insert failed");
                    }
                }
                $ins->close();
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            error_log("Resident::updateSpecialGroups error: " . $e->getMessage());
            return false;
        }
    }

public function countActive()
{
    $sql = "SELECT COUNT(*) AS total FROM residents WHERE is_active = 1";
    $res = $this->conn->query($sql);
    $row = $res ? $res->fetch_assoc() : null;
    return (int)($row['total'] ?? 0);
}



}
<?php

class Resident
{
    private mysqli $conn;
    private string $table = "residents";

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

        // default: active only
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

        // ROWS (basic fields)
        $sql = "SELECT
                    id,
                    household_id,
                    purok_id,
                    residency_type_id,
                    first_name,
                    middle_name,
                    last_name,
                    suffix,
                    sex,
                    birthdate,
                    civil_status_id,
                    contact_number,
                    email,
                    occupation,
                    education_level_id,
                    employment_status_id,
                    voter_status,
                    is_head_of_household,
                    is_active,
                    created_at,
                    updated_at
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

    private function emptyList(): array
    {
        return ["rows" => [], "total" => 0, "page" => 1, "perPage" => 10, "pages" => 1];
    }

    /* =========================
       GET SINGLE BY ID
    ========================= */
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
       CREATE RESIDENT
    ========================= */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (
                    household_id,
                    purok_id,
                    residency_type_id,
                    first_name,
                    middle_name,
                    last_name,
                    suffix,
                    sex,
                    birthdate,
                    civil_status_id,
                    contact_number,
                    email,
                    occupation,
                    education_level_id,
                    employment_status_id,
                    voter_status,
                    is_head_of_household,
                    is_active
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $get = fn($k, $def='') => trim((string)($data[$k] ?? $def));
        $getInt = function($k, $def = null) use ($data) {
            if (!isset($data[$k]) || $data[$k] === '') return $def;
            return (int)$data[$k];
        };

        $household_id = $getInt("household_id", null);
        $purok_id = $getInt("purok_id", 0); // required in DB (NOT NULL) based on your DESCRIBE
        $residency_type_id = $getInt("residency_type_id", 0); // required (NOT NULL)

        $first_name = $get("first_name");
        $middle_name = $get("middle_name");
        $last_name = $get("last_name");
        $suffix = $get("suffix");

        $sex = $get("sex"); // enum('Male','Female','Other')
        $birthdate = $get("birthdate"); // YYYY-MM-DD

        $civil_status_id = $getInt("civil_status_id", 0); // required (NOT NULL)

        $contact_number = $get("contact_number");
        $email = $get("email");
        $occupation = $get("occupation");

        $education_level_id = $getInt("education_level_id", null);
        $employment_status_id = $getInt("employment_status_id", null);

        $voter_status = isset($data["voter_status"]) ? (int)$data["voter_status"] : 0;
        $is_head_of_household = !empty($data["is_head_of_household"]) ? 1 : 0;
        $is_active = isset($data["is_active"]) ? (int)$data["is_active"] : 1;

        // NOTE: household_id, education_level_id, employment_status_id can be NULL
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

    /* =========================
       UPDATE RESIDENT
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
            $is_active,
            $id
        );

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    /* =========================
       DEACTIVATE (soft delete)
    ========================= */
    public function deactivate(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET is_active = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}

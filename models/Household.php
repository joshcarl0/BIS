<?php
/**
 * Household Model (OOP, MySQLi)
 * Table: households
 *
 * Supports:
 * - generateHouseholdCode(): HH-YYYY-0001
 * - getAll($status, $q): list with joins (purok_name, head_name)
 * - getById($id)
 * - create($data)
 * - update($id, $data)
 * - activate/deactivate/dissolve
 */

class Household
{
    private mysqli $conn;
    private string $table = 'households';

    public function __construct(mysqli $db)
    {
        $this->conn = $db;
    }

    /* =========================
       Small helpers
    ========================= */
    private function strOrNull($v): ?string
    {
        $v = trim((string)($v ?? ''));
        return $v === '' ? null : $v;
    }

    private function intOrNull($v): ?int
    {
        if ($v === null) return null;
        $v = trim((string)$v);
        return $v === '' ? null : (int)$v;
    }

    private function bool01($v): int
    {
        if ($v === true) return 1;
        $v = strtolower(trim((string)($v ?? '')));
        return in_array($v, ['1', 'true', 'on', 'yes'], true) ? 1 : 0;
    }

    private function cleanStatus(?string $status): string
    {
        $status = trim((string)($status ?? 'Active'));
        if (!in_array($status, ['Active', 'Inactive', 'Dissolved'], true)) {
            $status = 'Active';
        }
        return $status;
    }

    /* =========================
       Household Code Generator
       HH-YYYY-0001
    ========================= */
public function generateHouseholdCode(): string
{
    return $this->generateCode();
}
private function generateCode(): string
{
    $year = date('Y');
    $prefix = "HH-{$year}-";

    $sql = "SELECT household_code
            FROM {$this->table}
            WHERE household_code LIKE CONCAT(?, '%')
            ORDER BY household_code DESC
            LIMIT 1";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return $prefix . "0001";
    }

    $stmt->bind_param("s", $prefix);
    $stmt->execute();
    $res = $stmt->get_result();
    $last = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$last || empty($last['household_code'])) {
        return $prefix . "0001";
    }

    $parts = explode('-', $last['household_code']);
    $lastNum = (int) end($parts);
    $nextNum = $lastNum + 1;

    return $prefix . str_pad((string)$nextNum, 4, '0', STR_PAD_LEFT);
}


    /* =========================
       READ: List (with joins)
       $status: 'Active'|'Inactive'|'Dissolved' or '' for ALL
       $q: search keyword
    ========================= */
    public function getAll(string $status = 'Active', string $q = ''): array
    {
        $status = trim($status);
        $q = trim($q);

        $sql = "SELECT
                    h.*,
                    p.name AS purok_name,
                    CONCAT(COALESCE(r.first_name,''),' ',COALESCE(r.last_name,'')) AS head_name
                FROM {$this->table} h
                LEFT JOIN puroks p ON p.id = h.purok_id
                LEFT JOIN residents r ON r.id = h.head_resident_id
                WHERE (? = '' OR h.status = ?)
                  AND (
                        ? = ''
                        OR h.household_code LIKE ?
                        OR h.address_line LIKE ?
                        OR p.name LIKE ?
                        OR CONCAT(COALESCE(r.first_name,''),' ',COALESCE(r.last_name,'')) LIKE ?
                  )
                ORDER BY h.id DESC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Household::getAll prepare failed: " . $this->conn->error);
            return [];
        }

        $statusParam = $status === 'All' ? '' : $status;
        $like = '%' . $q . '%';

        $stmt->bind_param("sssssss", $statusParam, $statusParam, $q, $like, $like, $like, $like);
        $stmt->execute();
        $res = $stmt->get_result();

        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    /* =========================
       READ: single
    ========================= */
    public function getById(int $id): ?array
    {
        $sql = "SELECT
                    h.*,
                    p.name AS purok_name,
                    CONCAT(COALESCE(r.first_name,''),' ',COALESCE(r.last_name,'')) AS head_name
                FROM {$this->table} h
                LEFT JOIN puroks p ON p.id = h.purok_id
                LEFT JOIN residents r ON r.id = h.head_resident_id
                WHERE h.id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Household::getById prepare failed: " . $this->conn->error);
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = ($res) ? $res->fetch_assoc() : null;
        $stmt->close();

        return $row ?: null;
    }

    /* =========================
       CREATE
       Required fields (based on your table):
       - household_code (auto)
       - purok_id
       - address_line
       Optional: head_resident_id, years_residing, etc.
    ========================= */
    public function create(array $data): bool
{
    $sql = "INSERT INTO {$this->table} (
                household_code, purok_id, address_line, housing_type,
                head_resident_id, years_residing, landmark,
                household_type, tenure_status, monthly_income_range,
                income_source, employment_type, socio_economic_class,
                is_4ps_beneficiary, is_social_pension, is_tupad_beneficiary,
                is_akap_beneficiary, is_solo_parent_assistance,
                housing_status, house_material, water_source, electricity_access,
                toilet_facility, internet_access,
                has_vehicle, has_motorcycle, has_refrigerator, has_tv,
                has_washing_machine, has_aircon, has_computer, has_smartphone,
                land_ownership, business_ownership,
                highest_education, health_insurance, malnutrition_cases,
                registration_date, remarks, status, created_by
            )
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,
                    ?,?,?,?,?,
                    ?,?,?,?,?,?,
                    ?,?,?,?,?,?,?,?,
                    ?,?,
                    ?,?,?,
                    ?,?,?,?)";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        error_log("Household::create prepare failed: " . $this->conn->error);
        return false;
    }

    // basic
    $household_code = trim((string)($data['household_code'] ?? ''));

if ($household_code === '') {
    $household_code = $this->generateHouseholdCode();
}

    $purok_id       = (int)($data['purok_id'] ?? 0);
    $address_line   = $data['address_line'] ?? null;
    $housing_type   = $data['housing_type'] ?? null;

    $head_resident_id = !empty($data['head_resident_id']) ? (int)$data['head_resident_id'] : null;
    $years_residing = isset($data['years_residing']) && $data['years_residing'] !== ''
    ? (int)$data['years_residing']
    : 0;

$address_line = trim($data['address_line'] ?? '');

    $landmark         = $data['landmark'] ?? null;

    $household_type       = $data['household_type'] ?? null;
    $tenure_status        = $data['tenure_status'] ?? null;
    $monthly_income_range = $data['monthly_income_range'] ?? null;

    $income_source        = $data['income_source'] ?? null;
    $employment_type      = $data['employment_type'] ?? null;
    $socio_economic_class = $data['socio_economic_class'] ?? null;

    // program flags
    $is_4ps_beneficiary       = !empty($data['is_4ps_beneficiary']) ? 1 : 0;
    $is_social_pension        = !empty($data['is_social_pension']) ? 1 : 0;
    $is_tupad_beneficiary     = !empty($data['is_tupad_beneficiary']) ? 1 : 0;
    $is_akap_beneficiary      = !empty($data['is_akap_beneficiary']) ? 1 : 0;
    $is_solo_parent_assistance= !empty($data['is_solo_parent_assistance']) ? 1 : 0;

    // utilities
    $housing_status     = $data['housing_status'] ?? null;
    $house_material     = $data['house_material'] ?? null;
    $water_source       = $data['water_source'] ?? null;
    $electricity_access = $data['electricity_access'] ?? null;
    $toilet_facility    = $data['toilet_facility'] ?? null;
    $internet_access    = $data['internet_access'] ?? null;

    // assets flags
    $has_vehicle        = !empty($data['has_vehicle']) ? 1 : 0;
    $has_motorcycle     = !empty($data['has_motorcycle']) ? 1 : 0;
    $has_refrigerator   = !empty($data['has_refrigerator']) ? 1 : 0;
    $has_tv             = !empty($data['has_tv']) ? 1 : 0;
    $has_washing_machine= !empty($data['has_washing_machine']) ? 1 : 0;
    $has_aircon         = !empty($data['has_aircon']) ? 1 : 0;
    $has_computer       = !empty($data['has_computer']) ? 1 : 0;
    $has_smartphone     = !empty($data['has_smartphone']) ? 1 : 0;

    // others
    $land_ownership     = $data['land_ownership'] ?? null;
    $business_ownership = $data['business_ownership'] ?? null;

    $highest_education  = $data['highest_education'] ?? null;
    $health_insurance   = $data['health_insurance'] ?? null;
    $malnutrition_cases = $data['malnutrition_cases'] ?? null;

    $registration_date  = $data['registration_date'] ?? null; // YYYY-MM-DD
    $remarks            = $data['remarks'] ?? null;
    $status             = $data['status'] ?? 'Active';
    $created_by         = !empty($data['created_by']) ? (int)$data['created_by'] : null;

    // 41 letters + 41 variables (MATCH!)
    $stmt->bind_param(
        "sissiiissssss" .  // 13
        "iiiii" .          // 5
        "ssssss" .         // 6
        "iiiiiiii" .       // 8
        "ss" .             // 2
        "sss" .            // 3
        "sss" .            // 3
        "i",               // 1
        $household_code,
        $purok_id,
        $address_line,
        $housing_type,

        $head_resident_id,
        $years_residing,
        $landmark,

        $household_type,
        $tenure_status,
        $monthly_income_range,

        $income_source,
        $employment_type,
        $socio_economic_class,

        $is_4ps_beneficiary,
        $is_social_pension,
        $is_tupad_beneficiary,
        $is_akap_beneficiary,
        $is_solo_parent_assistance,

        $housing_status,
        $house_material,
        $water_source,
        $electricity_access,
        $toilet_facility,
        $internet_access,

        $has_vehicle,
        $has_motorcycle,
        $has_refrigerator,
        $has_tv,
        $has_washing_machine,
        $has_aircon,
        $has_computer,
        $has_smartphone,

        $land_ownership,
        $business_ownership,

        $highest_education,
        $health_insurance,
        $malnutrition_cases,

        $registration_date,
        $remarks,
        $status,
        $created_by
    );

    $ok = $stmt->execute();
    if (!$ok) {
        error_log("Household::create execute failed: " . $stmt->error);
    }
    $stmt->close();
    return $ok;
}


    /* =========================
       Status helpers
    ========================= */
    public function setStatus(int $id, string $status): bool
    {
        $status = $this->cleanStatus($status);

        $sql = "UPDATE {$this->table}
                SET status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Household::setStatus prepare failed: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("si", $status, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function activate(int $id): bool
    {
        return $this->setStatus($id, 'Active');
    }

    public function deactivate(int $id): bool
    {
        return $this->setStatus($id, 'Inactive');
    }

    public function dissolve(int $id): bool
    {
        return $this->setStatus($id, 'Dissolved');
    }

public function update(int $id, array $data): bool
{
    $sql = "UPDATE {$this->table} SET
              purok_id = ?,
              head_resident_id = ?,
              address_line = ?,
              landmark = ?,
              years_residing = ?,
              housing_type = ?,
              household_type = ?,
              tenure_status = ?,
              housing_status = ?,
              monthly_income_range = ?,
              income_source = ?,
              employment_type = ?,
              socio_economic_class = ?,
              registration_date = ?,
              remarks = ?,
              status = ?,
              updated_at = CURRENT_TIMESTAMP
            WHERE id = ?";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        error_log("Household::update prepare failed: " . $this->conn->error);
        return false;
    }

    $purok_id = (int)($data['purok_id'] ?? 0);

    // head can be null
    $head = $data['head_resident_id'] ?? null;
    $head_resident_id = ($head === null || $head === '') ? null : (int)$head;

    $address_line = trim((string)($data['address_line'] ?? ''));
    $landmark = $data['landmark'] ?? null;

    $years_residing = isset($data['years_residing']) && $data['years_residing'] !== ''
        ? (int)$data['years_residing'] : null;

    $housing_type = $data['housing_type'] ?? null;
    $household_type = $data['household_type'] ?? null;
    $tenure_status = $data['tenure_status'] ?? null;
    $housing_status = $data['housing_status'] ?? null;

    $monthly_income_range = $data['monthly_income_range'] ?? null;
    $income_source = $data['income_source'] ?? null;
    $employment_type = $data['employment_type'] ?? null;
    $socio_economic_class = $data['socio_economic_class'] ?? null;

    $registration_date = $data['registration_date'] ?? null;
    $remarks = $data['remarks'] ?? null;

    $status = $data['status'] ?? 'Active';
    if (!in_array($status, ['Active','Inactive','Dissolved'], true)) $status = 'Active';

    // NOTE: for nullable integer use "i" but must bind variable as null and set mysqli to allow it.
    // Easiest: convert null -> NULL via bind_param works in mysqli if variable is null.
    $stmt->bind_param(
        "iississssssssssi",
        $purok_id,
        $head_resident_id,
        $address_line,
        $landmark,
        $years_residing,
        $housing_type,
        $household_type,
        $tenure_status,
        $housing_status,
        $monthly_income_range,
        $income_source,
        $employment_type,
        $socio_economic_class,
        $registration_date,
        $remarks,
        $status,
        $id
    );

    $ok = $stmt->execute();
    if (!$ok) error_log("Household::update execute failed: " . $stmt->error);
    $stmt->close();
    return $ok;
}





}

<?php

class Household
{
    private mysqli $conn;
    private string $table = "households";

    public function __construct(mysqli $db)
    {
        $this->conn = $db;
    }

    /* =========================
       CREATE
    ========================== */
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

        // Defaults / safe getters
        $household_code = $data['household_code'] ?? null;
        $purok_id = $data['purok_id'] ?? null;
        $address_line = $data['address_line'] ?? null;
        $housing_type = $data['housing_type'] ?? null;

        $head_resident_id = $data['head_resident_id'] ?? null;
        $years_residing = $data['years_residing'] ?? null;
        $landmark = $data['landmark'] ?? null;

        $household_type = $data['household_type'] ?? null;
        $tenure_status = $data['tenure_status'] ?? null;
        $monthly_income_range = $data['monthly_income_range'] ?? null;

        $income_source = $data['income_source'] ?? null;
        $employment_type = $data['employment_type'] ?? null;
        $socio_economic_class = $data['socio_economic_class'] ?? null;

        // booleans (tinyint)
        $is_4ps_beneficiary = !empty($data['is_4ps_beneficiary']) ? 1 : 0;
        $is_social_pension = !empty($data['is_social_pension']) ? 1 : 0;
        $is_tupad_beneficiary = !empty($data['is_tupad_beneficiary']) ? 1 : 0;
        $is_akap_beneficiary = !empty($data['is_akap_beneficiary']) ? 1 : 0;
        $is_solo_parent_assistance = !empty($data['is_solo_parent_assistance']) ? 1 : 0;

        $housing_status = $data['housing_status'] ?? null;
        $house_material = $data['house_material'] ?? null;
        $water_source = $data['water_source'] ?? null;
        $electricity_access = $data['electricity_access'] ?? null;
        $toilet_facility = $data['toilet_facility'] ?? null;
        $internet_access = $data['internet_access'] ?? null;

        $has_vehicle = !empty($data['has_vehicle']) ? 1 : 0;
        $has_motorcycle = !empty($data['has_motorcycle']) ? 1 : 0;
        $has_refrigerator = !empty($data['has_refrigerator']) ? 1 : 0;
        $has_tv = !empty($data['has_tv']) ? 1 : 0;
        $has_washing_machine = !empty($data['has_washing_machine']) ? 1 : 0;
        $has_aircon = !empty($data['has_aircon']) ? 1 : 0;
        $has_computer = !empty($data['has_computer']) ? 1 : 0;
        $has_smartphone = !empty($data['has_smartphone']) ? 1 : 0;

        $land_ownership = $data['land_ownership'] ?? null;
        $business_ownership = $data['business_ownership'] ?? null;

        $highest_education = $data['highest_education'] ?? null;
        $health_insurance = $data['health_insurance'] ?? null;
        $malnutrition_cases = $data['malnutrition_cases'] ?? null;

        $registration_date = $data['registration_date'] ?? null; // YYYY-MM-DD
        $remarks = $data['remarks'] ?? null;

        $status = $data['status'] ?? 'Active';
        $created_by = $data['created_by'] ?? null;

        // Types string: keep ints as i, strings as s
        // NOTE: mysqli doesn't have a "null type"; passing null is okay.
        $stmt->bind_param(
            "sissiiissssss" .  // first 13
            "iiiii" .          // 5 flags
            "ssssss" .         // utilities 6
            "iiiiiiii" .       // assets 8
            "ss" .             // land/business 2
            "sss" .            // edu/insurance/malnutrition 3
            "sss" .            // registration_date/remarks/status 3
            "i",               // created_by 1
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
       READ (LIST)
       Includes purok + head name
    ========================== */
    public function getAll(string $status = 'Active'): array
    {
        $sql = "SELECT
                    h.*,
                    p.name AS purok_name,
                    CONCAT(r.first_name, ' ', r.last_name) AS head_name
                FROM {$this->table} h
                LEFT JOIN puroks p ON p.id = h.purok_id
                LEFT JOIN residents r ON r.id = h.head_resident_id
                WHERE (? = '' OR h.status = ?)
                ORDER BY h.id DESC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Household::getAll prepare failed: " . $this->conn->error);
            return [];
        }

        $statusParam = $status ?? '';
        $stmt->bind_param("ss", $statusParam, $statusParam);
        $stmt->execute();

        $result = $stmt->get_result();
        $rows = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        $stmt->close();
        return $rows;
    }

    /* =========================
       READ (SINGLE)
    ========================== */
    public function getById(int $id): ?array
    {
        $sql = "SELECT
                    h.*,
                    p.name AS purok_name,
                    CONCAT(r.first_name, ' ', r.last_name) AS head_name
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
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row ?: null;
    }

    /* =========================
       UPDATE
    ========================== */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table}
                SET purok_id = ?,
                    address_line = ?,
                    housing_type = ?,
                    head_resident_id = ?,
                    years_residing = ?,
                    landmark = ?,
                    household_type = ?,
                    tenure_status = ?,
                    monthly_income_range = ?,
                    income_source = ?,
                    employment_type = ?,
                    socio_economic_class = ?,
                    is_4ps_beneficiary = ?,
                    is_social_pension = ?,
                    is_tupad_beneficiary = ?,
                    is_akap_beneficiary = ?,
                    is_solo_parent_assistance = ?,
                    housing_status = ?,
                    house_material = ?,
                    water_source = ?,
                    electricity_access = ?,
                    toilet_facility = ?,
                    internet_access = ?,
                    has_vehicle = ?,
                    has_motorcycle = ?,
                    has_refrigerator = ?,
                    has_tv = ?,
                    has_washing_machine = ?,
                    has_aircon = ?,
                    has_computer = ?,
                    has_smartphone = ?,
                    land_ownership = ?,
                    business_ownership = ?,
                    highest_education = ?,
                    health_insurance = ?,
                    malnutrition_cases = ?,
                    registration_date = ?,
                    remarks = ?,
                    status = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Household::update prepare failed: " . $this->conn->error);
            return false;
        }

        $purok_id = $data['purok_id'] ?? null;
        $address_line = $data['address_line'] ?? null;
        $housing_type = $data['housing_type'] ?? null;
        $head_resident_id = $data['head_resident_id'] ?? null;
        $years_residing = $data['years_residing'] ?? null;
        $landmark = $data['landmark'] ?? null;

        $household_type = $data['household_type'] ?? null;
        $tenure_status = $data['tenure_status'] ?? null;
        $monthly_income_range = $data['monthly_income_range'] ?? null;
        $income_source = $data['income_source'] ?? null;
        $employment_type = $data['employment_type'] ?? null;
        $socio_economic_class = $data['socio_economic_class'] ?? null;

        $is_4ps_beneficiary = !empty($data['is_4ps_beneficiary']) ? 1 : 0;
        $is_social_pension = !empty($data['is_social_pension']) ? 1 : 0;
        $is_tupad_beneficiary = !empty($data['is_tupad_beneficiary']) ? 1 : 0;
        $is_akap_beneficiary = !empty($data['is_akap_beneficiary']) ? 1 : 0;
        $is_solo_parent_assistance = !empty($data['is_solo_parent_assistance']) ? 1 : 0;

        $housing_status = $data['housing_status'] ?? null;
        $house_material = $data['house_material'] ?? null;
        $water_source = $data['water_source'] ?? null;
        $electricity_access = $data['electricity_access'] ?? null;
        $toilet_facility = $data['toilet_facility'] ?? null;
        $internet_access = $data['internet_access'] ?? null;

        $has_vehicle = !empty($data['has_vehicle']) ? 1 : 0;
        $has_motorcycle = !empty($data['has_motorcycle']) ? 1 : 0;
        $has_refrigerator = !empty($data['has_refrigerator']) ? 1 : 0;
        $has_tv = !empty($data['has_tv']) ? 1 : 0;
        $has_washing_machine = !empty($data['has_washing_machine']) ? 1 : 0;
        $has_aircon = !empty($data['has_aircon']) ? 1 : 0;
        $has_computer = !empty($data['has_computer']) ? 1 : 0;
        $has_smartphone = !empty($data['has_smartphone']) ? 1 : 0;

        $land_ownership = $data['land_ownership'] ?? null;
        $business_ownership = $data['business_ownership'] ?? null;
        $highest_education = $data['highest_education'] ?? null;
        $health_insurance = $data['health_insurance'] ?? null;
        $malnutrition_cases = $data['malnutrition_cases'] ?? null;

        $registration_date = $data['registration_date'] ?? null;
        $remarks = $data['remarks'] ?? null;
        $status = $data['status'] ?? 'Active';

        $stmt->bind_param(
            "issiiissssss" .  // first 12
            "iiiii" .         // program flags 5
            "ssssss" .        // utilities 6
            "iiiiiiii" .      // assets 8
            "ss" .            // other assets 2
            "sss" .           // edu/insurance/malnutrition 3
            "sss" .           // reg_date/remarks/status 3
            "i",              // id 1
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
            $id
        );

        $ok = $stmt->execute();
        if (!$ok) {
            error_log("Household::update execute failed: " . $stmt->error);
        }
        $stmt->close();
        return $ok;
    }

    /* =========================
       SOFT DELETE (recommended)
    ========================== */
    public function deactivate(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET status = 'Inactive' WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /* =========================
       DASHBOARD HELPERS
    ========================== */
    public function countHouseholds(string $status = 'Active'): int
    {
        $sql = "SELECT COUNT(*) total FROM {$this->table} WHERE (? = '' OR status = ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return 0;

        $statusParam = $status ?? '';
        $stmt->bind_param("ss", $statusParam, $statusParam);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return (int)($row['total'] ?? 0);
    }

    public function householdsByPurok(): array
    {
        $sql = "SELECT p.name AS purok, COUNT(h.id) AS households
                FROM puroks p
                LEFT JOIN {$this->table} h
                    ON h.purok_id = p.id AND h.status = 'Active'
                GROUP BY p.id
                ORDER BY households DESC";

        $res = $this->conn->query($sql);
        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) $rows[] = $row;
        }
        return $rows;
    }

    /* =========================
       UTILITY: Generate household code
       Example: HH-2026-0001
    ========================== */
    public function generateCode(): string
    {
        $year = date('Y');
        $prefix = "HH-{$year}-";

        $sql = "SELECT household_code
                FROM {$this->table}
                WHERE household_code LIKE CONCAT(?, '%')
                ORDER BY id DESC
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
        $lastNum = (int)end($parts);
        $nextNum = $lastNum + 1;

        return $prefix . str_pad((string)$nextNum, 4, '0', STR_PAD_LEFT);
    }
}

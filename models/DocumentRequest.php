<?php

class DocumentRequest {

private $db;

public function __construct($db){
        $this->db = $db;

        }  
public function all() {
    $sql = "SELECT dr.*,
                   CONCAT_WS(' ', r.first_name, r.middle_name, r.last_name, r.suffix) AS resident_name,
                   dt.name AS document_name,
                   dt.category AS document_category
            FROM document_requests dr
            LEFT JOIN residents r ON r.id = dr.resident_id
            LEFT JOIN document_types dt ON dt.id = dr.document_type_id
            ORDER BY dr.id DESC";

    $res = $this->db->query($sql);
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}


public function updateStatus($id, $status, $adminId = null, $remarks = null)
{
    $id = (int)$id;
    $status = ucfirst(strtolower(trim((string)$status)));
    if ($id <= 0) return false;

    $approvedAt = null;
    $releasedAt = null;
    if ($status === 'Approved') $approvedAt = date('Y-m-d H:i:s');
    if ($status === 'Released') $releasedAt = date('Y-m-d H:i:s');

    // 1) Update request
    $sql = "UPDATE document_requests
            SET status = ?,
                approved_at = COALESCE(?, approved_at),
                released_at = COALESCE(?, released_at),
                processed_by = COALESCE(?, processed_by),
                remarks = COALESCE(?, remarks)
            WHERE id = ?";

    $stmt = $this->db->prepare($sql);
    if (!$stmt) return false;

    $adminIdParam = ($adminId === null || $adminId === '') ? null : (int)$adminId;

    $stmt->bind_param("sssisi", $status, $approvedAt, $releasedAt, $adminIdParam, $remarks, $id);
    $ok = $stmt->execute();
    $stmt->close();

    if (!$ok) return false;

    // 2) If Released: ensure cert_no + permit nos + payment with or_no
    if ($status === 'Released') {

        // get needed fields from request
        $stmt = $this->db->prepare("
            SELECT fee_snapshot, cert_no, form_no, business_plate_no, sticker_no
            FROM document_requests
            WHERE id = ?
            LIMIT 1
        ");
        if (!$stmt) return true;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $req = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $fee = (float)($req['fee_snapshot'] ?? 0);

        // generate cert_no if empty
        $certNo = trim((string)($req['cert_no'] ?? ''));
        if ($certNo === '') {
            $certNo = $this->generateNextCertNo();

            $stmt = $this->db->prepare("UPDATE document_requests SET cert_no = ? WHERE id = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("si", $certNo, $id);
                $stmt->execute();
                $stmt->close();
            }
        }

        // generate business permit numbers if empty
        $formNo = trim((string)($req['form_no'] ?? ''));
        $businessPlateNo = trim((string)($req['business_plate_no'] ?? ''));
        $stickerNo = trim((string)($req['sticker_no'] ?? ''));

        if ($formNo === '') {
            $formNo = $this->generateNextFormNo();
        }

        if ($businessPlateNo === '') {
            $businessPlateNo = $this->generateNextBusinessPlateNo();
        }

        if ($stickerNo === '') {
            $stickerNo = $this->generateNextStickerNo();
        }

        $stmt = $this->db->prepare("
            UPDATE document_requests
            SET form_no = ?,
                business_plate_no = ?,
                sticker_no = ?
            WHERE id = ?
            LIMIT 1
        ");
        if ($stmt) {
            $stmt->bind_param("sssi", $formNo, $businessPlateNo, $stickerNo, $id);
            $stmt->execute();
            $stmt->close();
        }

        // check existing payment
        $stmt = $this->db->prepare("SELECT id, or_no FROM payments WHERE request_id = ? LIMIT 1");
        if (!$stmt) return true;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // generate OR no
        $orNo = '';
        if (!$existing) {
            $orNo = $this->generateNextOrNo();

            $stmt = $this->db->prepare("
                INSERT INTO payments (request_id, or_no, amount, status, paid_at, created_at)
                VALUES (?, ?, ?, 'Paid', NOW(), NOW())
            ");
            if ($stmt) {
                $stmt->bind_param("isd", $id, $orNo, $fee);
                $stmt->execute();
                $stmt->close();
            }

        } else {
            // if payment exists but OR is empty, fill it + update amount
            $paymentId = (int)$existing['id'];
            $orNo = trim((string)($existing['or_no'] ?? ''));

            if ($orNo === '') {
                $orNo = $this->generateNextOrNo();
            }

            $stmt = $this->db->prepare("UPDATE payments SET or_no = ?, amount = ? WHERE id = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("sdi", $orNo, $fee, $paymentId);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    return true;
}

public function findById($id)
{
    $id = (int)$id;

    $sql = "SELECT 
                dr.*,
                dr.extra_json AS extra_json,
                dr.clearance_photo AS clearance_photo,

                TRIM(CONCAT(
                    COALESCE(r.first_name, ''), ' ',
                    COALESCE(r.middle_name, ''), ' ',
                    COALESCE(r.last_name, ''), ' ',
                    COALESCE(r.suffix, '')
                )) AS resident_name,

                COALESCE(r.contact_number, '') AS contact_no,

                CONCAT(
                    COALESCE(h.address_line, ''), ', ',
                    'Purok ', COALESCE(p.name, ''), ', ',
                    'Barangay Don Galo'
                ) AS resident_address,

                dt.name AS document_name,
                dt.category AS document_category,
                dt.fee AS document_fee,
                dt.processing_minutes,

                -- PAYMENT / PRINT FIELDS
                dr.fee_snapshot AS fee,
                COALESCE(pay.amount, dr.fee_snapshot, dt.fee, 0) AS amount_paid,
                pay.paid_at AS date_paid,
                COALESCE(dr.cert_no, '') AS cert_no,
                COALESCE(pay.or_no, dr.or_no, '') AS or_no,
                COALESCE(dr.form_no, '') AS form_no,
                COALESCE(dr.business_plate_no, '') AS business_plate_no,
                COALESCE(dr.sticker_no, '') AS sticker_no,
                
                dt.template_key,
                dt.extra_fields_json

            FROM document_requests dr
            LEFT JOIN residents r ON r.id = dr.resident_id
            LEFT JOIN households h ON h.id = r.household_id
            LEFT JOIN puroks p ON p.id = h.purok_id
            LEFT JOIN document_types dt ON dt.id = dr.document_type_id
            LEFT JOIN payments pay ON pay.request_id = dr.id
            WHERE dr.id = ?
            LIMIT 1";

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        error_log("DocumentRequest::findById prepare failed: " . $this->db->error);
        return null;
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;

    $stmt->close();

    return $row;
}

public function search($keyword = '')
{
    if ($keyword !== '') {

        $stmt = $this->db->prepare("
            SELECT dr.*, dt.name AS document_name,
                   CONCAT(r.first_name, ' ', r.last_name) AS resident_name
            FROM document_requests dr
            LEFT JOIN residents r ON r.id = dr.resident_id
            LEFT JOIN document_types dt ON dt.id = dr.document_type_id
            WHERE CONCAT(r.first_name, ' ', r.last_name) LIKE CONCAT('%', ?, '%')
               OR dr.ref_no LIKE CONCAT('%', ?, '%')
            ORDER BY dr.requested_at DESC
        ");

        $stmt->bind_param("ss", $keyword, $keyword);

    } else {

        $stmt = $this->db->prepare("
            SELECT dr.*, dt.name AS document_name,
                   CONCAT(r.first_name, ' ', r.last_name) AS resident_name
            FROM document_requests dr
            LEFT JOIN residents r ON r.id = dr.resident_id
            LEFT JOIN document_types dt ON dt.id = dr.document_type_id
            ORDER BY dr.requested_at DESC
        ");
    }

    $stmt->execute();
    return $stmt->get_result();
}

public function dashboardCounts()
{
    $sql = "
        SELECT
            SUM(status = 'Pending')  AS pending_count,
            SUM(status = 'Approved') AS approved_count,
            SUM(status = 'Released' AND DATE(released_at) = CURDATE()) AS released_today
        FROM document_requests
    ";

    $res = $this->db->query($sql);
    return $res ? ($res->fetch_assoc() ?: []) : [];
}

public function countByStatus(string $status): int
{
    $sql = "SELECT COUNT(*) AS c FROM document_requests WHERE status = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return (int)($res['c'] ?? 0);
}

public function countReleasedToday(): int
{

    $sql = "SELECT COUNT(*) AS c
            FROM document_requests
            WHERE status = 'Released'
              AND DATE(released_at) = CURDATE()";

    $stmt = $this->db->prepare($sql);


    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return (int)($res['c'] ?? 0);
}

public function createResidentRequest(int $residentId, int $documentTypeId, string $purpose, array $extra = [])
{
    $purpose = trim($purpose);

    if ($residentId <= 0 || $documentTypeId <= 0 || $purpose === '') {
        return false;
    }

    // Clean extra
    $cleanExtra = [];
    foreach ($extra as $k => $v) {
        if (is_array($v) || is_object($v)) continue;

        $key = trim((string)$k);
        if ($key === '') continue;

        $val = trim((string)$v);
        if ($val === '') continue;

        if (strlen($key) > 60)  $key = substr($key, 0, 60);
        if (strlen($val) > 500) $val = substr($val, 0, 500);

        $cleanExtra[$key] = $val;
    }

    $extraJson = !empty($cleanExtra)
        ? json_encode($cleanExtra, JSON_UNESCAPED_UNICODE)
        : null;

    // Fee snapshot from document_types
    $feeSnapshot = 0.00;
    $stmtFee = $this->db->prepare("SELECT fee FROM document_types WHERE id = ? LIMIT 1");
    if ($stmtFee) {
        $stmtFee->bind_param("i", $documentTypeId);
        $stmtFee->execute();
        $rowFee = $stmtFee->get_result()->fetch_assoc();
        $stmtFee->close();
        $feeSnapshot = (float)($rowFee['fee'] ?? 0);
    }

    // Generate REF-YYYY-0001
    $year   = date('Y');
    $prefix = "REF-$year-";

    $sqlLast = "SELECT ref_no
                FROM document_requests
                WHERE ref_no LIKE CONCAT(?, '%')
                ORDER BY id DESC
                LIMIT 1";

    $stmtLast = $this->db->prepare($sqlLast);
    if (!$stmtLast) {
        return false;
    }

    $stmtLast->bind_param("s", $prefix);
    $stmtLast->execute();
    $row = $stmtLast->get_result()->fetch_assoc();
    $lastRef = $row['ref_no'] ?? null;
    $stmtLast->close();

    $next = 1;
    if ($lastRef) {
        $num = (int)substr($lastRef, strlen($prefix));
        $next = $num + 1;
    }

    $refNo = $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);

    $sql = "INSERT INTO document_requests
            (ref_no, resident_id, document_type_id, purpose, status, fee_snapshot, requested_at, extra_json)
            VALUES (?, ?, ?, ?, 'Pending', ?, NOW(), ?)";

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "siisds",
        $refNo,
        $residentId,
        $documentTypeId,
        $purpose,
        $feeSnapshot,
        $extraJson
    );

    $ok = $stmt->execute();
    $stmt->close();

    return $ok ? ['ref_no' => $refNo] : false;
}


public function getByResident(int $residentId): array
{
        $sql = "SELECT
                dr.ref_no,
                dt.name AS document,
                dr.purpose,
                dr.fee_snapshot AS fee,
                dr.status,
                dr.requested_at
            FROM document_requests dr
            JOIN document_types dt ON dt.id = dr.document_type_id
            WHERE dr.resident_id = ?
            ORDER BY dr.requested_at DESC";


    $stmt = $this->db->prepare($sql);
    if (!$stmt) return [];

    $stmt->bind_param("i", $residentId);
    $stmt->execute();

    $rows = [];
    if (method_exists($stmt, 'get_result')) {
        $res = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    } else {
        // fallback if no mysqlnd
        $res = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    $stmt->close();
    return $rows;
}

public function incomeTotalReleased()
{
    $sql = "SELECT COALESCE(SUM(amount_paid),0) AS total
            FROM document_requests
            WHERE status='Released'";
    $res = $this->db->query($sql);
    $row = $res ? $res->fetch_assoc() : null;
    return (float)($row['total'] ?? 0);
}


public function incomeByDate($dateYmd)
{
    $sql = "SELECT COALESCE(SUM(amount_paid),0) AS total
            FROM document_requests
            WHERE status='Released' AND DATE(released_at)=?";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("s", $dateYmd);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (float)($row['total'] ?? 0);
}



public function statusCounts()
{
    $statuses = ['Pending','Approved','Rejected','Released'];
    $out = [];
    $sql = "SELECT COUNT(*) AS c FROM document_requests WHERE status=?";
    $stmt = $this->db->prepare($sql);

    foreach ($statuses as $st) {
        $stmt->bind_param("s", $st);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $out[$st] = (int)($row['c'] ?? 0);
    }
    return $out;
}

public function releasedTodayList($dateYmd, $limit = 20)
{
    $sql = "
        SELECT
            dr.ref_no,
            CONCAT(r.last_name, ', ', r.first_name, ' ', COALESCE(r.middle_name,'')) AS resident_name,
            dt.name AS document_type,
            COALESCE(pay.amount, dr.fee_snapshot, 0) AS amount_paid,
            dr.released_at
        FROM document_requests dr
        JOIN residents r ON r.id = dr.resident_id
        LEFT JOIN document_types dt ON dt.id = dr.document_type_id
        LEFT JOIN (
            SELECT request_id, SUM(amount) AS amount
            FROM payments
            GROUP BY request_id
        ) pay ON pay.request_id = dr.id

        WHERE dr.status = 'Released'
          AND DATE(dr.released_at) = ?
        ORDER BY dr.released_at DESC
        LIMIT ?
    ";

    $stmt = $this->db->prepare($sql);
    if (!$stmt) return [];

    $stmt->bind_param("si", $dateYmd, $limit);
    $stmt->execute();

    $res = $stmt->get_result();
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    return $rows;
}

public function releasedTodaySummary($dateYmd): array
{
    $sql = "
        SELECT
            COUNT(*) AS total_count,
            COALESCE(SUM(COALESCE(pay.amount, dr.fee_snapshot, 0)), 0) AS total_amount
        FROM document_requests dr
        LEFT JOIN (
            SELECT request_id, SUM(amount) AS amount
            FROM payments
            GROUP BY request_id
        ) pay ON pay.request_id = dr.id
        WHERE dr.status = 'Released'
          AND DATE(dr.released_at) = ?
    ";

    $stmt = $this->db->prepare($sql);
    if (!$stmt) return ['total_count' => 0, 'total_amount' => 0];

    $stmt->bind_param("s", $dateYmd);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: [];
    $stmt->close();

    return [
        'total_count'  => (int)($row['total_count'] ?? 0),
        'total_amount' => (float)($row['total_amount'] ?? 0),
    ];
}


private function generateNextCertNo(): string
{
    $year = date('Y');
    $prefix = "DG-$year-";

    $stmt = $this->db->prepare("
        SELECT cert_no
        FROM document_requests
        WHERE cert_no LIKE CONCAT(?, '%')
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->bind_param("s", $prefix);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $last = $row['cert_no'] ?? null;
    $next = 1;

    if ($last) {
        $num = (int)substr($last, strlen($prefix)); // DG-2026-000100 -> 000100
        $next = $num + 1;
    }

    return $prefix . str_pad((string)$next, 6, '0', STR_PAD_LEFT);
}

private function generateNextOrNo(): string
{
    $prefix = "OR-";

    $stmt = $this->db->prepare("
        SELECT or_no
        FROM payments
        WHERE or_no LIKE CONCAT(?, '%')
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->bind_param("s", $prefix);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $last = $row['or_no'] ?? null;
    $next = 1;

    if ($last) {
        $num = (int)substr($last, strlen($prefix)); // OR-00001 -> 00001
        $next = $num + 1;
    }

    return $prefix . str_pad((string)$next, 5, '0', STR_PAD_LEFT);
}

private function generateNextFormNo(): string
{
    $prefix = "FORM-" . date('Y') . "-";

    $stmt = $this->db->prepare("
        SELECT form_no
        FROM document_requests
        WHERE form_no IS NOT NULL
          AND form_no <> ''
          AND form_no LIKE CONCAT(?, '%')
        ORDER BY form_no DESC
        LIMIT 1
    ");

    if (!$stmt) {
        throw new \Exception("Prepare failed in generateNextFormNo(): " . $this->db->error);
    }

    $stmt->bind_param("s", $prefix);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $last = $row['form_no'] ?? null;
    $next = 1;

    if ($last && str_starts_with($last, $prefix)) {
        $num = (int)substr($last, strlen($prefix));
        if ($num > 0) {
            $next = $num + 1;
        }
    }

    return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
}

private function generateNextBusinessPlateNo(): string
{
    $prefix = "BPLATE-" . date('Y') . "-";

    $stmt = $this->db->prepare("
        SELECT business_plate_no
        FROM document_requests
        WHERE business_plate_no IS NOT NULL
          AND business_plate_no <> ''
          AND business_plate_no LIKE CONCAT(?, '%')
        ORDER BY business_plate_no DESC
        LIMIT 1
    ");

    if (!$stmt) {
        throw new \Exception("Prepare failed in generateNextBusinessPlateNo(): " . $this->db->error);
    }

    $stmt->bind_param("s", $prefix);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $last = $row['business_plate_no'] ?? null;
    $next = 1;

    if ($last && str_starts_with($last, $prefix)) {
        $num = (int)substr($last, strlen($prefix));
        if ($num > 0) {
            $next = $num + 1;
        }
    }

    return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
}

private function generateNextStickerNo(): string
{
    $prefix = "STICKER-" . date('Y') . "-";

    $stmt = $this->db->prepare("
        SELECT sticker_no
        FROM document_requests
        WHERE sticker_no IS NOT NULL
        AND sticker_no <> ''
        AND sticker_no LIKE CONCAT(?, '%')
        ORDER BY sticker_no DESC
        LIMIT 1
    ");

    if (!$stmt) {
        throw new \Exception("Prepare failed in generateNextStickerNo(): " . $this->db->error);
    }

    $stmt->bind_param("s", $prefix);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $last = $row['sticker_no'] ?? null;
    $next = 1;

    if ($last && str_starts_with($last, $prefix)) {
        $num = (int)substr($last, strlen($prefix));
        if ($num > 0) {
            $next = $num + 1;
        }
    }

    return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
}

}
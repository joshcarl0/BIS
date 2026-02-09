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


public function updateStatus($id, $status, $adminId = null, $remarks = null) {
// for Pending/ Approved/ Rejected/Released

    $status = ucfirst(strtolower($status)); 

    $approvedAt = null;
    $releasedAt = null;

    if ($status === 'Approved') $approvedAt = date('Y-m-d H:i:s');
        if ($status === 'Released') $releasedAt = date('Y-m-d H:i:s');

        $sql = "UPDATE document_requests
                SET status = ?,
                    approved_at = COALESCE(?, approved_at),
                    released_at = COALESCE(?, released_at),
                    processed_by = COALESCE(?, processed_by),
                    remarks = COALESCE(?, remarks)
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param(
            "sssssi",
            $status,
            $approvedAt,
            $releasedAt,
            $adminId,
            $remarks,
            $id
        );
        return $stmt->execute();
    }

public function findById($id)
{
    $id = (int)$id;

    $sql = "SELECT dr.*,
                   TRIM(CONCAT(
                        r.first_name, ' ',
                        IFNULL(r.middle_name, ''), ' ',
                        r.last_name, ' ',
                        IFNULL(r.suffix, '')
                   )) AS resident_name,
                   dt.name AS document_name,
                   dt.category AS document_category,
                   dt.fee AS document_fee,
                   dt.processing_minutes
            FROM document_requests dr
            LEFT JOIN residents r ON r.id = dr.resident_id
            LEFT JOIN document_types dt ON dt.id = dr.document_type_id
            WHERE dr.id = ?
            LIMIT 1";

    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $res = $stmt->get_result();
    return $res ? $res->fetch_assoc() : null;
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

public function createResidentRequest(int $residentId, int $documentTypeId, string $purpose)
{
    $purpose = trim($purpose);

    if ($residentId <= 0 || $documentTypeId <= 0 || $purpose === '') {
        return false;
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
    if (!$stmtLast) return false;

    $stmtLast->bind_param("s", $prefix);
    $stmtLast->execute();

    $lastRef = null;
    if (method_exists($stmtLast, 'get_result')) {
        $res = $stmtLast->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $lastRef = $row['ref_no'] ?? null;
    }
    $stmtLast->close();

    $next = 1;
    if ($lastRef) {
        $num  = (int)substr($lastRef, strlen($prefix)); // 0001
        $next = $num + 1;
    }

    $refNo = $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);

    $sql = "INSERT INTO document_requests
            (ref_no, resident_id, document_type_id, purpose, status, fee_snapshot, requested_at)
            VALUES (?, ?, ?, ?, 'Pending', 0.00, NOW())";

    $stmt = $this->db->prepare($sql);
    if (!$stmt) return false;

    $stmt->bind_param("siis", $refNo, $residentId, $documentTypeId, $purpose);

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
        $sql = "SELECT dr.ref_no,
                CONCAT(r.last_name, ', ', r.first_name, ' ', COALESCE(r.middle_name,'')) AS resident_name,
                dt.name AS document_type,
                dr.amount_paid,
                dr.released_at
            FROM document_requests dr
            JOIN residents r ON r.id = dr.resident_id
            LEFT JOIN document_types dt ON dt.id = dr.document_type_id
            WHERE dr.status='Released' AND DATE(dr.released_at)=?
            ORDER BY dr.released_at DESC
            LIMIT $limit";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("s", $dateYmd);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}







}
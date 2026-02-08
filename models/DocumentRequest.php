<?php

class DocumentRequest {

private $db;

public function __construct($db){
        $this->db = $db;

        }  
public function all() {

// connecting the important

$sql = " SELECT dr.*,
                r.full_name AS resident_name
                dt.name AS document_name,
                dt.category AS document_category
        FROM document_requests dr
        LEFT JOIN residents r ON r.id = dr.residents_id
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

}
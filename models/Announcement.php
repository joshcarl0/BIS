<?php

class Announcement {

private $conn;

public function __construct($db) {
$this->conn = $db;

}

// Get all the announecement



public function all(): array {

   $sql = "SELECT a.id, a.title, a.details, a.status, a.date_posted, a.posted_by,
                       u.full_name AS posted_by_name,
                       (SELECT COUNT(*) FROM announcement_attachments aa WHERE aa.announcement_id = a.id) AS attachments_count
                FROM announcements a
                JOIN users u ON u.id = a.posted_by
                ORDER BY a.date_posted DESC";

        $res = $this->conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

        foreach ($rows as &$r)
            $r ['attachments'] = $this->attachments((int)$r['id']);

        return $rows;
}

public function find(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM announcements WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row ?: null;
    }


// Insert announcements

public function create(string $title,string $details,int $posted_by,string $status= 'Active'): int 

{
    $stmt = $this->conn->prepare("
            INSERT INTO announcements (title, details, posted_by, status)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssis", $title, $details, $posted_by, $status);
        $stmt->execute();

        return (int)$this->conn->insert_id;
}


public function update(int $id,string $title,string $details,string $status= 'Active'): bool

{
    $stmt = $this->conn->prepare("
             UPDATE announcements
            SET title = ?, details = ?, status = ?
            WHERE id = ?
        ");

        $stmt->bind_param("ssis", $title, $details, $status, $id);
        return $stmt->execute();

}

public function delete(int $id): bool
{

$stmt = $this->conn->prepare("DELETE FROM announcements WHERE id = ?");
$stmt->bind_param("i", $id);
return $stmt->execute();

}

  public function attachments(int $announcement_id): array
{
                $stmt= $this->conn->prepare("
            SELECT * FROM announcement_attachments
                        WHERE announcement_id = ?
                        ORDER BY uploaded_at DESC");

                        
            $stmt->bind_param("i", $announcement_id);
                $stmt->execute();
                $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

public function addAttachments(
    int $announcement_id,
        string $file_name,
        string $file_path,
        string $file_type,
        int $file_size
): bool {

    $stmt = $this->conn->prepare("
            INSERT INTO announcement_attachments (announcement_id, file_name, file_path, file_type, file_size)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssi", $announcement_id, $file_name, $file_path, $file_type, $file_size);
        return $stmt->execute();
    }

public function active()
{
    $sql = "SELECT a.*,
                   aa.file_name AS attachment_name,
                   aa.file_path AS attachment_path,
                   aa.file_type AS attachment_type
            FROM announcements a
            LEFT JOIN announcement_attachments aa
              ON aa.announcement_id = a.id
             AND aa.id = (
                SELECT MAX(id)
                FROM announcement_attachments
                WHERE announcement_id = a.id
             )
            WHERE a.status = 'Active'
            ORDER BY a.date_posted DESC, a.id DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}




    }













<?php
class ActivityLog {
    private $db;
    private $table = "activity_logs";

    public function __construct($db) {
        $this->db = $db;
    }

    public function add($actorId, $actorRole, $action, $entityType, $entityId, $description) {
        $sql = "INSERT INTO {$this->table}
                (actor_id, actor_role, action, entity_type, entity_id, description)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("isssis", $actorId, $actorRole, $action, $entityType, $entityId, $description);
        return $stmt->execute();
    }

    public function latest($limit = 10) {
        $limit = (int)$limit;

        $sql = "SELECT id, actor_id, actor_role, action, entity_type, entity_id, description, created_at
                FROM {$this->table}
                ORDER BY created_at DESC
                LIMIT $limit";

        $res = $this->db->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }


    public function countAll(): int {
        $sql = "SELECT COUNT(*) AS c FROM {$this->table}";
        $res = $this->db->query($sql);
        $row = $res ? $res->fetch_assoc() : null;
        return (int)($row['c'] ?? 0);
    }

    public function latestPage(int $limit = 10, int $offset = 0): array {
        $limit = max(1, $limit);
        $offset = max(0, $offset);

        $sql = "SELECT id, actor_id, actor_role, action, entity_type, entity_id, description, created_at
                FROM {$this->table}
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

}

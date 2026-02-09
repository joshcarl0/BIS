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
}

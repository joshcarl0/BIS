<?php
class Official {
    private mysqli $conn;
    private string $table = "officials";

    public function __construct(mysqli $db) {
        $this->conn = $db;
    }

    public function all(): array {
        $sql = "SELECT * FROM {$this->table} ORDER BY status='Active' DESC, position ASC, full_name ASC";
        $res = $this->conn->query($sql);
        if (!$res) return [];
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function find(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id=? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        return $row ?: null;
    }

    public function create(array $data): array {
        $position = trim($data['position'] ?? '');
        $full_name = trim($data['full_name'] ?? '');
        $committee = trim($data['committee'] ?? '');
        $term_start = $data['term_start'] ?? null;
        $term_end = $data['term_end'] ?? null;
        $contact = trim($data['contact'] ?? '');
        $email = trim($data['email'] ?? '');
        $status = ($data['status'] ?? 'Active') === 'Inactive' ? 'Inactive' : 'Active';
        $photo = $data['photo'] ?? null;

        if ($position === '' || $full_name === '') {
            return ['ok' => false, 'msg' => 'Position and Full Name are required.'];
        }

        $stmt = $this->conn->prepare("
            INSERT INTO {$this->table}
            (position, full_name, committee, term_start, term_end, contact, email, status, photo)
            VALUES (?,?,?,?,?,?,?,?,?)
        ");
        if (!$stmt) return ['ok' => false, 'msg' => 'DB error: prepare failed'];

        $stmt->bind_param(
            "sssssssss",
            $position, $full_name, $committee, $term_start, $term_end, $contact, $email, $status, $photo
        );

        $ok = $stmt->execute();
        return $ok ? ['ok' => true] : ['ok' => false, 'msg' => 'DB error: execute failed'];
    }

    public function update(int $id, array $data): array {
        $position = trim($data['position'] ?? '');
        $full_name = trim($data['full_name'] ?? '');
        $committee = trim($data['committee'] ?? '');
        $term_start = $data['term_start'] ?? null;
        $term_end = $data['term_end'] ?? null;
        $contact = trim($data['contact'] ?? '');
        $email = trim($data['email'] ?? '');
        $status = ($data['status'] ?? 'Active') === 'Inactive' ? 'Inactive' : 'Active';
        $photo = $data['photo'] ?? null;

        if ($position === '' || $full_name === '') {
            return ['ok' => false, 'msg' => 'Position and Full Name are required.'];
        }

        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET position=?, full_name=?, committee=?, term_start=?, term_end=?, contact=?, email=?, status=?, photo=?
            WHERE id=?
        ");
        if (!$stmt) return ['ok' => false, 'msg' => 'DB error: prepare failed'];

        $stmt->bind_param(
            "sssssssssi",
            $position, $full_name, $committee, $term_start, $term_end, $contact, $email, $status, $photo, $id
        );

        $ok = $stmt->execute();
        return $ok ? ['ok' => true] : ['ok' => false, 'msg' => 'DB error: execute failed'];
    }

    public function delete(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}

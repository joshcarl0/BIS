<?php

class PasswordReset
{
    private mysqli $conn;

    public function __construct(mysqli $db)
    {
        $this->conn = $db;
    }

    public function create(int $userId, string $token, string $expires): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO password_resets (user_id, token, expires_at, used)
             VALUES (?, ?, ?, 0)"
        );
        $stmt->bind_param('iss', $userId, $token, $expires);
        return $stmt->execute();
    }

    public function findValid(string $token): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM password_resets
             WHERE token = ? AND used = 0 AND expires_at > NOW()
             LIMIT 1"
        );
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc() ?: null;
    }

    public function markUsed(int $id): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE password_resets SET used = 1 WHERE id = ?"
        );
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}

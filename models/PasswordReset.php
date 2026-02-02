<?php

class PasswordReset
{
    private mysqli $conn;
    private string $table = 'password_resets';

    public function __construct(mysqli $db)
    {
        $this->conn = $db;
    }

    /** Create reset row for BOTH methods: link + otp */
    public function createBoth(int $userId, string $tokenHash, string $expiresAt, string $otpHash, string $otpExpiresAt): bool
    {
        // optional cleanup: invalidate previous resets for this user
        $this->deleteActiveByUserId($userId);

        $sql = "INSERT INTO {$this->table} (user_id, token, expires_at, used, otp_hash, otp_expires_at, otp_used, otp_attempts)
                VALUES (?, ?, ?, 0, ?, ?, 0, 0)";
        $stmt = $this->conn->prepare($sql);
        if (! $stmt) return false;

        $stmt->bind_param('issss', $userId, $tokenHash, $expiresAt, $otpHash, $otpExpiresAt);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    /** Validate link token: returns reset row or null */
    public function findValidByTokenHash(string $tokenHash): ?array
    {
        $sql = "SELECT id, user_id, expires_at, used
                FROM {$this->table}
                WHERE token = ? AND used = 0 AND expires_at > NOW()
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (! $stmt) return null;

        $stmt->bind_param('s', $tokenHash);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        return $row ?: null;
    }

    /** Validate OTP: returns reset row or null (rate-limited) */
    public function findValidByOtpHash(string $otpHash): ?array
    {
        $sql = "SELECT id, user_id, otp_expires_at, otp_used, otp_attempts
                FROM {$this->table}
                WHERE otp_hash = ? AND otp_used = 0 AND otp_expires_at > NOW()
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (! $stmt) return null;

        $stmt->bind_param('s', $otpHash);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        // basic rate limit: max 5 tries
        if ($row && (int)$row['otp_attempts'] >= 5) {
            return null;
        }

        return $row ?: null;
    }

    public function incrementOtpAttempts(int $id): void
    {
        $sql = "UPDATE {$this->table} SET otp_attempts = otp_attempts + 1 WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (! $stmt) return;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    public function markTokenUsed(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET used = 1 WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (! $stmt) return false;
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function markOtpUsed(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET otp_used = 1 WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (! $stmt) return false;
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    private function deleteActiveByUserId(int $userId): void
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (! $stmt) return;
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
    }
}

<?php

class ResidentRegistration
{
    private mysqli $conn;
    private string $table = 'resident_registrations';

    public function __construct(mysqli $db)
    {
        $this->conn = $db;
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ref_no VARCHAR(30) NOT NULL UNIQUE,
            full_name VARCHAR(150) NOT NULL,
            username VARCHAR(100) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            email VARCHAR(190) NOT NULL,
            contact_number VARCHAR(30) NULL,
            otp_hash VARCHAR(64) NULL,
            otp_expires_at DATETIME NULL,
            otp_attempts INT UNSIGNED NOT NULL DEFAULT 0,
            otp_verified_at DATETIME NULL,
            id_file_path VARCHAR(255) NULL,
            id_file_name VARCHAR(255) NULL,
            status ENUM('pending_otp','pending_id','pending_approval','approved','rejected') NOT NULL DEFAULT 'pending_otp',
            user_id INT UNSIGNED NULL,
            approved_at DATETIME NULL,
            approved_by INT UNSIGNED NULL,
            admin_notes TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_username (username),
            INDEX idx_status (status),
            INDEX idx_email (email),
            INDEX idx_username (username),
            CONSTRAINT uq_rr_user UNIQUE (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->conn->query($sql);
    }

    public function generateReferenceNumber(): string
    {
        $year = date('Y');
        do {
            $rand = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $ref = "REG-{$year}-{$rand}";
        } while ($this->findByReference($ref));

        return $ref;
    }

   public function hasUsernameOrEmail(string $username, string $email): bool
{
    $sql = "SELECT id
            FROM {$this->table}
            WHERE (username = ? OR email = ?)
              AND status <> 'rejected'
            LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) return false;
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}


    public function createPendingOtp(string $refNo, string $fullName, string $username, string $email, string $contactNumber, string $passwordHash, string $otpHash, string $otpExpiresAt): ?int
    {
        $sql = "INSERT INTO {$this->table}
                (ref_no, full_name, username, password_hash, email, contact_number, otp_hash, otp_expires_at, otp_attempts, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 'pending_otp')";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param('ssssssss', $refNo, $fullName, $username, $passwordHash, $email, $contactNumber, $otpHash, $otpExpiresAt);
        $ok = $stmt->execute();
        $id = $ok ? (int) $this->conn->insert_id : null;
        $stmt->close();

        return $id;
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        return $row ?: null;
    }

    public function findByReference(string $refNo): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE ref_no = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param('s', $refNo);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        return $row ?: null;
    }

    public function findLatestByLoginIdentifier(string $identifier): ?array
    {
        $sql = "SELECT id, ref_no, status, email, username
                FROM {$this->table}
                WHERE email = ? OR username = ?
                ORDER BY id DESC
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param('ss', $identifier, $identifier);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        return $row ?: null;
    }

    public function incrementOtpAttempts(int $id): void
    {
        $sql = "UPDATE {$this->table} SET otp_attempts = otp_attempts + 1 WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    public function verifyOtpAndMoveToPendingId(int $id): bool
{
    $status = 'pending_id';
    $sql = "UPDATE {$this->table}
            SET otp_verified_at = NOW(),
                status = ?,
                otp_attempts = 0
            WHERE id = ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) return false;
    $stmt->bind_param("si", $status, $id);
    return $stmt->execute();
}


    public function resendOtp(int $id, string $otpHash, string $otpExpiresAt): bool
    {
        $sql = "UPDATE {$this->table}
                SET otp_hash = ?, otp_expires_at = ?, otp_attempts = 0
                WHERE id = ? AND status = 'pending_otp'
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('ssi', $otpHash, $otpExpiresAt, $id);
        $ok = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        return $ok && $affected > 0;
    }

    public function saveValidIdAndSubmit(int $id, string $filePath, string $fileName): bool
    {
        $sql = "UPDATE {$this->table}
                SET id_file_path = ?, id_file_name = ?, status = 'pending_approval'
                WHERE id = ? AND status = 'pending_id'
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('ssi', $filePath, $fileName, $id);
        $ok = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        return $ok && $affected > 0;
    }

    public function getPendingApprovalList(string $search = ''): array
    {
        $search = trim($search);

        if ($search !== '') {
            $like = '%' . $search . '%';
            $sql = "SELECT rr.*, u.full_name AS approver_name
                    FROM {$this->table} rr
                    LEFT JOIN users u ON u.id = rr.approved_by
                    WHERE rr.ref_no LIKE ? OR rr.full_name LIKE ? OR rr.email LIKE ?
                    ORDER BY rr.created_at DESC";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return [];
            $stmt->bind_param('sss', $like, $like, $like);
        } else {
            $sql = "SELECT rr.*, u.full_name AS approver_name
                    FROM {$this->table} rr
                    LEFT JOIN users u ON u.id = rr.approved_by
                    ORDER BY rr.created_at DESC";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return [];
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($res && ($row = $res->fetch_assoc())) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    public function approve(int $id, int $approverId, int $userId, ?string $notes = null): bool
    {
        $sql = "UPDATE {$this->table}
                SET status = 'approved', user_id = ?, approved_at = NOW(), approved_by = ?, admin_notes = ?
                WHERE id = ? AND status = 'pending_approval' LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('iisi', $userId, $approverId, $notes, $id);
        $ok = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $ok && $affected > 0;
    }

    public function reject(int $id, int $approverId, ?string $notes = null): bool
    {
        $sql = "UPDATE {$this->table}
                SET status = 'rejected', approved_at = NOW(), approved_by = ?, admin_notes = ?
                WHERE id = ? AND status IN ('pending_otp','pending_id','pending_approval') LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('isi', $approverId, $notes, $id);
        $ok = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $ok && $affected > 0;
    }


public function createPendingRegistration(array $data): array
{
    $full = trim((string)($data['full_name'] ?? ''));
    $user = trim((string)($data['username'] ?? ''));
    $email = trim((string)($data['email'] ?? ''));
    $contact = (string)($data['contact_number'] ?? '');
    $password = (string)($data['password'] ?? '');

    if ($full === '' || $user === '' || $email === '' || $password === '') {
        throw new InvalidArgumentException('Missing required fields.');
    }

    if ($this->hasUsernameOrEmail($user, $email)) {
        throw new RuntimeException('Username or email already used in registrations.');
    }

    $refNo = $this->generateReferenceNumber();

    $otp = (string) random_int(100000, 999999);
    $otpHash = hash('sha256', $otp);
    $otpExpiresAt = date('Y-m-d H:i:s', time() + 600);

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $id = $this->createPendingOtp(
        $refNo,
        $full,
        $user,
        $email,
        $contact,
        $passwordHash,
        $otpHash,
        $otpExpiresAt
    );

    if (!$id) {
        throw new RuntimeException('Failed to create pending registration.');
    }

    return [
        'id' => $id,
        'ref_no' => $refNo,
        'otp' => $otp,
    ];
}

public function updateStatus(int $id, string $status): bool {
    $sql = "UPDATE {$this->table} SET status = ? WHERE id = ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("si", $status, $id);
    return $stmt->execute();
}

public function markOtpVerified(int $id): bool {
    $sql = "UPDATE {$this->table}
            SET otp_verified_at = NOW()
            WHERE id = ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

public function attachIdAndSetPending(int $id, string $path, string $name): bool
{
    $status = 'pending_approval';
    $sql = "UPDATE {$this->table}
            SET id_file_path = ?, id_file_name = ?, status = ?
            WHERE id = ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) return false;
    $stmt->bind_param("sssi", $path, $name, $status, $id);
    return $stmt->execute();
}

public function retryRejected(int $id, array $data): array
{
    $full = trim((string)($data['full_name'] ?? ''));
    $user = trim((string)($data['username'] ?? ''));
    $email = trim((string)($data['email'] ?? ''));
    $contact = (string)($data['contact_number'] ?? '');
    $password = (string)($data['password'] ?? '');

    if ($full === '' || $user === '' || $email === '' || $password === '') {
        throw new InvalidArgumentException('Missing required fields.');
    }

    // block username/email kung may active/pending registration na iba (hindi rejected)
    $sql = "SELECT id FROM {$this->table}
            WHERE id <> ?
              AND (username = ? OR email = ?)
              AND status <> 'rejected'
            LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) throw new RuntimeException('DB prepare failed.');
    $stmt->bind_param('iss', $id, $user, $email);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();

    if ($exists) {
        throw new RuntimeException('Username or email already used in registrations.');
    }

    $refNo = $this->generateReferenceNumber();
    $otp = (string) random_int(100000, 999999);
    $otpHash = hash('sha256', $otp);
    $otpExpiresAt = date('Y-m-d H:i:s', time() + 600);
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "UPDATE {$this->table}
            SET ref_no = ?,
                full_name = ?,
                username = ?,
                email = ?,
                contact_number = ?,
                password_hash = ?,
                otp_hash = ?,
                otp_expires_at = ?,
                otp_attempts = 0,
                otp_verified_at = NULL,
                id_file_path = NULL,
                id_file_name = NULL,
                approved_at = NULL,
                approved_by = NULL,
                admin_notes = NULL,
                status = 'pending_otp'
            WHERE id = ? AND status = 'rejected'
            LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) throw new RuntimeException('DB prepare failed.');
    $stmt->bind_param(
        'ssssssssi',
        $refNo, $full, $user, $email, $contact, $passwordHash, $otpHash, $otpExpiresAt,
        $id
    );
    $ok = $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if (!$ok || $affected <= 0) {
        throw new RuntimeException('Retry failed.');
    }

    return ['id' => $id, 'ref_no' => $refNo, 'otp' => $otp];
}


public function findLatestByEmail(string $email): ?array
{
    $sql = "SELECT * FROM {$this->table} WHERE email = ? ORDER BY id DESC LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) return null;
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}


}

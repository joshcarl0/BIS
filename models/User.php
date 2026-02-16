<?php

class User
{
    private $conn;
    private $table = 'users';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /* =========================
       AUTH
    ========================= */

    public function login($username, $password)
    {
        $sql = "SELECT u.id, u.username, u.password, u.status, u.email, u.full_name,
                       u.role_id, r.role_name AS role
                FROM {$this->table} u
                LEFT JOIN roles r ON r.id = u.role_id
                WHERE u.username = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log('User::login prepare failed: ' . $this->conn->error);
            return false;
        }

        $stmt->bind_param('s', $username);
        $stmt->execute();

        $user = null;
        if (method_exists($stmt, 'get_result')) {
            $res = $stmt->get_result();
            $user = $res ? $res->fetch_assoc() : null;
        } else {
            // Fallback for environments without mysqlnd
            $id = $u = $pwd = $status = $email = $full_name = $role = null;
            $role_id = null;
            $stmt->bind_result($id, $u, $pwd, $status, $email, $full_name, $role_id, $role);
            if ($stmt->fetch()) {
                $user = [
                    'id' => $id,
                    'username' => $u,
                    'password' => $pwd,
                    'status' => $status,
                    'email' => $email,
                    'full_name' => $full_name,
                    'role_id' => $role_id,
                    'role' => $role,
                ];
            }
        }

        $stmt->close();

        if ($user) {
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                unset($user['password']);
                return $user;
            }
        }

        return false;
    }

    public function isUsernameTaken($username)
    {
        $sql = "SELECT id FROM {$this->table} WHERE username = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        $taken = $stmt->num_rows > 0;
        $stmt->close();

        return $taken;
    }

    public function isEmailTaken($email)
    {
        $sql = "SELECT id FROM {$this->table} WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        $taken = $stmt->num_rows > 0;
        $stmt->close();

        return $taken;
    }

    // Default role_id = 3 (resident)
    public function register($username, $email, $password, $full_name, $role_id = 3, $status = 'active')
    {
        if ($this->isUsernameTaken($username) || $this->isEmailTaken($email)) {
            return false;
        }

        $sql = "INSERT INTO {$this->table}
                (username, email, password, full_name, role_id, status)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log('User::register prepare failed: ' . $this->conn->error);
            return false;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param('ssssis', $username, $email, $hashed, $full_name, $role_id, $status);

        $ok = $stmt->execute();
        $newId = $ok ? $this->conn->insert_id : false;
        if (!$ok) error_log('User::register execute failed: ' . $stmt->error);

        $stmt->close();
        return $newId;
    }

    /* =========================
       FETCH USER
    ========================= */

    public function getUserById($id)
    {
        $sql = "SELECT u.id, u.username, u.email, u.full_name, u.status,
                       u.role_id, r.role_name AS role
                FROM {$this->table} u
                LEFT JOIN roles r ON r.id = u.role_id
                WHERE u.id = ? LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $user = null;
        if (method_exists($stmt, 'get_result')) {
            $res = $stmt->get_result();
            $user = $res ? $res->fetch_assoc() : null;
        }

        $stmt->close();
        return $user;
    }

    public function updatePassword($userId, $newPassword)
    {
        $sql = "UPDATE {$this->table} SET password = ? WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt->bind_param('si', $hashed, $userId);

        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /* =========================
       USER MANAGEMENT (ADMIN)
    ========================= */

    public function getAllUsers(string $search = ''): array
    {
        $search = trim($search);

        if ($search !== '') {
            $like = '%' . $search . '%';
            $sql = "SELECT u.id, u.username, u.email, u.full_name, u.status, u.created_at,
                           u.role_id, r.role_name AS role
                    FROM {$this->table} u
                    LEFT JOIN roles r ON r.id = u.role_id
                    WHERE u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?
                    ORDER BY u.id DESC";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return [];
            $stmt->bind_param('sss', $like, $like, $like);
        } else {
            $sql = "SELECT u.id, u.username, u.email, u.full_name, u.status, u.created_at,
                           u.role_id, r.role_name AS role
                    FROM {$this->table} u
                    LEFT JOIN roles r ON r.id = u.role_id
                    ORDER BY u.id DESC";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return [];
        }

        $stmt->execute();

        $rows = [];
        if (method_exists($stmt, 'get_result')) {
            $res = $stmt->get_result();
            while ($res && ($row = $res->fetch_assoc())) {
                $rows[] = $row;
            }
        }

        $stmt->close();
        return $rows;
    }

    public function getUserByIdAdmin(int $id): ?array
    {
        return $this->getUserById($id);
    }

    public function isUsernameTakenExcept(string $username, int $excludeId): bool
    {
        $sql = "SELECT id FROM {$this->table} WHERE username = ? AND id != ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param('si', $username, $excludeId);
        $stmt->execute();
        $stmt->store_result();
        $taken = $stmt->num_rows > 0;
        $stmt->close();
        return $taken;
    }

    public function isEmailTakenExcept(string $email, int $excludeId): bool
    {
        $sql = "SELECT id FROM {$this->table} WHERE email = ? AND id != ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param('si', $email, $excludeId);
        $stmt->execute();
        $stmt->store_result();
        $taken = $stmt->num_rows > 0;
        $stmt->close();
        return $taken;
    }

    // role_id: 1 admin, 2 official, 3 resident
    public function adminCreateUser(string $username, string $email, string $password, string $full_name, int $role_id, string $status): bool
    {
        if ($this->isUsernameTaken($username) || $this->isEmailTaken($email)) return false;

        $status = ($status === 'inactive') ? 'inactive' : 'active';
        if (!in_array($role_id, [1,2,3], true)) $role_id = 3; // default resident

        $sql = "INSERT INTO {$this->table} (username, email, password, full_name, role_id, status)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param('ssssis', $username, $email, $hash, $full_name, $role_id, $status);

        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function adminUpdateUser(int $id, string $username, string $email, string $full_name, int $role_id, string $status, string $newPassword = ''): bool
    {
        if ($this->isUsernameTakenExcept($username, $id) || $this->isEmailTakenExcept($email, $id)) return false;

        $status = ($status === 'inactive') ? 'inactive' : 'active';
        if (!in_array($role_id, [1,2,3], true)) $role_id = 3;

        if (trim($newPassword) !== '') {
            $sql = "UPDATE {$this->table}
                    SET username=?, email=?, password=?, full_name=?, role_id=?, status=?
                    WHERE id=? LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return false;

            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt->bind_param('ssssisi', $username, $email, $hash, $full_name, $role_id, $status, $id);
        } else {
            $sql = "UPDATE {$this->table}
                    SET username=?, email=?, full_name=?, role_id=?, status=?
                    WHERE id=? LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return false;

            $stmt->bind_param('sssisi', $username, $email, $full_name, $role_id, $status, $id);
        }

        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function deleteUser(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function updateUserStatus(int $id, string $status): bool
    {
        $status = ($status === 'inactive') ? 'inactive' : 'active';

        $sql = "UPDATE {$this->table} SET status = ? WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param('si', $status, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

public function getRoleIdByName(string $roleName): ?int
{
    $sql = "SELECT id FROM roles WHERE role_name = ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) return null;

    $stmt->bind_param('s', $roleName);
    $stmt->execute();

    $id = null;
    if (method_exists($stmt, 'get_result')) {
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $id = $row ? (int)$row['id'] : null;
    } else {
        $tmp = null;
        $stmt->bind_result($tmp);
        if ($stmt->fetch()) $id = (int)$tmp;
    }

    $stmt->close();
    return $id;
}

public function createUserFromRegistration(
    string $username,
    string $email,
    string $passwordHash,
    string $full_name,
    int $role_id = 3,
    string $status = 'active'
): ?int {
    // avoid duplicates
    if ($this->isUsernameTaken($username) || $this->isEmailTaken($email)) {
        return null;
    }

    $sql = "INSERT INTO {$this->table} (username, email, password, full_name, role_id, status)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) return null;

    // NOTE: passwordHash already hashed from resident_registrations.password_hash
    $stmt->bind_param('ssssis', $username, $email, $passwordHash, $full_name, $role_id, $status);

    $ok = $stmt->execute();
    $newId = $ok ? (int)$this->conn->insert_id : null;
    $stmt->close();

    return $newId;
}




}

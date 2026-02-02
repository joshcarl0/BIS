<?php

class User
{
    private $conn;
    private $table = 'users';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function login($username, $password)
    {
        $sql = "SELECT id, username, password, role, status, email, full_name 
                FROM {$this->table} WHERE username = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log('User::login prepare failed: ' . $this->conn->error);
            return false;
        }

        $stmt->bind_param('s', $username);
        $stmt->execute();

        $user = null;
        if (method_exists($stmt, 'get_result')) {
            $result = $stmt->get_result();
            $user = $result ? $result->fetch_assoc() : null;
        } else {
            // Fallback for environments without mysqlnd
            $id = $u = $pwd = $role = $status = $email = $full_name = null;
            $stmt->bind_result($id, $u, $pwd, $role, $status, $email, $full_name);
            if ($stmt->fetch()) {
                $user = [
                    'id' => $id,
                    'username' => $u,
                    'password' => $pwd,
                    'role' => $role,
                    'status' => $status,
                    'email' => $email,
                    'full_name' => $full_name
                ];
            }
        }

        $stmt->close();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                unset($user['password']); // don't return the hash
                return $user;
            }
            if ($password === $user['password']) { // legacy plaintext (not recommended)
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
        if (! $stmt) {
            error_log('User::isUsernameTaken prepare failed: ' . $this->conn->error);
            return false;
        }

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
        if (! $stmt) {
            error_log('User::isEmailTaken prepare failed: ' . $this->conn->error);
            return false;
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        $taken = $stmt->num_rows > 0;
        $stmt->close();

        return $taken;
    }

public function register($username, $email, $password, $full_name, $role = 'user', $status = 'active')
{
    // Basic uniqueness checks
    if ($this->isUsernameTaken($username) || $this->isEmailTaken($email)) {
        return false;
    }

    $sql = "INSERT INTO {$this->table} 
            (username, email, password, full_name, role, status) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        error_log('User::register prepare failed: ' . $this->conn->error);
        return false;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param('ssssss', $username, $email, $hashed, $full_name, $role, $status);

    $ok = $stmt->execute();
    if ($ok) {
        $insertId = $this->conn->insert_id;
        $stmt->close();
        return $insertId; // return new user id
    }

    error_log('User::register execute failed: ' . $stmt->error);
    $stmt->close();
    return false;
}

public function getUserById($id)
{
    $sql = "SELECT id, username, email, full_name, role, status 
            FROM {$this->table} WHERE id = ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (! $stmt) {
        error_log('User::getUserById prepare failed: ' . $this->conn->error);
        return null;
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();

    $user = null;
    if (method_exists($stmt, 'get_result')) {
        $result = $stmt->get_result();
        $user = $result ? $result->fetch_assoc() : null;
    } else {
        // Fallback for environments without mysqlnd
        $uid = $username = $email = $full_name = $role = $status = null;
        $stmt->bind_result($uid, $username, $email, $full_name, $role, $status);
        if ($stmt->fetch()) {
            $user = [
                'id' => $uid,
                'username' => $username,
                'email' => $email,
                'full_name' => $full_name,
                'role' => $role,
                'status' => $status
            ];
        }
    }

    $stmt->close();
    return $user;





}

public function updatePassword($userId, $newPassword)
{
    $sql = "UPDATE {$this->table} SET password = ? WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    if (! $stmt) {
        error_log('User::updatePassword prepare failed: ' . $this->conn->error);
        return false;
    }

    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt->bind_param('si', $hashed, $userId);

    $ok = $stmt->execute();
    if (! $ok) {
        error_log('User::updatePassword execute failed: ' . $stmt->error);
    }

    $stmt->close();
    return $ok;

}

public function updatePasswordById(int $userId, string $newPassword): bool
{
    $hash = password_hash($newPassword, PASSWORD_BCRYPT);

    $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ? LIMIT 1");
    if (! $stmt) return false;

    $stmt->bind_param('si', $hash, $userId);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

/* =========================
   USER MANAGEMENT (ADMIN)
========================= */

// Get all users (with optional search)
public function getAllUsers(string $search = ''): array
{
    $search = trim($search);

    if ($search !== '') {
        $like = '%' . $search . '%';
        $sql = "SELECT id, username, email, full_name, role, status, created_at
                FROM {$this->table}
                WHERE username LIKE ? OR email LIKE ? OR full_name LIKE ?
                ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        if (! $stmt) return [];
        $stmt->bind_param('sss', $like, $like, $like);
    } else {
        $sql = "SELECT id, username, email, full_name, role, status, created_at
                FROM {$this->table}
                ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        if (! $stmt) return [];
    }

    $stmt->execute();

    $rows = [];
    if (method_exists($stmt, 'get_result')) {
        $result = $stmt->get_result();
        while ($result && ($row = $result->fetch_assoc())) {
            $rows[] = $row;
        }
    } else {
        // Fallback if no mysqlnd (limited)
        // If your environment doesn't support get_result, we can provide bind_result version too.
    }

    $stmt->close();
    return $rows;
}

// Get user by ID (for edit form)
public function getUserByIdAdmin(int $id): ?array
{
    $sql = "SELECT id, username, email, full_name, role, status, created_at
            FROM {$this->table}
            WHERE id = ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (! $stmt) return null;

    $stmt->bind_param('i', $id);
    $stmt->execute();

    $user = null;
    if (method_exists($stmt, 'get_result')) {
        $result = $stmt->get_result();
        $user = $result ? $result->fetch_assoc() : null;
    } else {
        $uid=$u=$e=$fn=$r=$s=$ca=null;
        $stmt->bind_result($uid,$u,$e,$fn,$r,$s,$ca);
        if ($stmt->fetch()) {
            $user = [
                'id'=>$uid,'username'=>$u,'email'=>$e,'full_name'=>$fn,
                'role'=>$r,'status'=>$s,'created_at'=>$ca
            ];
        }
    }

    $stmt->close();
    return $user;
}

// Check taken username/email but allow exclude current id (for edit)
public function isUsernameTakenExcept(string $username, int $excludeId): bool
{
    $sql = "SELECT id FROM {$this->table} WHERE username = ? AND id != ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (! $stmt) return false;

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
    if (! $stmt) return false;

    $stmt->bind_param('si', $email, $excludeId);
    $stmt->execute();
    $stmt->store_result();
    $taken = $stmt->num_rows > 0;
    $stmt->close();
    return $taken;
}

// Admin create user (like register but for admin)
public function adminCreateUser(string $username, string $email, string $password, string $full_name, string $role, string $status): bool
{
    if ($this->isUsernameTaken($username) || $this->isEmailTaken($email)) {
        return false;
    }

    $role   = ($role === 'admin') ? 'admin' : 'user';
    $status = ($status === 'inactive') ? 'inactive' : 'active';

    $sql = "INSERT INTO {$this->table} (username, email, password, full_name, role, status)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    if (! $stmt) return false;

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param('ssssss', $username, $email, $hash, $full_name, $role, $status);

    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// Admin update user (password optional)
public function adminUpdateUser(int $id, string $username, string $email, string $full_name, string $role, string $status, string $newPassword = ''): bool
{
    if ($this->isUsernameTakenExcept($username, $id) || $this->isEmailTakenExcept($email, $id)) {
        return false;
    }

    $role   = ($role === 'admin') ? 'admin' : 'user';
    $status = ($status === 'inactive') ? 'inactive' : 'active';

    if (trim($newPassword) !== '') {
        $sql = "UPDATE {$this->table}
                SET username=?, email=?, password=?, full_name=?, role=?, status=?
                WHERE id=? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (! $stmt) return false;

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt->bind_param('ssssssi', $username, $email, $hash, $full_name, $role, $status, $id);
    } else {
        $sql = "UPDATE {$this->table}
                SET username=?, email=?, full_name=?, role=?, status=?
                WHERE id=? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (! $stmt) return false;

        $stmt->bind_param('sssssi', $username, $email, $full_name, $role, $status, $id);
    }

    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// Delete user
public function deleteUser(int $id): bool
{
    $sql = "DELETE FROM {$this->table} WHERE id = ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (! $stmt) return false;

    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// Update status (active/inactive)
public function updateUserStatus(int $id, string $status): bool
{
    $status = ($status === 'inactive') ? 'inactive' : 'active';

    $sql = "UPDATE {$this->table} SET status = ? WHERE id = ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (! $stmt) return false;

    $stmt->bind_param('si', $status, $id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}



}
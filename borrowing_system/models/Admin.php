<?php
/**
 * Admin Model Class
 * Handles admin authentication and management
 */

require_once '../classes/Database.php';

class Admin {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function login($username, $password) {
        $username = $this->db->escape($username);

        $sql = "SELECT admin_id, username, password_hash, full_name, email
                FROM admins
                WHERE username = '$username'";

        $result = $this->db->query($sql);

        if (mysqli_num_rows($result) == 1) {
            $admin = mysqli_fetch_assoc($result);

            if (password_verify($password, $admin['password_hash'])) {
                // Update last login
                $this->updateLastLogin($admin['admin_id']);
                return $admin;
            }
        }

        return false;
    }

    public function updateLastLogin($admin_id) {
        $admin_id = $this->db->escape($admin_id);

        $sql = "UPDATE admins
                SET updated_at = CURRENT_TIMESTAMP
                WHERE admin_id = $admin_id";

        return $this->db->query($sql);
    }

    public function changePassword($admin_id, $new_password) {
        $admin_id = $this->db->escape($admin_id);
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $sql = "UPDATE admins
                SET password_hash = '$password_hash',
                    updated_at = CURRENT_TIMESTAMP
                WHERE admin_id = $admin_id";

        return $this->db->query($sql);
    }

    public function logActivity($admin_id, $action, $table_name, $record_id, $old_values = null, $new_values = null) {
        $admin_id = $this->db->escape($admin_id);
        $action = $this->db->escape($action);
        $table_name = $this->db->escape($table_name);
        $record_id = $this->db->escape($record_id);

        $old_values_json = $old_values ? json_encode($old_values) : 'NULL';
        $new_values_json = $new_values ? json_encode($new_values) : 'NULL';

        $sql = "INSERT INTO activity_logs
                (admin_id, action, table_name, record_id, old_values, new_values)
                VALUES
                ($admin_id, '$action', '$table_name', $record_id, $old_values_json, $new_values_json)";

        return $this->db->query($sql);
    }

    public function getActivityLogs($limit = 50) {
        $sql = "SELECT al.*, a.full_name as admin_name
                FROM activity_logs al
                JOIN admins a ON al.admin_id = a.admin_id
                ORDER BY al.created_at DESC
                LIMIT $limit";

        $result = $this->db->query($sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getSystemSettings() {
        $sql = "SELECT setting_key, setting_value, setting_description
                FROM system_settings";

        $result = $this->db->query($sql);
        $settings = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_key']] = [
                'value' => $row['setting_value'],
                'description' => $row['setting_description']
            ];
        }

        return $settings;
    }

    public function updateSystemSetting($key, $value) {
        $key = $this->db->escape($key);
        $value = $this->db->escape($value);

        $sql = "UPDATE system_settings
                SET setting_value = '$value',
                    updated_at = CURRENT_TIMESTAMP
                WHERE setting_key = '$key'";

        return $this->db->query($sql);
    }
}
?>
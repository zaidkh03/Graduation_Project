<?php
require_once '../../db_connection.php';

if (isset($_POST['field']) && isset($_POST['value'])) {
    $field = $_POST['field'];
    $value = $_POST['value'];

    $allowed_fields = ['national_id', 'email', 'phone'];
    if (!in_array($field, $allowed_fields)) {
        echo json_encode(['valid' => false, 'message' => 'Invalid field']);
        exit;
    }

    $value = trim($value);
    $is_duplicate = false;

    // Check National ID only in `users`
    if ($field === 'national_id') {
        $excludeId = isset($_POST['exclude_id']) ? (int)$_POST['exclude_id'] : 0;
        $role = $_POST['role'] ?? '';
    
        if ($excludeId && $role) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE national_id = ? AND NOT (related_id = ? AND role = ?)");
            $stmt->bind_param("sis", $value, $excludeId, $role);
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE national_id = ?");
            $stmt->bind_param("s", $value);
        }
    
        $stmt->execute();
        $stmt->store_result();
        $is_duplicate = $stmt->num_rows > 0;
        $stmt->close();
    }
    

    if (in_array($field, ['email', 'phone'])) {
        $tables = ['admins', 'teachers', 'parents'];
        $excludeId = isset($_POST['exclude_id']) ? (int)$_POST['exclude_id'] : 0;
        $role = $_POST['role'] ?? '';
    
        foreach ($tables as $table) {
            $tableRole = rtrim($table, 's');
    
            // Check if value exists and is not from this same record
            $stmt = $conn->prepare("SELECT id FROM $table WHERE $field = ? LIMIT 1");
            $stmt->bind_param("s", $value);
            $stmt->execute();
            $stmt->store_result();
    
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($foundId);
                $stmt->fetch();
                if (!($excludeId && $role === $tableRole && $foundId == $excludeId)) {
                    $is_duplicate = true;
                    $stmt->close();
                    break;
                }
            }
    
            $stmt->close();
        }
    }
    
    
    if ($is_duplicate) {
        echo json_encode([
            'valid' => false,
            'message' => "This $field already exists in the system."
        ]);
    } else {
        echo json_encode(['valid' => true]);
    }
}
?>

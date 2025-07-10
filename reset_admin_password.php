<?php
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

$newPassword = 'admin123';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare("UPDATE users SET password = :password WHERE username = 'admin'");
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo "Admin password has been reset successfully.";
    } else {
        echo "Admin user not found or password already set.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

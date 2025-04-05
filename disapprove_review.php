<?php
session_start();
require_once 'config.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$review_id = $_GET['id'] ?? '';
if (empty($review_id)) {
    $_SESSION['error'] = "No review ID specified.";
    header("Location: dashboard.php");
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE reviews SET approved_status = 0 WHERE review_id = ?");
    $stmt->execute([$review_id]);
    $_SESSION['success'] = "Review status set to not approved.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error updating review: " . $e->getMessage();
}

header("Location: dashboard.php");
exit;
?>

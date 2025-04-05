<?php
session_start();
require_once 'config.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$building_id = $_GET['id'] ?? '';
if (empty($building_id)) {
    $_SESSION['error'] = "No building ID specified.";
    header('Location: manage_buildings.php');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM buildings WHERE building_id = ?");
    $stmt->execute([$building_id]);
    $_SESSION['success'] = "Building deleted successfully!";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error deleting building: " . $e->getMessage();
}

header("Location: manage_buildings.php");
exit;
?>

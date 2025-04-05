<?php
session_start();
require_once 'config.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Dashboard - Admin';

// Fetch system statistics
try {
    $stats = [];
    $stats['tenants']   = $pdo->query("SELECT COUNT(*) FROM tenants")->fetchColumn();
    $stats['landlords'] = $pdo->query("SELECT COUNT(*) FROM landlords")->fetchColumn();
    $stats['buildings'] = $pdo->query("SELECT COUNT(*) FROM buildings")->fetchColumn();
    $stats['reviews']   = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
} catch (PDOException $e) {
    $error = $e->getMessage();
}

require_once 'header.php'; // Your provided header.php is used here.
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Super Admin Dashboard</h1>
    
    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Statistics Panel -->
    <div class="row my-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">Tenants: <?= htmlspecialchars($stats['tenants']) ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">Landlords: <?= htmlspecialchars($stats['landlords']) ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">Buildings: <?= htmlspecialchars($stats['buildings']) ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">Reviews: <?= htmlspecialchars($stats['reviews']) ?></div>
            </div>
        </div>
    </div>

<?php require_once 'footer.php'; ?>

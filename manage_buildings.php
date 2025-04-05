<?php
session_start();
require_once 'config.php';

// Ensure admin is logged in (adjust as needed for your admin authentication)
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = "Manage Buildings";

try {
    // Fetch building details along with landlord name for reference
    $stmt = $pdo->prepare("SELECT b.building_id, b.building_name, b.address, b.city, b.state_county, b.image_url, b.created_at, l.name AS landlord_name
                           FROM buildings b
                           JOIN landlords l ON b.landlord_id = l.landlord_id
                           ORDER BY b.created_at DESC");
    $stmt->execute();
    $buildings = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching buildings: " . $e->getMessage();
}

require_once 'header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Buildings</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($buildings)): ?>
        <div class="alert alert-info">No buildings found.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($buildings as $building): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($building['image_url'])): ?>
                            <img src="<?= htmlspecialchars($building['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($building['building_name']) ?>">
                        <?php else: ?>
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                                <i class="fas fa-building fa-3x text-secondary"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($building['building_name']) ?></h5>
                            <p class="card-text">
                                <strong>Location:</strong> <?= htmlspecialchars($building['city']) ?>, <?= htmlspecialchars($building['state_county']) ?><br>
                                <strong>Address:</strong> <?= htmlspecialchars($building['address']) ?><br>
                                <strong>Landlord:</strong> <?= htmlspecialchars($building['landlord_name']) ?>
                            </p>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="edit_building.php?id=<?= urlencode($building['building_id']) ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="delete_building.php?id=<?= urlencode($building['building_id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this building?');">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>

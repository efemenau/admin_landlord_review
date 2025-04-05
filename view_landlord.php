<?php
session_start();
require_once 'config.php';

/**
 * Helper function to validate and return the image URL or a placeholder.
 *
 * @param string|null $imageUrl
 * @return string
 */
function getValidImageUrl(?string $imageUrl): string
{
    return (!empty($imageUrl) && filter_var($imageUrl, FILTER_VALIDATE_URL)) ? htmlspecialchars($imageUrl) : '';
}

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get landlord ID from query string
$landlord_id = $_GET['id'] ?? '';
if (empty($landlord_id)) {
    $_SESSION['error'] = "No landlord ID specified.";
    header('Location: manage_landlords.php');
    exit;
}

try {
    // Fetch landlord details
    $stmt = $pdo->prepare("SELECT * FROM landlords WHERE landlord_id = ?");
    $stmt->execute([$landlord_id]);
    $landlord = $stmt->fetch();

    if (!$landlord) {
        $_SESSION['error'] = "Landlord not found.";
        header('Location: manage_landlords.php');
        exit;
    }

    // Fetch houses (buildings with type 'house') associated with this landlord
    $stmtHouses = $pdo->prepare("SELECT * FROM buildings WHERE landlord_id = ? ORDER BY created_at DESC");
    $stmtHouses->execute([$landlord_id]);
    $associatedHouses = $stmtHouses->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching details: " . $e->getMessage();
    header('Location: manage_landlords.php');
    exit;
}

$page_title = "Landlord Details";
require_once 'header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Landlord Details</h1>
    <p><strong>Registered On:</strong> <?= htmlspecialchars((new DateTime($landlord['created_at']))->format('d M Y, H:i')) ?></p>
    <!-- Landlord Details Card -->
    <div class="card mb-4 shadow">
        <div class="card-header">
            <h3><?= htmlspecialchars($landlord['name']) ?></h3>
        </div>
        <div class="card-body">
            <p><strong>Email:</strong> <?= htmlspecialchars($landlord['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($landlord['phone']) ?></p>
            <p><strong>Company:</strong> <?= htmlspecialchars($landlord['company']) ?></p>
        </div>
        <div class="card-footer">
            <a href="manage_landlords.php" class="btn btn-secondary">Back to Landlords</a>
        </div>
    </div>

    <!-- Houses Associated with Landlord -->
    <h3 class="mb-3">Houses Associated</h3>
    <?php
    if (empty($associatedHouses)):
        // Display message if no houses are found
        echo "<div class='alert alert-info'>No houses associated with this landlord.</div>";
    else: ?>
        <div class="row">
            <?php foreach ($associatedHouses as $house): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php $imageUrl = getValidImageUrl($house['image_url'] ?? ''); ?>
                        <?php if ($imageUrl): ?>
                            <img src="<?= $imageUrl ?>"
                                class="card-img-top"
                                style="height: 200px; object-fit: cover;"
                                alt="<?= htmlspecialchars($house['building_name']) ?>">
                        <?php else: ?>
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light"
                                style="height: 200px;">
                                <i class="fas fa-home fa-3x text-secondary"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title">
                                <?= htmlspecialchars($house['building_name']) ?>
                            </h5>
                            <p class="card-text">
                                <strong>Location:</strong><br>
                                <?= htmlspecialchars($house['address']) ?><br>
                                <?= htmlspecialchars($house['city']) ?>,
                                <?= htmlspecialchars($house['state_county']) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php require_once 'footer.php'; ?>
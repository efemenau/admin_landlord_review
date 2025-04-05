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

    // Fetch latest 5 reviews (as comments) with building and tenant info
    $latestReviewsStmt = $pdo->prepare("SELECT r.review_id, r.review_title, r.review_text, r.created_at, 
                                               b.building_name, t.name AS tenant_name
                                          FROM reviews r
                                          LEFT JOIN buildings b ON r.building_id = b.building_id
                                          LEFT JOIN tenants t ON r.tenant_id = t.tenant_id
                                          ORDER BY r.created_at DESC
                                          LIMIT 5");
    $latestReviewsStmt->execute();
    $latestReviews = $latestReviewsStmt->fetchAll();
} catch (PDOException $e) {
    $error = $e->getMessage();
}

require_once 'header.php'; 
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Super Admin Dashboard</h1>
    
    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Enhanced Statistics Panel -->
    <div class="row my-4">
        <!-- Tenants Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-gradient-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col me-2">
                            <div class="text-uppercase fw-bold small">Tenants</div>
                            <div class="h5 mb-0"><?= htmlspecialchars($stats['tenants']) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Landlords Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-gradient-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col me-2">
                            <div class="text-uppercase fw-bold small">Landlords</div>
                            <div class="h5 mb-0"><?= htmlspecialchars($stats['landlords']) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tie fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Buildings Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-gradient-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col me-2">
                            <div class="text-uppercase fw-bold small">Buildings</div>
                            <div class="h5 mb-0"><?= htmlspecialchars($stats['buildings']) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Reviews Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-gradient-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col me-2">
                            <div class="text-uppercase fw-bold small">Reviews</div>
                            <div class="h5 mb-0"><?= htmlspecialchars($stats['reviews']) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-comments fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Comments Section -->
    <div class="row">
        <div class="col-12">
            <h3 class="mb-3">Latest Comments on Buildings</h3>
        </div>
        <?php if (empty($latestReviews)): ?>
            <div class="col-12">
                <div class="alert alert-info">No comments available.</div>
            </div>
        <?php else: ?>
            <?php foreach ($latestReviews as $review): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header">
                            <strong><?= htmlspecialchars($review['review_title']) ?></strong>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?= nl2br(htmlspecialchars(substr($review['review_text'], 0, 100))) ?>...</p>
                        </div>
                        <div class="card-footer text-muted d-flex justify-content-between align-items-center">
                            <small>
                                <i class="fas fa-building"></i> <?= htmlspecialchars($review['building_name'] ?? 'Unknown Building') ?><br>
                                <i class="fas fa-user"></i> <?= htmlspecialchars($review['tenant_name'] ?? 'Anonymous') ?><br>
                                <?= date('M j, Y', strtotime($review['created_at'])) ?>
                            </small>
                            <a href="disapprove_review.php?id=<?= urlencode($review['review_id']) ?>" class="btn btn-sm btn-secondary" onclick="return confirm('Disapprove this review?');">Disapprove</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php require_once 'footer.php'; ?>

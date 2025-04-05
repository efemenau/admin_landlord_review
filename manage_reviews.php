<?php
session_start();
require_once 'config.php';

$page_title = 'Manage Reviews';

// Get search term from GET parameter
$search = trim($_GET['search'] ?? '');

// Build the SQL query with optional filtering
$sql = "SELECT r.review_id, r.review_title, r.review_text, r.created_at, r.approved_status, 
               t.name AS tenant_name, b.building_name 
        FROM reviews r 
        JOIN tenants t ON r.tenant_id = t.tenant_id 
        LEFT JOIN buildings b ON r.building_id = b.building_id ";

$params = [];
if (!empty($search)) {
    $sql .= "WHERE r.review_title LIKE ? 
             OR r.review_text LIKE ? 
             OR t.name LIKE ? 
             OR b.building_name LIKE ? ";
    $likeSearch = "%" . $search . "%";
    $params = [$likeSearch, $likeSearch, $likeSearch, $likeSearch];
}

$sql .= "ORDER BY r.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error fetching reviews: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
<?php require_once 'header.php'; ?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Manage Reviews</h3>
    
    <!-- Search Form -->
    <form method="GET" action="manage_reviews.php" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search reviews..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i> Search</button>
        </div>
    </form>
    
    <!-- Reviews Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Review ID</th>
                    <th>Title</th>
                    <th>Review Text</th>
                    <th>Reviewer</th>
                    <th>Building</th>
                    <th>Created At</th>
                    <th>Approved</th>
                    <!-- <th>Actions</th> -->
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td><?= htmlspecialchars($review['review_id']) ?></td>
                            <td><?= htmlspecialchars($review['review_title']) ?></td>
                            <td><?= htmlspecialchars(substr($review['review_text'], 0, 100)) ?>...</td>
                            <td><?= htmlspecialchars($review['tenant_name']) ?></td>
                            <td><?= htmlspecialchars($review['building_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($review['created_at']) ?></td>
                            <td><?= ($review['approved_status'] ?? 0) == 1 ? 'Yes' : 'No' ?></td>
                            <!-- <td>
                                <a href="disapprove_review.php?id=<?= urlencode($review['review_id']) ?>" class="btn btn-sm btn-warning">Reject</a>
                            </td> -->
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No reviews found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>

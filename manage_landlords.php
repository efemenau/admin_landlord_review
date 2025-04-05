<?php
session_start();
require_once 'config.php';

// Get search term from GET parameter
$search = trim($_GET['search'] ?? '');

// Determine current page and set results per page
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $limit;

// Count total number of landlords (with search filter if provided)
if (!empty($search)) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM landlords WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? OR company LIKE ?");
    $likeSearch = "%" . $search . "%";
    $countStmt->execute([$likeSearch, $likeSearch, $likeSearch, $likeSearch]);
    $totalRecords = $countStmt->fetchColumn();
} else {
    $countStmt = $pdo->query("SELECT COUNT(*) FROM landlords");
    $totalRecords = $countStmt->fetchColumn();
}
$totalPages = ceil($totalRecords / $limit);

// Fetch landlords for the current page with search filter if provided
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT landlord_id, name, email, phone, company, created_at 
                           FROM landlords 
                           WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? OR company LIKE ?
                           ORDER BY created_at DESC 
                           LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $likeSearch, PDO::PARAM_STR);
    $stmt->bindValue(2, $likeSearch, PDO::PARAM_STR);
    $stmt->bindValue(3, $likeSearch, PDO::PARAM_STR);
    $stmt->bindValue(4, $likeSearch, PDO::PARAM_STR);
    $stmt->bindValue(5, $limit, PDO::PARAM_INT);
    $stmt->bindValue(6, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $landlords = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT landlord_id, name, email, phone, company, created_at 
                           FROM landlords 
                           ORDER BY created_at DESC 
                           LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $landlords = $stmt->fetchAll();
}

$page_title = "Manage Landlords";
require_once 'header.php';
?>

<!-- Inline CSS for enhanced styling -->
<style>
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, .05);
    }

    .table thead th {
        background-color: #343a40;
        color: #fff;
        border-color: #454d55;
    }

    .table tfoot th {
        background-color: #f8f9fa;
        color: #343a40;
    }

    .pagination {
        justify-content: center;
    }

    .page-link {
        border: none;
        background-color: #fff;
        color: #343a40;
    }

    .page-item.active .page-link {
        background-color: #343a40;
        border-color: #343a40;
        color: #fff;
    }
</style>

<div class="container-fluid px-4">
    <h3 class="mt-4">Manage Landlords</h3>

    <!-- Search Form -->
    <form method="GET" action="manage_landlords.php" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search landlords..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-outline-secondary" type="submit">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
    </form>

    <!-- Landlords Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Landlord ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Company</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($landlords)): ?>
                    <?php foreach ($landlords as $landlord): ?>
                        <tr>
                            <td><?= htmlspecialchars($landlord['landlord_id']) ?></td>
                            <td><?= htmlspecialchars($landlord['name']) ?></td>
                            <td><?= htmlspecialchars($landlord['email']) ?></td>
                            <td><?= htmlspecialchars($landlord['phone']) ?></td>
                            <td><?= htmlspecialchars($landlord['company']) ?></td>
                            <td><?= htmlspecialchars($landlord['created_at']) ?></td>
                            <td>
                                <a href="view_landlord.php?id=<?= urlencode($landlord['landlord_id']) ?>" class="btn btn-sm btn-info">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No landlords found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Landlord ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Company</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pagination -->
    <nav aria-label="Landlords Page Navigation">
        <ul class="pagination">
            <!-- Previous Page Link -->
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </span>
                </li>
            <?php endif; ?>

            <!-- Page Number Links -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <!-- Next Page Link -->
            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </span>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<?php require_once 'footer.php'; ?>
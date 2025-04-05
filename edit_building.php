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
    header('Location: manage_building.php');
    exit;
}

$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $building_name = trim($_POST['building_name'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $state_county = trim($_POST['state_county'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $town = trim($_POST['town'] ?? '');
    $zip_code = trim($_POST['zip_code'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $building_type = trim($_POST['building_type'] ?? 'apartment');
    $image_url = trim($_POST['image_url'] ?? '');

    if (empty($building_name)) { $errors[] = "Building name is required."; }
    if (empty($country)) { $errors[] = "Country is required."; }
    if (empty($state_county)) { $errors[] = "State/County is required."; }
    if (empty($city)) { $errors[] = "City is required."; }
    if (empty($address)) { $errors[] = "Address is required."; }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE buildings 
                SET building_name = ?, country = ?, state_county = ?, city = ?, town = ?, zip_code = ?, address = ?, image_url = ?, building_type = ?, last_updated = CURRENT_TIMESTAMP 
                WHERE building_id = ?");
            $stmt->execute([
                $building_name, $country, $state_county, $city, $town, $zip_code, $address, $image_url, $building_type, $building_id
            ]);
            $_SESSION['success'] = "Building updated successfully!";
            header("Location: manage_buildings.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Error updating building: " . $e->getMessage();
        }
    }
} else {
    // Fetch building details for editing
    try {
        $stmt = $pdo->prepare("SELECT * FROM buildings WHERE building_id = ?");
        $stmt->execute([$building_id]);
        $building = $stmt->fetch();
        if (!$building) {
            $_SESSION['error'] = "Building not found.";
            header("Location: manage_buildings.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error fetching building: " . $e->getMessage();
        header("Location: manage_building.php");
        exit;
    }
}
?>

<?php require_once 'header.php'; ?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Edit Building</h1>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $err): ?>
                <div><?= htmlspecialchars($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form action="edit_building.php?id=<?= urlencode($building_id) ?>" method="POST">
        <div class="mb-3">
            <label for="building_name" class="form-label">Building Name</label>
            <input type="text" class="form-control" id="building_name" name="building_name" value="<?= htmlspecialchars($building['building_name'] ?? $building_name ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="country" class="form-label">Country</label>
            <input type="text" class="form-control" id="country" name="country" value="<?= htmlspecialchars($building['country'] ?? $country ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="state_county" class="form-label">State/County</label>
            <input type="text" class="form-control" id="state_county" name="state_county" value="<?= htmlspecialchars($building['state_county'] ?? $state_county ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="city" class="form-label">City</label>
            <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($building['city'] ?? $city ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="town" class="form-label">Town</label>
            <input type="text" class="form-control" id="town" name="town" value="<?= htmlspecialchars($building['town'] ?? $town ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="zip_code" class="form-label">Zip Code</label>
            <input type="text" class="form-control" id="zip_code" name="zip_code" value="<?= htmlspecialchars($building['zip_code'] ?? $zip_code ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlspecialchars($building['address'] ?? $address ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label for="building_type" class="form-label">Building Type</label>
            <select class="form-select" id="building_type" name="building_type">
                <option value="apartment" <?= ((($building['building_type'] ?? $building_type ?? '') == 'apartment') ? 'selected' : '') ?>>Apartment</option>
                <option value="house" <?= ((($building['building_type'] ?? $building_type ?? '') == 'house') ? 'selected' : '') ?>>House</option>
                <option value="commercial" <?= ((($building['building_type'] ?? $building_type ?? '') == 'commercial') ? 'selected' : '') ?>>Commercial</option>
                <option value="other" <?= ((($building['building_type'] ?? $building_type ?? '') == 'other') ? 'selected' : '') ?>>Other</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="image_url" class="form-label">Image URL</label>
            <input type="text" class="form-control" id="image_url" name="image_url" value="<?= htmlspecialchars($building['image_url'] ?? $image_url ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-primary">Update Building</button>
        <a href="manage_buildings.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php require_once 'footer.php'; ?>

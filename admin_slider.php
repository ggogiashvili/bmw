<?php
/**
 * სლაიდერის მართვის გვერდი
 * 
 * ამ გვერდზე ადმინისტრატორი:
 * 1. ხედავს ყველა მოდელს
 * 2. შეუძლია მოდელის დამატება/ამოშლა სლაიდერიდან
 * 3. იცვლება is_slider ველი (0 ან 1)
 */
require_once 'config/db.php';
require_once 'includes/header.php';

// ადმინისტრატორის შემოწმება
if (!isLoggedIn() || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    redirect('index.php');
}

// სლაიდერის სტატუსის შეცვლა (დამატება/ამოშლა)
if (isset($_POST['toggle_id'])) {
    $id = sanitize($_POST['toggle_id']);
    $current_status = sanitize($_POST['current_status']);
    $new_status = $current_status ? 0 : 1; // 0 = არააქტიური, 1 = აქტიური

    $stmt = $pdo->prepare("UPDATE models SET is_slider = ? WHERE id = ?");
    $stmt->execute([$new_status, $id]);
    redirect('admin_slider.php');
}

// ყველა მოდელის მიღება
$stmt = $pdo->query("SELECT id, name, image, is_slider FROM models ORDER BY id DESC");
$cars = $stmt->fetchAll();
?>

<div class="container" style="margin-top: 3rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>სლაიდერის მართვა</h1>
        <a href="admin_dashboard.php" class="btn btn-outline">უკან დაბრუნება</a>
    </div>

    <div class="grid">
        <?php foreach ($cars as $car): ?>
            <div class="card glass-panel" style="display: flex; align-items: center; padding: 1rem; gap: 1rem;">
                <img src="uploads/<?php echo htmlspecialchars($car['image'] ?: 'default_bmw.jpg'); ?>"
                    style="width: 100px; height: 60px; object-fit: cover; border-radius: 8px;">

                <div style="flex: 1;">
                    <h3 style="font-size: 1.2rem;"><?php echo htmlspecialchars($car['name']); ?></h3>
                    <span style="color: <?php echo $car['is_slider'] ? 'lightgreen' : 'gray'; ?>; font-size: 0.9rem;">
                        <?php echo $car['is_slider'] ? 'აქტიური' : 'არააქტიური'; ?>
                    </span>
                </div>

                <form method="POST">
                    <input type="hidden" name="toggle_id" value="<?php echo $car['id']; ?>">
                    <input type="hidden" name="current_status" value="<?php echo $car['is_slider']; ?>">
                    <button type="submit" class="btn <?php echo $car['is_slider'] ? 'btn-outline' : 'btn-primary'; ?>"
                        style="padding: 8px 15px; font-size: 0.9rem; border-color: <?php echo $car['is_slider'] ? 'red' : ''; ?>; color: <?php echo $car['is_slider'] ? 'red' : ''; ?>;">
                        <?php echo $car['is_slider'] ? 'ამოშლა' : 'დამატება'; ?>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
<?php
/**
 * ყველა მოდელის სიის გვერდი
 * 
 * ამ გვერდზე:
 * 1. იღებს ყველა მოდელს ბაზიდან სერიებთან ერთად
 * 2. აჩვენებს მათ ბარათების სახით
 * 3. ადმინისტრატორისთვის აჩვენებს რედაქტირებისა და წაშლის ღილაკებს
 */
require_once 'config/db.php';
require_once 'includes/header.php';

// ყველა მოდელის მიღება სერიებთან ერთად, დალაგებული სერიის სახელის მიხედვით
try {
    $stmt = $pdo->query("SELECT m.*, s.name as series_name 
                         FROM models m 
                         JOIN series s ON m.series_id = s.id 
                         ORDER BY s.name ASC");
    $models = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching models: " . $e->getMessage());
}
?>

<div class="container" style="margin-top: 3rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <h1>ყველა მოდელი</h1>
        <div style="display: flex; gap: 1rem;">
            <a href="search.php" class="btn btn-outline"><i class="fas fa-search"></i> ძიება</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="add_car.php" class="btn btn-primary"><i class="fas fa-plus"></i> დამატება</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid">
        <?php foreach ($models as $car): ?>
            <div class="card glass-panel">
                <img src="uploads/<?php echo htmlspecialchars($car['image'] ?: 'default_bmw.jpg'); ?>"
                    alt="<?php echo htmlspecialchars($car['name']); ?>">
                <div class="card-body">
                    <div class="card-info">
                        <span><?php echo htmlspecialchars($car['series_name']); ?></span>
                        <span><?php echo $car['year']; ?></span>
                    </div>
                    <h3 class="card-title"><?php echo htmlspecialchars($car['name']); ?></h3>
                    <p style="color: var(--bmw-blue); font-weight: bold; font-size: 1.2rem; margin-bottom: 1rem;">
                        ფასი: $<?php echo number_format($car['price']); ?>-დან
                    </p>
                    <div style="display: flex; gap: 10px; margin-top: auto;">
                        <a href="details.php?id=<?php echo $car['id']; ?>" class="btn btn-outline"
                            style="flex: 1; text-align: center;">დეტალურად</a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="edit_car.php?id=<?php echo $car['id']; ?>" class="btn btn-outline"
                                style="border-color: var(--bmw-blue); color: var(--bmw-blue);"><i class="fas fa-edit"></i></a>
                            <a href="delete_car.php?id=<?php echo $car['id']; ?>" class="btn btn-outline"
                                style="border-color: red; color: red;" onclick="return confirm('ნამდვილად გსურთ წაშლა?')"><i
                                    class="fas fa-trash"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
<?php
/**
 * რჩეულების გვერდი
 * 
 * ამ გვერდზე:
 * 1. აჩვენებს მომხმარებლის რჩეულ მოდელებს
 * 2. საშუალებას აძლევს დაუმატოს ან წაშალოს რჩეულებიდან
 * 3. მოითხოვს ავტორიზაციას
 */
require_once 'config/db.php';
require_once 'includes/header.php';

// ავტორიზაციის შემოწმება
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// რჩეულებში დამატება
if (isset($_GET['add'])) {
    $model_id = sanitize($_GET['add']);
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO favorites (user_id, model_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $model_id]);
        redirect('favorites.php');
    } catch (PDOException $e) {
        // უკვე არსებობს ან შეცდომა
    }
}

// რჩეულებიდან წაშლა
if (isset($_GET['remove'])) {
    $model_id = sanitize($_GET['remove']);
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND model_id = ?");
    $stmt->execute([$user_id, $model_id]);
    redirect('favorites.php');
}

// მომხმარებლის რჩეულების მიღება
$sql = "SELECT m.*, s.name as series_name, f.created_at as favorited_at
        FROM favorites f
        JOIN models m ON f.model_id = m.id
        JOIN series s ON m.series_id = s.id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll();
?>

<div class="container" style="margin-top: 3rem;">
    <h1 style="margin-bottom: 2rem;">
        <i class="fas fa-heart" style="color: #ff6b6b;"></i> ჩემი რჩეულები
    </h1>

    <?php if (empty($favorites)): ?>
        <div class="glass-panel" style="padding: 3rem; text-align: center;">
            <i class="fas fa-heart" style="font-size: 4rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
            <h2 style="margin-bottom: 1rem;">რჩეულების სია ცარიელია</h2>
            <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                დაამატეთ მოდელები რჩეულებში მათი დეტალური გვერდიდან
            </p>
            <a href="models.php" class="btn btn-primary">იხილეთ მოდელები</a>
        </div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($favorites as $car): ?>
                <div class="card glass-panel">
                    <div style="position: relative;">
                        <img src="uploads/<?php echo htmlspecialchars($car['image'] ?: 'default_bmw.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($car['name']); ?>">
                        <a href="favorites.php?remove=<?php echo $car['id']; ?>" 
                           class="favorite-btn"
                           style="position: absolute; top: 10px; right: 10px; background: rgba(255,107,107,0.9); color: white; border: none; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: var(--transition);"
                           onclick="return confirm('ნამდვილად გსურთ წაშლა რჩეულებიდან?')">
                            <i class="fas fa-heart"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="card-info">
                            <span><?php echo htmlspecialchars($car['series_name']); ?></span>
                            <span><?php echo $car['year']; ?></span>
                        </div>
                        <h3 class="card-title"><?php echo htmlspecialchars($car['name']); ?></h3>
                        <p class="card-info" style="color: var(--bmw-blue); font-size: 1.1rem; font-weight: bold;">
                            $<?php echo number_format($car['price']); ?>
                        </p>
                        <p style="color: var(--text-secondary); font-size: 0.8rem; margin-top: 0.5rem;">
                            დამატებული: <?php echo date('d.m.Y', strtotime($car['favorited_at'])); ?>
                        </p>
                        <div style="display: flex; gap: 10px; margin-top: auto;">
                            <a href="details.php?id=<?php echo $car['id']; ?>" class="btn btn-outline" style="flex: 1; text-align: center;">
                                დეტალურად
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>

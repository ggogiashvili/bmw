<?php
/**
 * ადმინისტრატორის დეშბორდი
 * 
 * ამ გვერდზე:
 * 1. აჩვენებს ყველა მოდელს ცხრილის სახით
 * 2. საშუალებას აძლევს რედაქტირებასა და წაშლას
 * 3. მოითხოვს ადმინისტრატორის უფლებებს
 */
require_once 'config/db.php';
require_once 'includes/header.php';

// ადმინისტრატორის შემოწმება
if (!isLoggedIn() || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    redirect('index.php');
}

// ყველა მოდელის მიღება დეშბორდის ცხრილისთვის
$stmt = $pdo->query("SELECT m .*, s.name as series_name 
                     FROM models m 
                     JOIN series s ON m.series_id = s.id 
                     ORDER BY m.id DESC");
$cars = $stmt->fetchAll();
?>

<div class="container" style="margin-top: 3rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>ადმინ პანელი</h1>
        <div>
            <a href="admin_users.php" class="btn btn-outline" style="margin-right: 1rem;">მომხმარებლები</a>
            <a href="admin_slider.php" class="btn btn-outline" style="margin-right: 1rem;">სლაიდერი</a>
            <a href="add_car.php" class="btn btn-primary"><i class="fas fa-plus"></i> დამატება</a>
        </div>
    </div>

    <div class="glass-panel" style="overflow-x: auto; padding: 1rem;">
        <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="border-bottom: 1px solid var(--glass-border); padding: 1rem;">ფოტო</th>
                    <th style="border-bottom: 1px solid var(--glass-border); padding: 1rem;">მოდელი</th>
                    <th style="border-bottom: 1px solid var(--glass-border); padding: 1rem;">სერია</th>
                    <th style="border-bottom: 1px solid var(--glass-border); padding: 1rem;">წელი</th>
                    <th style="border-bottom: 1px solid var(--glass-border); padding: 1rem;">ფასი</th>
                    <th style="border-bottom: 1px solid var(--glass-border); padding: 1rem;">მოქმედება</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cars as $car): ?>
                    <tr>
                        <td style="padding: 1rem; border-bottom: 1px solid var(--glass-border);">
                            <img src="uploads/<?php echo htmlspecialchars($car['image'] ?: 'default_bmw.jpg'); ?>"
                                style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px;">
                        </td>
                        <td style="padding: 1rem; border-bottom: 1px solid var(--glass-border); font-weight: bold;">
                            <?php echo htmlspecialchars($car['name']); ?></td>
                        <td style="padding: 1rem; border-bottom: 1px solid var(--glass-border);">
                            <?php echo htmlspecialchars($car['series_name']); ?></td>
                        <td style="padding: 1rem; border-bottom: 1px solid var(--glass-border);"><?php echo $car['year']; ?>
                        </td>
                        <td style="padding: 1rem; border-bottom: 1px solid var(--glass-border);">
                            $<?php echo number_format($car['price']); ?></td>
                        <td style="padding: 1rem; border-bottom: 1px solid var(--glass-border);">
                            <a href="edit_car.php?id=<?php echo $car['id']; ?>" class="btn btn-outline"
                                style="padding: 8px 15px; font-size: 0.8rem; margin-right: 5px; color: var(--text-primary); border-color: rgba(255,255,255,0.2);">რედაქტირება</a>
                            <a href="delete_car.php?id=<?php echo $car['id']; ?>" class="btn btn-outline"
                                style="padding: 8px 15px; font-size: 0.8rem; color: #ff6b6b; border-color: rgba(255,107,107,0.3);"
                                onclick="return confirm('წავშალოთ?')">წაშლა</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (empty($cars)): ?>
            <p style="text-align: center; padding: 2rem;">მანქანები არ მოიძებნა.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
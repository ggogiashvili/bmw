<?php
/**
 * მოდელის დეტალური გვერდი
 * 
 * ამ გვერდზე:
 * 1. იღებს მოდელის სრულ ინფორმაციას (მოდელი, სერია, ძრავი, სპეციფიკაციები)
 * 2. აჩვენებს გალერეის ფოტოებს სლაიდერში
 * 3. აჩვენებს ტექნიკურ მახასიათებლებს
 * 4. საშუალებას აძლევს მომხმარებელს დაუმატოს რჩეულებში
 */
require_once 'config/db.php';
require_once 'includes/header.php';

// ID-ის შემოწმება
if (!isset($_GET['id'])) {
    redirect('models.php');
}

$id = sanitize($_GET['id']);

// მოდელის სრული ინფორმაციის მიღება (მოდელი, სერია, ძრავი, სპეციფიკაციები)
try {
    $sql = "SELECT m.*, s.name as series_name, s.description as series_desc, 
                   e.type as engine_type, e.horsepower, e.torque,
                   sp.fuel_economy, sp.acceleration, sp.weight_kg
            FROM models m
            JOIN series s ON m.series_id = s.id 
            LEFT JOIN engines e ON m.id = e.model_id
            LEFT JOIN specifications sp ON m.id = sp.model_id
            WHERE m.id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $car = $stmt->fetch();

    if (!$car) {
        echo "<div class='container'><p>მანქანა არ მოიძებნა.</p></div>";
        require_once 'includes/footer.php';
        exit;
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<div class="container" style="margin-top: 3rem;">
    <a href="models.php"
        style="color: var(--text-secondary); text-decoration: none; margin-bottom: 2rem; display: block;">
        <i class="fas fa-arrow-left"></i> უკან დაბრუნება
    </a>

    <div class="detail-header">
        <div class="detail-image"
            style="position: relative; overflow: hidden; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            <!-- გალერეის ფოტოების მიღება -->
            <?php
            // გალერეის ფოტოების მიღება ბაზიდან
            $gallery = $pdo->prepare("SELECT * FROM car_images WHERE model_id = ?");
            $gallery->execute([$id]);
            $images = $gallery->fetchAll();

            // მთავარი ფოტოს დამატება გალერეაში
            $all_images = [['image_path' => $car['image'] ?: 'default_bmw.jpg']];
            foreach ($images as $img) {
                $all_images[] = $img;
            }
            ?>

            <div class="detail-slider" style="display: flex; transition: transform 0.5s ease;">
                <?php foreach ($all_images as $img): ?>
                    <img src="uploads/<?php echo htmlspecialchars($img['image_path']); ?>"
                        style="width: 100%; min-width: 100%; object-fit: cover; aspect-ratio: 16/9;" alt="BMW Image">
                <?php endforeach; ?>
            </div>

            <?php if (count($all_images) > 1): ?>
                <button onclick="moveDetailSlide(-1)"
                    style="position: absolute; top: 50%; left: 10px; transform: translateY(-50%); background: rgba(0,0,0,0.5); border: none; color: white; padding: 10px; cursor: pointer; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">&#10094;</button>
                <button onclick="moveDetailSlide(1)"
                    style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); background: rgba(0,0,0,0.5); border: none; color: white; padding: 10px; cursor: pointer; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">&#10095;</button>

                <script>
                    let detailIndex = 0;
                    const totalSlides = <?php echo count($all_images); ?>;

                    /**
                     * დეტალური გვერდის სლაიდერის გადატანა
                     * @param {number} n -1 უკან, 1 წინ
                     */
                    function moveDetailSlide(n) {
                        detailIndex += n;
                        if (detailIndex >= totalSlides) detailIndex = 0; // თუ ბოლოზე მეტია, პირველზე
                        if (detailIndex < 0) detailIndex = totalSlides - 1; // თუ პირველზე ნაკლებია, ბოლოზე

                        // CSS transform-ით სლაიდერის გადატანა
                        document.querySelector('.detail-slider').style.transform = `translateX(-${detailIndex * 100}%)`;
                    }
                </script>
            <?php endif; ?>
        </div>
        <div class="detail-info glass-panel" style="padding: 2rem;">
            <span style="color: var(--bmw-blue); font-weight: bold; letter-spacing: 1px;">
                <?php echo htmlspecialchars($car['series_name']); ?>
            </span>
            <h1 style="font-size: 3rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($car['name']); ?></h1>
            <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                <?php echo nl2br(htmlspecialchars($car['description'] ? $car['description'] : $car['series_desc'])); ?>
            </p>
            <h2 style="color: var(--text-primary); margin-bottom: 2rem;">
                $<?php echo number_format($car['price']); ?>
            </h2>

            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <?php if (isLoggedIn()): ?>
                    <?php
                    // შემოწმება არის თუ არა მოდელი უკვე რჩეულებში
                    $is_favorite = false;
                    try {
                        $fav_check = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND model_id = ?");
                        $fav_check->execute([$_SESSION['user_id'], $id]);
                        $is_favorite = $fav_check->rowCount() > 0;
                    } catch (PDOException $e) {
                        // ცხრილი არ არსებობს
                    }
                    ?>
                    <a href="favorites.php?<?php echo $is_favorite ? 'remove' : 'add'; ?>=<?php echo $car['id']; ?>" 
                       class="btn <?php echo $is_favorite ? 'btn-primary' : 'btn-outline'; ?>"
                       style="<?php echo $is_favorite ? 'background: #ff6b6b; border-color: #ff6b6b;' : ''; ?>">
                        <i class="fas fa-heart"></i> <?php echo $is_favorite ? 'რჩეულებიდან წაშლა' : 'რჩეულებში დამატება'; ?>
                    </a>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="edit_car.php?id=<?php echo $car['id']; ?>" class="btn btn-primary">რედაქტირება</a>
                    <a href="delete_car.php?id=<?php echo $car['id']; ?>" class="btn btn-outline"
                        style="border-color: red; color: red;" onclick="return confirm('ნამდვილად გსურთ წაშლა?')">წაშლა</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <h2 style="margin-bottom: 2rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 1rem;">ტექნიკური
        მახასიათებლები</h2>

    <div class="specs-grid">
        <div class="glass-panel" style="padding: 1.5rem;">
            <h3>ძრავი</h3>
            <div style="margin-top: 1rem;">
                <div class="spec-item" style="margin-bottom: 1rem;">
                    <div>ტიპი</div>
                    <div><?php echo htmlspecialchars($car['engine_type'] ?? 'N/A'); ?></div>
                </div>
                <div class="spec-item" style="margin-bottom: 1rem;">
                    <div>ცხენის ძალა</div>
                    <div><?php echo htmlspecialchars($car['horsepower'] ?? 'N/A'); ?> hp</div>
                </div>
                <div class="spec-item">
                    <div>თორქი</div>
                    <div><?php echo htmlspecialchars($car['torque'] ?? 'N/A'); ?></div>
                </div>
            </div>
        </div>

        <div class="glass-panel" style="padding: 1.5rem;">
            <h3>მონაცემები</h3>
            <div style="margin-top: 1rem;">
                <div class="spec-item" style="margin-bottom: 1rem;">
                    <div>0-100 კმ/სთ</div>
                    <div><?php echo htmlspecialchars($car['acceleration'] ?? 'N/A'); ?></div>
                </div>
                <div class="spec-item" style="margin-bottom: 1rem;">
                    <div>წვა</div>
                    <div><?php echo htmlspecialchars($car['fuel_economy'] ?? 'N/A'); ?></div>
                </div>
                <div class="spec-item">
                    <div>წონა</div>
                    <div><?php echo htmlspecialchars($car['weight_kg'] ?? 'N/A'); ?> კგ</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
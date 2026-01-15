<?php
/**
 * ძიებისა და ფილტრაციის გვერდი
 * 
 * ამ გვერდზე:
 * 1. მომხმარებელი შეუძლია მოძებნოს მოდელები სახელით, სერიით, წლით, ფასით
 * 2. აჩვენებს ბარათების სახით
 */
require_once 'config/db.php';
require_once 'includes/header.php';

// GET პარამეტრების მიღება და გაწმენდა
$search_query = sanitize($_GET['q'] ?? '');
$series_filter = sanitize($_GET['series'] ?? '');
$year_filter = sanitize($_GET['year'] ?? '');
$price_min = sanitize($_GET['price_min'] ?? '');
$price_max = sanitize($_GET['price_max'] ?? '');

// SQL მოთხოვნის აგება დინამიურად
$sql = "SELECT m.*, s.name as series_name, s.description as series_desc,
               e.type as engine_type, e.horsepower, e.torque,
               sp.fuel_economy, sp.acceleration, sp.weight_kg
        FROM models m
        JOIN series s ON m.series_id = s.id
        LEFT JOIN engines e ON m.id = e.model_id
        LEFT JOIN specifications sp ON m.id = sp.model_id
        WHERE 1=1";

$params = [];

// ძიების ტექსტის დამატება (მოდელის სახელი, აღწერა, სერია)
if ($search_query) {
    $sql .= " AND (m.name LIKE ? OR m.description LIKE ? OR s.name LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// სერიის ფილტრი
if ($series_filter) {
    $sql .= " AND m.series_id = ?";
    $params[] = $series_filter;
}

// წლის ფილტრი
if ($year_filter) {
    $sql .= " AND m.year = ?";
    $params[] = $year_filter;
}

// მინიმალური ფასის ფილტრი
if ($price_min) {
    $sql .= " AND m.price >= ?";
    $params[] = $price_min;
}

// მაქსიმალური ფასის ფილტრი
if ($price_max) {
    $sql .= " AND m.price <= ?";
    $params[] = $price_max;
}

$sql .= " ORDER BY m.name ASC";

// მოთხოვნის შესრულება
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
} catch (PDOException $e) {
    $results = [];
}

// ფილტრებისთვის სერიებისა და წლების სიის მიღება
$all_series = $pdo->query("SELECT * FROM series ORDER BY name")->fetchAll();
$all_years = $pdo->query("SELECT DISTINCT year FROM models ORDER BY year DESC")->fetchAll();
?>

<div class="container" style="margin-top: 3rem;">
    <h1 style="margin-bottom: 2rem;">მოძებნა და ფილტრაცია</h1>

    <!-- Search and Filter Form -->
    <div class="glass-panel" style="padding: 2rem; margin-bottom: 2rem;">
        <form method="GET" action="search.php" class="search-form">
            <div class="form-group">
                <label>ძიება</label>
                <input type="text" name="q" class="form-control" placeholder="მოძებნეთ მოდელი, სერია..." 
                       value="<?php echo htmlspecialchars($search_query); ?>">
            </div>

            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); padding: 0; gap: 1rem;">
                <div class="form-group">
                    <label>სერია</label>
                    <select name="series" class="form-control">
                        <option value="">ყველა</option>
                        <?php foreach ($all_series as $series): ?>
                            <option value="<?php echo $series['id']; ?>" 
                                <?php echo $series_filter == $series['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($series['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>წელი</label>
                    <select name="year" class="form-control">
                        <option value="">ყველა</option>
                        <?php foreach ($all_years as $year_row): ?>
                            <option value="<?php echo $year_row['year']; ?>"
                                <?php echo $year_filter == $year_row['year'] ? 'selected' : ''; ?>>
                                <?php echo $year_row['year']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>მინ. ფასი ($)</label>
                    <input type="number" name="price_min" class="form-control" 
                           placeholder="0" value="<?php echo htmlspecialchars($price_min); ?>">
                </div>

                <div class="form-group">
                    <label>მაქს. ფასი ($)</label>
                    <input type="number" name="price_max" class="form-control" 
                           placeholder="1000000" value="<?php echo htmlspecialchars($price_max); ?>">
                </div>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">ძიება</button>
                <a href="search.php" class="btn btn-outline">გასუფთავება</a>
            </div>
        </form>
    </div>

    <!-- Results -->
    <div style="margin-bottom: 1rem;">
        <h2>შედეგები: <?php echo count($results); ?> მოდელი</h2>
    </div>

    <?php if (empty($results)): ?>
        <div class="glass-panel" style="padding: 3rem; text-align: center;">
            <p style="color: var(--text-secondary); font-size: 1.2rem;">მოძებნილი მოდელები არ მოიძებნა.</p>
            <a href="models.php" class="btn btn-primary" style="margin-top: 1rem;">იხილეთ ყველა მოდელი</a>
        </div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($results as $car): ?>
                <div class="card glass-panel">
                    <img src="uploads/<?php echo htmlspecialchars($car['image'] ?: 'default_bmw.jpg'); ?>"
                        alt="<?php echo htmlspecialchars($car['name']); ?>">
                    <div class="card-body">
                        <div class="card-info">
                            <span><?php echo htmlspecialchars($car['series_name']); ?></span>
                            <span><?php echo $car['year']; ?></span>
                        </div>
                        <h3 class="card-title"><?php echo htmlspecialchars($car['name']); ?></h3>
                        <?php if ($car['engine_type']): ?>
                            <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0.5rem 0;">
                                <?php echo htmlspecialchars($car['engine_type']); ?> | 
                                <?php echo $car['horsepower']; ?> hp
                            </p>
                        <?php endif; ?>
                        <p class="card-info" style="color: var(--bmw-blue); font-size: 1.1rem; font-weight: bold;">
                            $<?php echo number_format($car['price']); ?>
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

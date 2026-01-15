<?php
/**
 * მოდელის რედაქტირების გვერდი
 * 
 * ამ გვერდზე ადმინისტრატორი:
 * 1. იღებს არსებულ მოდელის ინფორმაციას
 * 2. საშუალებას აძლევს განაახლოს ყველა მონაცემი
 * 3. ატვირთავს ახალ ფოტოებს (თუ არის)
 * 4. წაშლის გალერეის ფოტოებს (თუ საჭიროა)
 */
require_once 'config/db.php';
require_once 'includes/header.php';

// ავტორიზაციისა და ID-ის შემოწმება
if (!isLoggedIn() || !isset($_GET['id'])) {
    redirect('index.php');
}

$id = sanitize($_GET['id']);
$error = '';

// არსებული მონაცემების მიღება
try {
    $stmt = $pdo->prepare("SELECT m.*, e.type as engine_type, e.horsepower, e.torque,
                           sp.fuel_economy, sp.acceleration, sp.weight_kg
                           FROM models m
                           LEFT JOIN engines e ON m.id = e.model_id
                           LEFT JOIN specifications sp ON m.id = sp.model_id
                           WHERE m.id = ?");
    $stmt->execute([$id]);
    $car = $stmt->fetch();

    if (!$car)
        redirect('models.php');

    $series_list = $pdo->query("SELECT * FROM series")->fetchAll();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $series_id = $_POST['series_id'];
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $year = $_POST['year'];
    $price = $_POST['price'];

    $engine_type = sanitize($_POST['engine_type']);
    $horsepower = $_POST['horsepower'];
    $torque = sanitize($_POST['torque']);

    $fuel_economy = sanitize($_POST['fuel_economy']);
    $acceleration = sanitize($_POST['acceleration']);
    $weight_kg = $_POST['weight_kg'];

    $image_sql_part = "";
    $params = [$series_id, $name, $year, $price];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed)) {
            $new_filename = uniqid() . '.' . $file_ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $new_filename)) {
                $image_sql_part = ", image = ?";
                $params[] = $new_filename;
            }
        }
    }

    $params[] = $id;

    // Handle Gallery Deletion
    if (isset($_POST['delete_gallery_image'])) {
        $img_id = sanitize($_POST['delete_gallery_image']);
        $stmt = $pdo->prepare("SELECT image_path FROM car_images WHERE id = ?");
        $stmt->execute([$img_id]);
        $img = $stmt->fetch();
        if ($img) {
            $pdo->prepare("DELETE FROM car_images WHERE id = ?")->execute([$img_id]);
        }
    }

    if (!$error && !isset($_POST['delete_gallery_image'])) {
        try {
            $pdo->beginTransaction();
            $sql = "UPDATE models SET series_id = ?, name = ?, description = ?, year = ?, price = ? $image_sql_part WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Handle Gallery Uploads
            if (isset($_FILES['gallery'])) {
                $count = count($_FILES['gallery']['name']);
                $stmt_img = $pdo->prepare("INSERT INTO car_images (model_id, image_path) VALUES (?, ?)");

                for ($i = 0; $i < $count; $i++) {
                    if ($_FILES['gallery']['error'][$i] === 0) {
                        $filename = $_FILES['gallery']['name'][$i];
                        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

                        if (in_array($file_ext, $allowed)) {
                            $new_filename = uniqid() . '_' . $i . '.' . $file_ext;
                            if (move_uploaded_file($_FILES['gallery']['tmp_name'][$i], 'uploads/' . $new_filename)) {
                                $stmt_img->execute([$id, $new_filename]);
                            }
                        }
                    }
                }
            }

            $pdo->prepare("DELETE FROM engines WHERE model_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM specifications WHERE model_id = ?")->execute([$id]);

            $stmt = $pdo->prepare("INSERT INTO engines (model_id, type, horsepower, torque) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id, $engine_type, $horsepower, $torque]);

            $stmt = $pdo->prepare("INSERT INTO specifications (model_id, fuel_economy, acceleration, weight_kg) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id, $fuel_economy, $acceleration, $weight_kg]);

            $pdo->commit();
            redirect("details.php?id=$id");

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "განახლება ვერ მოხერხდა: " . $e->getMessage();
        }
    }
}
?>

<div class="container" style="max-width: 800px; margin-top: 3rem;">
    <h1 style="margin-bottom: 2rem;">რედაქტირება</h1>

    <form method="POST" enctype="multipart/form-data" class="glass-panel" style="padding: 2rem;">
        <h3>ძირითადი ინფორმაცია</h3>
        <div class="form-group">
            <label>სერია</label>
            <select name="series_id" class="form-control" required>
                <?php foreach ($series_list as $series): ?>
                    <option value="<?php echo $series['id']; ?>" <?php echo ($series['id'] == $car['series_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($series['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid" style="padding: 0; gap: 1rem;">
            <div class="form-group">
                <label>მოდელის სახელი</label>
                <input type="text" name="name" class="form-control" required
                    value="<?php echo htmlspecialchars($car['name']); ?>">
            </div>
            <div class="form-group">
                <label>წელი</label>
                <input type="number" name="year" class="form-control" required
                    value="<?php echo htmlspecialchars($car['year']); ?>">
            </div>
        </div>

        <div class="grid" style="padding: 0; gap: 1rem;">
            <div class="form-group">
                <label>ფასი ($)</label>
                <input type="number" name="price" class="form-control" required step="0.01"
                    value="<?php echo htmlspecialchars($car['price']); ?>">
            </div>
            <div class="form-group">
                <label>ახალი ფოტო (არაა სავალდებული)</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <?php if ($car['image']): ?>
                    <small>მიმდინარე: <?php echo htmlspecialchars($car['image']); ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>დამატებითი ფოტოები (დამატება)</label>
                <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
            </div>
        </div>

        <div class="form-group">
            <label>აღწერა</label>
            <textarea name="description" class="form-control" rows="4"
                placeholder="მანქანის მოკლე აღწერა..."><?php echo htmlspecialchars($car['description'] ?? ''); ?></textarea>
        </div>

        <!-- Gallery Preview / Manage -->
        <?php
        $gallery = $pdo->prepare("SELECT * FROM car_images WHERE model_id = ?");
        $gallery->execute([$id]);
        $images = $gallery->fetchAll();
        ?>
        <?php if (!empty($images)): ?>
            <h4>გალერეა</h4>
            <div class="gallery-grid">
                <?php foreach ($images as $img): ?>
                    <div class="gallery-item">
                        <img src="uploads/<?php echo htmlspecialchars($img['image_path']); ?>">
                        <button type="submit" name="delete_gallery_image" value="<?php echo $img['id']; ?>"
                            class="delete-btn-badge" onclick="return confirm('წავშალოთ ფოტო?')">X</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <hr style="border-color: var(--glass-border); margin: 2rem 0;">

        <h3>ძრავის მონაცემები</h3>
        <div class="form-group">
            <label>ძრავის ტიპი</label>
            <input type="text" name="engine_type" class="form-control" required
                value="<?php echo htmlspecialchars($car['engine_type']); ?>">
        </div>
        <div class="grid" style="padding: 0; gap: 1rem;">
            <div class="form-group">
                <label>ცხენის ძალა</label>
                <input type="number" name="horsepower" class="form-control" required
                    value="<?php echo htmlspecialchars($car['horsepower']); ?>">
            </div>
            <div class="form-group">
                <label>თორქი</label>
                <input type="text" name="torque" class="form-control" required
                    value="<?php echo htmlspecialchars($car['torque']); ?>">
            </div>
        </div>

        <hr style="border-color: var(--glass-border); margin: 2rem 0;">

        <h3>სხვა მახასიათებლები</h3>
        <div class="grid" style="padding: 0; gap: 1rem;">
            <div class="form-group">
                <label>0-100 კმ/სთ</label>
                <input type="text" name="acceleration" class="form-control"
                    value="<?php echo htmlspecialchars($car['acceleration']); ?>">
            </div>
            <div class="form-group">
                <label>წვა</label>
                <input type="text" name="fuel_economy" class="form-control"
                    value="<?php echo htmlspecialchars($car['fuel_economy']); ?>">
            </div>
            <div class="form-group">
                <label>წონა (კგ)</label>
                <input type="number" name="weight_kg" class="form-control"
                    value="<?php echo htmlspecialchars($car['weight_kg']); ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 2rem; width: 100%;">განახლება</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
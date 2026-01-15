<?php
/**
 * ახალი მოდელის დამატების გვერდი
 * 
 * ამ გვერდზე ადმინისტრატორი:
 * 1. ამატებს ახალ მოდელს ბაზაში
 * 2. ამატებს ძრავის ინფორმაციას
 * 3. ამატებს სპეციფიკაციებს
 * 4. ატვირთავს მთავარ ფოტოს და გალერეის ფოტოებს
 */
require_once 'config/db.php';
require_once 'includes/header.php';

// ავტორიზაციის შემოწმება
if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

// სერიების სიის მიღება dropdown-ისთვის
$series_list = $pdo->query("SELECT * FROM series")->fetchAll();

// ფორმის დამუშავება
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // მოდელის მონაცემები
    $series_id = $_POST['series_id'];
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $year = $_POST['year'];
    $price = $_POST['price'];

    // ძრავის მონაცემები
    $engine_type = sanitize($_POST['engine_type']);
    $horsepower = $_POST['horsepower'];
    $torque = sanitize($_POST['torque']);

    // სპეციფიკაციების მონაცემები
    $fuel_economy = sanitize($_POST['fuel_economy']);
    $acceleration = sanitize($_POST['acceleration']);
    $weight_kg = $_POST['weight_kg'];

    // მთავარი ფოტოს ატვირთვა
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed)) {
            $new_filename = uniqid() . '.' . $file_ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $new_filename)) {
                $image = $new_filename;
            }
        } else {
            $error = "ფოტოს არასწორი ფორმატი.";
        }
    }

    // თუ შეცდომა არ არის, ბაზაში დამატება
    if (!$error) {
        try {
            $pdo->beginTransaction(); // Transaction-ის დაწყება

            // მოდელის დამატება
            $stmt = $pdo->prepare("INSERT INTO models (series_id, name, description, year, price, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$series_id, $name, $description, $year, $price, $image]);
            $model_id = $pdo->lastInsertId(); // ახლად დამატებული მოდელის ID

            // ძრავის ინფორმაციის დამატება
            $stmt = $pdo->prepare("INSERT INTO engines (model_id, type, horsepower, torque) VALUES (?, ?, ?, ?)");
            $stmt->execute([$model_id, $engine_type, $horsepower, $torque]);

            // სპეციფიკაციების დამატება
            $stmt = $pdo->prepare("INSERT INTO specifications (model_id, fuel_economy, acceleration, weight_kg) VALUES (?, ?, ?, ?)");
            $stmt->execute([$model_id, $fuel_economy, $acceleration, $weight_kg]);

            // გალერეის ფოტოების დამუშავება (რამდენიმე ფოტო)
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
                                $stmt_img->execute([$model_id, $new_filename]);
                            }
                        }
                    }
                }
            }

            $pdo->commit(); // Transaction-ის დასრულება
            redirect('models.php');

        } catch (Exception $e) {
            $pdo->rollBack(); // შეცდომის შემთხვევაში rollback
            $error = "შეცდომა: " . $e->getMessage();
        }
    }
}
?>

<div class="container" style="max-width: 800px; margin-top: 3rem;">
    <h1 style="margin-bottom: 2rem;">ახალი მანქანის დამატება</h1>

    <?php if ($error): ?>
        <div style="background: rgba(255,0,0,0.1); border: 1px solid red; color: red; padding: 10px; margin-bottom: 1rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="glass-panel" style="padding: 2rem;">
        <h3>ძირითადი ინფორმაცია</h3>
        <div class="form-group">
            <label>სერია</label>
            <select name="series_id" class="form-control" required>
                <?php foreach ($series_list as $series): ?>
                    <option value="<?php echo $series['id']; ?>"><?php echo htmlspecialchars($series['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid" style="padding: 0; gap: 1rem;">
            <div class="form-group">
                <label>მოდელის სახელი</label>
                <input type="text" name="name" class="form-control" required placeholder="მაგ: M3 Competition">
            </div>
            <div class="form-group">
                <label>წელი</label>
                <input type="number" name="year" class="form-control" required value="<?php echo date('Y'); ?>">
            </div>
        </div>

        <div class="grid" style="padding: 0; gap: 1rem;">
            <div class="form-group">
                <label>ფასი ($)</label>
                <input type="number" name="price" class="form-control" required step="0.01">
            </div>
            <div class="form-group">
                <label>ფოტო (მთავარი)</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label>დამატებითი ფოტოები (გალერეა)</label>
                <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
            </div>
        </div>

        <div class="form-group">
            <label>აღწერა</label>
            <textarea name="description" class="form-control" rows="4"
                placeholder="მანქანის მოკლე აღწერა..."></textarea>
        </div>

        <hr style="border-color: var(--glass-border); margin: 2rem 0;">

        <h3>ძრავის მონაცემები</h3>
        <div class="form-group">
            <label>ძრავის ტიპი</label>
            <input type="text" name="engine_type" class="form-control" required placeholder="მაგ: 3.0L TwinPower Turbo">
        </div>
        <div class="grid" style="padding: 0; gap: 1rem;">
            <div class="form-group">
                <label>ცხენის ძალა</label>
                <input type="number" name="horsepower" class="form-control" required>
            </div>
            <div class="form-group">
                <label>თორქი</label>
                <input type="text" name="torque" class="form-control" required placeholder="მაგ: 400 lb-ft">
            </div>
        </div>

        <hr style="border-color: var(--glass-border); margin: 2rem 0;">

        <h3>სხვა მახასიათებლები</h3>
        <div class="grid" style="padding: 0; gap: 1rem;">
            <div class="form-group">
                <label>0-100 კმ/სთ</label>
                <input type="text" name="acceleration" class="form-control" placeholder="მაგ: 3.8s">
            </div>
            <div class="form-group">
                <label>წვა</label>
                <input type="text" name="fuel_economy" class="form-control" placeholder="მაგ: 10 ლ/100კმ">
            </div>
            <div class="form-group">
                <label>წონა (კგ)</label>
                <input type="number" name="weight_kg" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 2rem; width: 100%;">დამატება</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
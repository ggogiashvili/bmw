<?php
/**
 * ავტორიზაციის გვერდი
 * 
 * ამ გვერდზე მომხმარებელი შედის სისტემაში:
 * 1. ამოწმებს ელ-ფოსტას და პაროლს
 * 2. ამოწმებს პაროლის hash-ს
 * 3. ინახავს session-ში მომხმარებლის ინფორმაციას
 * 4. ადმინისტრატორის შემთხვევაში გადამისამართებს ადმინ პანელზე
 */
require_once 'config/db.php';
require_once 'includes/header.php';

$error = '';
$success = '';

// რეგისტრაციის შემდეგ წარმატების შეტყობინება
if (isset($_GET['registered'])) {
    $success = "რეგისტრაცია წარმატებულია! გთხოვთ გაიაროთ ავტორიზაცია.";
}

// ფორმის გაგზავნის დამუშავება
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    // მომხმარებლის ძიება ბაზაში ელ-ფოსტის მიხედვით
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // პაროლის შემოწმება და session-ის შექმნა
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'] ?? 'user';

        // როლის მიხედვით გადამისამართება
        if ($_SESSION['role'] === 'admin') {
            redirect('admin_dashboard.php');
        } else {
            redirect('index.php');
        }
    } else {
        $error = "არასწორი მეილი ან პაროლი.";
    }
}
?>

<div class="auth-container glass-panel">
    <h2 style="text-align: center; margin-bottom: 2rem;">ავტორიზაცია</h2>

    <?php if ($success): ?>
        <div
            style="background: rgba(0,255,0,0.1); border: 1px solid green; color: lightgreen; padding: 10px; margin-bottom: 1rem; border-radius: 4px;">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div
            style="background: rgba(255,0,0,0.1); border: 1px solid red; color: red; padding: 10px; margin-bottom: 1rem; border-radius: 4px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>ელ-ფოსტა</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label>პაროლი</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%">შესვლა</button>
    </form>
    <p style="text-align: center; margin-top: 1rem; font-size: 0.9rem;">
        არ გაქვთ ანგარიში? <a href="register.php" style="color: var(--bmw-blue)">რეგისტრაცია</a>
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>
<?php
/**
 * რეგისტრაციის გვერდი
 * 
 * ამ გვერდზე მომხმარებელი რეგისტრირდება:
 * 1. ამოწმებს პაროლების დამთხვევას
 * 2. ამოწმებს მომხმარებლის სახელისა და ელ-ფოსტის უნიკალურობას
 * 3. შიფრავს პაროლს და ინახავს ბაზაში
 * 4. გადამისამართებს ავტორიზაციის გვერდზე
 */
require_once 'config/db.php';
require_once 'includes/header.php';

$error = '';

// ფორმის დამუშავება
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // პაროლების დამთხვევის შემოწმება
    if ($password !== $confirm_password) {
        $error = "პაროლები არ ემთხვევა.";
    } else {
        // მომხმარებლის სახელისა და ელ-ფოსტის უნიკალურობის შემოწმება
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);

        if ($stmt->rowCount() > 0) {
            $error = "მომხმარებლის სახელი ან მეილი დაკავებულია.";
        } else {
            // პაროლის შიფრაცია და მომხმარებლის დამატება ბაზაში
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");

            if ($stmt->execute([$username, $email, $hashed_password])) {
                redirect('login.php?registered=1');
            } else {
                $error = "რეგისტრაცია ვერ მოხერხდა.";
            }
        }
    }
}
?>

<div class="auth-container glass-panel">
    <h2 style="text-align: center; margin-bottom: 2rem;">რეგისტრაცია</h2>

    <?php if ($error): ?>
        <div
            style="background: rgba(255,0,0,0.1); border: 1px solid red; color: red; padding: 10px; margin-bottom: 1rem; border-radius: 4px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>მომხმარებლის სახელი</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label>ელ-ფოსტა</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label>პაროლი</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label>გაიმეორეთ პაროლი</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%">რეგისტრაცია</button>
    </form>
    <p style="text-align: center; margin-top: 1rem; font-size: 0.9rem;">
        უკვე გაქვთ ანგარიში? <a href="login.php" style="color: var(--bmw-blue)">შესვლა</a>
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>
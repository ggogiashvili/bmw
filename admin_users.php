<?php
/**
 * მომხმარებლების მართვის გვერდი
 * 
 * ამ გვერდზე ადმინისტრატორი:
 * 1. ხედავს ყველა მომხმარებელს
 * 2. შეუძლია მომხმარებლის წაშლა (საკუთარი თავის გარდა)
 * 3. შეუძლია პაროლის შეცვლა (admin/user)
 */
require_once 'config/db.php';
require_once 'includes/header.php';

// ადმინისტრატორის შემოწმება
if (!isLoggedIn() || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    redirect('index.php');
}

// POST მოთხოვნის დამუშავება
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // მომხმარებლის წაშლა
    if (isset($_POST['delete_id'])) {
        $id = sanitize($_POST['delete_id']);
        if ($id != $_SESSION['user_id']) { // თვითონ თავის წაშლა არ შეიძლება
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
        }
    } elseif (isset($_POST['toggle_role_id'])) {
        // როლის შეცვლა (admin <-> user)
        $id = sanitize($_POST['toggle_role_id']);
        $current_role = sanitize($_POST['current_role']);
        $new_role = ($current_role === 'admin') ? 'user' : 'admin';

        if ($id != $_SESSION['user_id']) { // თვითონ თავის როლის შეცვლა არ შეიძლება
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$new_role, $id]);
        }
    }
    echo "<script>window.location.href='admin_users.php';</script>";
    exit;
}

// ყველა მომხმარებლის მიღება
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<div class="container" style="margin-top: 3rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>მომხმარებლები</h1>
        <a href="admin_dashboard.php" class="btn btn-outline">უკან დაბრუნება</a>
    </div>

    <div class="glass-panel" style="overflow-x: auto; padding: 1rem;">
        <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="border-bottom: 1px solid var(--glass-border); padding: 1rem;">ID</th>
                    <th style="border-bottom: 1px solid var(--glass-border); padding: 1rem;">სახელი</th>
                    <th style="border-bottom: 1px solid var(--glass-border); padding: 1rem;">ელ-ფოსტა</th>
                    <th style="border-bottom: 1px solid var(--glass-border); padding: 1rem;">როლი</th>
                    <th style="border-bottom: 1px solid var(--glass-border); padding: 1rem;">რეგისტრაციის თარიღი</th>
                    <th style="border-bottom: 1px solid var(--glass-border); padding: 1rem;">მოქმედება</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td style="padding: 1rem; border-bottom: 1px solid var(--glass-border);">#<?php echo $user['id']; ?>
                        </td>
                        <td style="padding: 1rem; border-bottom: 1px solid var(--glass-border); font-weight: bold;">
                            <?php echo htmlspecialchars($user['username']); ?></td>
                        <td style="padding: 1rem; border-bottom: 1px solid var(--glass-border);">
                            <?php echo htmlspecialchars($user['email']); ?></td>
                        <td style="padding: 1rem; border-bottom: 1px solid var(--glass-border);">
                            <span
                                style="color: <?php echo $user['role'] === 'admin' ? 'var(--bmw-blue)' : 'var(--text-secondary)'; ?>; font-weight: 500;">
                                <?php echo ($user['role'] === 'admin') ? 'ადმინისტრატორი' : 'მომხმარებელი'; ?>
                            </span>
                        </td>
                        <td style="padding: 1rem; border-bottom: 1px solid var(--glass-border); font-size: 0.9rem;">
                            <?php echo date('Y-m-d', strtotime($user['created_at'])); ?>
                        </td>
                        <td style="padding: 1rem; border-bottom: 1px solid var(--glass-border);">
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <div style="display: flex; gap: 10px;">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="toggle_role_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="current_role" value="<?php echo $user['role'] ?? 'user'; ?>">
                                        <button type="submit" class="btn btn-outline"
                                            style="padding: 5px 10px; font-size: 0.8rem;">
                                            როლის შეცვლა
                                        </button>
                                    </form>

                                    <form method="POST" style="display:inline;"
                                        onsubmit="return confirm('წავშალოთ მომხმარებელი?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-outline"
                                            style="padding: 5px 10px; font-size: 0.8rem; color: #ff6b6b; border-color: rgba(255,107,107,0.3);">
                                            წაშლა
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span style="color: var(--text-secondary); font-size: 0.8rem;">(თქვენ)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
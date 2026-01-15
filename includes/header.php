<?php require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="ka">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMW Experience</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Optional: Add Georgian font support if needed, but Outfit usually supports latin. Will stick to Outfit or system fallbacks -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <header>
        <nav class="container">
            <a href="index.php" class="logo">
                <i class="fab fa-bmw"></i> BMW <span style="color: var(--bmw-blue)">Experience</span>
            </a>
            <div class="nav-links">
                <a href="index.php">მთავარი</a>
                <a href="models.php">მოდელები</a>
                <a href="search.php">ძიება</a>
                <?php if (isLoggedIn()): ?>
                    <a href="favorites.php" style="position: relative;">
                        <i class="fas fa-heart"></i> რჩეულები
                        <?php
                        try {
                            $fav_count = $pdo->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = ?");
                            $fav_count->execute([$_SESSION['user_id']]);
                            $count = $fav_count->fetch()['count'];
                            if ($count > 0):
                        ?>
                            <span style="position: absolute; top: -8px; right: -8px; background: #ff6b6b; color: white; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;"><?php echo $count; ?></span>
                        <?php 
                            endif;
                        } catch (PDOException $e) {
                            // Table doesn't exist yet, skip count
                        }
                        ?>
                    </a>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="admin_dashboard.php"
                            style="color: var(--bmw-blue); font-weight: bold; position: relative;">ადმინ პანელი</a>
                    <?php endif; ?>
                    <a href="logout.php">გასვლა</a>
                <?php else: ?>
                    <a href="login.php">შესვლა</a>
                    <a href="register.php" class="btn btn-primary">რეგისტრაცია</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main class="container">
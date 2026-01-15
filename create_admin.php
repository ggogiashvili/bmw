<?php
/**
 * ადმინისტრატორის მომხმარებლის შექმნის სკრიპტი
 * 
 * ამ სკრიპტი:
 * 1. ამოწმებს არსებობს თუ არა ადმინისტრატორი
 * 2. თუ არ არსებობს, ქმნის ახალ ადმინისტრატორს
 * 3. აშიფრავს პაროლს password_hash-ით
 * 4. ანიჭებს 'admin' როლს
 */
require_once 'config/db.php';

$username = 'admin';
$email = 'admin@admin.com';
$password = 'admin123';

try {
    // ადმინისტრატორის არსებობის შემოწმება
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        echo "Admin user already exists.\n";
        exit;
    }

    // პაროლის აშიფრაცია
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // ადმინისტრატორის დამატება ბაზაში
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->execute([$username, $email, $hashed_password]);

    echo "Admin user created successfully.\n";
    echo "Username: $username\n";
    echo "Email: $email\n";
    echo "Password: $password\n";

} catch (PDOException $e) {
    echo "Error creating admin: " . $e->getMessage() . "\n";
}
?>
<?php
/**
 * მოდელის წაშლის ფუნქცია
 * 
 * ამ ფაილში:
 * 1. წაშლის მოდელს ბაზიდან
 * 2. ON DELETE CASCADE-ის გამო ავტომატურად წაიშლება:
 *    - ძრავის ინფორმაცია
 *    - სპეციფიკაციები
 *    - გალერეის ფოტოები
 * 3. გადამისამართებს მოდელების სიაზე
 */
require_once 'config/db.php';
require_once 'includes/functions.php';

// ავტორიზაციისა და ID-ის შემოწმება
if (!isLoggedIn() || !isset($_GET['id'])) {
    redirect('index.php');
}

$id = sanitize($_GET['id']);

try {
    // ON DELETE CASCADE-ის გამო საკმარისია მხოლოდ models ცხრილიდან წაშლა
    // ძრავის, სპეციფიკაციებისა და ფოტოების ჩანაწერები ავტომატურად წაიშლება MySQL-ის მიერ

    $stmt = $pdo->prepare("DELETE FROM models WHERE id = ?");
    $stmt->execute([$id]);

    redirect('models.php?deleted=1');

} catch (PDOException $e) {
    die("შეცდომა წაშლისას: " . $e->getMessage());
}
?>
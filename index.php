<?php
require_once 'config/db.php';
require_once 'includes/header.php';

/**
 * მთავარი გვერდი - აჩვენებს სლაიდერს და რჩეულ მოდელებს
 * 
 * ამ გვერდზე:
 * 1. იღებს სლაიდერისთვის მოდელებს (is_slider = 1)
 * 2. იღებს ბოლო 3 მოდელს როგორც რჩეულებს
 * 3. აჩვენებს სლაიდერს JavaScript-ით
 */

// სლაიდერისთვის მოდელების მიღება (ის მოდელები რომლებიც სლაიდერში უნდა გამოჩნდნენ)
$slider_items = $pdo->query("SELECT m.*, s.name as series_name FROM models m JOIN series s ON m.series_id = s.id WHERE m.is_slider = 1 ORDER BY m.id DESC")->fetchAll();
if (empty($slider_items)) {
    // თუ სლაიდერის მოდელები არ არის, გამოიყენება fallback ჰერო სექცია
}

// რჩეული მოდელების მიღება (ბოლო 3 მოდელი გრიდისთვის)
try {
    $stmt = $pdo->query("SELECT m .*, s.name as series_name 
                         FROM models m 
                         JOIN series s ON m.series_id = s.id 
                         ORDER BY m.id DESC LIMIT 3");
    $featured_cars = $stmt->fetchAll();
} catch (PDOException $e) {
    $featured_cars = [];
}
?>

<!-- Simple CSS/JS Slider -->
<div class="slider-container"
    style="position: relative; height: 85vh; overflow: hidden; margin-top: -1rem; margin-inline: -5%; width: 110%;">
    <?php foreach ($slider_items as $index => $item): ?>
        <div class="slide fade" style="display: <?php echo $index === 0 ? 'flex' : 'none'; ?>; 
                    height: 100%; 
                    background: linear-gradient(to bottom, rgba(0,0,0,0.3), var(--bmw-dark)), url('uploads/<?php echo htmlspecialchars($item['image'] ?: 'default_bmw.jpg'); ?>');
                    background-size: cover;
                    background-position: center;
                    align-items: center;
                    justify-content: center;
                    text-align: center;
                    width: 100%;">
            <div class="container">
                <h1 style="text-shadow: 0 4px 20px rgba(0,0,0,0.8); font-size: 4rem; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($item['name']); ?></h1>
                <p
                    style="font-size: 1.5rem; margin-bottom: 2rem; text-transform: uppercase; letter-spacing: 2px; color: white;">
                    <?php echo htmlspecialchars($item['series_name']); ?></p>
                <a href="details.php?id=<?php echo $item['id']; ?>" class="btn btn-primary">ვრცლად</a>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Fallback Hero IF NO SLIDER -->
    <?php if (empty($slider_items)): ?>
        <div class="slide fade"
            style="display: flex; height: 100%; background: linear-gradient(to bottom, rgba(0,0,0,0.3), var(--bmw-dark)), url('assets/images/hero-bg.jpg'); background-size: cover; background-position: center; align-items: center; justify-content: center; text-align: center; width: 100%;">
            <div class="container">
                <h1 style="text-shadow: 0 2px 10px rgba(0,0,0,0.8);">შეუცვლელი მართვის გამოცდილება</h1>
                <p style="text-shadow: 0 2px 10px rgba(0,0,0,0.8); color: white;">მართვის საუკეთესო მანქანა</p>
                <a href="models.php" class="btn btn-primary">იხილეთ მოდელები</a>
            </div>
        </div>
    <?php endif; ?>

    <button class="prev" onclick="moveSlide(-1)"
        style="position: absolute; top: 50%; left: 20px; background: rgba(0,0,0,0.5); border: none; color: white; padding: 1rem; cursor: pointer; border-radius: 50%; z-index: 10;">&#10094;</button>
    <button class="next" onclick="moveSlide(1)"
        style="position: absolute; top: 50%; right: 20px; background: rgba(0,0,0,0.5); border: none; color: white; padding: 1rem; cursor: pointer; border-radius: 50%; z-index: 10;">&#10095;</button>
</div>

<script>
    let slideIndex = 1;

    // ავტომატური სლაიდერის გაშვება (5 წამში ერთხელ)
    let slideInterval = setInterval(function () { moveSlide(1); }, 5000);

    /**
     * სლაიდერის გადატანა (წინ ან უკან)
     * @param {number} n -1 უკან, 1 წინ
     */
    function moveSlide(n) {
        showSlides(slideIndex += n);
        clearInterval(slideInterval); // ხელით დაჭერისას ტაიმერის გადატვირთვა
        slideInterval = setInterval(function () { moveSlide(1); }, 5000);
    }

    /**
     * აჩვენებს კონკრეტულ სლაიდს
     * @param {number} n სლაიდის ინდექსი
     */
    function showSlides(n) {
        let slides = document.getElementsByClassName("slide");
        if (n > slides.length) { slideIndex = 1 } // თუ ბოლოზე მეტია, პირველზე გადავა
        if (n < 1) { slideIndex = slides.length } // თუ პირველზე ნაკლებია, ბოლოზე გადავა
        for (let i = 0; i < slides.length; i++) {
            slides[i].style.display = "none"; // ყველა სლაიდის დამალვა
        }
        if (slides.length > 0) slides[slideIndex - 1].style.display = "flex"; // მიმდინარე სლაიდის ჩვენება
    }
</script>

<div class="container">
    <h2 style="margin-top: 4rem; margin-bottom: 2rem; border-left: 4px solid var(--bmw-blue); padding-left: 1rem;">
        რჩეული მოდელები
    </h2>

    <div class="grid">
        <?php foreach ($featured_cars as $car): ?>
            <div class="card glass-panel">
                <img src="uploads/<?php echo htmlspecialchars($car['image'] ?: 'default_bmw.jpg'); ?>"
                    alt="<?php echo htmlspecialchars($car['name']); ?>">
                <div class="card-body">
                    <div class="card-info">
                        <span><?php echo htmlspecialchars($car['series_name']); ?></span>
                        <span><?php echo $car['year']; ?></span>
                    </div>
                    <h3 class="card-title"><?php echo htmlspecialchars($car['name']); ?></h3>
                    <p class="card-info" style="color: var(--bmw-blue); font-size: 1.1rem; font-weight: bold;">
                        $<?php echo number_format($car['price']); ?>
                    </p>
                    <a href="details.php?id=<?php echo $car['id']; ?>" class="btn btn-outline"
                        style="width: 100%; text-align: center; margin-top: auto;">
                        მონაცემები
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
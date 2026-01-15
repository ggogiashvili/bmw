</main>
<footer style="margin-top: 5rem; padding: 3rem 2rem; text-align: center; color: var(--text-secondary); border-top: 1px solid var(--glass-border);">
    <div class="container">
        <div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 2rem; margin-bottom: 2rem;">
            <div>
                <h4 style="color: var(--text-primary); margin-bottom: 1rem;">ბმულები</h4>
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <a href="index.php" style="color: var(--text-secondary); text-decoration: none;">მთავარი</a>
                    <a href="models.php" style="color: var(--text-secondary); text-decoration: none;">მოდელები</a>
                    <a href="search.php" style="color: var(--text-secondary); text-decoration: none;">ძიება</a>
                </div>
            </div>
            <div>
                <h4 style="color: var(--text-primary); margin-bottom: 1rem;">ინფორმაცია</h4>
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <?php if (isLoggedIn()): ?>
                        <a href="favorites.php" style="color: var(--text-secondary); text-decoration: none;">რჩეულები</a>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <h4 style="color: var(--text-primary); margin-bottom: 1rem;">კონტაქტი</h4>
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <p><i class="fas fa-envelope"></i> info@bmw.ge</p>
                    <p><i class="fas fa-phone"></i> +995 32 2 12 34 56</p>
                </div>
            </div>
        </div>
        <p style="border-top: 1px solid var(--glass-border); padding-top: 2rem; margin-top: 2rem;">
            &copy; <?php echo date('Y'); ?> BMW Experience. Developed by Tazo Pantsulaia
        </p>
    </div>
</footer>
</body>

</html>
<?php
require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/layout.php';
$user = current_user($db);
page_header('Domů', $user);
?>
<section class="hero">
    <div>
        <span class="eyebrow">MVP</span>
        <h1>Dárky bez trapných situací.</h1>
        <p>Dárkomat sdílí přání v rodině nebo skupině a zároveň schová, kdo už dárek rezervoval.</p>
        <?php if ($user): ?>
            <a class="button" href="/dashboard.php">Přejít do aplikace</a>
        <?php else: ?>
            <a class="button" href="/register.php">Začít</a>
            <a class="button secondary" href="/login.php">Přihlásit se</a>
        <?php endif; ?>
    </div>
    <div class="hero-card">
        <div class="gift-preview">
            <div class="gift-image placeholder">🎁</div>
            <div>
                <strong>Nová káva do kávovaru</strong>
                <p>Konkrétní model, odkaz na e-shop…</p>
                <span class="pill">Vánoce</span>
            </div>
        </div>
        <div class="secret">🤫 Rezervaci uvidí ostatní. Autor přání ne.</div>
    </div>
</section>
<?php page_footer(); ?>

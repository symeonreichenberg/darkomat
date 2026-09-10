<?php
function page_header(string $title, ?array $user = null): void {
?>
<!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · Dárkomat</title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="/"><?= e('Dárkomat') ?></a>
    <?php if ($user): ?>
        <nav>
            <a href="/dashboard.php">Moje skupiny</a>
            <a href="/gift_new.php">Přidat dárek</a>
            <a href="/event_new.php">Přidat událost</a>
            <a href="/logout.php">Odhlásit</a>
        </nav>
    <?php endif; ?>
</header>
<main class="container">
<?php foreach (consume_flashes() as $flash): ?>
    <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endforeach; ?>
<?php
}

function page_footer(): void {
?>
</main>
<footer class="footer">Dárkomat MVP · PHP + SQLite</footer>
</body>
</html>
<?php
}

<?php
require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/layout.php';

$user = require_login($db);
$groups = user_groups($db, (int)$user['id']);

if (!$groups) {
    page_header('Moje skupiny', $user);
    ?>
    <div class="empty-state">
        <h1>Vítej v Dárkomatu, <?= e($user['name']) ?>.</h1>
        <p>Nejdřív vytvoř skupinu nebo se přidej pomocí pozvánkového kódu.</p>
        <div class="actions">
            <a class="button" href="/group_new.php">Vytvořit skupinu</a>
            <a class="button secondary" href="/group_join.php">Připojit se ke skupině</a>
        </div>
    </div>
    <?php
    page_footer();
    exit;
}

$group = selected_group($db, (int)$user['id']);
$membersStmt = $db->prepare(
    'SELECT u.id, u.name FROM users u
     JOIN group_members gm ON gm.user_id = u.id
     WHERE gm.group_id = ? ORDER BY u.name'
);
$membersStmt->execute([$group['id']]);
$members = $membersStmt->fetchAll();

$eventsStmt = $db->prepare('SELECT * FROM events WHERE group_id = ? ORDER BY event_date IS NULL, event_date');
$eventsStmt->execute([$group['id']]);
$events = $eventsStmt->fetchAll();

$giftsStmt = $db->prepare(
    'SELECT g.id, g.group_id, g.owner_id, g.event_id, g.title, g.description, g.shop_url,
            g.image_mime, u.name AS owner_name, e.title AS event_title,
            CASE WHEN g.image_data IS NULL THEN 0 ELSE 1 END AS has_image,
            CASE WHEN r.id IS NULL THEN 0 ELSE 1 END AS reserved
     FROM gifts g
     JOIN users u ON u.id = g.owner_id
     LEFT JOIN events e ON e.id = g.event_id
     LEFT JOIN reservations r ON r.gift_id = g.id
     WHERE g.group_id = ?
     ORDER BY u.name, g.created_at DESC'
);
$giftsStmt->execute([$group['id']]);
$gifts = $giftsStmt->fetchAll();

$byOwner = [];
foreach ($gifts as $gift) {
    $byOwner[$gift['owner_name']][] = $gift;
}

page_header($group['name'], $user);
?>
<div class="page-head">
    <div>
        <span class="eyebrow">SKUPINA</span>
        <h1><?= e($group['name']) ?></h1>
        <p><?= count($members) ?> členů · Kód pro pozvání: <code><?= e($group['invite_code']) ?></code></p>
    </div>
    <div class="actions">
        <a class="button" href="/gift_new.php?group=<?= (int)$group['id'] ?>">+ Přidat dárek</a>
        <a class="button secondary" href="/group_join.php">Připojit člena</a>
    </div>
</div>

<?php if ($events): ?>
<div class="events">
    <?php foreach ($events as $event): ?>
        <span class="event-chip">📅 <?= e($event['title']) ?><?= $event['event_date'] ? ' · ' . e($event['event_date']) : '' ?></span>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php foreach ($byOwner as $ownerName => $ownerGifts): ?>
<section class="wishlist">
    <h2><?= e($ownerName) ?><?= $ownerName === $user['name'] ? ' <span class="muted">(já)</span>' : '' ?></h2>
    <div class="gift-grid">
    <?php foreach ($ownerGifts as $gift): ?>
        <?php $isMine = (int)$gift['owner_id'] === (int)$user['id']; ?>
        <article class="gift-card">
            <?php if ((int)$gift['has_image']): ?>
                <img src="/image.php?id=<?= (int)$gift['id'] ?>" alt="">
            <?php else: ?>
                <div class="gift-image placeholder">🎁</div>
            <?php endif; ?>
            <div class="gift-body">
                <div class="gift-title-row">
                    <h3><?= e($gift['title']) ?></h3>
                    <?php if ($gift['event_title']): ?><span class="pill"><?= e($gift['event_title']) ?></span><?php endif; ?>
                </div>
                <?php if ($gift['description']): ?><p><?= nl2br(e($gift['description'])) ?></p><?php endif; ?>
                <?php if ($gift['shop_url']): ?><a target="_blank" rel="noopener" href="<?= e($gift['shop_url']) ?>">Otevřít e-shop ↗</a><?php endif; ?>

                <?php if (!$isMine): ?>
                    <?php if ((int)$gift['reserved']): ?>
                        <div class="reserved">✓ Někdo tento dárek rezervoval</div>
                    <?php else: ?>
                        <form method="post" action="/gift_reserve.php" class="inline-form">
                            <input type="hidden" name="gift_id" value="<?= (int)$gift['id'] ?>">
                            <input type="hidden" name="group_id" value="<?= (int)$group['id'] ?>">
                            <button class="button small" type="submit">🎁 Koupím tento dárek</button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="owner-note">🔒 Rezervace tohoto dárku jsou před tebou skryté.</div>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
    </div>
</section>
<?php endforeach; ?>

<?php if (!$gifts): ?>
<div class="empty-state"><h2>Zatím žádná přání.</h2><p>Buď první a přidej svůj dárek.</p></div>
<?php endif; ?>

<div class="members">
<h2>Členové skupiny</h2>
<div class="member-list"><?php foreach ($members as $member): ?><span>👤 <?= e($member['name']) ?></span><?php endforeach; ?></div>
</div>
<?php page_footer(); ?>

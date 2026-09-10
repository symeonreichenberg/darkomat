<?php
require __DIR__ . '/../src/bootstrap.php';
$user = require_login($db);

$giftId = (int)($_POST['gift_id'] ?? 0);
$groupId = (int)($_POST['group_id'] ?? 0);

$stmt = $db->prepare(
    'SELECT g.* FROM gifts g
     JOIN group_members gm ON gm.group_id = g.group_id
     WHERE g.id = ? AND gm.user_id = ? AND g.group_id = ?'
);
$stmt->execute([$giftId, $user['id'], $groupId]);
$gift = $stmt->fetch();

if (!$gift) {
    flash('error', 'Dárek nebyl nalezen v této skupině.');
    redirect('/dashboard.php');
}

if ((int)$gift['owner_id'] === (int)$user['id']) {
    flash('error', 'Nemůžeš rezervovat vlastní dárek.');
    redirect('/dashboard.php?group=' . $groupId);
}

$stmt = $db->prepare('INSERT OR IGNORE INTO reservations (gift_id, reserved_by) VALUES (?, ?)');
$stmt->execute([$giftId, $user['id']]);

flash('success', 'Dárek je rezervovaný. Autor přání o tom nebude informován.');
redirect('/dashboard.php?group=' . $groupId);

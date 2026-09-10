<?php
require __DIR__ . '/../src/bootstrap.php';
$user = require_login($db);

$giftId = (int)($_POST['gift_id'] ?? 0);
$groupId = (int)($_POST['group_id'] ?? 0);

$stmt = $db->prepare('DELETE FROM reservations WHERE gift_id = ? AND reserved_by = ?');
$stmt->execute([$giftId, $user['id']]);

flash('success', 'Rezervace byla zrušena.');
redirect('/dashboard.php?group=' . $groupId);

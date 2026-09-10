<?php
require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/layout.php';
$user = require_login($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        flash('error', 'Název skupiny je povinný.');
    } else {
        do {
            $code = strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
            $check = $db->prepare('SELECT 1 FROM groups WHERE invite_code = ?');
            $check->execute([$code]);
        } while ($check->fetchColumn());

        $db->beginTransaction();
        $stmt = $db->prepare('INSERT INTO groups (name, invite_code, created_by) VALUES (?, ?, ?)');
        $stmt->execute([$name, $code, $user['id']]);
        $groupId = (int)$db->lastInsertId();
        $stmt = $db->prepare('INSERT INTO group_members (group_id, user_id) VALUES (?, ?)');
        $stmt->execute([$groupId, $user['id']]);
        $db->commit();

        $_SESSION['group_id'] = $groupId;
        flash('success', 'Skupina vytvořena. Pozvi ostatní pomocí kódu ' . $code . '.');
        redirect('/dashboard.php');
    }
}
page_header('Nová skupina', $user);
?>
<div class="form-card narrow">
<h1>Vytvořit skupinu</h1>
<form method="post">
<label>Název skupiny<input name="name" placeholder="Novákovi" required maxlength="100"></label>
<button class="button" type="submit">Vytvořit</button>
</form>
</div>
<?php page_footer(); ?>

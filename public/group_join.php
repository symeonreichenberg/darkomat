<?php
require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/layout.php';
$user = require_login($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['invite_code'] ?? ''));
    $stmt = $db->prepare('SELECT * FROM groups WHERE invite_code = ?');
    $stmt->execute([$code]);
    $group = $stmt->fetch();

    if (!$group) {
        flash('error', 'Skupina s tímto kódem neexistuje.');
    } else {
        $stmt = $db->prepare('INSERT OR IGNORE INTO group_members (group_id, user_id) VALUES (?, ?)');
        $stmt->execute([$group['id'], $user['id']]);
        $_SESSION['group_id'] = (int)$group['id'];
        flash('success', 'Připojil/a ses ke skupině ' . $group['name'] . '.');
        redirect('/dashboard.php');
    }
}
page_header('Připojit se', $user);
?>
<div class="form-card narrow">
<h1>Připojit se ke skupině</h1>
<form method="post">
<label>Pozvánkový kód<input name="invite_code" placeholder="AB12CD34" required maxlength="8"></label>
<button class="button" type="submit">Připojit</button>
</form>
</div>
<?php page_footer(); ?>

<?php
require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/layout.php';
$user = require_login($db);
$group = selected_group($db, (int)$user['id']);
if (!$group) redirect('/dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $date = trim($_POST['event_date'] ?? '');
    if ($title === '') {
        flash('error', 'Název události je povinný.');
    } else {
        $stmt = $db->prepare('INSERT INTO events (group_id, title, event_date) VALUES (?, ?, ?)');
        $stmt->execute([$group['id'], $title, $date ?: null]);
        flash('success', 'Událost přidána.');
        redirect('/dashboard.php');
    }
}
page_header('Nová událost', $user);
?>
<div class="form-card narrow">
<h1>Nová událost</h1>
<form method="post">
<label>Název *<input name="title" placeholder="Vánoce" required maxlength="150"></label>
<label>Datum <input type="date" name="event_date"></label>
<button class="button" type="submit">Přidat událost</button>
</form>
</div>
<?php page_footer(); ?>

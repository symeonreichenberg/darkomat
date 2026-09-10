<?php
require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/layout.php';
$user = require_login($db);
$group = selected_group($db, (int)$user['id']);

if (!$group) {
    flash('error', 'Nejdřív vytvoř nebo vyber skupinu.');
    redirect('/dashboard.php');
}

$eventsStmt = $db->prepare('SELECT * FROM events WHERE group_id = ? ORDER BY event_date IS NULL, event_date');
$eventsStmt->execute([$group['id']]);
$events = $eventsStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $shopUrl = trim($_POST['shop_url'] ?? '');
    $eventId = (int)($_POST['event_id'] ?? 0);
    $imageData = null;
    $imageMime = null;

    if ($title === '') {
        flash('error', 'Název dárku je povinný.');
    } elseif ($shopUrl !== '' && !filter_var($shopUrl, FILTER_VALIDATE_URL)) {
        flash('error', 'Odkaz na e-shop není platná URL.');
    } else {
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['image']['size'] > $config['upload_max_bytes']) {
                flash('error', 'Obrázek je příliš velký (max. 5 MB).');
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($_FILES['image']['tmp_name']);
                $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($mime, $allowed, true)) {
                    flash('error', 'Povolené jsou JPG, PNG a WebP.');
                } else {
                    $imageData = file_get_contents($_FILES['image']['tmp_name']);
                    $imageMime = $mime;
                }
            }
        }

        $validEvent = null;
        if ($eventId) {
            $stmt = $db->prepare('SELECT id FROM events WHERE id = ? AND group_id = ?');
            $stmt->execute([$eventId, $group['id']]);
            $validEvent = $stmt->fetchColumn();
        }

        $stmt = $db->prepare(
            'INSERT INTO gifts (group_id, owner_id, event_id, title, description, shop_url, image_data, image_mime)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bindValue(1, $group['id'], PDO::PARAM_INT);
        $stmt->bindValue(2, $user['id'], PDO::PARAM_INT);
        $stmt->bindValue(3, $validEvent ?: null, $validEvent ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(4, $title);
        $stmt->bindValue(5, $description ?: null);
        $stmt->bindValue(6, $shopUrl ?: null);
        if ($imageData !== null) {
            $stmt->bindValue(7, $imageData, PDO::PARAM_LOB);
        } else {
            $stmt->bindValue(7, null, PDO::PARAM_NULL);
        }
        $stmt->bindValue(8, $imageMime ?: null);
        $stmt->execute();
        flash('success', 'Dárek byl přidán na tvůj seznam.');
        redirect('/dashboard.php');
    }
}

page_header('Přidat dárek', $user);
?>
<div class="form-card">
<h1>Přidat dárek</h1>
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="group_id" value="<?= (int)$group['id'] ?>">
    <label>Název *<input name="title" required maxlength="200" placeholder="Např. Kniha Atomic Habits"></label>
    <label>Popis<textarea name="description" rows="5" placeholder="Velikost, barva, konkrétní varianta…"></textarea></label>
    <label>Odkaz na e-shop<input type="url" name="shop_url" placeholder="https://…"></label>
    <label>Obrázek<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
    <label>Událost
        <select name="event_id">
            <option value="0">Bez události</option>
            <?php foreach ($events as $event): ?>
                <option value="<?= (int)$event['id'] ?>"><?= e($event['title']) ?><?= $event['event_date'] ? ' · ' . e($event['event_date']) : '' ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="button" type="submit">Přidat dárek</button>
</form>
</div>
<?php page_footer(); ?>

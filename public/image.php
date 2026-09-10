<?php
require __DIR__ . '/../src/bootstrap.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT image_data, image_mime FROM gifts WHERE id = ? AND image_data IS NOT NULL');
$stmt->execute([$id]);
$image = $stmt->fetch();

if (!$image) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . $image['image_mime']);
header('Cache-Control: public, max-age=86400');
echo stream_get_contents($image['image_data']);

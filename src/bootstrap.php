<?php
declare(strict_types=1);

session_start();

$config = require __DIR__ . '/../config.php';

if ($config['database_url'] === '') {
    http_response_code(500);
    exit('DATABASE_URL is not configured.');
}

$dbUrl = parse_url($config['database_url']);
if ($dbUrl === false || empty($dbUrl['host'])) {
    http_response_code(500);
    exit('Invalid DATABASE_URL.');
}

$host = $dbUrl['host'];
$port = $dbUrl['port'] ?? 5432;
$dbname = ltrim($dbUrl['path'] ?? '', '/');
$user = $dbUrl['user'] ?? '';
$pass = $dbUrl['pass'] ?? '';

$dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $dbname);
$db = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function ensure_database(PDO $db): void {
    $exists = $db->query("SELECT to_regclass('public.users')")->fetchColumn();
    if (!$exists) {
        $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
        $db->exec($schema);
    }
}
ensure_database($db);

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never {
    header('Location: ' . $path);
    exit;
}

function flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function consume_flashes(): array {
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

function current_user(PDO $db): ?array {
    if (empty($_SESSION['user_id'])) return null;
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function require_login(PDO $db): array {
    $user = current_user($db);
    if (!$user) redirect('/login.php');
    return $user;
}

function group_for_user(PDO $db, int $userId, int $groupId): ?array {
    $stmt = $db->prepare(
        'SELECT g.* FROM groups g
         JOIN group_members gm ON gm.group_id = g.id
         WHERE gm.user_id = ? AND g.id = ?'
    );
    $stmt->execute([$userId, $groupId]);
    return $stmt->fetch() ?: null;
}

function user_groups(PDO $db, int $userId): array {
    $stmt = $db->prepare(
        'SELECT g.* FROM groups g
         JOIN group_members gm ON gm.group_id = g.id
         WHERE gm.user_id = ?
         ORDER BY g.name'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function selected_group(PDO $db, int $userId): ?array {
    $groups = user_groups($db, $userId);
    if (!$groups) return null;
    $wanted = (int)($_GET['group'] ?? $_POST['group_id'] ?? $_SESSION['group_id'] ?? 0);
    foreach ($groups as $group) {
        if ((int)$group['id'] === $wanted) {
            $_SESSION['group_id'] = (int)$group['id'];
            return $group;
        }
    }
    $_SESSION['group_id'] = (int)$groups[0]['id'];
    return $groups[0];
}

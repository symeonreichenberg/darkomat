<?php
require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int)$user['id'];
        redirect('/dashboard.php');
    }
    flash('error', 'Nesprávný e-mail nebo heslo.');
}
page_header('Přihlášení');
?>
<div class="form-card narrow">
<h1>Přihlášení</h1>
<form method="post">
    <label>E-mail<input type="email" name="email" required></label>
    <label>Heslo<input type="password" name="password" required></label>
    <button class="button" type="submit">Přihlásit</button>
</form>
<p>Nemáš účet? <a href="/register.php">Zaregistruj se</a>.</p>
</div>
<?php page_footer(); ?>

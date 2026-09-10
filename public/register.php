<?php
require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        flash('error', 'Vyplň jméno, platný e-mail a heslo dlouhé alespoň 8 znaků.');
    } else {
        try {
            $stmt = $db->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $_SESSION['user_id'] = (int)$db->lastInsertId();
            redirect('/dashboard.php');
        } catch (PDOException $e) {
            flash('error', 'Tento e-mail už pravděpodobně existuje.');
        }
    }
}
page_header('Registrace');
?>
<div class="form-card narrow">
<h1>Vytvořit účet</h1>
<form method="post">
    <label>Jméno<input name="name" required maxlength="100"></label>
    <label>E-mail<input type="email" name="email" required maxlength="190"></label>
    <label>Heslo<input type="password" name="password" required minlength="8"></label>
    <button class="button" type="submit">Registrovat</button>
</form>
</div>
<?php page_footer(); ?>

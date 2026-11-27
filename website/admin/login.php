<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if (($_SESSION['authenticated'] ?? false) === true) {
    header('Location: enquiries.php');
    exit;
}

$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $expectedUsername = getenv('ADMIN_USERNAME');
    $passwordHash = getenv('ADMIN_PASSWORD_HASH');

    if (!verify_admin_csrf()) {
        http_response_code(400);
        $errorMessage = 'Your session expired. Reload the page and try again.';
    } elseif (
        is_string($username)
        && is_string($password)
        && $expectedUsername !== false
        && $passwordHash !== false
        && $expectedUsername !== ''
        && $passwordHash !== ''
        && hash_equals($expectedUsername, $username)
        && password_verify($password, $passwordHash)
    ) {
        session_regenerate_id(true);
        $_SESSION['authenticated'] = true;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: enquiries.php');
        exit;
    } else {
        http_response_code(401);
        $errorMessage = 'Sign-in failed. Check your credentials or server configuration.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Administrator sign in | St. Michael Senior Secondary School Naama-Mityana</title>
  <link rel="stylesheet" href="../css/micheal.css">
  <style>
    body { min-height: 100vh; display: grid; place-items: center; padding: 24px; background: #f6f4f1; }
    .admin-card { width: min(440px, 100%); padding: 34px; border: 1px solid #e8e3df; background: #fffefa; }
    .admin-card h1 { margin: 20px 0 4px; color: #1d1b1b; font-family: Georgia, serif; font-size: 30px; font-weight: 500; }
    .admin-card > p { margin: 0 0 22px; color: #716b68; font-size: 12px; }
    .admin-form { display: grid; gap: 14px; }
    .admin-form label { display: grid; gap: 6px; font-size: 11px; font-weight: 700; }
    .admin-form input { min-height: 44px; padding: 10px 12px; border: 1px solid #e3ddda; font-size: 12px; }
    .admin-error { margin: 0 0 14px; padding: 10px; color: #8d291f; background: #fcf3f2; font-size: 11px; }
    .admin-back { display: inline-flex; margin-top: 20px; color: #64182d; font-size: 11px; font-weight: 700; }
  </style>
</head>
<body>
  <main class="admin-card">
    <a class="brand" href="../index.html"><img class="brand-logo" src="../images/logo.png" alt=""><span class="brand-copy"><strong>ST. MICHAEL</strong><small>SENIOR SECONDARY SCHOOL<br>NAAMA-MITYANA</small></span></a>
    <h1>Administrator sign in</h1>
    <p>Access school enquiry submissions.</p>
    <?php if ($errorMessage !== ''): ?>
      <p class="admin-error" role="alert"><?= escape_html($errorMessage) ?></p>
    <?php endif; ?>
    <form class="admin-form" method="post" action="login.php">
      <input type="hidden" name="csrf_token" value="<?= escape_html(admin_csrf_token()) ?>">
      <label>Username<input name="username" type="text" autocomplete="username" maxlength="120" required></label>
      <label>Password<input name="password" type="password" autocomplete="current-password" maxlength="4096" required></label>
      <button class="button button-maroon" type="submit">Sign in</button>
    </form>
    <a class="admin-back" href="../index.html">Return to the school website</a>
  </main>
</body>
</html>

<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_admin();

$errorMessage = '';
$enquiries = [];
try {
    $connection = database_connection();
    $statement = $connection->query(
        'SELECT id, kind, student_name, guardian_name, phone, class_applying_for, email, message, created_at
         FROM enquiries ORDER BY created_at DESC LIMIT 500'
    );
    $enquiries = $statement->fetchAll();
} catch (PDOException $error) {
    error_log('School enquiry dashboard could not load submissions: ' . $error->getMessage());
    http_response_code(503);
    $errorMessage = 'Enquiries could not be loaded. Check the database connection and schema, then reload this page.';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Enquiry dashboard | St. Michael Senior Secondary School Naama-Mityana</title>
  <link rel="stylesheet" href="../css/micheal.css">
  <style>
    body { min-height: 100vh; background: #f6f4f1; }
    .admin-header { background: #fffefa; border-bottom: 1px solid #e8e3df; }
    .admin-header-inner { display: flex; min-height: 78px; align-items: center; justify-content: space-between; gap: 20px; }
    .admin-header-inner > form { margin: 0; }
    .admin-main { padding-block: 42px; }
    .admin-title { display: flex; align-items: end; justify-content: space-between; gap: 20px; margin-bottom: 22px; }
    .admin-title h1 { margin: 0; font-family: Georgia, serif; font-size: 34px; font-weight: 500; }
    .admin-title p { margin: 4px 0 0; color: #716b68; font-size: 11px; }
    .admin-count { color: #64182d; font-size: 11px; font-weight: 700; }
    .admin-alert { margin: 0 0 18px; padding: 13px; color: #8d291f; background: #fcf3f2; font-size: 12px; }
    .table-wrap { overflow-x: auto; border: 1px solid #e8e3df; background: #fffefa; }
    .enquiry-table { width: 100%; border-collapse: collapse; text-align: left; }
    .enquiry-table th, .enquiry-table td { min-width: 115px; padding: 13px; border-bottom: 1px solid #eee9e5; vertical-align: top; font-size: 10px; }
    .enquiry-table th { color: #64182d; background: #f8f3f4; font-size: 9px; letter-spacing: .06em; text-transform: uppercase; }
    .enquiry-table td:last-child { min-width: 230px; white-space: pre-wrap; overflow-wrap: anywhere; }
    .enquiry-table td:nth-child(2) { text-transform: capitalize; }
    .admin-empty { padding: 24px; color: #716b68; font-size: 12px; text-align: center; }
    @media (max-width: 650px) {
      .admin-header-inner { align-items: flex-start; flex-direction: column; padding-block: 13px; }
      .admin-title { align-items: flex-start; flex-direction: column; }
      .admin-main { padding-block: 28px; }
    }
  </style>
</head>
<body>
  <header class="admin-header">
    <div class="container admin-header-inner">
      <a class="brand" href="../index.html"><img class="brand-logo" src="../images/logo.png" alt=""><span class="brand-copy"><strong>ST. MICHAEL</strong><small>SENIOR SECONDARY SCHOOL<br>NAAMA-MITYANA</small></span></a>
      <form method="post" action="logout.php">
        <input type="hidden" name="csrf_token" value="<?= escape_html(admin_csrf_token()) ?>">
        <button class="button button-maroon button-small" type="submit">Sign out</button>
      </form>
    </div>
  </header>
  <main class="container admin-main">
    <div class="admin-title"><div><h1>Enquiry dashboard</h1><p>Private school admission and contact messages.</p></div><span class="admin-count"><?= count($enquiries) ?> recent enquiries</span></div>
    <?php if ($errorMessage !== ''): ?>
      <p class="admin-alert" role="alert"><?= escape_html($errorMessage) ?></p>
    <?php elseif ($enquiries === []): ?>
      <p class="admin-empty">No enquiries have been received yet.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="enquiry-table">
          <thead><tr><th>Received</th><th>Type</th><th>Student / name</th><th>Parent / guardian</th><th>Phone</th><th>Class</th><th>Email</th><th>Message</th></tr></thead>
          <tbody>
            <?php foreach ($enquiries as $enquiry): ?>
              <tr>
                <td><?= escape_html((string) $enquiry['created_at']) ?></td>
                <td><?= escape_html((string) $enquiry['kind']) ?></td>
                <td><?= escape_html((string) $enquiry['student_name']) ?></td>
                <td><?= escape_html((string) ($enquiry['guardian_name'] ?? '—')) ?></td>
                <td><?= escape_html((string) $enquiry['phone']) ?></td>
                <td><?= escape_html((string) ($enquiry['class_applying_for'] ?? '—')) ?></td>
                <td><?= escape_html((string) ($enquiry['email'] ?? '—')) ?></td>
                <td><?= escape_html((string) $enquiry['message']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>

<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

function respond(int $status, string $message): void
{
    http_response_code($status);
    echo json_encode(['message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, 'Use the enquiry form to send a message.');
}

foreach (['kind', 'name', 'guardian', 'phone', 'class_applying_for', 'email', 'message', 'website'] as $field) {
    if (isset($_POST[$field]) && !is_string($_POST[$field])) {
        respond(422, 'Please check the form fields and try again.');
    }
}

$kind = trim($_POST['kind'] ?? '');
$name = trim($_POST['name'] ?? '');
$guardian = trim($_POST['guardian'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$class = trim($_POST['class_applying_for'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
$website = trim($_POST['website'] ?? '');

if ($website !== '') {
    respond(422, 'The enquiry could not be accepted. Please try again.');
}

if (!in_array($kind, ['admission', 'contact'], true)) {
    respond(422, 'Please select a valid enquiry type.');
}

if ($name === '' || strlen($name) > 120 || $phone === '' || strlen($phone) > 24) {
    respond(422, 'Please provide the student’s name and a valid telephone number.');
}

if (!preg_match('/^[+0-9().\s-]+$/', $phone) || preg_match_all('/[0-9]/', $phone) < 7) {
    respond(422, 'Please enter a valid telephone number.');
}

if ($kind === 'admission') {
    if ($guardian === '' || strlen($guardian) > 120) {
        respond(422, 'Please provide the parent or guardian’s name.');
    }
    if (!in_array($class, ['O-Level', 'A-Level', 'Other / not sure'], true)) {
        respond(422, 'Please select the class or level you are asking about.');
    }
} else {
    $guardian = '';
    $class = '';
}

if ($email !== '' && (strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false)) {
    respond(422, 'Please enter a valid email address or leave the field blank.');
}

if ($message === '' || strlen($message) > 2000) {
    respond(422, 'Please add a message of up to 2,000 characters.');
}

$savedToDatabase = false;
try {
    $connection = database_connection();
    $statement = $connection->prepare(
        'INSERT INTO enquiries (kind, student_name, guardian_name, phone, class_applying_for, email, message)
         VALUES (:kind, :student_name, :guardian_name, :phone, :class_applying_for, :email, :message)'
    );
    $statement->execute([
        'kind' => $kind,
        'student_name' => $name,
        'guardian_name' => $guardian !== '' ? $guardian : null,
        'phone' => $phone,
        'class_applying_for' => $class !== '' ? $class : null,
        'email' => $email !== '' ? $email : null,
        'message' => $message,
    ]);
    $savedToDatabase = true;
} catch (PDOException $error) {
    error_log('School enquiry could not be saved: ' . $error->getMessage());
}

$schoolEmail = 'stmichaelsssn@gmail.com';
$kindLabel = $kind === 'admission' ? 'Admission' : 'Contact';
$mailBody = implode("\r\n", [
    'New message from the St. Michael Senior Secondary School website',
    '',
    'Type: ' . $kindLabel,
    'Name: ' . $name,
    'Parent or guardian: ' . ($guardian !== '' ? $guardian : 'Not provided'),
    'Phone: ' . $phone,
    'Class applying for: ' . ($class !== '' ? $class : 'Not applicable'),
    'Visitor email: ' . ($email !== '' ? $email : 'Not provided'),
    '',
    'Message:',
    $message,
]);
$mailHeaders = ['Content-Type: text/plain; charset=UTF-8'];
if ($email !== '') {
    $mailHeaders[] = 'Reply-To: ' . $email;
}

if (!function_exists('mail') || !mail($schoolEmail, 'New school website ' . strtolower($kindLabel) . ' enquiry', $mailBody, implode("\r\n", $mailHeaders))) {
    error_log('School enquiry email could not be sent to ' . $schoolEmail . '.');
    respond(
        503,
        $savedToDatabase
            ? 'Your enquiry was saved, but could not be emailed to the school. Please contact the school by phone.'
            : 'Your enquiry could not be emailed or saved. Please contact the school by phone.'
    );
}

respond(
    201,
    $savedToDatabase
        ? 'Thank you. Your enquiry has been sent to the school.'
        : 'Your enquiry was emailed to the school, but could not be added to the website records.'
);

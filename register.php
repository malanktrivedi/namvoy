<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (current_user()) redirect('/');
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? null);
    $first = trim($_POST['first_name'] ?? '');
    $last = trim($_POST['last_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = ($_POST['role'] ?? 'traveler') === 'provider' ? 'provider' : 'traveler';

    if ($first === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Enter a valid name/email and a password of at least 8 characters.';
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email); $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare('INSERT INTO users (role, first_name, last_name, email, phone, password_hash) VALUES (?,?,?,?,?,?)');
            $stmt->bind_param('ssssss', $role, $first, $last, $email, $phone, $hash); $stmt->execute();
            $userId = db()->insert_id;
            if ($role === 'provider') {
                $business = trim($_POST['business_name'] ?? ($first . ' Travel'));
                $stmt = db()->prepare('INSERT INTO providers (user_id,business_name,business_email,business_phone) VALUES (?,?,?,?)');
                $stmt->bind_param('isss', $userId, $business, $email, $phone); $stmt->execute();
            }
            $_SESSION['user_id'] = $userId;
            session_regenerate_id(true);
            redirect($role === 'provider' ? '/provider/dashboard.php' : '/account/dashboard.php');
        }
    }
}
$page_title = 'Create Account'; require __DIR__ . '/includes/header.php';
?>
<div class="container py-5" style="max-width:700px"><div class="card p-4 p-lg-5"><h1 class="h3 mb-4">Create your NamVoy account</h1>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="row g-3"><div class="col-md-6"><label class="form-label">First name</label><input class="form-control" name="first_name" required></div><div class="col-md-6"><label class="form-label">Last name</label><input class="form-control" name="last_name"></div>
<div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div><div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone"></div>
<div class="col-md-6"><label class="form-label">Account type</label><select class="form-select" name="role" id="role"><option value="traveler">Traveler</option><option value="provider">Travel Provider</option></select></div><div class="col-md-6"><label class="form-label">Password</label><input class="form-control" type="password" name="password" minlength="8" required></div>
<div class="col-12" id="business-wrap" style="display:none"><label class="form-label">Business name</label><input class="form-control" name="business_name"></div></div>
<button class="btn btn-dark mt-4">Create Account</button> <a class="btn btn-link mt-4" href="/login.php">Already have an account?</a></form></div></div>
<script>document.getElementById('role').addEventListener('change',e=>document.getElementById('business-wrap').style.display=e.target.value==='provider'?'block':'none');</script>
<?php require __DIR__ . '/includes/footer.php'; ?>

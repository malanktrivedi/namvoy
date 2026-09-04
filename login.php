<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (current_user()) redirect('/');
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? null);
    $email = strtolower(trim($_POST['email'] ?? '')); $password = $_POST['password'] ?? '';
    $stmt = db()->prepare('SELECT id,password_hash,role,status FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s',$email); $stmt->execute(); $account = $stmt->get_result()->fetch_assoc();
    if (!$account || !password_verify($password,$account['password_hash']) || $account['status'] !== 'active') {
        $error = 'Invalid email/password or inactive account.';
    } else {
        session_regenerate_id(true); $_SESSION['user_id']=(int)$account['id'];
        redirect($account['role']==='provider'?'/provider/dashboard.php':($account['role']==='admin'?'/admin/index.php':'/account/dashboard.php'));
    }
}
$page_title='Login'; require __DIR__.'/includes/header.php';
?>
<div class="container py-5" style="max-width:520px"><div class="card p-4 p-lg-5"><h1 class="h3 mb-4">Welcome back</h1><?php if($error): ?><div class="alert alert-danger"><?=e($error)?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" required><label class="form-label">Password</label><input class="form-control" type="password" name="password" required><button class="btn btn-dark w-100 mt-4">Login</button></form><p class="mt-3 mb-0 text-secondary">New to NamVoy? <a href="/register.php">Create an account</a></p></div></div>
<?php require __DIR__.'/includes/footer.php'; ?>

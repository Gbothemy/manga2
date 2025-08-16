<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_admin()) {
	header('Location: dashboard.php');
	exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';
	if (login_user($email, $password) && is_admin()) {
		header('Location: dashboard.php');
		exit;
	}
	$error = 'Invalid admin credentials';
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Admin Login - CrypyedManga</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?php echo AppConfig::baseUrl(); ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container" style="padding-top:4rem; max-width:560px;">
	<div class="card">
		<div class="card-body">
			<h3 class="mb-3">Admin Login</h3>
			<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
			<form method="post">
				<div class="mb-3">
					<label class="form-label">Email</label>
					<input type="email" name="email" class="form-control" required>
				</div>
				<div class="mb-3">
					<label class="form-label">Password</label>
					<input type="password" name="password" class="form-control" required>
				</div>
				<button class="btn btn-primary w-100" type="submit">Login</button>
			</form>
		</div>
	</div>
</div>
</body>
</html>
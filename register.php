<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Register - CrypyedManga';
$error = ''; $ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';
	if (!$name || !$email || !$password) {
		$error = 'All fields are required';
	} else {
		$res = register_user($name, $email, $password);
		if ($res['ok']) {
			$ok = true;
		} else {
			$error = $res['error'] ?? 'Registration failed';
		}
	}
}
include __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
	<div class="col-md-5">
		<div class="card">
			<div class="card-body">
				<h3 class="mb-3">Register</h3>
				<?php if ($ok): ?><div class="alert alert-success">Registered! You can now <a href="<?php echo AppConfig::baseUrl(); ?>login">login</a>.</div><?php endif; ?>
				<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
				<form method="post">
					<div class="mb-3">
						<label class="form-label">Name</label>
						<input name="name" class="form-control" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Email</label>
						<input type="email" name="email" class="form-control" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Password</label>
						<input type="password" name="password" class="form-control" required>
					</div>
					<button type="submit" class="btn btn-primary w-100">Register</button>
				</form>
			</div>
		</div>
	</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
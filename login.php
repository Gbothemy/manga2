<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Login - CrypyedManga';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';
	if (login_user($email, $password)) {
		header('Location: ' . AppConfig::baseUrl());
		exit;
	} else {
		$error = 'Invalid credentials';
	}
}
include __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
	<div class="col-md-5">
		<div class="card">
			<div class="card-body">
				<h3 class="mb-3">Login</h3>
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
					<button type="submit" class="btn btn-primary w-100">Login</button>
				</form>
			</div>
		</div>
	</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';
$page_title = 'Contact - CrypyedManga';
$sent = false; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$message = trim($_POST['message'] ?? '');
	$token = $_POST['csrf_token'] ?? '';
	if (!verify_csrf($token)) {
		$error = 'Invalid request.';
	} elseif (!$name || !$email || !$message) {
		$error = 'All fields are required.';
	} else {
		try {
			$pdo = get_db_connection();
			$stmt = $pdo->prepare('INSERT INTO messages (name, email, message, created_at) VALUES (:n,:e,:m,NOW())');
			$stmt->execute([':n'=>$name, ':e'=>$email, ':m'=>$message]);
			send_mail(AppConfig::emailFrom(), 'New Contact Message', nl2br(htmlspecialchars($message)) . '<br>From: ' . htmlspecialchars($name) . ' &lt;' . htmlspecialchars($email) . '&gt;');
			$sent = true;
		} catch (Throwable $t) {
			$error = 'Failed to send. Please try again later.';
		}
	}
}

include __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
	<div class="col-md-8">
		<div class="card">
			<div class="card-body">
				<h3>Contact Us</h3>
				<?php if ($sent): ?>
					<div class="alert alert-success">Thanks! We have received your message.</div>
				<?php elseif ($error): ?>
					<div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
				<?php endif; ?>
				<form method="post">
					<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
					<div class="mb-3">
						<label class="form-label">Name</label>
						<input class="form-control" name="name" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Email</label>
						<input type="email" class="form-control" name="email" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Message</label>
						<textarea class="form-control" name="message" rows="5" required></textarea>
					</div>
					<button class="btn btn-primary" type="submit">Send</button>
				</form>
			</div>
		</div>
	</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
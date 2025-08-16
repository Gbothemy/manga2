<?php
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/../includes/mailer.php';
$pdo = get_db_connection();

if (isset($_POST['reply_id'])) {
	$id = (int)$_POST['reply_id'];
	$reply = trim($_POST['reply_body'] ?? '');
	$stmt = $pdo->prepare('SELECT * FROM messages WHERE id = :id');
	$stmt->execute([':id'=>$id]);
	$msg = $stmt->fetch();
	if ($msg && $reply) {
		send_mail($msg['email'], 'Re: Your message to CrypyedManga', nl2br(htmlspecialchars($reply)));
		echo '<div class="alert alert-success">Reply sent to ' . htmlspecialchars($msg['email']) . '.</div>';
	}
}

$messages = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();
?>
<div class="card">
	<div class="card-header">Messages</div>
	<div class="list-group list-group-flush">
		<?php foreach ($messages as $m): ?>
			<div class="list-group-item">
				<div class="d-flex justify-content-between">
					<div>
						<strong><?php echo htmlspecialchars($m['name']); ?></strong>
						<div class="small text-muted"><?php echo htmlspecialchars($m['email']); ?> • <?php echo htmlspecialchars($m['created_at']); ?></div>
					</div>
				</div>
				<p class="mt-2 mb-2"><?php echo nl2br(htmlspecialchars($m['message'])); ?></p>
				<form method="post" class="mt-2">
					<input type="hidden" name="reply_id" value="<?php echo (int)$m['id']; ?>">
					<textarea class="form-control mb-2" name="reply_body" rows="3" placeholder="Write a reply..."></textarea>
					<button class="btn btn-sm btn-primary" type="submit">Send Reply</button>
				</form>
			</div>
		<?php endforeach; ?>
	</div>
</div>
<?php require_once __DIR__ . '/partials/footer.php';
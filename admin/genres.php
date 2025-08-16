<?php
require_once __DIR__ . '/partials/header.php';
$pdo = get_db_connection();

// Delete
if (isset($_GET['delete'])) {
	$id = (int)$_GET['delete'];
	$pdo->prepare('DELETE FROM genres WHERE id = :id')->execute([':id'=>$id]);
	echo '<div class="alert alert-success">Genre deleted.</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['name'] ?? '');
	if ($name) {
		$pdo->prepare('INSERT INTO genres (name) VALUES (:n)')->execute([':n'=>$name]);
		echo '<div class="alert alert-success">Genre added.</div>';
	}
}

$genres = $pdo->query('SELECT * FROM genres ORDER BY name')->fetchAll();
?>
<div class="row">
	<div class="col-md-6">
		<div class="card mb-3">
			<div class="card-header">Genres</div>
			<div class="list-group list-group-flush">
				<?php foreach ($genres as $g): ?>
					<div class="list-group-item d-flex justify-content-between">
						<div><?php echo htmlspecialchars($g['name']); ?></div>
						<a class="btn btn-sm btn-danger" href="?delete=<?php echo (int)$g['id']; ?>" onclick="return confirm('Delete this genre?')">Delete</a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="card">
			<div class="card-header">Add Genre</div>
			<div class="card-body">
				<form method="post">
					<input class="form-control mb-2" name="name" placeholder="Genre name" required>
					<button class="btn btn-primary" type="submit">Add</button>
				</form>
			</div>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/partials/footer.php';
}
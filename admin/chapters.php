<?php
require_once __DIR__ . '/partials/header.php';
$pdo = get_db_connection();

// Load mangas for selection
$mangas = $pdo->query('SELECT id, title FROM mangas ORDER BY title ASC')->fetchAll();

// Delete chapter
if (isset($_GET['delete'])) {
	$id = (int)$_GET['delete'];
	$pdo->prepare('DELETE FROM chapters WHERE id = :id')->execute([':id' => $id]);
	echo '<div class="alert alert-success">Chapter deleted.</div>';
}

// Editing
$editing = null;
if (isset($_GET['edit'])) {
	$id = (int)$_GET['edit'];
	$stmt = $pdo->prepare('SELECT * FROM chapters WHERE id = :id');
	$stmt->execute([':id' => $id]);
	$editing = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$chapter_manga_id = (int)($_POST['manga_id'] ?? 0);
	$chapter_number = trim($_POST['chapter_number'] ?? '');
	$title = trim($_POST['title'] ?? '');

	if ($editing) {
		$stmt = $pdo->prepare('UPDATE chapters SET manga_id=:m, chapter_number=:n, title=:t WHERE id=:id');
		$stmt->execute([':m'=>$chapter_manga_id, ':n'=>$chapter_number, ':t'=>$title, ':id'=>$editing['id']]);
		echo '<div class="alert alert-success">Chapter updated.</div>';
	} else {
		$stmt = $pdo->prepare('INSERT INTO chapters (manga_id, chapter_number, title, created_at) VALUES (:m,:n,:t,NOW())');
		$stmt->execute([':m'=>$chapter_manga_id, ':n'=>$chapter_number, ':t'=>$title]);
		$editing = ['id' => $pdo->lastInsertId(), 'manga_id'=>$chapter_manga_id, 'chapter_number'=>$chapter_number, 'title'=>$title];
		echo '<div class="alert alert-success">Chapter created. You can now upload images below.</div>';
	}

	// Multiple image upload
	if (!empty($_FILES['images']['name'][0])) {
		for ($i=0; $i < count($_FILES['images']['name']); $i++) {
			if (!is_uploaded_file($_FILES['images']['tmp_name'][$i])) continue;
			$ext = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
			$fname = 'uploads/mangas/chapter_' . $editing['id'] . '_' . time() . '_' . $i . '.' . $ext;
			$abs = __DIR__ . '/../' . $fname;
			@mkdir(dirname($abs), 0777, true);
			move_uploaded_file($_FILES['images']['tmp_name'][$i], $abs);
			// Determine next page number
			$maxStmt = $pdo->prepare('SELECT COALESCE(MAX(page_number),0)+1 FROM chapter_images WHERE chapter_id = :cid');
			$maxStmt->execute([':cid' => $editing['id']]);
			$page_num = (int)$maxStmt->fetchColumn();
			$pdo->prepare('INSERT INTO chapter_images (chapter_id, page_number, image_path) VALUES (:cid,:p,:path)')
				->execute([':cid'=>$editing['id'], ':p'=>$page_num, ':path'=>$fname]);
		}
		echo '<div class="alert alert-success">Images uploaded.</div>';
	}
}

// List chapters
$stmt = $pdo->query('SELECT c.*, m.title AS manga_title FROM chapters c INNER JOIN mangas m ON m.id = c.manga_id ORDER BY m.title, c.chapter_number');
$chapters = $stmt->fetchAll();
?>
<div class="row">
	<div class="col-md-7">
		<div class="card mb-3">
			<div class="card-header d-flex justify-content-between">Chapters <a class="btn btn-sm btn-primary" href="?new=1">New</a></div>
			<div class="list-group list-group-flush">
				<?php foreach ($chapters as $c): ?>
					<div class="list-group-item d-flex justify-content-between align-items-center">
						<div>
							<strong><?php echo htmlspecialchars($c['manga_title']); ?></strong>
							<div class="small text-muted">Chapter <?php echo htmlspecialchars($c['chapter_number']); ?> — #<?php echo (int)$c['id']; ?></div>
						</div>
						<div>
							<a class="btn btn-sm btn-secondary" href="?edit=<?php echo (int)$c['id']; ?>">Edit</a>
							<a class="btn btn-sm btn-danger" href="?delete=<?php echo (int)$c['id']; ?>" onclick="return confirm('Delete this chapter?')">Delete</a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div class="col-md-5">
		<div class="card">
			<div class="card-header"><?php echo $editing ? 'Edit Chapter' : 'New Chapter'; ?></div>
			<div class="card-body">
				<form method="post" enctype="multipart/form-data">
					<div class="mb-2">
						<label class="form-label">Manga</label>
						<select class="form-select" name="manga_id" required>
							<option value="">Choose…</option>
							<?php foreach ($mangas as $m): ?>
								<option value="<?php echo (int)$m['id']; ?>" <?php echo ($editing['manga_id'] ?? 0) == $m['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($m['title']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="mb-2">
						<label class="form-label">Chapter Number</label>
						<input class="form-control" name="chapter_number" value="<?php echo htmlspecialchars($editing['chapter_number'] ?? ''); ?>" required>
					</div>
					<div class="mb-2">
						<label class="form-label">Title</label>
						<input class="form-control" name="title" value="<?php echo htmlspecialchars($editing['title'] ?? ''); ?>">
					</div>
					<div class="mb-2">
						<label class="form-label">Upload Images</label>
						<input type="file" class="form-control" name="images[]" multiple accept="image/*">
					</div>
					<button class="btn btn-primary w-100" type="submit"><?php echo $editing ? 'Save' : 'Create'; ?></button>
				</form>
			</div>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/partials/footer.php';
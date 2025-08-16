<?php
require_once __DIR__ . '/partials/header.php';
?>
<div class="row">
	<div class="col-md-6">
		<div class="card mb-3">
			<div class="card-header">MangaDex API</div>
			<div class="card-body">
				<form method="post" action="../manga_retriever.php">
					<input type="hidden" name="mode" value="api">
					<button class="btn btn-primary" type="submit">Run Retrieval (English)</button>
				</form>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="card mb-3">
			<div class="card-header">Local ZIP Upload</div>
			<div class="card-body">
				<form method="post" action="../manga_retriever.php">
					<input type="hidden" name="mode" value="local">
					<div class="mb-2">
						<label class="form-label">Server ZIP Path</label>
						<input class="form-control" name="zip" placeholder="/absolute/path/to/chapter.zip" required>
					</div>
					<div class="mb-2">
						<label class="form-label">Manga ID</label>
						<input class="form-control" name="manga_id" required>
					</div>
					<div class="mb-2">
						<label class="form-label">Chapter Number</label>
						<input class="form-control" name="chapter" required>
					</div>
					<button class="btn btn-secondary" type="submit">Import ZIP</button>
				</form>
			</div>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/partials/footer.php';
<?php
require_once __DIR__ . '/partials/header.php';
?>
<div class="row g-3">
	<div class="col-md-3">
		<a class="card text-decoration-none" href="mangas.php">
			<div class="card-body">
				<h5 class="card-title">Mangas</h5>
				<p class="card-text">Add, edit, delete mangas.</p>
			</div>
		</a>
	</div>
	<div class="col-md-3">
		<a class="card text-decoration-none" href="chapters.php">
			<div class="card-body">
				<h5 class="card-title">Chapters</h5>
				<p class="card-text">Manage chapters and images.</p>
			</div>
		</a>
	</div>
	<div class="col-md-3">
		<a class="card text-decoration-none" href="genres.php">
			<div class="card-body">
				<h5 class="card-title">Genres</h5>
				<p class="card-text">Create and edit genres.</p>
			</div>
		</a>
	</div>
	<div class="col-md-3">
		<a class="card text-decoration-none" href="messages.php">
			<div class="card-body">
				<h5 class="card-title">Messages</h5>
				<p class="card-text">View and reply to messages.</p>
			</div>
		</a>
	</div>
</div>
<?php
require_once __DIR__ . '/partials/footer.php';
?>
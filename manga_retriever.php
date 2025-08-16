<?php
require_once __DIR__ . '/includes/functions.php';

function http_get_json(string $url): array {
	$opts = ['http' => ['header' => "User-Agent: CrypyedManga/1.0\r\n", 'timeout' => 30]];
	$ctx = stream_context_create($opts);
	$raw = @file_get_contents($url, false, $ctx);
	if ($raw === false) return [];
	$decoded = json_decode($raw, true);
	return is_array($decoded) ? $decoded : [];
}

function ensure_dir(string $dir): void { if (!is_dir($dir)) @mkdir($dir, 0777, true); }

function save_image(string $url, string $destRelative): ?string {
	$dest = __DIR__ . '/' . ltrim($destRelative, '/');
	ensure_dir(dirname($dest));
	$data = @file_get_contents($url);
	if ($data === false) return null;
	file_put_contents($dest, $data);
	return $destRelative;
}

function upsert_manga(array $info): int {
	$pdo = get_db_connection();
	// Try by external_id if provided
	if (!empty($info['external_id'])) {
		$stmt = $pdo->prepare('SELECT id FROM mangas WHERE external_id = :eid');
		$stmt->execute([':eid'=>$info['external_id']]);
		$id = $stmt->fetchColumn();
		if ($id) {
			$stmt = $pdo->prepare('UPDATE mangas SET title=:t, slug=:s, author=:a, description=:d, release_date=:r, cover_image=:c, updated_at=NOW() WHERE id=:id');
			$stmt->execute([':t'=>$info['title'], ':s'=>slugify($info['title']), ':a'=>$info['author'] ?? null, ':d'=>$info['description'] ?? null, ':r'=>$info['release_date'] ?? null, ':c'=>$info['cover_image'] ?? null, ':id'=>$id]);
			return (int)$id;
		}
	}
	// Else insert
	$stmt = $pdo->prepare('INSERT INTO mangas (external_id, title, slug, author, description, release_date, cover_image, created_at, updated_at) VALUES (:eid,:t,:s,:a,:d,:r,:c,NOW(),NOW())');
	$stmt->execute([':eid'=>$info['external_id'] ?? null, ':t'=>$info['title'], ':s'=>slugify($info['title']), ':a'=>$info['author'] ?? null, ':d'=>$info['description'] ?? null, ':r'=>$info['release_date'] ?? null, ':c'=>$info['cover_image'] ?? null]);
	return (int)$pdo->lastInsertId();
}

function set_manga_genres(int $manga_id, array $genre_names): void {
	$pdo = get_db_connection();
	// Ensure genres
	$ids = [];
	foreach ($genre_names as $name) {
		$name = trim($name);
		if ($name === '') continue;
		$stmt = $pdo->prepare('SELECT id FROM genres WHERE name = :n');
		$stmt->execute([':n'=>$name]);
		$id = $stmt->fetchColumn();
		if (!$id) {
			$pdo->prepare('INSERT INTO genres (name) VALUES (:n)')->execute([':n'=>$name]);
			$id = $pdo->lastInsertId();
		}
		$ids[] = (int)$id;
	}
	$pdo->prepare('DELETE FROM manga_genres WHERE manga_id = :mid')->execute([':mid'=>$manga_id]);
	foreach ($ids as $gid) {
		$pdo->prepare('INSERT INTO manga_genres (manga_id, genre_id) VALUES (:mid,:gid)')->execute([':mid'=>$manga_id, ':gid'=>$gid]);
	}
}

function upsert_chapter(int $manga_id, array $info): int {
	$pdo = get_db_connection();
	$stmt = $pdo->prepare('SELECT id FROM chapters WHERE manga_id = :mid AND external_id = :eid');
	$stmt->execute([':mid'=>$manga_id, ':eid'=>$info['external_id']]);
	$id = $stmt->fetchColumn();
	if ($id) {
		$pdo->prepare('UPDATE chapters SET chapter_number=:n, title=:t, updated_at=NOW() WHERE id=:id')->execute([':n'=>$info['chapter_number'], ':t'=>$info['title'] ?? null, ':id'=>$id]);
		return (int)$id;
	}
	$pdo->prepare('INSERT INTO chapters (manga_id, external_id, chapter_number, title, created_at, updated_at) VALUES (:mid,:eid,:n,:t,NOW(),NOW())')->execute([':mid'=>$manga_id, ':eid'=>$info['external_id'], ':n'=>$info['chapter_number'], ':t'=>$info['title'] ?? null]);
	return (int)$pdo->lastInsertId();
}

function add_chapter_image(int $chapter_id, int $page, string $relative_path): void {
	$pdo = get_db_connection();
	$pdo->prepare('INSERT INTO chapter_images (chapter_id, page_number, image_path) VALUES (:cid,:p,:path) ON DUPLICATE KEY UPDATE image_path = VALUES(image_path)')->execute([':cid'=>$chapter_id, ':p'=>$page, ':path'=>$relative_path]);
}

function retrieve_from_mangadex(string $language = 'en'): void {
	// This is a simplified retrieval using MangaDex API for demonstration.
	// It fetches some popular titles and their latest chapters.
	$base = 'https://api.mangadex.org';
	$list = http_get_json($base . '/manga?limit=5&contentRating[]=safe&availableTranslatedLanguage[]=' . urlencode($language));
	if (empty($list['data'])) return;
	foreach ($list['data'] as $manga) {
		$attrs = $manga['attributes'] ?? [];
		$title = $attrs['title']['en'] ?? ($attrs['title'][array_key_first($attrs['title'] ?? [])] ?? 'Untitled');
		$desc = $attrs['description'][$language] ?? '';
		$tags = [];
		foreach ($attrs['tags'] ?? [] as $tag) {
			$tags[] = $tag['attributes']['name'][$language] ?? null;
		}
		$tags = array_filter($tags);

		$cover_rel = null;
		// Fetch cover art relationship
		$coverId = null;
		foreach ($manga['relationships'] ?? [] as $rel) {
			if (($rel['type'] ?? '') === 'cover_art') { $coverId = $rel['id']; break; }
		}
		if ($coverId) {
			$cover = http_get_json($base . '/cover/' . $coverId);
			$file = $cover['data']['attributes']['fileName'] ?? null;
			if ($file) {
				$cover_url = 'https://uploads.mangadex.org/covers/' . $manga['id'] . '/' . $file . '.256.jpg';
				$cover_rel = 'uploads/mangas/covers/' . $manga['id'] . '_' . $file . '.jpg';
				save_image($cover_url, $cover_rel);
			}
		}

		$manga_id = upsert_manga([
			'external_id' => $manga['id'],
			'title' => $title,
			'author' => null,
			'description' => $desc,
			'release_date' => null,
			'cover_image' => $cover_rel ?? 'assets/images/mangas/placeholder_cover.jpg',
		]);
		set_manga_genres($manga_id, $tags);

		// Fetch chapters
		$chresp = http_get_json($base . '/chapter?manga=' . $manga['id'] . '&translatedLanguage[]=' . urlencode($language) . '&limit=3&order[chapter]=asc');
		if (!empty($chresp['data'])) {
			$page = 1;
			foreach ($chresp['data'] as $ch) {
				$chapNum = $ch['attributes']['chapter'] ?? ('0.' . $page);
				$chapter_id = upsert_chapter($manga_id, [
					'external_id' => $ch['id'],
					'chapter_number' => (string)$chapNum,
					'title' => $ch['attributes']['title'] ?? null,
				]);
				// Simulate images from at-home server
				$pages = http_get_json($base . '/at-home/server/' . $ch['id']);
				$host = $pages['baseUrl'] ?? null;
				if ($host) {
					$hash = $ch['attributes']['hash'] ?? ($pages['chapter']['hash'] ?? null);
					$imgs = $pages['chapter']['data'] ?? [];
					$pg = 1;
					foreach ($imgs as $imgfile) {
						$url = $host . '/data/' . ($pages['chapter']['hash'] ?? $hash) . '/' . $imgfile;
						$rel = 'uploads/mangas/' . $manga_id . '/' . $chapter_id . '/' . str_pad((string)$pg, 3, '0', STR_PAD_LEFT) . '_' . basename($imgfile);
						$ok = save_image($url, $rel);
						if ($ok) add_chapter_image($chapter_id, $pg, $rel);
						$pg++;
					}
				}
				$page++;
			}
		}
	}
}

function import_local_zip(string $zipPath, int $manga_id, string $chapter_number, ?string $title = null): void {
	$pdo = get_db_connection();
	$chapter_id = upsert_chapter($manga_id, [ 'external_id' => 'local-' . md5($zipPath), 'chapter_number' => $chapter_number, 'title' => $title ]);
	$destDir = __DIR__ . '/uploads/mangas/' . $manga_id . '/' . $chapter_id;
	ensure_dir($destDir);
	$zip = new ZipArchive();
	if ($zip->open($zipPath) === true) {
		$zip->extractTo($destDir);
		$zip->close();
		$files = glob($destDir . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
		sort($files, SORT_NATURAL);
		$page = 1;
		foreach ($files as $f) {
			$relative = 'uploads/mangas/' . $manga_id . '/' . $chapter_id . '/' . basename($f);
			add_chapter_image($chapter_id, $page, $relative);
			$page++;
		}
	}
}

// CLI usage: php manga_retriever.php --mode=api --lang=en
// or: php manga_retriever.php --mode=local --zip=/path/file.zip --manga-id=1 --chapter=1

if (php_sapi_name() === 'cli') {
	$opts = getopt('', ['mode:', 'lang::', 'zip::', 'manga-id::', 'chapter::', 'title::']);
	$mode = $opts['mode'] ?? 'api';
	if ($mode === 'api') {
		retrieve_from_mangadex($opts['lang'] ?? AppConfig::defaultLanguage());
		fwrite(STDOUT, "Done API retrieval\n");
		exit(0);
	}
	if ($mode === 'local') {
		if (empty($opts['zip']) || empty($opts['manga-id']) || empty($opts['chapter'])) {
			fwrite(STDERR, "Missing required params for local mode.\n");
			exit(1);
		}
		import_local_zip($opts['zip'], (int)$opts['manga-id'], (string)$opts['chapter'], $opts['title'] ?? null);
		fwrite(STDOUT, "Done local import\n");
		exit(0);
	}
	fwrite(STDERR, "Unknown mode\n");
	exit(1);
}

// Minimal admin trigger endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	require_once __DIR__ . '/includes/auth.php';
	if (!is_admin()) { header('HTTP/1.1 403 Forbidden'); echo 'Forbidden'; exit; }
	$mode = $_POST['mode'] ?? 'api';
	if ($mode === 'api') {
		retrieve_from_mangadex(AppConfig::defaultLanguage());
		echo 'Triggered API retrieval.';
		return;
	}
	if ($mode === 'local') {
		// Here we would accept a file upload, but for simplicity expect server path
		$zip = $_POST['zip'] ?? '';
		$manga_id = (int)($_POST['manga_id'] ?? 0);
		$chapter = (string)($_POST['chapter'] ?? '1');
		if ($zip && $manga_id) { import_local_zip($zip, $manga_id, $chapter); echo 'Imported local zip.'; return; }
		echo 'Missing params'; return;
	}
	echo 'Unknown mode';
}
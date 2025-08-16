<?php
$src = $_GET['src'] ?? '';
$src = ltrim($src, '/');
$base = __DIR__ . '/';
$path = realpath($base . $src);
$allowedRoots = [realpath(__DIR__ . '/uploads/mangas'), realpath(__DIR__ . '/assets/images/mangas')];
$ok = false;
if ($path && file_exists($path)) {
	foreach ($allowedRoots as $root) {
		if ($root && strpos($path, $root) === 0) { $ok = true; break; }
	}
}
if ($ok) {
	$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
	switch ($ext) {
		case 'jpg': case 'jpeg': header('Content-Type: image/jpeg'); break;
		case 'png': header('Content-Type: image/png'); break;
		case 'webp': header('Content-Type: image/webp'); break;
		case 'svg': header('Content-Type: image/svg+xml'); break;
		default: header('Content-Type: application/octet-stream'); break;
	}
	readfile($path);
	exit;
}
// Fallback SVG placeholder
header('Content-Type: image/svg+xml');
echo "<svg xmlns='http://www.w3.org/2000/svg' width='800' height='1200' viewBox='0 0 800 1200'>\n";
echo "<rect width='100%' height='100%' fill='#1a1f4a'/>\n";
echo "<text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='48' fill='white'>Image</text>\n";
echo "</svg>";
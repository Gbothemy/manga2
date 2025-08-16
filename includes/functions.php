<?php
require_once __DIR__ . '/db.php';

function slugify(string $text): string {
    $text = preg_replace('~[\p{Pd}\s]+~u', '-', $text);
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('~[^-a-zA-Z0-9]+~', '', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    return $text ?: 'n-a';
}

function current_user_id(): ?int {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function is_admin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: ' . AppConfig::baseUrl() . 'admin/index.php');
        exit;
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function paginate(int $page, int $per_page, int $total): array {
    $total_pages = (int)ceil(max(1, $total) / max(1, $per_page));
    $page = max(1, min($page, $total_pages));
    $offset = ($page - 1) * $per_page;
    return [
        'page' => $page,
        'per_page' => $per_page,
        'total' => $total,
        'total_pages' => $total_pages,
        'offset' => $offset,
    ];
}

function fetch_featured_mangas(int $limit = 5): array {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT m.*, (SELECT COUNT(*) FROM reading_history rh WHERE rh.manga_id = m.id) AS popularity FROM mangas m ORDER BY m.is_featured DESC, popularity DESC, m.created_at DESC LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function fetch_latest_updates(int $limit = 12): array {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT m.*, MAX(c.created_at) AS last_update FROM mangas m LEFT JOIN chapters c ON c.manga_id = m.id GROUP BY m.id ORDER BY (last_update IS NULL), last_update DESC, m.created_at DESC LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function fetch_genres(): array {
    $pdo = get_db_connection();
    $stmt = $pdo->query('SELECT id, name FROM genres ORDER BY name ASC');
    return $stmt->fetchAll();
}

function fetch_mangas(array $filters, int $page, int $per_page, string $sort): array {
    $pdo = get_db_connection();
    $where = [];
    $params = [];

    if (!empty($filters['q'])) {
        $where[] = '(m.title LIKE :q OR m.description LIKE :q)';
        $params[':q'] = '%' . $filters['q'] . '%';
    }
    if (!empty($filters['genre_id'])) {
        $where[] = 'EXISTS (SELECT 1 FROM manga_genres mg WHERE mg.manga_id = m.id AND mg.genre_id = :gid)';
        $params[':gid'] = (int)$filters['genre_id'];
    }

    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    switch ($sort) {
        case 'popular':
            $order = 'popularity DESC, m.created_at DESC';
            break;
        case 'az':
            $order = 'm.title ASC';
            break;
        case 'latest':
        default:
            $order = '(last_update IS NULL), last_update DESC, m.created_at DESC';
            break;
    }

    $count_sql = "SELECT COUNT(*) FROM mangas m $where_sql";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $p = paginate($page, $per_page, $total);

    $sql = "SELECT m.*, MAX(c.created_at) AS last_update, (SELECT COUNT(*) FROM reading_history rh WHERE rh.manga_id = m.id) AS popularity
            FROM mangas m LEFT JOIN chapters c ON c.manga_id = m.id
            $where_sql
            GROUP BY m.id
            ORDER BY $order
            LIMIT :limit OFFSET :offset";
    // Replace NULLS LAST for MySQL compatibility
    $sql = str_replace('NULLS LAST', '', $sql);
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        if ($k === ':gid') {
            $stmt->bindValue($k, (int)$v, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
    }
    $stmt->bindValue(':limit', $p['per_page'], PDO::PARAM_INT);
    $stmt->bindValue(':offset', $p['offset'], PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    return ['items' => $rows, 'pagination' => $p];
}

function fetch_manga(int $id): ?array {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT * FROM mangas WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function fetch_manga_chapters(int $manga_id): array {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT * FROM chapters WHERE manga_id = :manga_id ORDER BY chapter_number ASC');
    $stmt->execute([':manga_id' => $manga_id]);
    return $stmt->fetchAll();
}

function fetch_chapter(int $chapter_id): ?array {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT * FROM chapters WHERE id = :id');
    $stmt->execute([':id' => $chapter_id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function fetch_chapter_images(int $chapter_id): array {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT * FROM chapter_images WHERE chapter_id = :cid ORDER BY page_number ASC');
    $stmt->execute([':cid' => $chapter_id]);
    return $stmt->fetchAll();
}

function record_reading_history(int $user_id, int $manga_id, int $chapter_id): void {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('INSERT INTO reading_history (user_id, manga_id, chapter_id, read_at) VALUES (:uid, :mid, :cid, NOW())');
    $stmt->execute([':uid' => $user_id, ':mid' => $manga_id, ':cid' => $chapter_id]);
}

function is_bookmarked(int $user_id, int $manga_id): bool {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT 1 FROM bookmarks WHERE user_id = :uid AND manga_id = :mid');
    $stmt->execute([':uid' => $user_id, ':mid' => $manga_id]);
    return (bool)$stmt->fetchColumn();
}

function toggle_bookmark(int $user_id, int $manga_id): bool {
    if (is_bookmarked($user_id, $manga_id)) {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare('DELETE FROM bookmarks WHERE user_id = :uid AND manga_id = :mid');
        $stmt->execute([':uid' => $user_id, ':mid' => $manga_id]);
        return false;
    } else {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare('INSERT INTO bookmarks (user_id, manga_id, created_at) VALUES (:uid, :mid, NOW())');
        $stmt->execute([':uid' => $user_id, ':mid' => $manga_id]);
        return true;
    }
}

function build_manga_url(array $manga): string {
    $slug = $manga['slug'] ?? slugify($manga['title']);
    return AppConfig::baseUrl() . 'manga/' . $slug . '-' . $manga['id'];
}

function build_chapter_url(array $manga, array $chapter): string {
    $slug = $manga['slug'] ?? slugify($manga['title']);
    return AppConfig::baseUrl() . 'read/' . $slug . '-' . $manga['id'] . '/chapter-' . $chapter['chapter_number'] . '-' . $chapter['id'];
}
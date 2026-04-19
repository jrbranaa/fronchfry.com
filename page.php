<?php
require_once __DIR__ . '/includes/markdown.php';

$slug = preg_replace('/[^a-z0-9_-]/i', '', $_GET['slug'] ?? '');
$page = $slug ? load_page($slug) : null;

if (!$page) {
    http_response_code(404);
    $page_title = '404 — Not Found';
    require __DIR__ . '/includes/header.php';
    echo '<p class="error">Page not found.</p>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$page_title = htmlspecialchars($page['meta']['title']) . ' — fronchfry';
require __DIR__ . '/includes/header.php';
?>

<article class="page">
    <header class="page-header">
        <h1><?= htmlspecialchars($page['meta']['title']) ?></h1>
    </header>
    <div class="page-content">
        <?= $page['html'] ?>
    </div>
</article>

<?php require __DIR__ . '/includes/footer.php'; ?>

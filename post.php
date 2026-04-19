<?php
require_once __DIR__ . '/includes/markdown.php';

$slug = preg_replace('/[^a-z0-9_-]/i', '', $_GET['slug'] ?? '');
$post = $slug ? load_post($slug) : null;

if (!$post) {
    http_response_code(404);
    $page_title = '404 — Not Found';
    require __DIR__ . '/includes/header.php';
    echo '<p class="error">Post not found.</p>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$page_title = htmlspecialchars($post['meta']['title']) . ' — fronchfry';
require __DIR__ . '/includes/header.php';
?>

<article class="post">
    <header class="post-header">
        <h1><?= htmlspecialchars($post['meta']['title']) ?></h1>
        <?php if ($post['meta']['date']): ?>
            <time datetime="<?= htmlspecialchars($post['meta']['date']) ?>">
                <?= htmlspecialchars(date('F j, Y', strtotime($post['meta']['date']))) ?>
            </time>
        <?php endif; ?>
    </header>
    <div class="post-content">
        <?= $post['html'] ?>
    </div>
    <footer class="post-footer">
        <a href="/">&larr; Back to posts</a>
    </footer>
</article>

<?php require __DIR__ . '/includes/footer.php'; ?>

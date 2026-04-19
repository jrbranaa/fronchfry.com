<?php
require_once __DIR__ . '/includes/markdown.php';

$page_title = 'fronchfry — Blog';
$posts = get_all_posts();

require __DIR__ . '/includes/header.php';
?>

<section class="post-list">
    <h1>Posts</h1>
    <?php if (empty($posts)): ?>
        <p>No posts yet.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
        <article class="post-summary">
            <h2><a href="/posts/<?= htmlspecialchars($post['slug']) ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
            <?php if ($post['date']): ?>
                <time datetime="<?= htmlspecialchars($post['date']) ?>"><?= htmlspecialchars(date('F j, Y', strtotime($post['date']))) ?></time>
            <?php endif; ?>
            <?php if ($post['description']): ?>
                <p><?= htmlspecialchars($post['description']) ?></p>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

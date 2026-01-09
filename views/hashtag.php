<?php require __DIR__ . '/../controllers/HashtagPage.php'; ?>
<?php
$title = $hashtag ?: '#';
$currentPage = 'hashtag';
require __DIR__ . '/../components/_header.php';
?>

        <main class="feed-column">

            <h2 class="hashtag-title">Showing posts for <?= $hashtag ?></h2>

            <?php if (empty($posts)): ?>
                <p class="hashtag-empty">No posts found for <?= $hashtag ?>.</p>
            <?php endif; ?>

            <?php foreach ($posts as $post): ?>
                <?php require __DIR__ . '/../components/_post.php'; ?>
            <?php endforeach; ?>

        </main>

        <?php require __DIR__ . '/../components/_aside.php'; ?>

        <?php require __DIR__ . '/../components/_footer.php'; ?>

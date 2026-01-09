<?php require __DIR__ . '/../controllers/HomePage.php'; ?>
<?php
$title = 'Home';
$currentPage = 'home';
require __DIR__ . '/../components/_header.php';
?>

        <main>
            <div class="main-overlay" id="mainOverlay"></div>
            <div
                id="homeFeed"
                data-feed-limit="<?php echo htmlspecialchars($feedLimit ?? 25); ?>"
                data-feed-offset="<?php echo htmlspecialchars(count($posts)); ?>"
                data-feed-has-more="<?php echo !empty($feedHasMore) ? '1' : '0'; ?>"
            >
                <?php foreach ($posts as $post): ?>
                    <?php require __DIR__ . "/../components/_post.php"; ?>
                <?php endforeach; ?>
            </div>
        </main>
        <?php require __DIR__ . '/../components/_aside.php'; ?>
        <?php require __DIR__ . '/../components/_footer.php'; ?>

    

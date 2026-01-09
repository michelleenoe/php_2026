<div class="trending" id="trendingList">
<?php foreach ($trending as $row): ?>
    <?php
        $tag = $row['tag'];      
        $count = $row['count'];  
        $clean = urlencode(ltrim($tag, '#'));
    ?>
    <div class="trending-item">
        <div class="trending-info">
            <span class="item_title">
                Trending · <?= $count ?> posts
            </span>

            <p>
                <a class="hashtag-link" href="/hashtag/<?= $clean ?>">
                    <?= htmlspecialchars($tag) ?>
                </a>
            </p>
        </div>
    </div>
<?php endforeach; ?>
</div>

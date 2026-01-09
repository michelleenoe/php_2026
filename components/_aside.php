<?php
require_once __DIR__ . '/../x.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../classes/FollowService.php';
require_once __DIR__ . '/../classes/TrendingService.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$currentUserPk = $_SESSION['user']['user_pk'] ?? null;

$followService = $followService ?? new FollowService();
$trendingService = $trendingService ?? new TrendingService();

$trendingLimit = $trendingLimit ?? 4;
$trendingOffset = $trendingOffset ?? 0;
$maxItems = $maxItems ?? 10;

$trending = $trendingService->getTrending($trendingLimit, $trendingOffset);

if (!empty($currentPage) && ($currentPage === 'profile' || $currentPage === 'user')) {
    $profileUserPk = $currentPage === 'profile' ? $currentUserPk : ($profileUser['user_pk'] ?? $currentUserPk);

    $followers = $followService->getFollowers($profileUserPk, 3, 0);
    $following = $followService->getFollowing($profileUserPk, 3, 0);
}


if (!isset($usersToFollow)) {
    $followLimit = $followLimit ?? 3;
    $usersToFollow = [];
    if ($currentUserPk && $currentPage !== 'user' && $currentPage !== 'profile') {
        $usersToFollow = $followService->getSuggestions($currentUserPk, $followLimit, 0);
    }
}
$initialFollowCount = count($usersToFollow);
?>

<aside>
    <form id="home-search-form">
        <input
            id="home-search-input"
            type="text"
            placeholder="Search Weave"
            autocomplete="off">
        <button type="submit">Search</button>
    </form>

    <?php if (!empty($currentPage) && $currentPage === 'profile'): ?>

        <div class="following">
            <h2>Following</h2>
            <?php if (!empty($following)): ?>
                <div class="follow-suggestion" id="followingList">
                    <?php foreach ($following as $user): ?>
                        <?php require __DIR__ . '/_follow_tag.php'; ?>
                    <?php endforeach; ?>
                </div>
                <?php if (count($following) >= 3): ?>
                    <button id="followingShowMore" class="show-more-btn"
                        data-offset="<?= count($following) ?>"
                        data-limit="3"
                        data-user-pk="<?= htmlspecialchars($currentUserPk) ?>">Show more</button>
                <?php endif; ?>
            <?php else: ?>
                <p>Not following anyone yet.</p>
            <?php endif; ?>
        </div>
        <hr>
        <div class="who-to-follow">
            <h2>Followers</h2>
            <?php if (!empty($followers)): ?>
                <div class="follow-suggestion" id="followersList">
                    <?php foreach ($followers as $follower): ?>
                        <?php $user = $follower; ?>
                        <?php require __DIR__ . '/_follow_tag_user.php'; ?>
                    <?php endforeach; ?>
                </div>
                <?php if (count($followers) >= 3): ?>
                    <button id="followersShowMore" class="show-more-btn"
                        data-offset="<?= count($followers) ?>"
                        data-limit="3"
                        data-user-pk="<?= htmlspecialchars($currentUserPk) ?>">Show more</button>
                <?php endif; ?>
            <?php else: ?>
                <p>No followers yet.</p>
            <?php endif; ?>
        </div>

    <?php elseif (!empty($currentPage) && $currentPage === 'user'): ?>

        <div class="following">
            <h2>Following</h2>
            <?php if (!empty($following)): ?>
                <div class="follow-suggestion" id="followingList">
                    <?php foreach ($following as $user): ?>
                        <?php require __DIR__ . '/_follow_tag.php'; ?>
                    <?php endforeach; ?>
                </div>
                <?php if (count($following) >= 3): ?>
                    <button id="followingShowMore" class="show-more-btn"
                        data-offset="<?= count($following) ?>"
                        data-limit="3"
                        data-user-pk="<?= htmlspecialchars($profileUserPk) ?>">Show more</button>
                <?php endif; ?>
            <?php else: ?>
                <p>Not following anyone yet.</p>
            <?php endif; ?>
        </div>
        <hr>
        <div class="who-to-follow">
            <h2>Followers</h2>
            <?php if (!empty($followers)): ?>
                <div class="follow-suggestion" id="followersList">
                    <?php foreach ($followers as $follower): ?>
                        <?php $user = $follower; ?>
                        <?php require __DIR__ . '/_follow_tag_user.php'; ?>
                    <?php endforeach; ?>
                </div>
                <?php if (count($followers) >= 3): ?>
                    <button id="followersShowMore" class="show-more-btn"
                        data-offset="<?= count($followers) ?>"
                        data-limit="3"
                        data-user-pk="<?= htmlspecialchars($profileUserPk) ?>">Show more</button>
                <?php endif; ?>
            <?php else: ?>
                <p>No followers yet.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
    <div class="happening-now">
        <h2>What's happening now</h2>

        <?php require __DIR__ . '/_trending.php'; ?>

        <?php if ($trendingLimit < $maxItems): ?>
        <button id="trendingShowMore" class="show-more-btn" data-offset="<?= $trendingLimit ?>" data-limit="2"
            data-initial="<?= $trendingLimit ?>" data-max="<?= $maxItems ?>">
            Show more
        </button>
        <?php endif; ?>
    </div>
        <hr>
        <div class="who-to-follow">
            <h2>Who to follow</h2>
            <?php if (!empty($usersToFollow)): ?>
                <div class="follow-suggestion" id="whoToFollowList">
                    <?php foreach ($usersToFollow as $user): ?>
                        <?php require __DIR__ . '/_follow_tag.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No more users to follow.</p>
            <?php endif; ?>

            <?php if ($initialFollowCount >= 3): ?>
                <button id="followShowMore" class="show-more-btn"
                    data-offset="<?= $initialFollowCount ?>"
                    data-limit="<?= $followLimit ?>">Show more</button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php require __DIR__ . '/_footer_links.php'; ?>
</aside>
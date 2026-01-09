<?php

require_once __DIR__ . '/private/env.php';
loadEnvFile(__DIR__ . '/.env');

$dbHost = getenv('DB_HOST');
$dbName = getenv('DB_NAME');
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

$db = new PDO($dsn, $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);


$db->exec("SET FOREIGN_KEY_CHECKS = 0");
$db->exec("TRUNCATE TABLE notifications");
$db->exec("TRUNCATE TABLE likes");
$db->exec("TRUNCATE TABLE comments");
$db->exec("TRUNCATE TABLE reposts");
$db->exec("TRUNCATE TABLE follows");
$db->exec("TRUNCATE TABLE posts");
$db->exec("TRUNCATE TABLE users");
$db->exec("SET FOREIGN_KEY_CHECKS = 1");

function randPk(){
    return bin2hex(random_bytes(25));
}

function fakeUser(){
    $first = [
        "Alex","Emma","Liam","Olivia","Noah","Ava","Ethan","Mia","Lucas","Isabella",
        "Mason","Sophia","James","Amelia","Benjamin","Harper","Henry","Ella","Daniel","Grace",
        "Jack","Scarlett","Samuel","Chloe","Logan","Victoria","Leo","Hazel","Owen","Lily"
    ];
    $last  = [
        "Smith","Johnson","Williams","Brown","Jones","Miller","Davis","Garcia","Rodriguez","Wilson",
        "Anderson","Taylor","Thomas","Moore","Jackson","Martin","Lee","Perez","Thompson","White"
    ];

    $f = $first[array_rand($first)];
    $l = $last[array_rand($last)];
    $full = "$f $l";

    $base = strtolower($f.$l);
    $base = preg_replace("/[^a-z0-9]/", "", $base);
    $username = substr($base, 0, 14).rand(10,99);
    $email = $username."@example.com";

    return [
        "full" => $full,
        "username" => $username,
        "email" => $email
    ];
}

function fakePostMessage(){
    $emojis = ["🔥","✨","😊","🎉","📸","💬","🙌","👍","💡","📍","⭐","😎","🛠️","❤️","🚀","📢"];
    $hashtags = ["#update","#news","#hello","#random","#coding","#project","#daily","#vibes","#now","#trending","#life","#post"];
    $words = [
        "new","post","today","great","check","feature","system",
        "working","live","testing","nice","message","social",
        "feed","example","update","amazing","cool","fresh",
        "happening","random","latest"
    ];

    shuffle($words);
    $count = rand(4, 10);
    $parts = array_slice($words, 0, $count);

    $emoji = $emojis[array_rand($emojis)];
    $tag = $hashtags[array_rand($hashtags)];

    return ucfirst(implode(" ", $parts)." ".$emoji." ".$tag);
}

function fakeCommentMessage($withHashtag){
    $emojis = ["🔥","✨","😊","🎉","📸","💬","🙌","👍","💡","⭐","😎","❤️"];
    $hashtags = ["#nice","#agree","#true","#coding","#thoughts","#reply","#comment","#chat"];
    $words = [
        "love","this","totally","makes","sense","great","point",
        "nice","idea","interesting","thought","cool","update",
        "looks","good","simple","clean","solid","work"
    ];

    shuffle($words);
    $count = rand(4, 12);
    $parts = array_slice($words, 0, $count);

    $emoji = $emojis[array_rand($emojis)];

    $msg = ucfirst(implode(" ", $parts)." ".$emoji);

    if($withHashtag){
        $tag = $hashtags[array_rand($hashtags)];
        $msg .= " ".$tag;
    }

    return $msg;
}

function pickPicsumId(string $key, array $pool): int {
    $hash = abs(crc32($key));
    return $pool[$hash % count($pool)];
}

$users = [];

$weavePk     = "09b8d0ce216dca1ce841c2f5e889f4fe38e42abd4f089ddfea";
$weaveUser   = "weave";
$weaveEmail  = "weave@example.com";
$weaveFull   = "Weave Official";
$weaveAvatar = "/public/uploads/avatars/d0f2df86de765b1b89cbd433.png";
$weaveCover  = "/public/uploads/covers/3d8befc88ef703c54c0358cb.png";
$weavePass   = password_hash("test1234", PASSWORD_DEFAULT);

$stmtUser = $db->prepare("
    INSERT INTO users (
        user_pk,
        user_username,
        user_email,
        user_password,
        user_is_verified,
        user_verify_token,
        user_full_name,
        deleted_at,
        created_at,
        updated_at,
        user_avatar,
        user_cover
    )
    VALUES (:pk, :un, :em, :pw, 1, NULL, :fn, NULL, NOW(), NULL, :avatar, :cover)
");

$stmtUser->execute([
    ":pk"     => $weavePk,
    ":un"     => $weaveUser,
    ":em"     => $weaveEmail,
    ":pw"     => $weavePass,
    ":fn"     => $weaveFull,
    ":avatar" => $weaveAvatar,
    ":cover"  => $weaveCover
]);

$users[] = $weavePk;

$totalRandomUsers = 100;
$password = password_hash("test1234", PASSWORD_DEFAULT);

for($i = 0; $i < $totalRandomUsers; $i++){
    $pk = randPk();
    if($pk === $weavePk){
        $pk = randPk();
    }

    $u = fakeUser();

    // Deterministic but varied covers/avatars per user
    $coverPool = [1003, 1004, 1005, 1006, 1011, 1015, 1016, 1024, 1025, 1036];
    $avatarSeed = substr($pk, 0, 12);
    $coverId = pickPicsumId($pk, $coverPool);
    $cover  = "https://picsum.photos/id/{$coverId}/800/300";
    $avatar = "https://api.dicebear.com/9.x/bottts/svg?seed={$avatarSeed}&backgroundColor=b6e3f4";

    $stmtUser->execute([
        ":pk"     => $pk,
        ":un"     => $u["username"],
        ":em"     => $u["email"],
        ":pw"     => $password,
        ":fn"     => $u["full"],
        ":avatar" => $avatar,
        ":cover"  => $cover
    ]);

    $users[] = $pk;
}

echo "Users created: ".count($users)."<br>";

$posts = [];
$stmtPost = $db->prepare("
    INSERT INTO posts (
        post_pk,
        post_message,
        post_image_path,
        post_user_fk,
        deleted_at,
        created_at,
        updated_at
    )
    VALUES (:pk, :msg, :img, :ufk, NULL, :created_at, NULL)
");

foreach($users as $userPk){
    $numPosts = rand(3, 7);

    for($i = 0; $i < $numPosts; $i++){
        $postPk = randPk();

        if (rand(0, 3) === 1) {
            $postPool = [10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120, 130, 140, 150, 160];
            $postId = pickPicsumId($postPk, $postPool);
            $img = "https://picsum.photos/id/{$postId}/600/300"; // deterministic per post
        } else {
            $img = "";
        }

        $createdAt = (new DateTimeImmutable('now'))
            ->sub(new DateInterval('PT' . rand(0, 60 * 24 * 7) . 'M')); // spread posts over last ~7 days

        $stmtPost->execute([
            ":pk"  => $postPk,
            ":msg" => fakePostMessage(),
            ":img" => $img,
            ":ufk" => $userPk,
            ":created_at" => $createdAt->format('Y-m-d H:i:s')
        ]);

        $posts[] = [
            "pk"       => $postPk,
            "user_pk"  => $userPk,
            "post_image_path" => $img,
            "created_at" => $createdAt
        ];
    }
}

echo "Posts created: ".count($posts)."<br>";


$stmtFollow = $db->prepare("
    INSERT IGNORE INTO follows (follower_user_fk, follow_user_fk)
    VALUES (:follower, :follow)
");

$userCount = count($users);

for($i = 0; $i < $userCount; $i++){
    $u = $users[$i];

    $pool = $users;
    $pool = array_values(array_filter($pool, function($x) use ($u){ return $x !== $u; }));

    shuffle($pool);
    $toFollow = array_slice($pool, 0, min(10, count($pool)));

    foreach($toFollow as $target){
        $stmtFollow->execute([
            ":follower" => $u,
            ":follow"   => $target
        ]);
    }
}

echo "Follows created (approx 10 per user)<br>";

$stmtLike = $db->prepare("
    INSERT IGNORE INTO likes (like_user_fk, like_post_fk)
    VALUES (:user, :post)
");

$postCount = count($posts);

foreach($users as $u){
    $otherPosts = array_filter($posts, function($p) use ($u){
        return $p["user_pk"] !== $u;
    });

    $otherPosts = array_values($otherPosts);
    shuffle($otherPosts);

    $slice = array_slice($otherPosts, 0, min(50, count($otherPosts)));

    foreach($slice as $p){
        $stmtLike->execute([
            ":user" => $u,
            ":post" => $p["pk"]
        ]);
    }
}

echo "Likes created (~50 per user)<br>";


$stmtRepost = $db->prepare("
    INSERT IGNORE INTO reposts (repost_pk, repost_user_fk, repost_post_fk, created_at)
    VALUES (:pk, :user, :post, :created_at)
");

for($i = 0; $i < $userCount; $i++){
    $u = $users[$i];

    if($i % 10 !== 0) continue;

    $otherPosts = array_filter($posts, function($p) use ($u){
        return $p["user_pk"] !== $u;
    });
    $otherPosts = array_values($otherPosts);
    shuffle($otherPosts);

    $slice = array_slice($otherPosts, 0, min(5, count($otherPosts)));

    foreach ($slice as $p){
        // Make the repost happen shortly after the original post time to mix ordering in the feed.
        $offsetMinutes = rand(5, 60 * 24); // up to 24 hours later
        $repostCreated = $p["created_at"]->add(new DateInterval('PT' . $offsetMinutes . 'M'));

        $stmtRepost->execute([
            ":pk"   => randPk(),
            ":user" => $u,
            ":post" => $p["pk"],
            ":created_at" => $repostCreated->format('Y-m-d H:i:s')
        ]);
    }
}

echo "Reposts created (for every 10th user)<br>";


$stmtComment = $db->prepare("
    INSERT INTO comments (
        comment_pk,
        comment_post_fk,
        comment_user_fk,
        comment_message,
        comment_created_at,
        updated_at,
        deleted_at
    )
    VALUES (:pk, :pfk, :ufk, :msg, NOW(), NULL, NULL)
");

foreach($posts as $p){
    $numComments = rand(0, 6);

    for($i = 0; $i < $numComments; $i++){
        $commentPk = randPk();
        $userPk    = $users[array_rand($users)];
        $withTag   = (rand(0, 1) === 1);

        $stmtComment->execute([
            ":pk"  => $commentPk,
            ":pfk" => $p["pk"],
            ":ufk" => $userPk,
            ":msg" => fakeCommentMessage($withTag)
        ]);
    }
}

echo "Comments created<br>";


$follows = $db->query("SELECT follower_user_fk, follow_user_fk FROM follows")->fetchAll(PDO::FETCH_ASSOC);
$stmtNotifFollow = $db->prepare("
    INSERT INTO notifications (
        notification_pk,
        notification_user_fk,
        notification_actor_fk,
        notification_post_fk,
        notification_message,
        is_read,
        created_at,
        deleted_at
    )
    VALUES (:pk, :user, :actor, NULL, 'started following you', 0, NOW(), NULL)
");

foreach($follows as $f){
    $stmtNotifFollow->execute([
        ":pk"    => randPk(),
        ":user"  => $f["follow_user_fk"],
        ":actor" => $f["follower_user_fk"]
    ]);
}

$likes = $db->query("
    SELECT l.like_user_fk, l.like_post_fk, p.post_user_fk
    FROM likes l
    JOIN posts p ON l.like_post_fk = p.post_pk
")->fetchAll(PDO::FETCH_ASSOC);

$stmtNotifLike = $db->prepare("
    INSERT INTO notifications (
        notification_pk,
        notification_user_fk,
        notification_actor_fk,
        notification_post_fk,
        notification_message,
        is_read,
        created_at,
        deleted_at
    )
    VALUES (:pk, :user, :actor, :post, 'liked your post', 0, NOW(), NULL)
");

foreach($likes as $l){
    if($l["like_user_fk"] === $l["post_user_fk"]){
        continue;
    }

    $stmtNotifLike->execute([
        ":pk"    => randPk(),
        ":user"  => $l["post_user_fk"],
        ":actor" => $l["like_user_fk"],
        ":post"  => $l["like_post_fk"]
    ]);
}

$comments = $db->query("
    SELECT c.comment_user_fk, c.comment_post_fk, p.post_user_fk
    FROM comments c
    JOIN posts p ON c.comment_post_fk = p.post_pk
")->fetchAll(PDO::FETCH_ASSOC);

$stmtNotifComment = $db->prepare("
    INSERT INTO notifications (
        notification_pk,
        notification_user_fk,
        notification_actor_fk,
        notification_post_fk,
        notification_message,
        is_read,
        created_at,
        deleted_at
    )
    VALUES (:pk, :user, :actor, :post, 'commented on your post', 0, NOW(), NULL)
");

foreach($comments as $c){
    if($c["comment_user_fk"] === $c["post_user_fk"]){
        continue;
    }

    $stmtNotifComment->execute([
        ":pk"    => randPk(),
        ":user"  => $c["post_user_fk"],
        ":actor" => $c["comment_user_fk"],
        ":post"  => $c["comment_post_fk"]
    ]);
}

echo "Notifications created<br>";
echo "<hr>SEED COMPLETE<br>";

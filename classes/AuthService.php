<?php
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/ImageService.php';

class AuthService {
    private ImageService $imageService;
    private const WEAVE_USER_PK_FALLBACK = '09b8d0ce216dca1ce841c2f5e889f4fe38e42abd4f089ddfea';
    private const WEAVE_FOLLOW_USERNAME = 'weave';
    private const WEAVE_FOLLOW_EMAIL    = 'hi@michelleenoe.com';

    public function __construct(?ImageService $imageService = null) {
        $this->imageService = $imageService ?? new ImageService();
    }

    public function registerUser(PDO $db, string $userPk, string $username, string $fullName, string $email, string $hashedPassword, ?string $token = null): void {
        $cover  = $this->imageService->generateCover($userPk);
        $avatar = $this->imageService->generateAvatar($userPk);
        User::create($db, $userPk, $username, $fullName, $email, $hashedPassword, $token, $cover, $avatar);
        $this->ensureWeaveFollows($db, $userPk);
        $this->sendWeaveWelcomeNotification($db, $userPk);
    }

    public function authenticate(PDO $db, string $email, string $password): array {
        $stmt = $db->prepare("
            SELECT * 
            FROM users 
            WHERE user_email = :email 
            AND deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("There is no account with this email");
        }

        if (!password_verify($password, $user['user_password'])) {
            throw new Exception("Incorrect password");
        }

        return $user;
    }

    public function findExistingByUsernameOrEmail(PDO $db, string $username, string $email): ?array {
        $check = $db->prepare("
            SELECT user_username, user_email 
            FROM users 
            WHERE user_username = :u OR user_email = :e 
            LIMIT 1
        ");
        $check->execute([':u' => $username, ':e' => $email]);
        $existing = $check->fetch();
        return $existing ?: null;
    }

    private function ensureWeaveFollows(PDO $db, string $newUserPk): void {
        $weavePk = $this->findWeaveUserPk($db);

        if (!$weavePk || $weavePk === $newUserPk) {
            return;
        }

        $stmt = $db->prepare("
            INSERT IGNORE INTO follows (follower_user_fk, follow_user_fk)
            VALUES (:follower, :follow)
        ");

        try {
            $stmt->execute([
                ':follower' => $weavePk,
                ':follow'   => $newUserPk
            ]);

            $stmt->execute([
                ':follower' => $newUserPk,
                ':follow'   => $weavePk
            ]);
        } catch (Exception $e) {
            error_log("Failed to auto-follow new user with Weave: " . $e->getMessage());
        }
    }

    private function sendWeaveWelcomeNotification(PDO $db, string $recipientPk): void {
        $weavePk = $this->findWeaveUserPk($db);

        if (!$weavePk || $weavePk === $recipientPk) {
            return;
        }

        $stmt = $db->prepare("
            INSERT INTO notifications (notification_pk, notification_user_fk, notification_actor_fk, notification_post_fk, notification_message, is_read, created_at)
            VALUES (:pk, :user, :actor, NULL, :msg, 0, NOW())
        ");

        try {
            $stmt->execute([
                ':pk'    => bin2hex(random_bytes(50)),
                ':user'  => $recipientPk,
                ':actor' => $weavePk,
                ':msg'   => 'Welcome to Weave!'
            ]);
        } catch (Exception $e) {
            error_log("Failed to send Weave welcome notification: " . $e->getMessage());
        }
    }

    private function findWeaveUserPk(PDO $db): ?string {
        $envPk = $_ENV['WEAVE_USER_PK'] ?? $_SERVER['WEAVE_USER_PK'] ?? null;
        if (!empty($envPk)) {
            return $envPk;
        }

        if (!empty(self::WEAVE_USER_PK_FALLBACK)) {
            return self::WEAVE_USER_PK_FALLBACK;
        }

        $stmt = $db->prepare("
            SELECT user_pk
            FROM users
            WHERE deleted_at IS NULL
              AND (
                LOWER(user_username) = :username
                OR LOWER(user_full_name) = :fullName
                OR LOWER(user_email) = :email
              )
            ORDER BY created_at ASC
            LIMIT 1
        ");

        $stmt->bindValue(':username', self::WEAVE_FOLLOW_USERNAME);
        $stmt->bindValue(':fullName', self::WEAVE_FOLLOW_USERNAME);
        $stmt->bindValue(':email', self::WEAVE_FOLLOW_EMAIL);
        $stmt->execute();

        $pk = $stmt->fetchColumn();
        return $pk ? (string) $pk : null;
    }
}

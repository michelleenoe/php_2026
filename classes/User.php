<?php
class User {
    public static function create($db, $userPk, $username, $fullName, $email, $password, $token = null, $cover = null, $avatar = null) {
        
        $cover  = $cover  ?: "https://picsum.photos/800/" . rand(200, 400);
        $avatar = $avatar ?: "https://api.dicebear.com/9.x/bottts/svg?seed={$userPk}&backgroundColor=b6e3f4";

        $sql = "INSERT INTO users (
            user_pk,
            user_username,
            user_email,
            user_password,
            user_full_name,
            user_cover,
            user_avatar,
            user_verify_token,
            created_at
        ) VALUES (
            :pk,
            :username,
            :email,
            :password,
            :fullname,
            :cover,
            :avatar,
            :token,
            NOW()
        )";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':pk',       $userPk);
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':email',    $email);
        $stmt->bindValue(':password', $password);
        $stmt->bindValue(':fullname', $fullName);
        $stmt->bindValue(':cover',    $cover);
        $stmt->bindValue(':avatar',   $avatar);
        $stmt->bindValue(':token',    $token);

        $stmt->execute();
    }
}

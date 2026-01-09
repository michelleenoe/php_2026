<?php

class ImageService {

    public function generateAvatar(string $userPk): string {
        return "https://api.dicebear.com/9.x/bottts/svg?seed={$userPk}&backgroundColor=b6e3f4";
    }

    public function generateCover(?string $seed = null): string {
        $seed = $seed ?? bin2hex(random_bytes(8));
        return "https://picsum.photos/seed/{$seed}/800/300";
    }
}

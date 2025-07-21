<?php

namespace App\Models;

use Echo\Framework\Database\Model;

class User extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('users', $id);
    }

    public function gravatar(int $size = 80, string $default = "mp", string $rating = "g")
    {
        $hash = hash( "sha256", strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d={$default}&r={$rating}";
    }
}

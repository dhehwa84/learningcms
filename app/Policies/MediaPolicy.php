<?php
// app/Policies/MediaPolicy.php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function view(User $user, Media $media): bool
    {
        return $media->user_id === $user->id || $user->isAdmin();
    }

    public function delete(User $user, Media $media): bool
    {
        return $media->user_id === $user->id || $user->isAdmin();
    }
}
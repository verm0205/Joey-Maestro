<?php

namespace App\Models;

class Profile
{
    public int $id;
    public string $about_me;
    public ?string $eager_to_learn;
    public ?string $perseverance;
    public ?string $team_player;
    public ?string $languages;
    public ?string $github_url;
    public ?string $email;
}

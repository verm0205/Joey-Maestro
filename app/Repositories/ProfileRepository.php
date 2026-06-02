<?php

namespace App\Repositories;

use App\Models\Profile;
use Framework\Database;

class ProfileRepository implements ProfileRepositoryInterface
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function get(): ?Profile
    {
        $row = $this->database->run('SELECT * FROM profiles WHERE id = 1')->fetch();
        return $row ? $this->fromRow($row) : null;
    }

    public function update(Profile $profile): bool
    {
        $this->database->run(
            'UPDATE profiles
             SET about_me = :about_me, eager_to_learn = :eager_to_learn, perseverance = :perseverance,
                 team_player = :team_player, languages = :languages, github_url = :github_url, email = :email
             WHERE id = 1',
            [
                'about_me' => $profile->about_me,
                'eager_to_learn' => $profile->eager_to_learn,
                'perseverance' => $profile->perseverance,
                'team_player' => $profile->team_player,
                'languages' => $profile->languages,
                'github_url' => $profile->github_url,
                'email' => $profile->email,
            ]
        );

        return true;
    }

    private function fromRow(mixed $row): Profile
    {
        $profile = new Profile();
        $profile->id = (int) $row->id;
        $profile->about_me = $row->about_me;
        $profile->eager_to_learn = $row->eager_to_learn;
        $profile->perseverance = $row->perseverance;
        $profile->team_player = $row->team_player;
        $profile->languages = $row->languages;
        $profile->github_url = $row->github_url;
        $profile->email = $row->email;
        return $profile;
    }
}

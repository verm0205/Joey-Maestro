<?php

namespace Tests\Integration;

use App\Models\Profile;
use App\Repositories\ProfileRepository;

class ProfileRepositoryTest extends DatabaseTestCase
{
    private ProfileRepository $repository;

    protected function createTables(): void
    {
        $this->db->exec('
            CREATE TABLE profiles (
                id       INTEGER PRIMARY KEY AUTOINCREMENT,
                about_me TEXT NOT NULL,
                eager_to_learn TEXT,
                perseverance TEXT,
                team_player TEXT,
                languages TEXT,
                github_url VARCHAR(255),
                email VARCHAR(255)
            )
        ');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProfileRepository($this->db);
    }

    public function testGetReturnsNullWhenTableIsEmpty(): void
    {
        $this->assertNull($this->repository->get());
    }

    public function testGetReturnsProfileAfterInsert(): void
    {
        $this->db->run('INSERT INTO profiles (id, about_me, eager_to_learn, perseverance, team_player, languages, github_url, email)
            VALUES (1, :about, :eager, :pers, :team, :langs, :github, :email)', [
            'about'  => 'About me text',
            'eager'  => 'Eager to learn',
            'pers'   => 'Perseverance',
            'team'   => 'Team player',
            'langs'  => 'PHP, JS',
            'github' => 'github.com/test',
            'email'  => 'test@hz.nl',
        ]);

        $profile = $this->repository->get();

        $this->assertNotNull($profile);
        $this->assertSame('About me text', $profile->about_me);
        $this->assertSame('github.com/test', $profile->github_url);
    }

    public function testUpdateChangesProfileData(): void
    {
        $this->db->run('INSERT INTO profiles (id, about_me, eager_to_learn, perseverance, team_player, languages, github_url, email)
            VALUES (1, :about, :eager, :pers, :team, :langs, :github, :email)', [
            'about'  => 'Old about',
            'eager'  => 'Old eager',
            'pers'   => 'Old pers',
            'team'   => 'Old team',
            'langs'  => 'Old langs',
            'github' => 'old.com',
            'email'  => 'old@hz.nl',
        ]);

        $profile = $this->repository->get();
        $profile->about_me = 'Updated about';
        $profile->github_url = 'new.com';

        $result = $this->repository->update($profile);
        $this->assertTrue($result);

        $updated = $this->repository->get();
        $this->assertSame('Updated about', $updated->about_me);
        $this->assertSame('new.com', $updated->github_url);
    }
}

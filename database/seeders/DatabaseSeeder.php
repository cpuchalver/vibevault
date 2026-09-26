<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\LabelRole;
use App\Models\Artist;
use App\Models\Label;
use App\Models\MusicGroup;
use App\Models\Release;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Local demo data: two isolated labels, their staff and one artist portal account each.
 * All accounts use the password "password". Never run outside local/testing.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo seeding is only allowed in local/testing environments.');
        }

        foreach (['nova' => 'Nova Records', 'echo' => 'Echo Music'] as $key => $labelName) {
            $owner = User::factory()->create(['name' => "Owner {$labelName}", 'email' => "owner@{$key}.test"]);
            $viewer = User::factory()->create(['name' => "Viewer {$labelName}", 'email' => "viewer@{$key}.test"]);

            $label = Label::factory()
                ->withMember($owner, LabelRole::Owner)
                ->withMember($viewer, LabelRole::Viewer)
                ->create(['name' => $labelName]);

            $artists = Artist::factory()
                ->count(4)
                ->for($label)
                ->create()
                ->each(function (Artist $artist): void {
                    Release::factory()->count(3)->forArtist($artist)->create();
                    Release::factory()->forArtist($artist)->draft()->create();
                });

            // The second artist plays in both groups.
            MusicGroup::factory()->for($label)->withMembers($artists->take(3))->create();
            MusicGroup::factory()->for($label)->withMembers($artists->slice(1, 2))->create();

            $portalUser = User::factory()->create(['name' => "Artist {$labelName}", 'email' => "artist@{$key}.test"]);
            $label->artists()->first()->users()->attach($portalUser);
        }
    }
}

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * A release is credited to a band, not to an artist.
 *
 * Existing releases are preserved: every artist that owns releases gets a "solo"
 * band (same label, same name) with themself as the only member, and their
 * releases are moved to it. Releases can no longer be cascade-deleted with
 * their credit: a band with releases cannot be hard-deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('releases', function (Blueprint $table) {
            $table->foreignId('band_id')->nullable()->after('label_id')->constrained()->restrictOnDelete();
        });

        DB::transaction(function (): void {
            $artistIds = DB::table('releases')->distinct()->pluck('artist_id');

            foreach (DB::table('artists')->whereIn('id', $artistIds)->get() as $artist) {
                $now = now();

                $bandId = DB::table('bands')->insertGetId([
                    'label_id' => $artist->label_id,
                    'name' => $artist->name,
                    'slug' => $this->uniqueBandSlug($artist->slug),
                    'type' => 'solo',
                    'country' => $artist->country,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('artist_band')->insert([
                    'band_id' => $bandId,
                    'artist_id' => $artist->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('releases')->where('artist_id', $artist->id)->update(['band_id' => $bandId]);
            }
        });

        Schema::table('releases', function (Blueprint $table) {
            $table->dropIndex(['artist_id', 'status']);
            $table->dropConstrainedForeignId('artist_id');
        });

        Schema::table('releases', function (Blueprint $table) {
            $table->foreignId('band_id')->nullable(false)->change();
            $table->index(['band_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('releases', function (Blueprint $table) {
            $table->foreignId('artist_id')->nullable()->after('label_id')->constrained()->cascadeOnDelete();
        });

        // Best effort: credit each release back to the first member of its band.
        foreach (DB::table('releases')->select('id', 'band_id')->get() as $release) {
            $artistId = DB::table('artist_band')
                ->where('band_id', $release->band_id)
                ->orderBy('id')
                ->value('artist_id');

            DB::table('releases')->where('id', $release->id)->update(['artist_id' => $artistId]);
        }

        Schema::table('releases', function (Blueprint $table) {
            $table->dropIndex(['band_id', 'status']);
            $table->dropConstrainedForeignId('band_id');
            $table->index(['artist_id', 'status']);
        });
    }

    private function uniqueBandSlug(string $base): string
    {
        $slug = $base;

        while (DB::table('bands')->where('slug', $slug)->exists()) {
            $slug = "{$base}-".Str::lower(Str::random(5));
        }

        return $slug;
    }
};

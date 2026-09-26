<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Artists\Pages;

use App\Enums\BandType;
use App\Filament\Label\Resources\Artists\ArtistResource;
use App\Models\Artist;
use App\Models\Band;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Gate;

class CreateArtist extends CreateRecord
{
    protected static string $resource = ArtistResource::class;

    /**
     * Optionally creates the artist's solo project in the same transaction
     * (panel database transactions are enabled), so the artist can release
     * under their own name right away. The band inherits the artist's label.
     */
    protected function afterCreate(): void
    {
        if (! ($this->data['create_solo_project'] ?? false)) {
            return;
        }

        Gate::authorize('create', Band::class);

        /** @var Artist $artist */
        $artist = $this->getRecord();

        $band = new Band([
            'name' => $artist->name,
            'type' => BandType::Solo,
            'country' => $artist->country,
        ]);
        $band->label()->associate($artist->label_id);
        $band->save();

        $band->members()->attach($artist);
    }
}

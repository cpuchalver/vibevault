<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Releases\Pages;

use App\Filament\Label\Resources\Releases\ReleaseResource;
use App\Models\Label;
use App\Models\Release;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateRelease extends CreateRecord
{
    protected static string $resource = ReleaseResource::class;

    /**
     * The owning label is attached explicitly from the tenant (never from user
     * input) *before* saving, so the model's label/artist integrity guard can
     * validate the pair on the very first write.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Release
    {
        /** @var Label $label */
        $label = Filament::getTenant();

        $release = new Release($data);
        $release->label()->associate($label);
        $release->save();

        return $release;
    }
}

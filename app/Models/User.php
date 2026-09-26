<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LabelPermission;
use App\Enums\LabelRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasTenants, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;

    public const string LabelPanel = 'label';

    public const string ArtistPanel = 'artist';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return BelongsToMany<Label, $this, LabelMembership>
     */
    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class)
            ->using(LabelMembership::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Artist profiles this account is allowed to consult in the artist portal.
     *
     * @return BelongsToMany<Artist, $this>
     */
    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class)->withTimestamps();
    }

    /**
     * Membership check only. Email verification is enforced separately by the
     * panels' `emailVerification()` (Filament's `verified` middleware on every
     * page), which redirects to the verification prompt instead of a 403, so
     * that the signed verification link itself remains reachable.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            self::LabelPanel => $this->labels()->exists(),
            self::ArtistPanel => $this->artists()->exists(),
            default => false,
        };
    }

    /**
     * @return Collection<int, Label|Artist>
     */
    public function getTenants(Panel $panel): Collection
    {
        return match ($panel->getId()) {
            self::LabelPanel => $this->labels,
            self::ArtistPanel => $this->artists,
            default => collect(),
        };
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return match (true) {
            $tenant instanceof Label => $this->belongsToLabel($tenant),
            $tenant instanceof Artist => $this->isLinkedToArtist($tenant),
            default => false,
        };
    }

    public function belongsToLabel(Label|int $label): bool
    {
        return $this->roleInLabel($label) !== null;
    }

    /**
     * Resolved from the (memoized) `labels` relation to avoid a query per check.
     */
    public function roleInLabel(Label|int $label): ?LabelRole
    {
        $labelId = $label instanceof Label ? $label->getKey() : $label;

        /** @var Label|null $membership */
        $membership = $this->labels->firstWhere('id', $labelId);

        return $membership?->pivot->role;
    }

    public function hasLabelPermission(Label|int $label, LabelPermission $permission): bool
    {
        return $this->roleInLabel($label)?->hasPermission($permission) ?? false;
    }

    public function isLinkedToArtist(Artist|int $artist): bool
    {
        $artistId = $artist instanceof Artist ? $artist->getKey() : $artist;

        return $this->artists->contains('id', $artistId);
    }
}

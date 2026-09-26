<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CatalogueSize;
use App\Enums\DemoRequesterRole;
use Database\Factories\DemoRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A prospect's demo request sent from the public marketing site.
 *
 * Not tenant-scoped: it is created before any label exists and belongs to
 * the platform. Personal data is minimised (no raw IP) and pruned after
 * `marketing.demo_requests.retention_months`.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $company
 * @property DemoRequesterRole $role
 * @property CatalogueSize|null $catalogue_size
 * @property string|null $message
 * @property Carbon $consented_at
 * @property string|null $ip_hash
 */
class DemoRequest extends Model
{
    /** @use HasFactory<DemoRequestFactory> */
    use HasFactory;

    use MassPrunable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'company',
        'role',
        'catalogue_size',
        'message',
        'consented_at',
        'ip_hash',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'ip_hash',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => DemoRequesterRole::class,
            'catalogue_size' => CatalogueSize::class,
            'consented_at' => 'datetime',
        ];
    }

    /**
     * @return Builder<self>
     */
    public function prunable(): Builder
    {
        $retentionMonths = config('marketing.demo_requests.retention_months');

        return static::query()->where('created_at', '<=', now()->subMonths($retentionMonths));
    }

    public static function hashIp(?string $ip): ?string
    {
        if ($ip === null) {
            return null;
        }

        return hash_hmac('sha256', $ip, (string) config('app.key'));
    }
}

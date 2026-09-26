<?php

declare(strict_types=1);

namespace App\Filament\Label\Tenancy;

use App\Enums\LabelRole;
use App\Models\Label;
use App\Policies\LabelPolicy;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

/**
 * Access is authorized by {@see LabelPolicy::create()} through
 * the parent `canView()`, checked on mount, hydrate and submit.
 */
class RegisterLabel extends RegisterTenant
{
    public static function getLabel(): string
    {
        return __('Créer un label');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components(LabelForm::components());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(array $data): Label
    {
        $label = Label::create($data);

        $label->members()->attach(Auth::user(), ['role' => LabelRole::Owner]);

        return $label;
    }
}

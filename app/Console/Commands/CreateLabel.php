<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\LabelRole;
use App\Models\Label;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Platform onboarding: creates a label and its owner account.
 *
 * Label self-registration in the panel is restricted to existing label members,
 * so the very first owner of a label is provisioned here by the platform operator.
 * No password is ever printed: the owner sets it through the password-reset flow.
 */
#[Signature('app:create-label {name : Nom commercial du label} {owner-email : E-mail du propriétaire} {--owner-name= : Nom du propriétaire}')]
#[Description('Crée un label et son compte propriétaire (onboarding plateforme)')]
class CreateLabel extends Command
{
    public function handle(): int
    {
        $validator = Validator::make([
            'name' => $this->argument('name'),
            'email' => $this->argument('owner-email'),
        ], [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $email = Str::lower((string) $this->argument('owner-email'));

        [$label, $owner, $isNewOwner] = DB::transaction(function () use ($email): array {
            $owner = User::query()->firstWhere('email', $email);
            $isNewOwner = $owner === null;

            $owner ??= User::query()->forceCreate([
                'name' => $this->option('owner-name') ?: Str::before($email, '@'),
                'email' => $email,
                'password' => Str::password(64),
                'email_verified_at' => now(),
            ]);

            $label = Label::query()->create(['name' => $this->argument('name')]);
            $label->members()->attach($owner, ['role' => LabelRole::Owner]);

            return [$label, $owner, $isNewOwner];
        });

        $this->components->info("Label « {$label->name} » créé (slug : {$label->slug}), propriétaire : {$owner->email}.");

        if ($isNewOwner) {
            $this->components->warn('Compte créé avec un mot de passe aléatoire non communiqué : le propriétaire doit utiliser « Mot de passe oublié » sur /label/login.');
        }

        return self::SUCCESS;
    }
}

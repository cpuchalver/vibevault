<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Legal entity
    |--------------------------------------------------------------------------
    |
    | Used by the legal pages (mentions légales, confidentialité, CGU).
    | Any missing value is rendered as a visible "[à compléter]" placeholder
    | so that an incomplete legal page can never go unnoticed.
    |
    */

    'company' => [
        'name' => env('MARKETING_COMPANY_NAME'),
        'legal_form' => env('MARKETING_COMPANY_LEGAL_FORM'),
        'share_capital' => env('MARKETING_COMPANY_SHARE_CAPITAL'),
        'registration' => env('MARKETING_COMPANY_REGISTRATION'),
        'vat_number' => env('MARKETING_COMPANY_VAT_NUMBER'),
        'address' => env('MARKETING_COMPANY_ADDRESS'),
        'publication_director' => env('MARKETING_PUBLICATION_DIRECTOR'),
        'contact_email' => env('MARKETING_CONTACT_EMAIL'),
        'privacy_email' => env('MARKETING_PRIVACY_EMAIL'),
        'host_name' => env('MARKETING_HOST_NAME'),
        'host_address' => env('MARKETING_HOST_ADDRESS'),
    ],

    'legal_updated_at' => env('MARKETING_LEGAL_UPDATED_AT'),

    /*
    |--------------------------------------------------------------------------
    | Demo requests
    |--------------------------------------------------------------------------
    */

    'demo_requests' => [
        'notify_email' => env('MARKETING_DEMO_NOTIFY_EMAIL'),
        'max_attempts_per_hour' => (int) env('MARKETING_DEMO_MAX_ATTEMPTS', 3),
        'minimum_fill_seconds' => (int) env('MARKETING_DEMO_MIN_FILL_SECONDS', 3),
        'retention_months' => (int) env('MARKETING_DEMO_RETENTION_MONTHS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pricing
    |--------------------------------------------------------------------------
    |
    | Monthly prices are in euros, excluding VAT. A null price renders
    | "Sur devis". Yearly billing applies `yearly_free_months`.
    |
    */

    'yearly_free_months' => 2,

    'plans' => [
        [
            'key' => 'independant',
            'name' => 'Indépendant',
            'audience' => 'Pour un label qui gère ses premiers artistes.',
            'monthly_price' => 49,
            'highlighted' => false,
            'features' => [
                "Jusqu'à 10 artistes",
                'Catalogue de 250 titres',
                'Relevés de royalties trimestriels',
                'Portail artiste inclus',
                '2 utilisateurs côté label',
            ],
        ],
        [
            'key' => 'label',
            'name' => 'Label',
            'audience' => 'Pour un catalogue actif avec plusieurs distributeurs.',
            'monthly_price' => 149,
            'highlighted' => true,
            'features' => [
                "Jusqu'à 60 artistes",
                'Catalogue illimité',
                'Relevés mensuels ou trimestriels',
                'Import des rapports distributeurs',
                'Avances et recoupement automatiques',
                '10 utilisateurs, rôles personnalisés',
            ],
        ],
        [
            'key' => 'groupe',
            'name' => 'Groupe',
            'audience' => 'Pour plusieurs labels ou une maison d’édition.',
            'monthly_price' => null,
            'highlighted' => false,
            'features' => [
                'Plusieurs labels, données isolées',
                'Artistes et utilisateurs illimités',
                'Authentification unique (SSO)',
                'Journal d’accès exportable',
                'Accompagnement à la migration',
            ],
        ],
    ],

    'faq' => [
        [
            'question' => 'Un artiste peut-il voir les données d’un autre artiste ?',
            'answer' => 'Non. Chaque artiste n’accède qu’à ses propres sorties, contrats et relevés. Les données de chaque label sont isolées de celles des autres labels au niveau de chaque requête.',
        ],
        [
            'question' => 'Quels rapports de distributeurs pouvez-vous importer ?',
            'answer' => 'Les exports CSV des principaux distributeurs numériques. Si le vôtre n’est pas encore pris en charge, nous ajoutons son format lors de l’accompagnement.',
        ],
        [
            'question' => 'Puis-je changer de formule en cours d’année ?',
            'answer' => 'Oui. Le passage à une formule supérieure est immédiat et calculé au prorata. Le passage à une formule inférieure prend effet à la prochaine échéance.',
        ],
        [
            'question' => 'Que deviennent nos données si nous partons ?',
            'answer' => 'Vous exportez votre catalogue, vos contrats et vos relevés à tout moment. Après résiliation, les données sont supprimées selon les délais indiqués dans nos CGU.',
        ],
    ],

];

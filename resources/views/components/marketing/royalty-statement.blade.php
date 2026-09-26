@php
    $lines = [
        ['title' => 'Marée basse', 'streams' => 412380, 'gross' => 1237.14, 'share' => 247.43],
        ['title' => 'Néons', 'streams' => 198045, 'gross' => 594.14, 'share' => 118.83],
        ['title' => 'Sans bruit', 'streams' => 87612, 'gross' => 262.84, 'share' => 52.57],
        ['title' => 'Dernier train', 'streams' => 41208, 'gross' => 123.62, 'share' => 24.72],
    ];
    $maxStreams = max(array_column($lines, 'streams'));
    $totalGross = array_sum(array_column($lines, 'gross'));
    $totalShare = array_sum(array_column($lines, 'share'));
    $recouped = 300.00;
    $money = fn (float $amount): string => number_format($amount, 2, ',', "\u{202F}").' €';
    $count = fn (int $value): string => number_format($value, 0, ',', "\u{202F}");
@endphp

<figure {{ $attributes->class('rounded-xl border border-rule bg-surface shadow-[0_1px_0_rgba(19,24,58,0.04),0_24px_48px_-24px_rgba(19,24,58,0.25)] dark:shadow-[0_24px_48px_-24px_rgba(0,0,0,0.7)]') }}>
    <figcaption class="flex items-start justify-between gap-4 border-b border-rule px-5 py-4">
        <div>
            <p class="text-xs text-slate">Relevé de royalties · 2ᵉ trimestre 2026</p>
            <p class="mt-0.5 font-display text-lg font-bold">Nora Vale</p>
        </div>
        <span class="rounded-full bg-accent/15 px-2.5 py-1 text-xs font-medium text-accent">Publié</span>
    </figcaption>

    <table class="figures w-full text-sm">
        <caption class="sr-only">Exemple de relevé de royalties pour une artiste fictive</caption>
        <thead>
            <tr class="text-left text-xs text-slate">
                <th scope="col" class="px-5 pt-3 pb-2 font-medium">Titre</th>
                <th scope="col" class="hidden px-2 pt-3 pb-2 text-right font-medium sm:table-cell">Écoutes</th>
                <th scope="col" class="px-2 pt-3 pb-2 text-right font-medium">Brut</th>
                <th scope="col" class="px-5 pt-3 pb-2 text-right font-medium">Part artiste</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lines as $line)
                <tr class="border-t border-rule/70">
                    <th scope="row" class="px-5 py-2.5 text-left font-medium">
                        {{ $line['title'] }}
                        <span class="mt-1.5 block h-1 rounded-full bg-paper" aria-hidden="true">
                            <span class="block h-1 rounded-full bg-meter" style="width: {{ round($line['streams'] / $maxStreams * 100) }}%"></span>
                        </span>
                    </th>
                    <td class="hidden px-2 py-2.5 text-right text-slate sm:table-cell">{{ $count($line['streams']) }}</td>
                    <td class="px-2 py-2.5 text-right text-slate">{{ $money($line['gross']) }}</td>
                    <td class="px-5 py-2.5 text-right">{{ $money($line['share']) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="text-sm">
            <tr class="border-t border-ink/15">
                <th scope="row" class="px-5 pt-3 pb-1 text-left font-normal text-slate">Part artiste (taux 20 %)</th>
                <td class="hidden sm:table-cell"></td>
                <td class="px-2 pt-3 pb-1 text-right text-slate">{{ $money($totalGross) }}</td>
                <td class="px-5 pt-3 pb-1 text-right">{{ $money($totalShare) }}</td>
            </tr>
            <tr>
                <th scope="row" colspan="2" class="px-5 py-1 text-left font-normal text-slate sm:hidden">Avance recoupée</th>
                <th scope="row" colspan="3" class="hidden px-5 py-1 text-left font-normal text-slate sm:table-cell">Avance recoupée (solde restant : 0,00 €)</th>
                <td class="px-5 py-1 text-right text-slate">− {{ $money($recouped) }}</td>
            </tr>
            <tr>
                <th scope="row" colspan="2" class="px-5 pt-2 pb-4 text-left font-semibold sm:hidden">Net à payer</th>
                <th scope="row" colspan="3" class="hidden px-5 pt-2 pb-4 text-left font-semibold sm:table-cell">Net à payer</th>
                <td class="px-5 pt-2 pb-4 text-right font-display text-xl font-bold">{{ $money($totalShare - $recouped) }}</td>
            </tr>
        </tfoot>
    </table>
</figure>

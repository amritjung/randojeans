{{--
    Usage: <x-product.washing-instructions :instructions="$product->washing_instructions" />
--}}

@props(['instructions' => []])

@php
$symbols = [
    'machine_wash_30' => [
        'label' => 'Machine wash 30°C normal',
        'svg'   => <<<'SVG'
<rect x="4" y="8" width="24" height="18" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/>
<path d="M4 14h24" stroke="currentColor" stroke-width="1.5"/>
<text x="16" y="24" text-anchor="middle" font-size="8" font-weight="500" fill="currentColor" font-family="sans-serif">30°</text>
<path d="M8 11 Q10 9 12 11 Q14 13 16 11" stroke="currentColor" stroke-width="1" fill="none"/>
SVG,
    ],
    'no_bleach' => [
        'label' => 'Do not bleach',
        'svg'   => <<<'SVG'
<path d="M7 26 L16 8 L25 26 Z" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linejoin="round"/>
<line x1="9" y1="10" x2="23" y2="24" stroke="currentColor" stroke-width="1.5"/>
<line x1="23" y1="10" x2="9" y2="24" stroke="currentColor" stroke-width="1.5"/>
SVG,
    ],
    'tumble_dry_low' => [
        'label' => 'Tumble dry low heat',
        'svg'   => <<<'SVG'
<circle cx="16" cy="17" r="10" stroke="currentColor" stroke-width="1.5" fill="none"/>
<circle cx="16" cy="17" r="5" stroke="currentColor" stroke-width="1.5" fill="none"/>
<circle cx="16" cy="9" r="1.5" fill="currentColor"/>
SVG,
    ],
    'no_steam_iron' => [
        'label' => 'Do not iron with steam',
        'svg'   => <<<'SVG'
<path d="M5 18 Q8 13 28 13 L28 20 Q20 22 5 20 Z" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linejoin="round"/>
<path d="M10 13 L8 9" stroke="currentColor" stroke-width="1.5"/>
<path d="M15 13 L13 9" stroke="currentColor" stroke-width="1.5"/>
<path d="M20 13 L18 9" stroke="currentColor" stroke-width="1.5"/>
<line x1="6" y1="11" x2="26" y2="22" stroke="currentColor" stroke-width="1.5"/>
<line x1="6" y1="22" x2="26" y2="11" stroke="currentColor" stroke-width="1.5"/>
SVG,
    ],
    'dry_clean' => [
        'label' => 'Dry clean, any solvent',
        'svg'   => <<<'SVG'
<circle cx="16" cy="16" r="11" stroke="currentColor" stroke-width="1.5" fill="none"/>
<text x="16" y="21" text-anchor="middle" font-size="11" font-weight="500" fill="currentColor" font-family="sans-serif">P</text>
SVG,
    ],
    'hang_dry' => [
        'label' => 'Hang to dry in shade',
        'svg'   => <<<'SVG'
<rect x="5" y="8" width="22" height="16" rx="1.5" stroke="currentColor" stroke-width="1.5" fill="none"/>
<line x1="5" y1="8" x2="27" y2="24" stroke="currentColor" stroke-width="1.5"/>
<line x1="5" y1="24" x2="27" y2="8" stroke="currentColor" stroke-width="1.5"/>
SVG,
    ],
    'do_not_wash' => [
        'label' => 'Do not wash',
        'svg'   => <<<'SVG'
<rect x="4" y="8" width="24" height="18" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/>
<line x1="7" y1="10" x2="25" y2="24" stroke="currentColor" stroke-width="1.5"/>
<line x1="7" y1="24" x2="25" y2="10" stroke="currentColor" stroke-width="1.5"/>
SVG,
    ],
    'iron_low' => [
        'label' => 'Iron low heat (110°C max)',
        'svg'   => <<<'SVG'
<path d="M5 18 Q8 13 28 13 L28 20 Q20 22 5 20 Z" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linejoin="round"/>
<circle cx="12" cy="23" r="1" fill="currentColor"/>
SVG,
    ],
    'iron_medium' => [
        'label' => 'Iron medium heat (150°C max)',
        'svg'   => <<<'SVG'
<path d="M5 18 Q8 13 28 13 L28 20 Q20 22 5 20 Z" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linejoin="round"/>
<circle cx="10" cy="23" r="1" fill="currentColor"/>
<circle cx="14" cy="23" r="1" fill="currentColor"/>
SVG,
    ],
    'do_not_tumble' => [
        'label' => 'Do not tumble dry',
        'svg'   => <<<'SVG'
<circle cx="16" cy="17" r="10" stroke="currentColor" stroke-width="1.5" fill="none"/>
<line x1="9" y1="10" x2="23" y2="24" stroke="currentColor" stroke-width="1.5"/>
<line x1="23" y1="10" x2="9" y2="24" stroke="currentColor" stroke-width="1.5"/>
SVG,
    ],
];
@endphp

@if(count($instructions))
<div class="care-symbols">
    <p class="care-symbols__label">Washing instructions</p>
    <div class="care-symbols__grid">
        @foreach($instructions as $item)
            @php
                $key      = $item['symbol'] ?? '';
                $symbol   = $symbols[$key] ?? null;
                $tooltip  = $item['label'] ?? ($symbol['label'] ?? '');
                $svgInner = $symbol['svg'] ?? '';
            @endphp
            @if($symbol)
            <div class="care-symbol" role="img" aria-label="{{ $tooltip }}">
                <div class="care-symbol__icon">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"
                         aria-hidden="true">
                        {!! $svgInner !!}
                    </svg>
                </div>
                <span class="care-symbol__tooltip" aria-hidden="true">{{ $tooltip }}</span>
            </div>
            @endif
        @endforeach
    </div>
</div>
@endif

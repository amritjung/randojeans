{{--
    Usage: <x-product.custom-sections :sections="$product->custom_sections" />
--}}

@props(['sections' => []])

@foreach($sections as $index => $section)
    @if(!empty($section['title']) && !empty($section['body']))
    <div class="accordion__item" x-data="{ open: false }">
        <button
            class="accordion__trigger"
            @click="open = !open"
            :aria-expanded="open"
            aria-controls="custom-section-{{ $index }}"
        >
            {{ $section['title'] }}
            <svg class="accordion__chevron" :class="{ 'rotate-180': open }"
                 width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>

        <div
            id="custom-section-{{ $index }}"
            class="accordion__body"
            x-show="open"
            x-collapse
        >
            @if(!empty($section['tag_label']))
                <span class="accordion__tag">{{ $section['tag_label'] }}</span>
            @endif

            <div class="accordion__rich-content">
                {!! $section['body'] !!}
            </div>
        </div>
    </div>
    @endif
@endforeach

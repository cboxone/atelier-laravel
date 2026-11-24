@php
    $imagePath = $service->visualImagePath();
    $icon = $service->visualIcon();
@endphp
@if($imagePath)
    <span class="service-visual service-visual-image" aria-hidden="true">
        <img src="{{ asset('storage/' . $imagePath) }}" alt="" loading="lazy" decoding="async">
    </span>
@else
    <span class="service-visual service-visual-icon" data-service-icon="{{ $icon }}" aria-hidden="true">
        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            @switch($icon)
                @case('repair')
                    <path d="M29.5 9.5a11 11 0 0 0-13.8 13.8L7.8 31.2a5 5 0 0 0 7.1 7.1l7.9-7.9a11 11 0 0 0 13.8-13.8l-6.2 6.2-6.8-1.6-1.6-6.8 7.5-4.9Z" />
                    <circle cx="11.4" cy="34.7" r="1.2" fill="currentColor" stroke="none" />
                    @break
                @case('snowflake')
                    <path d="M24 7v34M9.3 15.5l29.4 17M9.3 32.5l29.4-17M20 11l4 4 4-4M20 37l4-4 4 4M10 20l5-1 1-5M38 28l-5 1-1 5M10 28l5 1 1 5M38 20l-5-1-1-5" />
                    @break
                @case('fan')
                    <circle cx="24" cy="24" r="16" />
                    <circle cx="24" cy="24" r="3" />
                    <path d="M24 21c-1-7-1-11 2-12 3-1 5 3 4 7l-4 7M27 24c7-1 11-1 12 2 1 3-3 5-7 4l-7-4M24 27c1 7 1 11-2 12-3 1-5-3-4-7l4-7M21 24c-7 1-11 1-12-2-1-3 3-5 7-4l7 4" />
                    @break
                @case('shield')
                    <path d="M24 6 38 11v11c0 9-5.8 15.6-14 20-8.2-4.4-14-11-14-20V11l14-5Z" />
                    <path d="m17.5 23.5 4.3 4.3 9-9" />
                    @break
                @case('droplet')
                    <path d="M24 6S12 20 12 29a12 12 0 0 0 24 0C36 20 24 6 24 6Z" />
                    <path d="M20 31a5 5 0 0 0 5 5" />
                    @break
                @default
                    <path d="M29.2 8.5a12 12 0 0 0-14.7 14.7L8.7 29a6 6 0 0 0 8.5 8.5l5.8-5.8a12 12 0 0 0 14.7-14.7l-7.1 7.1-6.9-1.9-1.9-6.9 7.4-6.8Z" />
                    @break
            @endswitch
        </svg>
    </span>
@endif

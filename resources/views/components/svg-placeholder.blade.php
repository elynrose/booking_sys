@props([
    'width' => '100%',
    'height' => '200px',
    'text' => 'No Image Available',
    'type' => 'image', // image, avatar, logo, etc.
    'class' => ''
])

@php
    $iconMap = [
        'image' => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21,15 16,10 5,21"></polyline>',
        'avatar' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>',
        'logo' => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="9"></line><line x1="9" y1="12" x2="15" y2="12"></line><line x1="9" y1="15" x2="13" y2="15"></line>',
        'schedule' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>',
        'trainer' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle><path d="M12 11v4"></path><path d="M12 15h.01"></path>',
        'child' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle><path d="M12 11v4"></path><path d="M12 15h.01"></path>',
        'payment' => '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line>',
        'booking' => '<path d="M9 12l2 2 4-4"></path><path d="M21 12c-1 0-2-1-2-2s1-2 2-2 2 1 2 2-1 2-2 2z"></path><path d="M3 12c1 0 2-1 2-2s-1-2-2-2-2 1-2 2 1 2 2 2z"></path><path d="M12 3c0 1-1 2-2 2s-2-1-2-2 1-2 2-2 2 1 2 2z"></path><path d="M12 21c0-1 1-2 2-2s2 1 2 2-1 2-2 2-2-1-2-2z"></path>'
    ];
    
    $icon = $iconMap[$type] ?? $iconMap['image'];
    $textSize = strlen($text) > 20 ? '14' : '16';
@endphp

<svg width="{{ $width }}" height="{{ $height }}" xmlns="http://www.w3.org/2000/svg" class="svg-placeholder {{ $class }}">
    <defs>
        <linearGradient id="placeholderGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#f8f9fa;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#e9ecef;stop-opacity:1" />
        </linearGradient>
    </defs>
    
    <!-- Background -->
    <rect width="100%" height="100%" fill="url(#placeholderGradient)" rx="8" ry="8"/>
    
    <!-- Icon -->
    <g transform="translate(50%, 40%) scale(0.8)">
        <g fill="none" stroke="#6c757d" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            {!! $icon !!}
        </g>
    </g>
    
    <!-- Text -->
    <text x="50%" y="75%" font-family="system-ui, -apple-system, sans-serif" font-size="{{ $textSize }}" fill="#6c757d" text-anchor="middle" dominant-baseline="middle" font-weight="500">
        {{ $text }}
    </text>
</svg> 
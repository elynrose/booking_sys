/**
 * Image Placeholder Handler
 * Automatically replaces broken or empty images with SVG placeholders
 */

class ImagePlaceholderHandler {
    constructor() {
        this.placeholderTypes = {
            'avatar': 'avatar',
            'profile': 'avatar',
            'user': 'avatar',
            'trainer': 'trainer',
            'child': 'child',
            'schedule': 'schedule',
            'class': 'schedule',
            'booking': 'booking',
            'payment': 'payment',
            'logo': 'logo',
            'favicon': 'logo',
            'cover': 'image',
            'hero': 'image',
            'banner': 'image'
        };
        
        this.init();
    }
    
    init() {
        // Handle existing images
        this.handleExistingImages();
        
        // Handle dynamically loaded images
        this.observeNewImages();
        
        // Handle image error events
        this.handleImageErrors();
    }
    
    handleExistingImages() {
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            this.processImage(img);
        });
    }
    
    observeNewImages() {
        // Use MutationObserver to watch for new images
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        // Check if the added node is an image
                        if (node.tagName === 'IMG') {
                            this.processImage(node);
                        }
                        
                        // Check for images within the added node
                        const images = node.querySelectorAll ? node.querySelectorAll('img') : [];
                        images.forEach(img => this.processImage(img));
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    handleImageErrors() {
        document.addEventListener('error', (e) => {
            if (e.target.tagName === 'IMG') {
                this.replaceWithPlaceholder(e.target);
            }
        }, true);
    }
    
    processImage(img) {
        // Skip if already processed
        if (img.dataset.placeholderProcessed) {
            return;
        }
        
        // Mark as processed
        img.dataset.placeholderProcessed = 'true';
        
        // Check if image is empty or has no src
        if (!img.src || img.src === '' || img.src === window.location.href) {
            this.replaceWithPlaceholder(img);
            return;
        }
        
        // Check if image loads successfully
        if (img.complete) {
            if (img.naturalWidth === 0 || img.naturalHeight === 0) {
                this.replaceWithPlaceholder(img);
            }
        } else {
            // Image is still loading, add error handler
            img.addEventListener('error', () => {
                this.replaceWithPlaceholder(img);
            });
        }
    }
    
    replaceWithPlaceholder(img) {
        const type = this.detectImageType(img);
        const text = this.getPlaceholderText(img, type);
        const width = img.width || img.offsetWidth || '100%';
        const height = img.height || img.offsetHeight || '200px';
        const classes = img.className;
        
        // Create SVG placeholder
        const svg = this.createSVGPlaceholder(type, text, width, height, classes);
        
        // Replace the image
        img.parentNode.replaceChild(svg, img);
    }
    
    detectImageType(img) {
        // Check class names for type hints
        const className = img.className.toLowerCase();
        const altText = (img.alt || '').toLowerCase();
        const src = (img.src || '').toLowerCase();
        
        // Check for specific patterns
        for (const [pattern, type] of Object.entries(this.placeholderTypes)) {
            if (className.includes(pattern) || altText.includes(pattern) || src.includes(pattern)) {
                return type;
            }
        }
        
        // Check for common class patterns
        if (className.includes('avatar') || className.includes('profile') || className.includes('user')) {
            return 'avatar';
        }
        
        if (className.includes('logo') || className.includes('brand')) {
            return 'logo';
        }
        
        if (className.includes('schedule') || className.includes('class')) {
            return 'schedule';
        }
        
        if (className.includes('trainer')) {
            return 'trainer';
        }
        
        if (className.includes('child') || className.includes('student')) {
            return 'child';
        }
        
        // Default to image
        return 'image';
    }
    
    getPlaceholderText(img, type) {
        const altText = img.alt || '';
        
        if (altText && altText !== '') {
            return altText;
        }
        
        // Default texts based on type
        const defaultTexts = {
            'avatar': 'No Photo',
            'trainer': 'No Trainer Photo',
            'child': 'No Child Photo',
            'schedule': 'No Class Image',
            'logo': 'No Logo',
            'payment': 'No Payment Image',
            'booking': 'No Booking Image',
            'image': 'No Image Available'
        };
        
        return defaultTexts[type] || 'No Image Available';
    }
    
    createSVGPlaceholder(type, text, width, height, classes) {
        const iconMap = {
            'image': '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21,15 16,10 5,21"></polyline>',
            'avatar': '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>',
            'logo': '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="9"></line><line x1="9" y1="12" x2="15" y2="12"></line><line x1="9" y1="15" x2="13" y2="15"></line>',
            'schedule': '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>',
            'trainer': '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle><path d="M12 11v4"></path><path d="M12 15h.01"></path>',
            'child': '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle><path d="M12 11v4"></path><path d="M12 15h.01"></path>',
            'payment': '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line>',
            'booking': '<path d="M9 12l2 2 4-4"></path><path d="M21 12c-1 0-2-1-2-2s1-2 2-2 2 1 2 2-1 2-2 2z"></path><path d="M3 12c1 0 2-1 2-2s-1-2-2-2-2 1-2 2 1 2 2 2z"></path><path d="M12 3c0 1-1 2-2 2s-2-1-2-2 1-2 2-2 2 1 2 2z"></path><path d="M12 21c0-1 1-2 2-2s2 1 2 2-1 2-2 2-2-1-2-2z"></path>'
        };
        
        const icon = iconMap[type] || iconMap['image'];
        const textSize = text.length > 20 ? '14' : '16';
        const uniqueId = 'gradient-' + Math.random().toString(36).substr(2, 9);
        
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('width', width);
        svg.setAttribute('height', height);
        svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        svg.className = 'svg-placeholder ' + classes;
        
        svg.innerHTML = `
            <defs>
                <linearGradient id="${uniqueId}" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#f8f9fa;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#e9ecef;stop-opacity:1" />
                </linearGradient>
            </defs>
            
            <rect width="100%" height="100%" fill="url(#${uniqueId})" rx="8" ry="8"/>
            
            <g transform="translate(50%, 40%) scale(0.8)">
                <g fill="none" stroke="#6c757d" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    ${icon}
                </g>
            </g>
            
            <text x="50%" y="75%" font-family="system-ui, -apple-system, sans-serif" font-size="${textSize}" fill="#6c757d" text-anchor="middle" dominant-baseline="middle" font-weight="500">
                ${text}
            </text>
        `;
        
        return svg;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ImagePlaceholderHandler();
});

// Also initialize immediately if DOM is already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new ImagePlaceholderHandler();
    });
} else {
    new ImagePlaceholderHandler();
} 
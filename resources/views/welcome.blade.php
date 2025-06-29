@extends('layouts.frontend')

@section('content')
<!-- Hero Banner -->
<section class="position-relative">
    @if($siteSettings->welcome_cover_image_url)
        <!-- Custom Banner Image -->
        <div class="banner-image" style="height: 500px; background: url('{{ $siteSettings->welcome_cover_image_url }}') center/cover no-repeat; position: relative;">
            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.3);"></div>
            <div class="container position-relative h-100">
                <div class="row h-100 align-items-center">
                    <div class="col-lg-8 text-white">
                        <h1 class="display-4 fw-bold mb-4 text-shadow">{{ $siteSettings->welcome_hero_title ?? 'Welcome to ' . ($siteSettings->site_name ?? 'Fitness First') }}</h1>
                        <p class="lead mb-4 text-shadow">{{ $siteSettings->welcome_hero_description ?? 'Transform your life with our expert trainers and state-of-the-art facilities' }}</p>
                        <a href="#schedule" class="btn btn-primary btn-lg">
                            <i class="fas fa-calendar-alt me-2"></i>View Schedule
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Fallback SVG Banner -->
        <div class="banner-image" style="height: 500px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-30"></div>
            <div class="container position-relative h-100">
                <div class="row h-100 align-items-center">
                    <div class="col-lg-8 text-white">
                        <h1 class="display-4 fw-bold mb-4">{{ $siteSettings->welcome_hero_title ?? 'Welcome to ' . ($siteSettings->site_name ?? 'Fitness First') }}</h1>
                        <p class="lead mb-4">{{ $siteSettings->welcome_hero_description ?? 'Transform your life with our expert trainers and state-of-the-art facilities' }}</p>
                        <a href="#schedule" class="btn btn-primary btn-lg">
                            <i class="fas fa-calendar-alt me-2"></i>View Schedule
                        </a>
                    </div>
                    <div class="col-lg-4 text-center">
                        <!-- Fitness-themed SVG -->
                        <svg width="300" height="300" viewBox="0 0 300 300" fill="none" xmlns="http://www.w3.org/2000/svg" class="opacity-75">
                            <!-- Background Circle -->
                            <circle cx="150" cy="150" r="140" fill="rgba(255,255,255,0.1)" stroke="rgba(255,255,255,0.2)" stroke-width="2"/>
                            
                            <!-- Dumbbell -->
                            <g transform="translate(100, 120)">
                                <rect x="0" y="15" width="100" height="10" rx="5" fill="rgba(255,255,255,0.8)"/>
                                <rect x="-10" y="10" width="20" height="20" rx="10" fill="rgba(255,255,255,0.6)"/>
                                <rect x="90" y="10" width="20" height="20" rx="10" fill="rgba(255,255,255,0.6)"/>
                            </g>
                            
                            <!-- Person Silhouette -->
                            <g transform="translate(150, 180)">
                                <!-- Head -->
                                <circle cx="0" cy="-30" r="15" fill="rgba(255,255,255,0.7)"/>
                                <!-- Body -->
                                <rect x="-8" y="-15" width="16" height="25" rx="8" fill="rgba(255,255,255,0.7)"/>
                                <!-- Arms -->
                                <rect x="-25" y="-10" width="8" height="20" rx="4" fill="rgba(255,255,255,0.7)"/>
                                <rect x="17" y="-10" width="8" height="20" rx="4" fill="rgba(255,255,255,0.7)"/>
                                <!-- Legs -->
                                <rect x="-12" y="10" width="8" height="25" rx="4" fill="rgba(255,255,255,0.7)"/>
                                <rect x="4" y="10" width="8" height="25" rx="4" fill="rgba(255,255,255,0.7)"/>
                            </g>
                            
                            <!-- Decorative Elements -->
                            <circle cx="50" cy="80" r="8" fill="rgba(255,255,255,0.4)"/>
                            <circle cx="250" cy="120" r="6" fill="rgba(255,255,255,0.4)"/>
                            <circle cx="80" cy="220" r="10" fill="rgba(255,255,255,0.3)"/>
                            <circle cx="220" cy="200" r="5" fill="rgba(255,255,255,0.4)"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>

<!-- Schedule Selection -->
<section id="schedule" class="py-5 py-md-6">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 mb-3">Choose Your Workout</h2>
            <p class="lead text-muted">Select between group classes or individual training sessions</p>
        </div>

        <div class="row g-4">
            <!-- Group Classes -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm hover-card">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center mb-4">
                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 me-4" style="width: 64px; height: 64px;">
                                <i class="fas fa-users fa-2x text-white"></i>
                            </div>
                            <h3 class="pl-2 h4 mb-0">Group Classes</h3>
                        </div>
                        <p class="text-muted mb-4">Join our energetic group sessions and train with like-minded individuals. <span class="badge bg-primary">{{ $groupClasses->count() }} classes available</span></p>
                        <ul class="list-unstyled mb-4 flex-grow-1">
                            @forelse($groupClasses as $class)
                                <li class="mb-3 d-flex align-items-start">
                                    <i class="fas fa-check-circle text-primary me-3 mt-1"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold text-dark">{{ $class->title }}</div>
                                        @if($class->trainer && $class->trainer->user)
                                            <div class="text-muted small">with {{ $class->trainer->user->name }}</div>
                                        @endif
                                        @if($class->start_date)
                                            <div class="text-muted small">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                {{ $class->start_date->format('M d, Y') }}
                                            </div>
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-info-circle text-muted me-3"></i>
                                    <span class="text-muted">No group classes available at the moment</span>
                                </li>
                            @endforelse
                        </ul>
                        <a href="{{ route('frontend.schedules.index', ['type' => 'group']) }}" class="btn btn-primary w-100 mt-auto">
                            <i class="fas fa-calendar-check me-2"></i>View Group Schedule
                        </a>
                    </div>
                </div>
            </div>

            <!-- Individual Training -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm hover-card">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center mb-4">
                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 me-4" style="width: 64px; height: 64px;">
                                <i class="fas fa-user-friends fa-2x text-white"></i>
                            </div>
                            <h3 class="pl-2 h4 mb-0">Individual Training</h3>
                        </div>
                        <p class="text-muted mb-4">Get personalized attention and customized workout plans from our expert trainers. <span class="badge bg-primary">{{ $privateClasses->count() }} sessions available</span></p>
                        <ul class="list-unstyled mb-4 flex-grow-1">
                            @forelse($privateClasses as $class)
                                <li class="mb-3 d-flex align-items-start">
                                    <i class="fas fa-check-circle text-primary me-3 mt-1"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold text-dark">{{ $class->title }}</div>
                                        @if($class->trainer && $class->trainer->user)
                                            <div class="text-muted small">with {{ $class->trainer->user->name }}</div>
                                        @endif
                                        @if($class->start_date)
                                            <div class="text-muted small">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                {{ $class->start_date->format('M d, Y') }}
                                            </div>
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-info-circle text-muted me-3"></i>
                                    <span class="text-muted">No private training sessions available at the moment</span>
                                </li>
                            @endforelse
                        </ul>
                        <a href="{{ route('frontend.schedules.index', ['type' => 'private']) }}" class="btn btn-primary w-100 mt-auto">
                            <i class="fas fa-calendar-check me-2"></i>View Individual Schedule
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- QR Code Check-in -->
<section class="py-5 py-md-6 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="display-5 mb-3">Quick Check-in</h2>
                <p class="lead text-muted mb-4">Scan the QR code to check in for your session. No more waiting in line!</p>
                <div class="d-flex gap-3">
               
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div class="qr-code-container p-4 bg-white rounded-3 shadow-sm d-inline-block">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=check-in-code" alt="Check-in QR Code" class="img-fluid">
                    <p class="text-muted mt-3 mb-0">Scan to check in</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQs -->
<section class="py-5 py-md-6">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 mb-3">Frequently Asked Questions</h2>
            <p class="lead text-muted">Find answers to common questions about our services</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div id="faqAccordion">
                    <!-- FAQ Item 1 -->
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h2 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    <i class="fas fa-question-circle me-2 text-primary"></i>
                                    What are your operating hours?
                                </button>
                            </h2>
                        </div>
                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#faqAccordion">
                            <div class="card-body">
                                We are open Monday through Friday from 6:00 AM to 10:00 PM, and Saturday through Sunday from 7:00 AM to 8:00 PM.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 2 -->
                    <div class="card">
                        <div class="card-header" id="headingTwo">
                            <h2 class="mb-0">
                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <i class="fas fa-question-circle me-2 text-primary"></i>
                                    How do I book a session?
                                </button>
                            </h2>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#faqAccordion">
                            <div class="card-body">
                                You can book sessions through Greenstreet, mobile app, or by visiting our front desk. We recommend booking in advance to secure your preferred time slot.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 3 -->
                    <div class="card">
                        <div class="card-header" id="headingThree">
                            <h2 class="mb-0">
                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    <i class="fas fa-question-circle me-2 text-primary"></i>
                                    What should I bring to my first session?
                                </button>
                            </h2>
                        </div>
                        <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#faqAccordion">
                            <div class="card-body">
                                Please bring comfortable workout clothes, a water bottle, and a towel. We provide all necessary equipment for your sessions.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 4 -->
                    <div class="card">
                        <div class="card-header" id="headingFour">
                            <h2 class="mb-0">
                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    <i class="fas fa-question-circle me-2 text-primary"></i>
                                    Can I cancel or reschedule my session?
                                </button>
                            </h2>
                        </div>
                        <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#faqAccordion">
                            <div class="card-body">
                                Yes, you can cancel or reschedule your session up to 24 hours before the scheduled time without any penalty. Late cancellations may be subject to a fee.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="mb-4">Fitness First</h5>
                <p class="text-white-50">Transform your life with our expert trainers and state-of-the-art facilities.</p>
                <div class="d-flex gap-3 mt-4">
                    <a href="#" class="text-white"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="col-lg-4">
                <h5 class="mb-4">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="#schedule" class="text-white-50 text-decoration-none">
                            <i class="fas fa-chevron-right me-2"></i>Schedule
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none">
                            <i class="fas fa-chevron-right me-2"></i>Membership
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none">
                            <i class="fas fa-chevron-right me-2"></i>Trainers
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none">
                            <i class="fas fa-chevron-right me-2"></i>Contact
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h5 class="mb-4">Contact Us</h5>
                <ul class="list-unstyled text-white-50">
                    <li class="mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        123 Fitness Street, Gym City, GC 12345
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-phone-alt me-2"></i>
                        (123) 456-7890
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        info@fitnessfirst.com
                    </li>
                </ul>
            </div>
        </div>
        <hr class="my-4">
        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 text-white-50">&copy; 2024 Fitness First. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="#" class="text-white-50 text-decoration-none me-3">Privacy Policy</a>
                <a href="#" class="text-white-50 text-decoration-none">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

@push('styles')
<style>
/* Custom styles */
.banner-image {
    position: relative;
    width: 100vw;
    margin-left: calc(-50vw + 50%);
    margin-right: calc(-50vw + 50%);
}

.hover-card {
    transition: transform 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-5px);
}

.qr-code-container {
    max-width: 300px;
    margin: 0 auto;
}

/* FAQ Accordion Styles */
#faqAccordion .card {
    border: none;
    margin-bottom: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#faqAccordion .card-header {
    background-color: #fff;
    border-bottom: none;
    padding: 0;
}

#faqAccordion .btn-link {
    width: 100%;
    text-align: left;
    text-decoration: none;
    color: #333;
    padding: 1rem;
    font-weight: 500;
}

#faqAccordion .btn-link:hover,
#faqAccordion .btn-link:focus {
    text-decoration: none;
    color: var(--bs-primary);
}

#faqAccordion .btn-link[aria-expanded="true"] {
    color: var(--bs-primary);
}

#faqAccordion .card-body {
    padding: 1rem 1.5rem;
    color: #666;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .display-5 {
        font-size: 2rem;
    }
    
    .lead {
        font-size: 1.1rem;
    }
}

.text-shadow {
    text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
}
</style>
@endpush
@endsection
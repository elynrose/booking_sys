@extends('layouts.frontend')

@section('content')
<!-- Hero Banner -->
<section class="position-relative">
    <div class="banner-image" style="height: 500px; background: url('https://images.pexels.com/photos/2294361/pexels-photo-2294361.jpeg') center/cover no-repeat;">
        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50"></div>
        <div class="container position-relative h-100">
            <div class="row h-100 align-items-center">
                <div class="col-lg-8 text-white">
                    <h1 class="display-4 fw-bold mb-4">Welcome to Fitness First</h1>
                    <p class="lead mb-4">Transform your life with our expert trainers and state-of-the-art facilities</p>
                    <a href="#schedule" class="btn btn-primary btn-lg">
                        <i class="fas fa-calendar-alt me-2"></i>View Schedule
                    </a>
                </div>
            </div>
        </div>
    </div>
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
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 me-4" style="width: 64px; height: 64px;">
                                <i class="fas fa-users fa-2x text-primary"></i>
                            </div>
                            <h3 class="h4 mb-0">Group Classes</h3>
                        </div>
                        <p class="text-muted mb-4">Join our energetic group sessions and train with like-minded individuals.</p>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check-circle text-primary me-3"></i>
                                <span>Yoga & Pilates</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check-circle text-primary me-3"></i>
                                <span>HIIT Training</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check-circle text-primary me-3"></i>
                                <span>Zumba & Dance</span>
                            </li>
                        </ul>
                        <a href="{{ route('frontend.schedules.index', ['category' => 'group']) }}" class="btn btn-primary w-100">
                            <i class="fas fa-calendar-check me-2"></i>View Group Schedule
                        </a>
                    </div>
                </div>
            </div>

            <!-- Individual Training -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm hover-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 me-4" style="width: 64px; height: 64px;">
                                <i class="fas fa-user-friends fa-2x text-primary"></i>
                            </div>
                            <h3 class="h4 mb-0">Individual Training</h3>
                        </div>
                        <p class="text-muted mb-4">Get personalized attention and customized workout plans from our expert trainers.</p>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check-circle text-primary me-3"></i>
                                <span>Personal Training</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check-circle text-primary me-3"></i>
                                <span>Nutrition Planning</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check-circle text-primary me-3"></i>
                                <span>Progress Tracking</span>
                            </li>
                        </ul>
                        <a href="{{ route('frontend.schedules.index', ['category' => 'individual']) }}" class="btn btn-primary w-100">
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
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-qrcode me-2"></i>Scan QR Code
                    </a>
                    <a href="#" class="btn btn-outline-primary">
                        <i class="fas fa-history me-2"></i>View History
                    </a>
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
                                You can book sessions through our website, mobile app, or by visiting our front desk. We recommend booking in advance to secure your preferred time slot.
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
</style>
@endpush
@endsection
<?php
// Redirect to login.html
header('Location: login.html');
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Care App - Your Pet's Health Partner</title>
    <link rel="stylesheet" href="css/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0, 121, 211, 0.8), rgba(0, 95, 163, 0.9)), url('https://images.unsplash.com/photo-1587559070757-b9e2e4a5baa2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 5rem 0;
            margin-bottom: 2rem;
            border-radius: 8px;
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .service-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .vet-profile {
            transition: transform 0.3s ease;
        }
        
        .vet-profile:hover {
            transform: translateY(-5px);
        }
        
        .vet-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            margin: 0 auto 1rem;
            border: 3px solid var(--primary-color);
        }
        
        .cta-section {
            background-color: var(--primary-color);
            color: white;
            padding: 3rem 0;
            border-radius: 8px;
            margin: 2rem 0;
        }
        
        .testimonial-card {
            border-left: 4px solid var(--primary-color);
            padding-left: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .footer {
            background-color: var(--secondary-color);
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }
        
        .footer a {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .footer a:hover {
            color: white;
            text-decoration: none;
        }
        
        .navbar {
            padding: 1rem 0;
        }
        
        .stat-card {
            text-align: center;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            background-color: var(--card-color);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-paw me-2"></i>Pet Care App</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#vets">Our Vets</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#testimonials">Testimonials</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a href="login.html" class="btn btn-primary">Login</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="php/api/register.php" class="btn btn-outline-primary">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="hero-section text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">Your Pet's Health is Our Priority</h1>
                    <p class="lead mb-5">We provide comprehensive healthcare services for your beloved pets through our network of experienced veterinarians in Bangladesh.</p>
                    <div>
                        <a href="login.html" class="btn btn-light btn-lg me-2">Book an Appointment</a>
                        <a href="php/api/register.php" class="btn btn-outline-light btn-lg">Join Us Today</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row text-center mb-5">
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="fas fa-user-md feature-icon"></i>
                    <h2 class="fw-bold">20+</h2>
                    <p class="text-muted">Expert Veterinarians</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="fas fa-hospital feature-icon"></i>
                    <h2 class="fw-bold">15+</h2>
                    <p class="text-muted">Partner Clinics</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="fas fa-paw feature-icon"></i>
                    <h2 class="fw-bold">10,000+</h2>
                    <p class="text-muted">Pets Cared For</p>
                </div>
            </div>
        </div>

        <section id="services" class="mb-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Our Services</h2>
                <p class="text-muted">Comprehensive care for your furry friends</p>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-stethoscope feature-icon"></i>
                            <h4>General Check-ups</h4>
                            <p>Regular health examinations to keep your pet in optimal condition.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-syringe feature-icon"></i>
                            <h4>Vaccinations</h4>
                            <p>Essential vaccines to protect your pet from common diseases.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-capsules feature-icon"></i>
                            <h4>Medications</h4>
                            <p>Prescription and over-the-counter medications for your pet's needs.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-heartbeat feature-icon"></i>
                            <h4>Emergency Care</h4>
                            <p>Prompt medical attention for urgent pet health situations.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-tooth feature-icon"></i>
                            <h4>Dental Care</h4>
                            <p>Professional teeth cleaning and oral health maintenance.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-cut feature-icon"></i>
                            <h4>Grooming</h4>
                            <p>Keep your pet looking their best with professional grooming services.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="cta-section text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="fw-bold mb-4">Ready to give your pet the care they deserve?</h2>
                    <p class="lead mb-4">Sign up today and get a free first consultation with one of our veterinarians.</p>
                    <a href="php/api/register.php" class="btn btn-light btn-lg">Register Now</a>
                </div>
            </div>
        </div>

        <section id="vets" class="mb-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Meet Our Veterinarians</h2>
                <p class="text-muted">Experienced professionals who love animals</p>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card vet-profile h-100">
                        <div class="card-body text-center">
                            <div class="vet-image" style="background-color: #e9ecef;"></div>
                            <h4>Dr. Farida Rahman</h4>
                            <p class="text-muted">Small Animal Specialist</p>
                            <p>10+ years of experience in treating cats, dogs, and other small pets.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card vet-profile h-100">
                        <div class="card-body text-center">
                            <div class="vet-image" style="background-color: #e9ecef;"></div>
                            <h4>Dr. Kamal Hossain</h4>
                            <p class="text-muted">Surgery Specialist</p>
                            <p>Expert in pet surgery with specialized training in emergency treatments.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card vet-profile h-100">
                        <div class="card-body text-center">
                            <div class="vet-image" style="background-color: #e9ecef;"></div>
                            <h4>Dr. Nusrat Jahan</h4>
                            <p class="text-muted">Nutritionist</p>
                            <p>Specialized in pet nutrition and dietary management for optimal health.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="testimonials" class="mb-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">What Pet Owners Say</h2>
                <p class="text-muted">Testimonials from our satisfied clients</p>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="testimonial-card">
                        <p class="lead">"The care my dog received was exceptional. The vets were thorough and took their time explaining everything to me."</p>
                        <p class="fw-bold mb-0">Rahim Ahmed</p>
                        <small class="text-muted">Owner of Max, Golden Retriever</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="testimonial-card">
                        <p class="lead">"I love how easy it is to book appointments and manage my pet's health records through the app. Highly recommend!"</p>
                        <p class="fw-bold mb-0">Samia Khan</p>
                        <small class="text-muted">Owner of Luna, Persian Cat</small>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-3">Pet Care App</h5>
                    <p>Your trusted partner for pet healthcare in Bangladesh. We provide comprehensive services through our network of experienced veterinarians.</p>
                </div>
                <div class="col-md-3 mb-4 mb-md-0">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#services">Our Services</a></li>
                        <li><a href="#vets">Our Vets</a></li>
                        <li><a href="#testimonials">Testimonials</a></li>
                        <li><a href="login.html">Login</a></li>
                        <li><a href="php/api/register.php">Register</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4 mb-md-0">
                    <h5 class="mb-3">Working Hours</h5>
                    <ul class="list-unstyled">
                        <li>Monday - Friday: 9am - 7pm</li>
                        <li>Saturday: 10am - 5pm</li>
                        <li>Sunday: 10am - 2pm</li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h5 class="mb-3">Connect</h5>
                    <div class="d-flex">
                        <a href="#" class="me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <p class="mb-0">© <?php echo date('Y'); ?> Pet Care App. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery/jquery.min.js"></script>
    <script src="js/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
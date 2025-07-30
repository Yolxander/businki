<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bobbi - AI-Powered Project Management for Freelancers</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400..900&family=Rationale&family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&family=Turret+Road:wght@200;300;400;500;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/framer-motion@10.16.4/dist/framer-motion.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .orbitron { font-family: 'Orbitron', sans-serif; }
        .frosted-glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .circular-text {
            transform: rotate(-90deg);
        }
        .hero-image {
            background-image: url('/bobbi-dashboard.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* Animation classes */
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease-out;
        }

        .fade-in-left {
            opacity: 0;
            transform: translateX(-30px);
            transition: all 0.8s ease-out;
        }

        .fade-in-right {
            opacity: 0;
            transform: translateX(30px);
            transition: all 0.8s ease-out;
        }

        .scale-in {
            opacity: 0;
            transform: scale(0.9);
            transition: all 0.6s ease-out;
        }

        .slide-in-up {
            opacity: 0;
            transform: translateY(50px);
            transition: all 1s ease-out;
        }

        /* Animation states */
        .fade-in-up.animate,
        .fade-in-left.animate,
        .fade-in-right.animate,
        .scale-in.animate,
        .slide-in-up.animate {
            opacity: 1;
            transform: translateY(0) translateX(0) scale(1);
        }

        /* Stagger animations */
        .stagger-1 { transition-delay: 0.1s; }
        .stagger-2 { transition-delay: 0.2s; }
        .stagger-3 { transition-delay: 0.3s; }
        .stagger-4 { transition-delay: 0.4s; }
        .stagger-5 { transition-delay: 0.5s; }
        .stagger-6 { transition-delay: 0.6s; }
        .stagger-7 { transition-delay: 0.7s; }
        .stagger-8 { transition-delay: 0.8s; }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Section navigation styles */
        .section-nav {
            position: fixed;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .section-nav-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .section-nav-dot.active {
            background: #d1ff75;
            transform: scale(1.2);
        }

        .section-nav-dot:hover {
            background: rgba(209, 255, 117, 0.6);
            transform: scale(1.1);
        }
    </style>
</head>
<body class="text-gray-900 min-h-screen px-6" style="background-color: oklch(0.205 0 0);">
    <!-- Section Navigation Dots -->
    <div class="section-nav">
        <div class="section-nav-dot" data-section="hero"></div>
        <div class="section-nav-dot" data-section="values"></div>
        <div class="section-nav-dot" data-section="features"></div>
        <div class="section-nav-dot" data-section="stats"></div>
        <div class="section-nav-dot" data-section="about"></div>
        <div class="section-nav-dot" data-section="cta"></div>
    </div>

    <!-- Header -->
    <header class="w-full py-3 px-2 relative z-50">
        <nav class="w-full mx-auto flex items-center justify-between">
            <div class="text-2xl font-bold text-white orbitron fade-in-left animate">Bobbi</div>
            <div class="flex space-x-8">
                <a href="#values" class="text-white hover:text-gray-300 transition orbitron fade-in-right stagger-1 animate">Our Mission</a>
                <a href="#features" class="text-white hover:text-gray-300 transition orbitron fade-in-right stagger-2 animate">Features</a>
                <a href="#about" class="text-white hover:text-gray-300 transition orbitron fade-in-right stagger-3 animate">About</a>
                <a href="/login" class="text-white hover:text-gray-300 transition orbitron fade-in-right stagger-4 animate">Login</a>
            </div>
        </nav>
    </header>

    <!-- Import all sections -->
    @include('sections.hero')
    @include('sections.values')
    @include('sections.features')
    @include('sections.stats')
    @include('sections.about')
    @include('sections.cta')
    @include('sections.footer')

    <script>
        // Enhanced Intersection Observer for scroll-triggered animations
        const observerOptions = {
            threshold: 0.2,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Add animate class to trigger animations
                    const animatedElements = entry.target.querySelectorAll('.fade-in-up, .fade-in-left, .fade-in-right, .scale-in, .slide-in-up');
                    animatedElements.forEach(el => {
                        el.classList.add('animate');
                    });
                }
            });
        }, observerOptions);

        // Section navigation functionality
        const sections = document.querySelectorAll('section[id]');
        const navDots = document.querySelectorAll('.section-nav-dot');

        // Update active nav dot based on scroll position
        function updateActiveNav() {
            const scrollPosition = window.scrollY + window.innerHeight / 2;

            sections.forEach((section, index) => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;

                if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                    navDots.forEach(dot => dot.classList.remove('active'));
                    if (navDots[index]) {
                        navDots[index].classList.add('active');
                    }
                }
            });
        }

        // Smooth scroll to section when nav dot is clicked
        navDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                const targetSection = sections[index];
                if (targetSection) {
                    targetSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Initialize animations and observers
        document.addEventListener('DOMContentLoaded', () => {
            // Observe all sections for animation triggers
            sections.forEach(section => {
                observer.observe(section);
            });

            // Add scroll event listener for nav updates
            window.addEventListener('scroll', updateActiveNav);

            // Initial nav update
            updateActiveNav();

            // Add hover animations for interactive elements
            const interactiveElements = document.querySelectorAll('button, a, .frosted-glass');
            interactiveElements.forEach(el => {
                el.addEventListener('mouseenter', () => {
                    el.style.transform = 'translateY(-2px)';
                    el.style.transition = 'transform 0.2s ease-out';
                });

                el.addEventListener('mouseleave', () => {
                    el.style.transform = 'translateY(0)';
                });
            });

            // Smooth scroll for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Trigger initial animations for hero section
            const heroSection = document.getElementById('hero');
            if (heroSection) {
                const heroElements = heroSection.querySelectorAll('.fade-in-up, .fade-in-left, .fade-in-right, .scale-in, .slide-in-up');
                heroElements.forEach(el => {
                    el.classList.add('animate');
                });
            }
        });

        // Keyboard navigation support
        document.addEventListener('keydown', (e) => {
            const activeDot = document.querySelector('.section-nav-dot.active');
            const currentIndex = Array.from(navDots).indexOf(activeDot);

            if (e.key === 'ArrowUp' && currentIndex > 0) {
                navDots[currentIndex - 1].click();
            } else if (e.key === 'ArrowDown' && currentIndex < navDots.length - 1) {
                navDots[currentIndex + 1].click();
            }
        });
    </script>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bobbi</title>
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
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .fade-in-left {
            opacity: 0;
            transform: translateX(-30px);
            animation: fadeInLeft 0.8s ease-out forwards;
        }

        .fade-in-right {
            opacity: 0;
            transform: translateX(30px);
            animation: fadeInRight 0.8s ease-out forwards;
        }

        .scale-in {
            opacity: 0;
            transform: scale(0.9);
            animation: scaleIn 0.6s ease-out forwards;
        }

        .slide-in-up {
            opacity: 0;
            transform: translateY(50px);
            animation: slideInUp 1s ease-out forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes scaleIn {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes slideInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Stagger animations */
        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }
        .stagger-4 { animation-delay: 0.4s; }
        .stagger-5 { animation-delay: 0.5s; }
        .stagger-6 { animation-delay: 0.6s; }
    </style>
</head>
<body class="text-gray-900 min-h-screen px-6" style="background-color: oklch(0.205 0 0);">
    <!-- Header -->
    <header class="w-full py-3 px-2">
        <nav class="w-full mx-auto flex items-center justify-between">
            <div class="text-2xl font-bold text-white orbitron fade-in-left">Bobbi</div>
            <div class="flex space-x-8">
                <a href="#" class="text-white hover:text-gray-300 transition orbitron fade-in-right stagger-1">About Us</a>
                <a href="#" class="text-white hover:text-gray-300 transition orbitron fade-in-right stagger-2">Pricing</a>
                                    <a href="/login" class="text-white hover:text-gray-300 transition orbitron fade-in-right stagger-3">Login</a>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="relative w-full h-[90vh] mx-auto px-8 mb-24">
        <!-- Background Image Container -->
        <div class="hero-image absolute inset-0 rounded-2xl overflow-hidden shadow-md shadow-md shadow-[#D1FF75]/50">
            <!-- Overlay for better text readability -->
            <div class="absolute inset-0 bg-black bg-opacity-40"></div>

            <!-- Large "Title" Text Overlay -->
            <div class="absolute bottom-80 left-[5%] inset-0 flex items-center justify-center w-[800px]">
                <h1 class="text-xl md:text-8xl font-bold text-white tracking-wider slide-in-up">
                    The Future of Freelancing
                </h1>
            </div>

            <!-- Left Information Box -->
            <div class="absolute bottom-10 right-96 w-96 frosted-glass rounded-2xl p-8 fade-in-right stagger-2">
                <p class="text-white text-lg leading-relaxed mb-6">
                    Crafting spaces that harmonize modern aesthetics with timeless elegance, our contemporary interior designs breathe life into every room, redefining the essence of chic living.
                </p>
                <button class="bg-[#d1ff75] text-black px-6 py-3 rounded-lg font-medium hover:bg-[#d1ff75]/50 transition orbitron">
                    View More →
                </button>
            </div>

            <!-- Left Information Box -->
            <div class="absolute bottom-20 right-10 w-80 frosted-glass rounded-2xl p-8 fade-in-right stagger-3">
                <p class="text-white text-lg leading-relaxed mb-6">
                    Crafting spaces that harmonize modern aesthetics with timeless elegance, our contemporary interior designs breathe life into every room, redefining the essence of chic living.
                </p>
                <button class="bg-[#d1ff75] text-black px-6 py-3 rounded-lg font-medium hover:bg-[#d1ff75]/50 transition orbitron">
                    View More →
                </button>
            </div>

{{--            <!-- Right Circular Text -->--}}
{{--            <div class="absolute right-20 bottom-40">--}}
{{--                <div class="circular-text text-white font-medium text-sm tracking-wider orbitron">--}}
{{--                    Modern • Minimalist • Modern • Minimalist--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>
    </section>

    <!-- Values Section -->
    <section class="min-h-screen py-12 lg:py-24" style="background-color: oklch(0.205 0 0);">
        <div class="mx-auto w-full px-2">
             <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
                <!-- Left Content -->
                <div class="lg:w-1/2 space-y-8 fade-in-left">
                    <!-- Process Steps -->
                    <div class="space-y-6">
                        <div class="flex items-center space-x-4">
                            <span class="text-2xl font-bold text-white orbitron">01</span>
                            <div class="h-px bg-gray-600 flex-1"></div>
                            <span class="text-gray-400 text-sm orbitron">02 03 04</span>
                        </div>

                        <p class="text-gray-300 text-lg leading-relaxed max-w-md">
                            We create intuitive and visually engaging digital experiences that enhance user interaction and
                            satisfaction.
                        </p>
                    </div>

                    <!-- Main Heading -->
                    <div class="space-y-12">
                        <div class="text-sm text-gray-400 uppercase tracking-wider orbitron stagger-1">/About</div>

                        <h1 class="text-2xl lg:text-5xl xl:text-6xl font-bold text-white leading-tight stagger-2">
                            Empowering
                            <br />
                            Visionary Founders
                            <br />
                            From Day One
                        </h1>

                                                        <a href="/login" class="bg-transparent text-white px-0 py-2 rounded-full text-lg font-medium group transition-all orbitron inline-flex items-center hover:underline stagger-3" style="text-decoration-color: #d1ff75;">
                                    Start Today
                                    <svg class="ml-2 h-5 w-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </a>
                    </div>
                </div>

                <!-- Right Content - Value Cards -->
                <div class="lg:w-1/2 flex gap-4 h-full items-start fade-in-right">
                    <!-- Vision Provider Card -->
                    <div class="relative rounded-3xl overflow-hidden h-[570px] flex-1 inset-0 bg-black bg-opacity-40 shadow-md shadow-[#D1FF75]/50 scale-in stagger-1">
                        <div class="absolute inset-0">
                            <img src="/bobbi-dashboard.png"
                                 alt="Abstract curved design"
                                 class="w-full h-full object-cover opacity-50">
                        </div>

                        <div class="relative z-10 p-6 h-full flex flex-col">
                            <!-- Top Section -->
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <div class="text-white/60 text-xs font-medium mb-1">Our</div>
                                    <div class="text-white/60 text-xs font-medium">Values</div>
                                </div>
                                <div class="text-white text-xl font-light italic orbitron">
                                    Vision-<span class="font-normal">provider</span>
                                </div>
                            </div>

                            <!-- Bottom Section -->
                            <div class="mt-auto">
                                <h3 class="text-white text-lg lg:text-xl font-bold mb-3 leading-tight">
                                    Beautiful Vision Oriented
                                </h3>
                                <p class="text-white/80 text-xs mb-4 leading-relaxed">
                                    from logos to brand guidelines, we shape identities that leave lasting impressions.
                                </p>

                                <button class="bg-white/15 hover:bg-white/25 text-white border border-white/20 rounded-full px-4 py-2 text-sm backdrop-blur-sm group transition-all">
                                    Learn More
                                    <svg class="ml-2 h-3 w-3 inline-block group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </button>

                                <!-- Large Number -->
                                <div class="absolute bottom-4 right-4 text-6xl font-bold text-white/10 leading-none">01</div>
                            </div>
                        </div>
                    </div>

                    <!-- Second Card and Navigation Container -->
                    <div class="flex flex-col gap-6 flex-1">
                        <!-- Nextgen Styled Card -->
                        <div class="relative rounded-3xl overflow-hidden  h-[500px] inset-0 bg-black bg-opacity-40 shadow-md shadow-[#D1FF75]/50 scale-in stagger-2">
                            <div class="absolute inset-0">
                                <img src="/bobbi-dashboard.png"
                                     alt="Modern wood abstract design"
                                     class="w-full h-full object-cover  opacity-50">
                            </div>

                            <div class="relative z-10 p-6 h-full flex flex-col">
                                <!-- Top Section -->
                                <div class="flex justify-between items-start mb-6">
                                    <div>
                                        <div class="text-white/60 text-xs font-medium mb-1">Our</div>
                                        <div class="text-white/60 text-xs font-medium">Values</div>
                                    </div>
                                    <div class="text-white text-xl font-light italic orbitron">
                                        Nextgen-<span class="font-normal">styled</span>
                                    </div>
                                </div>

                                <!-- Bottom Section -->
                                <div class="mt-auto">
                                    <h3 class="text-white text-lg lg:text-xl font-bold mb-3 leading-tight">Modern Wood Provided</h3>
                                    <p class="text-white/80 text-xs mb-4 leading-relaxed">
                                        from logos to brand guidelines, we shape identities that leave lasting impressions.
                                    </p>

                                    <button class="bg-white/15 hover:bg-white/25 text-white border border-white/20 rounded-full px-4 py-2 text-sm backdrop-blur-sm group transition-all">
                                        Learn More
                                        <svg class="ml-2 h-3 w-3 inline-block group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    </button>

                                    <!-- Large Number -->
                                    <div class="absolute bottom-4 right-4 text-6xl font-bold text-white/10 leading-none">02</div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation -->
                        <div class="flex justify-end space-x-4 fade-in-up stagger-3">
                            <button class="rounded-full bg-white border border-gray-200 hover:bg-gray-50 p-3 transition-all">
                                <svg class="h-5 w-5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <button class="rounded-full bg-[#d1ff75] text-black border border-gray-900 hover:bg-gray-800 p-3 transition-all">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </section>

        <!-- Features Section -->
    <section class="min-h-screen py-12 lg:py-24" style="background-color: oklch(0.205 0 0);">
        <div class="mx-auto w-full px-2">
                        <!-- Header -->
            <div class="text-center mb-16 fade-in-up">
                <div class="text-sm text-gray-400 uppercase tracking-wider mb-8 orbitron stagger-1">/Features</div>

                <h2 class="text-4xl lg:text-5xl xl:text-6xl font-bold leading-tight max-w-6xl mx-auto stagger-2">
                    <span class="text-white">We are a team of visionary designers, strategists, and innovators </span>
                    <span class="text-gray-300">
                        dedicated to crafting exceptional digital experiences. Our mission is to bridge creativity with
                        functionality,
                    </span>
                </h2>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                <!-- Video Testimonial Card -->
                <div class="lg:col-span-8 fade-in-left stagger-3">
                    <div class="relative rounded-3xl overflow-hidden h-96 lg:h-[500px] inset-0 bg-black bg-opacity-40 shadow-md shadow-[#D1FF75]/50 scale-in">
                        <div class="absolute inset-0">
                            <img src="/bobbi-dashboard.png"
                                 alt="Abstract flowing wood design"
                                 class="w-full h-full object-cover opacity-50">
                        </div>

                        <!-- Play Button -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <button class="w-20 h-20 rounded-full bg-white/90 hover:bg-white text-gray-900 shadow-lg flex items-center justify-center transition-all hover:scale-110 scale-in stagger-4">
                                <svg class="h-8 w-8 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Bottom Content -->
                        <div class="absolute bottom-0 left-0 right-0 p-8 fade-in-up stagger-5">
                            <div class="flex items-end justify-between">
                                <div>
                                    <div class="text-white text-lg font-medium mb-2">Ryhan</div>
                                    <div class="bg-black/50 backdrop-blur-sm rounded-full px-4 py-2 inline-block">
                                        <span class="text-white text-sm">Can't imagine without tham</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Audio Testimonial Card -->
                <div class="lg:col-span-4 fade-in-right stagger-4">
                    <div class="rounded-3xl p-8 h-96 lg:h-[500px] flex flex-col inset-0 bg-black bg-opacity-40 shadow-md shadow-[#D1FF75]/50 scale-in">
                        <!-- Header -->
                        <div class="flex justify-between items-start mb-8 fade-in-up stagger-5">
                            <div class="text-sm text-white/60 font-medium">Woodmetal</div>
                            <div class="text-sm text-white/60 font-medium underline cursor-pointer hover:text-white transition">Insights</div>
                        </div>

                        <!-- Title -->
                        <h3 class="text-3xl lg:text-4xl font-bold text-white mb-8 fade-in-up stagger-6">Ryan Said</h3>

                        <!-- Audio Waveform -->
                        <div class="flex items-center space-x-1 mb-8">
                            @for ($i = 0; $i < 40; $i++)
                                <div class="bg-white/40 rounded-full animate-pulse scale-in"
                                     style="width: 3px; height: {{ rand(10, 40) }}px; animation-delay: {{ $i * 0.05 + 0.8 }}s;"></div>
                            @endfor
                        </div>

                        <!-- Content -->
                        <div class="mt-auto fade-in-up stagger-7">
                            <p class="text-white/80 text-sm mb-6 leading-relaxed">
                                Global best agency Seative Digital recommended...
                            </p>

                            <!-- Profile -->
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full bg-white/20 overflow-hidden backdrop-blur-sm scale-in stagger-8">
                                    <img src="https://source.unsplash.com/48x48/?portrait,man"
                                         alt="Ryan profile"
                                         class="w-full h-full object-cover">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </section>

    <!-- Stats Section -->
    <section class="min-h-screen py-12 lg:py-24" style="background-color: oklch(0.205 0 0);">
        <div class="mx-auto w-full px-2">
            <div class="max-w-7xl mx-auto">
                <div class="space-y-6">
                    <!-- Stat Card 1 -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-3xl p-8 lg:p-12 shadow-md shadow-[#D1FF75]/20 border border-white/10 fade-in-up stagger-1">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                            <!-- Left Content -->
                            <div class="flex-1">
                                <p class="text-white/80 text-lg lg:text-xl leading-relaxed max-w-md">
                                    By making your digital marketing agency website look professional, you can increase your
                                </p>
                            </div>

                            <!-- Right Stats -->
                            <div class="text-right lg:text-left">
                                <div class="text-2xl lg:text-2xl xl:text-4xl font-bold text-[#D1FF75] leading-none mb-2 orbitron">
                                    12+
                                </div>
                                <div class="text-white/80 text-lg font-medium">Years of Services</div>
                            </div>
                        </div>
                    </div>

                    <!-- Stat Card 2 -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-3xl p-8 lg:p-12 shadow-md shadow-[#D1FF75]/20 border border-white/10 fade-in-up stagger-2">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                            <!-- Left Content -->
                            <div class="flex-1">
                                <p class="text-white/80 text-lg lg:text-xl leading-relaxed max-w-md">
                                    By making your digital marketing agency website look professional, you can increase your
                                </p>
                            </div>

                            <!-- Right Stats -->
                            <div class="text-right lg:text-left">
                                <div class="text-2xl lg:text-2xl xl:text-2xl font-bold text-[#D1FF75] leading-none mb-2 orbitron">
                                    80+
                                </div>
                                <div class="text-white/80 text-lg font-medium">Team Members</div>
                            </div>
                        </div>
                    </div>

                    <!-- Stat Card 3 -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-3xl p-8 lg:p-12 shadow-md shadow-[#D1FF75]/20 border border-white/10 fade-in-up stagger-3">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                            <!-- Left Content -->
                            <div class="flex-1">
                                <p class="text-white/80 text-lg lg:text-xl leading-relaxed max-w-md">
                                    By making your digital marketing agency website look professional, you can increase your
                                </p>
                            </div>

                            <!-- Right Stats -->
                            <div class="text-right lg:text-left">
                                <div class="text-2xl lg:text-2xl xl:text-2xl font-bold text-[#D1FF75] leading-none mb-2 orbitron">
                                    3K+
                                </div>
                                <div class="text-white/80 text-lg font-medium">Happy Clients</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="min-h-screen py-12 lg:py-24" style="background-color: oklch(0.205 0 0);">
        <div class="mx-auto w-full px-2">
            <div class="max-w-8xl mx-auto">
                <div class="flex flex-col lg:flex-row gap-2 lg:gap-6 min-h-[600px]">
                    <!-- Left Content -->
                    <div class="lg:w-1/2 bg-white/10 backdrop-blur-sm rounded-3xl p-8 lg:p-12 flex flex-col justify-between border border-white/10 shadow-md shadow-[#D1FF75]/20 fade-in-left stagger-1">
                        <!-- Top Section -->
                        <div class="space-y-8">
                            <!-- Process Steps -->
                            <div class="space-y-6">
                                <div class="flex items-center space-x-4">
                                    <span class="text-2xl font-bold text-white orbitron">01</span>
                                    <div class="h-px bg-gray-600 flex-1"></div>
                                    <span class="text-gray-400 text-sm orbitron">02 03 04</span>
                                </div>

                                <p class="text-white/80 text-lg leading-relaxed">
                                    We create intuitive and visually engaging digital experiences that enhance user interaction and
                                    satisfaction.
                                </p>
                            </div>

                            <!-- Main Content -->
                            <div class="space-y-6">
                                <div class="text-sm text-gray-400 uppercase tracking-wider orbitron">/About</div>

                                <h2 class="text-4xl lg:text-5xl xl:text-6xl font-bold text-white leading-tight">
                                    we specialize in designing and crafting high-quality wooden products that blend aesthetics.
                                </h2>
                            </div>
                        </div>

                        <!-- Navigation -->
                        <div class="flex space-x-4 mt-8 fade-in-up stagger-2">
                            <button class="rounded-full bg-white/20 border border-white/30 hover:bg-white/30 p-3 transition-all">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <button class="rounded-full bg-[#D1FF75] text-black border border-[#D1FF75] hover:bg-[#D1FF75]/80 p-3 transition-all">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Right Visual Section -->
                    <div class="lg:w-1/2 fade-in-right stagger-3">
                        <div class="rounded-3xl overflow-hidden h-full min-h-[600px] relative inset-0 bg-black bg-opacity-40 shadow-md shadow-[#D1FF75]/50">
                            <!-- Main Image -->
                            <div class="relative h-full">
                                <img src="/bobbi-dashboard.png"
                                     alt="Curved wooden sculpture with natural grain and moss"
                                     class="w-full h-full object-cover opacity-50">

                                <!-- Bottom Right Info Card -->
                                <div class="absolute bottom-8 right-8 scale-in stagger-4">
                                    <div class="bg-white/15 backdrop-blur-md rounded-3xl p-6 flex items-center space-x-5 shadow-xl shadow-[#D1FF75]/20 border border-white/20 hover:bg-white/20 transition-all duration-300 group">
                                        <!-- Small Thumbnail -->
                                        <div class="w-16 h-16 rounded-2xl overflow-hidden bg-white/20 ring-2 ring-white/20 group-hover:ring-[#D1FF75]/50 transition-all duration-300">
                                            <img src="https://source.unsplash.com/64x64/?wood,texture,modern"
                                                 alt="Wooden texture detail"
                                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                        </div>

                                        <!-- Text Content -->
                                        <div class="space-y-1">
                                            <div class="text-xs text-white/60 font-medium orbitron tracking-wider">/01</div>
                                            <div class="text-base font-bold text-white leading-tight">Designing in</div>
                                            <div class="text-base font-bold text-[#D1FF75] leading-tight">Solution</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Hero CTA Section -->
    <section class="relative min-h-screen mx-2 lg:mx-4 pt-24" style="background-color: oklch(0.205 0 0);">
        <div class="rounded-3xl overflow-hidden h-full min-h-screen relative inset-0 bg-black bg-opacity-40 shadow-md shadow-[#D1FF75]/50">
            <!-- Background Image -->
            <div class="absolute inset-0">
                <img src="/bobbi-dashboard.png"
                     alt="3D rendered scene with wooden elements and architectural structures"
                     class="w-full h-full object-cover opacity-50">
                <div class="absolute inset-0 bg-black/40"></div>
            </div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center items-center min-h-screen p-8 lg:p-12">
                <!-- Left Side Card -->
                <div class="absolute left-8 lg:left-16 bottom-20 transform -translate-y-1/2 fade-in-left stagger-1">
                    <div class="bg-black/60 backdrop-blur-sm rounded-2xl p-4 max-w-xs border border-white/10">
                        <p class="text-white text-sm mb-4 leading-relaxed">
                            Evolving ideas into their final form through the development
                        </p>
                        <div class="flex items-center space-x-2">
                            <!-- Profile Images -->
                            <div class="w-8 h-8 rounded-full bg-gray-400 overflow-hidden">
                                <img src="https://source.unsplash.com/32x32/?portrait,person"
                                     alt="Team member"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="w-8 h-8 rounded-full bg-gray-400 overflow-hidden">
                                <img src="https://source.unsplash.com/32x32/?portrait,woman"
                                     alt="Team member"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="w-8 h-8 rounded-full bg-gray-400 overflow-hidden">
                                <img src="https://source.unsplash.com/32x32/?portrait,man"
                                     alt="Team member"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="text-center space-y-8 fade-in-up stagger-2">
                    <h1 class="text-4xl lg:text-6xl xl:text-7xl font-bold text-white leading-tight max-w-4xl ">
                        Digital products
                        <br />
                        creatives & immersive
                    </h1>

                    <div class="relative inline-block scale-in stagger-3">
                        <a href="/login" class="bg-gradient-to-r from-white to-gray-50 hover:from-gray-50 hover:to-white text-gray-900 px-4 py-2 rounded-full text-xl font-bold group relative transition-all duration-500 hover:scale-110 shadow-2xl hover:shadow-[#D1FF75]/30 border-2 border-white/20 hover:border-[#D1FF75]/50 overflow-hidden inline-block">
                            <!-- Background glow effect -->
                            <div class="absolute inset-0 bg-gradient-to-r from-[#D1FF75]/0 via-[#D1FF75]/10 to-[#D1FF75]/0 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                            <!-- Content -->
                            <div class="relative flex items-center space-x-4">
                                <span class="relative z-10 group-hover:text-gray-800 transition-colors duration-300 orbitron">Start Today</span>
                                <div class="relative z-10 w-14 h-14 bg-gradient-to-br from-gray-900 to-gray-800 rounded-full flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-110">
                                    <svg class="h-6 w-6 text-white group-hover:rotate-12 transition-transform duration-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Ripple effect -->
                            <div class="absolute inset-0 rounded-full bg-white/20 scale-0 group-hover:scale-100 transition-transform duration-700 ease-out"></div>
                        </a>

                        <!-- Enhanced Cursor Pointer -->
                        <div class="absolute -bottom-6 -right-6 w-10 h-10 pointer-events-none">
                            <div class="relative w-full h-full animate-bounce">
                                <!-- Outer glow -->
                                <div class="absolute inset-0 bg-[#D1FF75]/30 rounded-full blur-sm animate-pulse"></div>
                                <!-- Main cursor -->
                                <svg viewBox="0 0 24 24" fill="white" class="w-full h-full relative z-10 drop-shadow-lg">
                                    <path d="M8.5 2.5L19.5 13.5L13 14L11 20L8.5 2.5Z" />
                                </svg>
                                <!-- Inner highlight -->
                                <div class="absolute inset-1 bg-white/20 rounded-full blur-sm"></div>
                            </div>
                        </div>

                        <!-- Floating particles effect -->
                        <div class="absolute -top-4 -left-4 w-3 h-3 bg-[#D1FF75]/60 rounded-full animate-ping"></div>
                        <div class="absolute -bottom-2 -left-8 w-2 h-2 bg-white/40 rounded-full animate-pulse" style="animation-delay: 0.5s;"></div>
                        <div class="absolute top-8 -right-12 w-2 h-2 bg-[#D1FF75]/40 rounded-full animate-pulse" style="animation-delay: 1s;"></div>
                    </div>
                </div>
                        </div>
        </div>
    </section>

    <!-- Footer Section -->
    <section class="py-8" style="background-color: oklch(0.205 0 0);">
        <div class="mx-auto max-w-7xl px-8 lg:px-6">
            <div class="flex flex-col lg:flex-row justify-between items-center space-y-4 lg:space-y-0 fade-in-up">
                <p class="text-white/80 text-sm">Copyright 2025, All Rights Reserved</p>

                <div class="flex items-center space-x-6">
                    <button class="text-white/80 hover:text-white hover:bg-white/10 p-2 rounded-full transition-all duration-300">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                    </button>
                    <button class="text-white/80 hover:text-white hover:bg-white/10 p-2 rounded-full transition-all duration-300">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 2c5.514 0 10 4.486 10 10s-4.486 10-10 10S2 17.514 2 12 6.486 2 12 2zm0 3c-3.866 0-7 3.134-7 7s3.134 7 7 7 7-3.134 7-7-3.134-7-7-7zm0 2c2.761 0 5 2.239 5 5s-2.239 5-5 5-5-2.239-5-5 2.239-5 5-5z"/>
                        </svg>
                    </button>
                    <button class="text-white/80 hover:text-white hover:bg-white/10 p-2 rounded-full transition-all duration-300">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Intersection Observer for scroll-triggered animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                }
            });
        }, observerOptions);

        // Observe all animated elements
        document.addEventListener('DOMContentLoaded', () => {
            const animatedElements = document.querySelectorAll('.fade-in-up, .fade-in-left, .fade-in-right, .scale-in, .slide-in-up');
            animatedElements.forEach(el => {
                observer.observe(el);
            });

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
    </script>

</body>
</html>

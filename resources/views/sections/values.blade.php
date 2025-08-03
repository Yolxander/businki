<!-- Values Section -->
<section class="min-h-screen py-8 md:py-12 lg:py-24" id="values" style="background-color: oklch(0.205 0 0);">
    <div class="mx-auto w-full px-2">
         <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
            <!-- Left Content -->
            <div class="lg:w-1/2 space-y-6 md:space-y-8 fade-in-left">
                <!-- Process Steps -->
                <div class="space-y-4 md:space-y-6">
                    <div class="flex items-center space-x-4">
                        <span id="current-slide" class="text-xl md:text-2xl font-bold text-white orbitron">01</span>
                        <div class="h-px bg-gray-600 flex-1"></div>
                        <span id="remaining-slides" class="text-gray-400 text-xs md:text-sm orbitron">02 03 04</span>
                    </div>

                    <p id="slide-description" class="text-gray-300 text-base md:text-lg leading-relaxed max-w-md">
                        We empower freelancers and small agencies with AI-driven tools that streamline workflows, enhance productivity, and boost client satisfaction.
                    </p>
                </div>

                <!-- Main Heading -->
                <div class="space-y-8 md:space-y-12">
                    <div class="text-xs md:text-sm text-gray-400 uppercase tracking-wider orbitron stagger-1">/Our Mission</div>

                    <h1 id="slide-heading" class="text-2xl md:text-4xl lg:text-5xl xl:text-6xl font-bold text-white leading-tight stagger-2">
                        Empowering
                        <br />
                        Freelancers & Agencies
                        <br />
                        With AI-Powered Tools
                    </h1>

                    <a href="/login" class="bg-transparent text-white px-0 py-2 rounded-full text-base md:text-lg font-medium group transition-all orbitron inline-flex items-center hover:underline stagger-3" style="text-decoration-color: #d1ff75;">
                        Start Your Journey
                        <svg class="ml-2 h-4 md:h-5 w-4 md:w-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Right Content - Value Cards -->
            <div class="lg:w-1/2 flex flex-col md:flex-row gap-4 h-full items-start fade-in-right">
                <!-- AI-Powered Card -->
                <div id="slide-card-1" class="relative rounded-2xl md:rounded-3xl overflow-hidden h-[400px] md:h-[570px] flex-1 inset-0 bg-black bg-opacity-40 shadow-md shadow-[#D1FF75]/50 scale-in stagger-1 slide-card">
                    <div class="absolute inset-0">
                        <img src="/bobbi-dashboard.png"
                             alt="AI-powered dashboard"
                             class="w-full h-full object-cover opacity-50">
                    </div>

                    <div class="relative z-10 p-4 md:p-6 h-full flex flex-col">
                        <!-- Top Section -->
                        <div class="flex justify-between items-start mb-4 md:mb-6">
                            <div>
                                <div class="text-white/60 text-xs font-medium mb-1">Our</div>
                                <div class="text-white/60 text-xs font-medium">Values</div>
                            </div>
                            <div class="text-white text-lg md:text-xl font-light italic orbitron">
                                AI-<span class="font-normal">Powered</span>
                            </div>
                        </div>

                        <!-- Bottom Section -->
                        <div class="mt-auto">
                            <h3 class="text-white text-base md:text-lg lg:text-xl font-bold mb-3 leading-tight">
                                Intelligent Automation
                            </h3>
                            <p class="text-white/80 text-xs mb-4 leading-relaxed">
                                Automate repetitive tasks, generate proposals, and manage client communications with AI assistance.
                            </p>

                            <button class="bg-white/15 hover:bg-white/25 text-white border border-white/20 rounded-full px-3 md:px-4 py-2 text-xs md:text-sm backdrop-blur-sm group transition-all">
                                Learn More
                                <svg class="ml-2 h-3 w-3 inline-block group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </button>

                            <!-- Large Number -->
                            <div class="absolute bottom-4 right-4 text-4xl md:text-6xl font-bold text-white/10 leading-none">01</div>
                        </div>
                    </div>
                </div>

                <!-- Second Card and Navigation Container -->
                <div class="flex flex-col gap-4 md:gap-6 flex-1">
                    <!-- Time-Saving Card -->
                    <div id="slide-card-2" class="relative rounded-2xl md:rounded-3xl overflow-hidden h-[350px] md:h-[500px] inset-0 bg-black bg-opacity-40 shadow-md shadow-[#D1FF75]/50 scale-in stagger-2 slide-card">
                        <div class="absolute inset-0">
                            <img src="/bobbi-dashboard.png"
                                 alt="Time tracking and productivity dashboard"
                                 class="w-full h-full object-cover opacity-50">
                        </div>

                        <div class="relative z-10 p-4 md:p-6 h-full flex flex-col">
                            <!-- Top Section -->
                            <div class="flex justify-between items-start mb-4 md:mb-6">
                                <div>
                                    <div class="text-white/60 text-xs font-medium mb-1">Our</div>
                                    <div class="text-white/60 text-xs font-medium">Values</div>
                                </div>
                                <div class="text-white text-lg md:text-xl font-light italic orbitron">
                                    Time-<span class="font-normal">Saving</span>
                                </div>
                            </div>

                            <!-- Bottom Section -->
                            <div class="mt-auto">
                                <h3 class="text-white text-base md:text-lg lg:text-xl font-bold mb-3 leading-tight">Boost Productivity</h3>
                                <p class="text-white/80 text-xs mb-4 leading-relaxed">
                                    Focus on what matters most while our platform handles the administrative overhead.
                                </p>

                                <button class="bg-white/15 hover:bg-white/25 text-white border border-white/20 rounded-full px-3 md:px-4 py-2 text-xs md:text-sm backdrop-blur-sm group transition-all">
                                    Learn More
                                    <svg class="ml-2 h-3 w-3 inline-block group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </button>

                                <!-- Large Number -->
                                <div class="absolute bottom-4 right-4 text-4xl md:text-6xl font-bold text-white/10 leading-none">02</div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="flex justify-end space-x-4 fade-in-up stagger-3">
                        <button id="prev-btn" class="rounded-full bg-white border border-gray-200 hover:bg-gray-50 p-2 md:p-3 transition-all">
                            <svg class="h-4 md:h-5 w-4 md:w-5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button id="next-btn" class="rounded-full bg-[#d1ff75] text-black border border-gray-900 hover:bg-gray-800 p-2 md:p-3 transition-all">
                            <svg class="h-4 md:h-5 w-4 md:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>

    <script>
        // Slide content data
        const slideContent = [
            {
                number: "01",
                description: "We empower freelancers and small agencies with AI-driven tools that streamline workflows, enhance productivity, and boost client satisfaction.",
                heading: "Empowering<br />Freelancers & Agencies<br />With AI-Powered Tools",
                card1Title: "AI-Powered",
                card1Subtitle: "Intelligent Automation",
                card1Description: "Automate repetitive tasks, generate proposals, and manage client communications with AI assistance.",
                card2Title: "Time-Saving",
                card2Subtitle: "Boost Productivity",
                card2Description: "Focus on what matters most while our platform handles the administrative overhead."
            },
            {
                number: "02",
                description: "Streamline your project management with intelligent workflows, automated client communication, and real-time collaboration tools.",
                heading: "Streamline<br />Project Management<br />With Smart Workflows",
                card1Title: "Smart Workflows",
                card1Subtitle: "Automated Processes",
                card1Description: "Create intelligent workflows that adapt to your business needs and automate routine tasks.",
                card2Title: "Real-time Sync",
                card2Subtitle: "Live Collaboration",
                card2Description: "Work together seamlessly with real-time updates and instant communication tools."
            },
            {
                number: "03",
                description: "Generate professional proposals, track project progress, and deliver exceptional results with AI-powered insights and analytics.",
                heading: "Generate<br />Professional Results<br />With AI Insights",
                card1Title: "AI Insights",
                card1Subtitle: "Smart Analytics",
                card1Description: "Get intelligent insights about your projects, clients, and business performance.",
                card2Title: "Proposal Generator",
                card2Subtitle: "Professional Output",
                card2Description: "Create compelling proposals and presentations with AI assistance and templates."
            },
            {
                number: "04",
                description: "Scale your freelance business with enterprise-grade tools designed for growth, client management, and revenue optimization.",
                heading: "Scale Your<br />Freelance Business<br />With Enterprise Tools",
                card1Title: "Enterprise Tools",
                card1Subtitle: "Growth Platform",
                card1Description: "Access enterprise-grade features to scale your business and manage multiple clients.",
                card2Title: "Revenue Optimization",
                card2Subtitle: "Smart Pricing",
                card2Description: "Optimize your pricing strategy and maximize revenue with intelligent recommendations."
            }
        ];

        let currentSlide = 0;
        const totalSlides = slideContent.length;

        // Function to update slide content
        function updateSlide() {
            const content = slideContent[currentSlide];

            // Update left content - show cumulative slide numbers
            const slideNumbers = [];
            for (let i = 0; i <= currentSlide; i++) {
                slideNumbers.push(String(i + 1).padStart(2, '0'));
            }
            document.getElementById('current-slide').textContent = slideNumbers.join(' ');

            document.getElementById('slide-description').textContent = content.description;
            document.getElementById('slide-heading').innerHTML = content.heading;

            // Update remaining slides - show only the slides that haven't been visited yet
            const remainingSlides = [];
            for (let i = currentSlide + 1; i < totalSlides; i++) {
                remainingSlides.push(String(i + 1).padStart(2, '0'));
            }
            document.getElementById('remaining-slides').textContent = remainingSlides.join(' ');

            // Update card content
            const card1 = document.getElementById('slide-card-1');
            const card2 = document.getElementById('slide-card-2');

            // Update card 1
            card1.querySelector('.orbitron').innerHTML = content.card1Title + '-<span class="font-normal">Powered</span>';
            card1.querySelector('h3').textContent = content.card1Subtitle;
            card1.querySelector('p').textContent = content.card1Description;
            card1.querySelector('.absolute.bottom-4.right-4').textContent = content.number;

            // Update card 2
            card2.querySelector('.orbitron').innerHTML = content.card2Title + '-<span class="font-normal">Saving</span>';
            card2.querySelector('h3').textContent = content.card2Subtitle;
            card2.querySelector('p').textContent = content.card2Description;
            card2.querySelector('.absolute.bottom-4.right-4').textContent = String(currentSlide + 2).padStart(2, '0');

            // Add fade transition
            const cards = document.querySelectorAll('.slide-card');
            cards.forEach(card => {
                card.style.opacity = '0';
                setTimeout(() => {
                    card.style.opacity = '1';
                }, 150);
            });
        }

        // Navigation functions
        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateSlide();
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateSlide();
        }

        // Event listeners
        document.getElementById('next-btn').addEventListener('click', nextSlide);
        document.getElementById('prev-btn').addEventListener('click', prevSlide);

        // Initialize first slide
        updateSlide();
    </script>

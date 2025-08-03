<!-- About Section -->
<section class="min-h-screen py-8 md:py-12 lg:py-24" id="about" style="background-color: oklch(0.205 0 0);">
    <div class="mx-auto w-full px-2">
        <div class="max-w-8xl mx-auto">
            <div class="flex flex-col lg:flex-row gap-4 md:gap-6 min-h-[500px] md:min-h-[600px]">
                <!-- Left Content -->
                <div class="lg:w-1/2 bg-white/10 backdrop-blur-sm rounded-2xl md:rounded-3xl p-6 md:p-8 lg:p-12 flex flex-col justify-between border border-white/10 shadow-md shadow-[#D1FF75]/20 fade-in-left stagger-1 h-[500px] md:h-[600px]">
                    <!-- Top Section -->
                    <div class="space-y-6 md:space-y-8">
                        <!-- Process Steps -->
                        <div class="space-y-4 md:space-y-6">
                            <div class="flex items-center space-x-4">
                                <span id="about-current-slide" class="text-xl md:text-2xl font-bold text-white orbitron">01</span>
                                <div class="h-px bg-gray-600 flex-1"></div>
                                <span id="about-remaining-slides" class="text-gray-400 text-xs md:text-sm orbitron">02 03 04</span>
                            </div>

                            <p id="about-slide-description" class="text-white/80 text-base md:text-lg leading-relaxed">
                                We understand the challenges freelancers and small agencies face. That's why we built Bobbi - to automate the tedious tasks so you can focus on what you do best.
                            </p>
                        </div>

                        <!-- Main Content -->
                        <div class="space-y-4 md:space-y-6">
                            <div class="text-xs md:text-sm text-gray-400 uppercase tracking-wider orbitron">/About Bobbi</div>

                            <h2 id="about-slide-heading" class="text-xl md:text-3xl lg:text-4xl xl:text-5xl font-bold text-white leading-tight">
                                Built by freelancers, for freelancers.
                            </h2>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="flex space-x-4 mt-6 md:mt-8 fade-in-up stagger-2">
                        <button id="about-prev-btn" class="rounded-full bg-white/20 border border-white/30 hover:bg-white/30 p-2 md:p-3 transition-all">
                            <svg class="h-4 md:h-5 w-4 md:w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button id="about-next-btn" class="rounded-full bg-[#D1FF75] text-black border border-[#D1FF75] hover:bg-[#D1FF75]/80 p-2 md:p-3 transition-all">
                            <svg class="h-4 md:h-5 w-4 md:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Right Visual Section -->
                <div class="lg:w-1/2 fade-in-right stagger-3">
                    <div class="rounded-2xl md:rounded-3xl overflow-hidden h-full min-h-[400px] md:min-h-[600px] relative inset-0 bg-black bg-opacity-40 shadow-md shadow-[#D1FF75]/50">
                        <!-- Main Image -->
                        <div class="relative h-full">
                            <img src="/bobbi-dashboard.png"
                                 alt="Bobbi dashboard showing project management interface"
                                 class="w-full h-full object-cover opacity-50">

                            <!-- Bottom Right Info Card -->
                            <div class="absolute bottom-4 md:bottom-8 right-4 md:right-8 scale-in stagger-4">
                                <div class="bg-white/15 backdrop-blur-md rounded-2xl md:rounded-3xl p-4 md:p-6 flex items-center space-x-3 md:space-x-5 shadow-xl shadow-[#D1FF75]/20 border border-white/20 hover:bg-white/20 transition-all duration-300 group">
                                    <!-- Small Thumbnail -->
                                    <div class="w-12 md:w-16 h-12 md:h-16 rounded-xl md:rounded-2xl overflow-hidden bg-white/20 ring-2 ring-white/20 group-hover:ring-[#D1FF75]/50 transition-all duration-300">
                                        <img src="https://source.unsplash.com/64x64/?dashboard,interface"
                                             alt="Dashboard interface"
                                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    </div>

                                    <!-- Text Content -->
                                    <div class="space-y-1">
                                        <div class="text-xs text-white/60 font-medium orbitron tracking-wider">/01</div>
                                        <div id="about-card-title" class="text-sm md:text-base font-bold text-white leading-tight">AI-Powered</div>
                                        <div id="about-card-subtitle" class="text-sm md:text-base font-bold text-[#D1FF75] leading-tight">Workflow</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // About slide content data
        const aboutSlideContent = [
            {
                number: "01",
                description: "We understand the challenges freelancers face. That's why we built Bobbi to automate tedious tasks so you can focus on what you do best.",
                heading: "Built by freelancers, for freelancers.",
                cardTitle: "AI-Powered",
                cardSubtitle: "Workflow"
            },
            {
                number: "02",
                description: "Our team has been in your shoes. We've juggled deadlines and struggled with inefficient tools. That's why we created a solution that actually works.",
                heading: "We've been there. Now we're here to help.",
                cardTitle: "Smart",
                cardSubtitle: "Automation"
            },
            {
                number: "03",
                description: "Bobbi isn't just another project management tool. It's your AI-powered business partner that learns your workflow and adapts to your needs.",
                heading: "Your AI-powered business partner.",
                cardTitle: "Adaptive",
                cardSubtitle: "Learning"
            },
            {
                number: "04",
                description: "Join thousands of freelancers who have transformed their business with Bobbi. Start your journey to better project management today.",
                heading: "Join the community of successful freelancers.",
                cardTitle: "Community",
                cardSubtitle: "Driven"
            }
        ];

        let currentAboutSlide = 0;
        const totalAboutSlides = aboutSlideContent.length;

        // Function to update about slide content
        function updateAboutSlide() {
            const content = aboutSlideContent[currentAboutSlide];

            // Update left content - show cumulative slide numbers
            const slideNumbers = [];
            for (let i = 0; i <= currentAboutSlide; i++) {
                slideNumbers.push(String(i + 1).padStart(2, '0'));
            }
            document.getElementById('about-current-slide').textContent = slideNumbers.join(' ');

            document.getElementById('about-slide-description').textContent = content.description;
            document.getElementById('about-slide-heading').textContent = content.heading;

            // Update remaining slides - show only the slides that haven't been visited yet
            const remainingSlides = [];
            for (let i = currentAboutSlide + 1; i < totalAboutSlides; i++) {
                remainingSlides.push(String(i + 1).padStart(2, '0'));
            }
            document.getElementById('about-remaining-slides').textContent = remainingSlides.join(' ');

            // Update card content
            document.getElementById('about-card-title').textContent = content.cardTitle;
            document.getElementById('about-card-subtitle').textContent = content.cardSubtitle;

            // Add fade transition
            const elements = document.querySelectorAll('#about-slide-description, #about-slide-heading, #about-card-title, #about-card-subtitle');
            elements.forEach(element => {
                element.style.opacity = '0';
                setTimeout(() => {
                    element.style.opacity = '1';
                }, 150);
            });
        }

        // Navigation functions
        function nextAboutSlide() {
            currentAboutSlide = (currentAboutSlide + 1) % totalAboutSlides;
            updateAboutSlide();
        }

        function prevAboutSlide() {
            currentAboutSlide = (currentAboutSlide - 1 + totalAboutSlides) % totalAboutSlides;
            updateAboutSlide();
        }

        // Event listeners
        document.getElementById('about-next-btn').addEventListener('click', nextAboutSlide);
        document.getElementById('about-prev-btn').addEventListener('click', prevAboutSlide);

        // Initialize first slide
        updateAboutSlide();
    </script>
</section>

<!-- Features Section -->
<section class="min-h-screen py-12 lg:py-24" id="features" style="background-color: oklch(0.205 0 0);">
    <div class="mx-auto w-full px-2">
        <!-- Header -->
        <div class="text-center mb-16 fade-in-up">
            <div class="text-sm text-gray-400 uppercase tracking-wider mb-8 orbitron stagger-1">/Features</div>

            <h2 class="text-4xl lg:text-5xl xl:text-6xl font-bold leading-tight max-w-6xl mx-auto stagger-2">
                <span class="text-white">Everything you need to run your freelance business </span>
                <span class="text-gray-300">
                    from project management to client communication, all powered by AI to help you work smarter, not harder.
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
                             alt="Bobbi dashboard interface"
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
                                <div class="text-white text-lg font-medium mb-2">Sarah Chen</div>
                                <div class="bg-black/50 backdrop-blur-sm rounded-full px-4 py-2 inline-block">
                                    <span class="text-white text-sm">"Bobbi transformed my freelance workflow"</span>
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
                        <div class="text-sm text-white/60 font-medium">Freelancer</div>
                        <div class="text-sm text-white/60 font-medium underline cursor-pointer hover:text-white transition">Testimonial</div>
                    </div>

                    <!-- Title -->
                    <h3 class="text-3xl lg:text-4xl font-bold text-white mb-8 fade-in-up stagger-6">Mike Rodriguez</h3>

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
                            "The AI-powered proposal generation saved me hours every week. My clients love the professional presentations."
                        </p>

                        <!-- Profile -->
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-full bg-white/20 overflow-hidden backdrop-blur-sm scale-in stagger-8">
                                <img src="https://source.unsplash.com/48x48/?portrait,man"
                                     alt="Mike profile"
                                     class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section> 
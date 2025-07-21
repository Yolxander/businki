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
    </style>
</head>
<body class="text-gray-900 min-h-screen px-6" style="background-color: oklch(0.205 0 0);">
    <!-- Header -->
    <header class="w-full py-3 px-2">
        <nav class="w-full mx-auto flex items-center justify-between">
            <div class="text-2xl font-bold text-white orbitron">Bobbi</div>
            <div class="flex space-x-8">
                <a href="#" class="text-white hover:text-gray-300 transition orbitron">About Us</a>
                <a href="#" class="text-white hover:text-gray-300 transition orbitron">Pricing</a>
                <a href="#" class="text-white hover:text-gray-300 transition orbitron">Login</a>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <main class="relative w-full h-[90vh] mx-auto px-8 mb-6">
        <!-- Background Image Container -->
        <div class="hero-image absolute inset-0 rounded-2xl overflow-hidden shadow-md">
            <!-- Overlay for better text readability -->
            <div class="absolute inset-0 bg-black bg-opacity-40"></div>

            <!-- Large "Title" Text Overlay -->
            <div class="absolute bottom-80 left-[5%] inset-0 flex items-center justify-center w-[800px]">
                <h1 class="text-xl md:text-8xl font-bold text-white tracking-wider ">
                    The Future of Freelance
                </h1>
            </div>

            <!-- Left Information Box -->
            <div class="absolute bottom-10 right-96 w-96 frosted-glass rounded-2xl p-8">
                <p class="text-white text-lg leading-relaxed mb-6">
                    Crafting spaces that harmonize modern aesthetics with timeless elegance, our contemporary interior designs breathe life into every room, redefining the essence of chic living.
                </p>
                <button class="bg-black text-white px-6 py-3 rounded-lg font-medium hover:bg-gray-800 transition orbitron">
                    View More →
                </button>
            </div>

            <!-- Left Information Box -->
            <div class="absolute bottom-20 right-10 w-80 frosted-glass rounded-2xl p-8">
                <p class="text-white text-lg leading-relaxed mb-6">
                    Crafting spaces that harmonize modern aesthetics with timeless elegance, our contemporary interior designs breathe life into every room, redefining the essence of chic living.
                </p>
                <button class="bg-black text-white px-6 py-3 rounded-lg font-medium hover:bg-gray-800 transition orbitron">
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
    </main>


</body>
</html>

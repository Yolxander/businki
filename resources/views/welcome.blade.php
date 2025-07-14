<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bobbi Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .orbitron { font-family: 'Orbitron', sans-serif; }
    </style>
</head>
<body class="bg-black text-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <header class="w-full flex justify-center pt-8 pb-4">
        <nav class="w-[95%] max-w-6xl bg-gray-900 rounded-lg flex items-center justify-between px-8 py-4 shadow border border-gray-800">
            <div class="orbitron text-2xl text-amber-400 tracking-widest font-bold">Bobbi <span class="text-white">Admin</span></div>
            <a href="/admin" class="bg-amber-400 hover:bg-amber-500 text-black font-semibold rounded-lg px-6 py-3 transition">
                @if(auth()->check())
                    Go to Dashboard
                @else
                    Login to Bobbi Admin
                @endif
            </a>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col lg:flex-row items-stretch justify-center w-full max-w-6xl mx-auto px-0 gap-0 mt-8">
        <!-- Left: Overview -->
        <section class="flex-1 flex flex-col items-start justify-center max-w-xl bg-black px-10 py-12">
            <div class="bg-gray-900 border border-gray-800 rounded-lg shadow-lg w-full p-8 mb-8">
                <h1 class="orbitron text-4xl md:text-5xl font-extrabold text-amber-400 mb-4 leading-tight">
                    Welcome to <span class="text-white">Bobbi</span> Admin
                </h1>
                <div class="prose dark:prose-invert max-w-none mb-6">
                    <h2 class="text-xl font-semibold">What you can do:</h2>
                    <ul class="list-disc pl-6 space-y-2 text-lg">
                        <li><span class="orbitron text-amber-400">Bobbi</span> Project &amp; Task Management</li>
                        <li>Automate Proposals &amp; Client Workflows</li>
                        <li>Track Progress, Deadlines, and Analytics</li>
                        <li>Manage Teams, Permissions, and Roles</li>
                        <li>Integrate AI-powered tools for productivity</li>
                        <li>Access detailed reports and dashboards</li>
                    </ul>
                </div>
                <a href="/admin" class="bg-amber-400 hover:bg-amber-500 text-black font-semibold rounded-lg px-6 py-3 transition w-full text-center block">
                    @if(auth()->check())
                        Go to Dashboard
                    @else
                        Login to Bobbi Admin
                    @endif
                </a>
            </div>
        </section>
        <!-- Right: Full Image -->
        <section class="flex-1 flex flex-col justify-stretch items-stretch">
            <div class="flex-1 h-full w-full">
                <img src="https://source.unsplash.com/1200x1200/?dashboard,technology,admin" alt="Bobbi Admin Hero" class="object-cover w-full h-full min-h-[400px] max-h-none rounded-none" style="min-width:320px; max-width:100%;" />
            </div>
        </section>
    </main>

    <!-- Bottom Bar: Left text, right image -->
    <div class="w-full max-w-6xl mx-auto flex flex-row items-stretch mt-0 mb-8 px-0">
        <div class="flex-1 bg-gray-900 border border-gray-800 text-amber-400 rounded-l-lg px-10 py-6 text-lg font-semibold shadow-lg orbitron flex items-center">
            <span class="text-white">Bobbi</span> Admin &mdash; Your all-in-one business control center
        </div>
        <div class="flex-1 hidden lg:block">
            <img src="https://source.unsplash.com/1200x400/?dashboard,technology,admin" alt="Bobbi Admin Banner" class="object-cover w-full h-full rounded-r-lg" style="min-width:320px; max-width:100%; min-height:100px;" />
        </div>
    </div>
</body>
</html>

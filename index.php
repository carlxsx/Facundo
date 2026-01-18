<?php 
// ==================================================================================
// 1. SYSTEM CONFIGURATION
// ==================================================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); 

// DATABASE CONNECTION
try {
    if (!file_exists('db_connection/db.php')) {
        throw new Exception("CRITICAL: 'db_connection/db.php' is missing.");
    }
    require 'db_connection/db.php'; 

    $isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
    $isLoggedIn = isset($_SESSION['user_id']);

    // FETCH INVENTORY
    $stmt = $pdo->query("SELECT * FROM vehicles WHERE status != 'Sold' ORDER BY id DESC");
    $units = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $units = []; 
} catch (Exception $e) {
    die("System Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FACUNDO | Automotive Excellence</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* === RESTORED ORIGINAL DESIGN & CSS === */
        :root { --cyberlime: #ccff00; --obsidian: #0d1117; --charcoal: #161b22; }
        
        body { font-family: 'Inter', sans-serif; background-color: black; color: white; overflow-x: hidden; }
        
        .text-cyberlime { 
            color: var(--cyberlime); 
            text-shadow: none; 
            font-weight: 800; 
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }
        .bg-cyberlime { background-color: var(--cyberlime); }
        .border-cyberlime { border-color: var(--cyberlime); }
        .transition-facundo { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        
        /* PREMIUM HERO STYLES (Restored) */
        .hero-bg {
            background-color: #000000;
            background-image: 
                radial-gradient(circle at 50% 0%, rgba(204, 255, 0, 0.15) 0%, transparent 50%),
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 100% 100%, 40px 40px, 40px 40px;
            background-position: center top;
        }

        .text-glow { text-shadow: 0 0 20px rgba(204, 255, 0, 0.3); }

        .nav-glass {
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Entrance Animation */
        .slide-up {
            animation: slideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
            opacity: 0;
            transform: translateY(30px);
        }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }

        @keyframes slideUp { to { opacity: 1; transform: translateY(0); } }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: var(--charcoal); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #374151; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: var(--cyberlime); }

        /* Choices.js Dark Mode Overrides */
        .choices__inner { 
            background-color: var(--obsidian) !important; 
            border: 1px solid #374151 !important; 
            color: white !important; 
            border-radius: 0.75rem !important; 
            min-height: 45px !important;
            display: flex; align-items: center;
        }
        .choices__list--dropdown { 
            background-color: var(--charcoal) !important; 
            border: 1px solid #374151 !important; 
            color: #9ca3af !important; 
            border-radius: 0.5rem !important;
            margin-top: 5px !important;
        }
        .choices__item--selectable.is-highlighted { 
            background-color: var(--cyberlime) !important; 
            color: black !important; 
        }
        .choices__input { background-color: transparent !important; color: white !important; }

        /* Facundo Input Standard */
        .input-facundo { 
            background-color: var(--obsidian); 
            border: 1px solid #374151; 
            color: white; 
            padding: 0.75rem 1rem; 
            border-radius: 0.75rem; 
            width: 100%; 
            font-size: 0.875rem; 
            transition: all 0.2s;
        }
        .input-facundo:focus { outline: none; border-color: var(--cyberlime); box-shadow: 0 0 0 1px var(--cyberlime); }

        /* Footer Typography */
        .footer-logo-bg { font-size: 18vw; opacity: 0.02; line-height: 0.8; font-weight: 900; }
        .footer-link { font-size: 10px; text-transform: uppercase; font-weight: bold; color: #6b7280; letter-spacing: 0.1em; transition: color 0.2s; display: block; margin-bottom: 0.5rem; }
        .footer-link:hover { color: var(--cyberlime); }

        /* Chat Window Animation */
        #chat-window { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform-origin: bottom right; }
        #chat-window.hidden { display: none; opacity: 0; transform: scale(0.9) translateY(20px); }
    </style>
</head>
<body>

    <?php if(isset($_GET['success'])): ?>
        <div id="success-alert" class="fixed top-24 right-6 bg-cyberlime/10 border border-cyberlime text-cyberlime px-6 py-4 rounded-xl z-[9000] backdrop-blur-md shadow-2xl animate-pulse flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <div>
                <span class="block text-[10px] font-black uppercase tracking-[0.2em]">System Update</span>
                <span class="text-xs font-bold">Operation Successful</span>
            </div>
        </div>
    <?php endif; ?>

<<<<<<< HEAD
    <div class="bg-[#050505] border-b border-white/5 px-6 py-2.5 flex justify-between items-center relative z-50">
        
        <div class="flex items-center gap-6">
            
            <div class="flex items-center gap-4">
            
                <div class="flex items-center gap-2" title="Connection Secure">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[9px] text-gray-500 font-mono uppercase tracking-widest group-hover:text-white transition-colors">Net: Online</span>
                </div>

                <div id="geo-locator" class="hidden sm:flex items-center gap-1.5 opacity-50 hover:opacity-100 transition-opacity cursor-help group" title="Triangulating User Location...">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-cyberlime group-hover:animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    
                    <div class="flex flex-col leading-none">
                        <span class="text-[6px] text-gray-500 font-bold uppercase tracking-widest mb-0.5">CURRENT LOC</span>
                        <span id="user-city" class="text-[9px] font-mono text-white tracking-wider font-bold uppercase">SEARCHING...</span>
                    </div>
                </div>

            </div>

            <div class="h-3 w-px bg-gray-800"></div>

            <div id="user-display" class="flex items-center gap-3">
                <?php if($isLoggedIn): ?>
                    <div class="w-5 h-5 rounded bg-gray-800 border border-gray-700 flex items-center justify-center text-[9px] font-bold text-cyberlime shadow-[0_0_10px_rgba(204,255,0,0.1)]">
                        <?php echo substr($_SESSION['name'] ?? 'U', 0, 1); ?>
                    </div>
                    
                    <div class="flex flex-col justify-center">
                        <span class="text-[10px] font-bold text-white leading-none tracking-wide">
                            <?php echo htmlspecialchars($_SESSION['name']); ?>
                        </span>
                        
                        <?php if($isAdmin): ?>
                            <span class="text-[8px] text-cyberlime font-mono uppercase tracking-wider leading-none mt-0.5">COMMANDER</span>
                        <?php else: ?>
                            <span class="text-[9px] text-gray-600 font-mono uppercase tracking-wider leading-none mt-0.5">Authorized Buyer</span>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <span class="text-xs font-bold text-gray-500 italic tracking-wide">Guest Access</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <div id="system-clock" class="hidden md:block text-[9px] font-mono text-gray-600 font-bold tracking-widest">
                00:00:00 UTC
            </div>

            <div class="flex gap-4">
                <?php if($isLoggedIn): ?>
                    <a href="logout.php" class="group flex items-center gap-2 text-[9px] text-red-500 uppercase font-bold tracking-widest hover:text-red-400 transition-colors">
                        <span>Disconnect</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </a>
                <?php else: ?>
                    <button onclick="window.openLogin()" class="bg-cyberlime/10 border border-cyberlime/50 hover:bg-cyberlime hover:text-black text-cyberlime px-4 py-1.5 rounded text-[9px] font-bold uppercase tracking-widest transition-all">
                        Initialize Session
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('system-clock').innerText = now.toLocaleTimeString('en-US', {hour12: false}) + " PHT";
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>

    <nav id="main-nav" class="sticky top-0 z-50 transition-all duration-300 bg-[#050505]/85 backdrop-blur-xl border-b border-white/5">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                
                <a href="#" class="group" onclick="window.scrollTo(0,0)">
                    <div class="text-2xl font-black tracking-tighter italic text-white transition-all duration-500 group-hover:tracking-wide group-hover:text-cyberlime">
                        FACU<span class="text-cyberlime group-hover:text-white transition-colors">NDO</span>
                    </div>
                </a>

                <div class="hidden md:flex items-center gap-10">
                    
                    <div class="flex gap-8 text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400">
                        <a href="#showroom" class="relative hover:text-white transition-colors group py-2">
                            Showroom
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-cyberlime transition-all duration-300 group-hover:w-full opacity-0 group-hover:opacity-100"></span>
                        </a>
                        <a href="#finance" class="relative hover:text-white transition-colors group py-2">
                            Financing
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-cyberlime transition-all duration-300 group-hover:w-full opacity-0 group-hover:opacity-100"></span>
                        </a>
                    </div>

                    <?php if($isAdmin): ?>
                        <div class="h-6 w-px bg-gray-800/50"></div>
                        <div class="flex items-center gap-4 text-[10px] font-bold uppercase tracking-[0.2em]">
                            <span class="text-gray-600 select-none">CMD:</span>
                            <a href="#stc" class="text-cyberlime hover:text-white transition-colors relative flex items-center gap-2">
                                <span class="w-1 h-1 bg-cyberlime rounded-full"></span> STC
                            </a>
                            <a href="#crm" class="text-cyberlime hover:text-white transition-colors relative flex items-center gap-2">
                                <span class="w-1 h-1 bg-cyberlime rounded-full"></span> CRM
                            </a>
                        </div>
                    <?php endif; ?>

                </div>

                <button onclick="window.toggleMobileMenu()" class="md:hidden text-white hover:text-cyberlime transition-colors p-2 rounded-lg hover:bg-white/5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden bg-[#0d1117] border-b border-gray-800 overflow-hidden transition-all animate-in-fade relative z-50">
            <div class="px-6 py-8 space-y-6 flex flex-col items-center text-center">
                <a href="#showroom" onclick="window.toggleMobileMenu()" class="text-sm font-bold uppercase tracking-widest text-white hover:text-cyberlime">Showroom</a>
                <a href="#finance" onclick="window.toggleMobileMenu()" class="text-sm font-bold uppercase tracking-widest text-white hover:text-cyberlime">Financing</a>
                
                <?php if($isAdmin): ?>
                    <div class="w-1/2 h-px bg-gray-800 my-4"></div>
                    <span class="text-[9px] text-gray-500 uppercase tracking-widest font-bold">Command Systems</span>
                    <a href="#stc" onclick="window.toggleMobileMenu()" class="text-sm font-bold uppercase tracking-widest text-cyberlime flex items-center justify-center gap-2"><span class="w-1.5 h-1.5 bg-cyberlime rounded-full"></span> Stock Control</a>
                    <a href="#crm" onclick="window.toggleMobileMenu()" class="text-sm font-bold uppercase tracking-widest text-cyberlime flex items-center justify-center gap-2"><span class="w-1.5 h-1.5 bg-cyberlime rounded-full"></span> CRM Portal</a>
                <?php endif; ?>
=======
    <div class="bg-black border-b border-gray-900 px-6 py-2 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <span class="text-[9px] text-gray-500 uppercase font-bold tracking-widest">Current Session:</span>
            <div id="user-display" class="flex items-center gap-2">
                <span class="text-xs font-bold text-white"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Guest'); ?></span>
            </div>
        </div>
        <div class="flex gap-4">
            <?php if($isLoggedIn): ?>
                <a href="logout.php" class="text-[9px] text-red-500 uppercase font-bold tracking-widest transition underline">Logout</a>
            <?php else: ?>
                <button onclick="window.openLogin()" class="text-[9px] text-cyberlime uppercase font-bold tracking-widest transition underline">Login</button>
            <?php endif; ?>
        </div>
    </div>

    <nav class="sticky top-0 z-50 bg-black/80 backdrop-blur-md border-b border-gray-800 px-6 py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="text-3xl font-black tracking-tighter italic text-white">
                FACU<span class="text-cyberlime font-black">NDO</span>
            </div>

            <div class="hidden md:flex gap-10 text-[10px] font-bold uppercase tracking-[0.2em]">
                <a href="#showroom" class="hover:text-cyberlime transition-facundo">Showroom</a>
                <?php if($isAdmin): ?>
                    <a href="#stc" class="text-cyberlime hover:text-white transition-facundo">Stock (STC)</a>
                    <a href="#crm" class="text-cyberlime hover:text-white transition-facundo">CRM Portal</a>
                <?php endif; ?>
                <a href="#finance" class="hover:text-cyberlime transition-facundo">Financing</a>
            </div>

            <div class="md:hidden text-cyberlime">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
>>>>>>> origin/main
            </div>
        </div>
    </nav>

<<<<<<< HEAD
    <script>
        window.toggleMobileMenu = function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        };
    </script>

=======
>>>>>>> origin/main
    <header class="relative min-h-[95vh] flex items-center justify-center overflow-hidden hero-bg pt-20">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 select-none pointer-events-none z-0">
            <h1 class="text-[25vw] font-black italic text-white opacity-[0.02] leading-none tracking-tighter">FLEET</h1>
        </div>

        <div class="max-w-7xl mx-auto w-full relative z-10 text-center px-6">
            <div class="slide-up">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 mb-8 border border-cyberlime/30 rounded-full bg-cyberlime/5 backdrop-blur-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyberlime animate-pulse"></span>
                    <span class="text-cyberlime text-[10px] font-bold tracking-[0.3em] uppercase">Premier Auto Commerce Manila</span>
                </div>
            </div>

            <h1 class="slide-up delay-100 text-5xl md:text-8xl font-black italic mb-6 tracking-tighter uppercase leading-[0.9] text-white">
                Drive the <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyberlime to-white text-glow">Standard</span>
            </h1>
            
            <p class="slide-up delay-200 text-gray-400 max-w-xl mx-auto mb-16 text-sm md:text-lg tracking-wide leading-relaxed font-light">
                Access the exclusive Facundo fleet. Verified units, high-performance maintenance, and transparent PHP pricing structures.
            </p>

<<<<<<< HEAD
            <div class="slide-up delay-300 max-w-5xl mx-auto relative z-30 px-4">
    
            <div class="bg-black/80 backdrop-blur-2xl border border-white/10 p-3 rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.5)] flex flex-col md:flex-row items-stretch gap-2 group transition-all duration-500 hover:border-white/20">

                <div class="relative flex-1 w-full bg-[#161b22]/50 hover:bg-[#161b22] border border-transparent hover:border-gray-700 rounded-2xl px-6 py-4 transition-all group/field focus-within:bg-[#161b22] focus-within:border-cyberlime/50 cursor-text" onclick="document.getElementById('search-make').focus()">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1 group-focus-within/field:text-cyberlime transition-colors">Target Unit</label>
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 text-gray-600 group-focus-within/field:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" id="search-make" onkeyup="window.filterFleet()" placeholder="e.g. Land Cruiser" class="w-full bg-transparent text-white font-bold font-mono uppercase text-sm placeholder:text-gray-700 outline-none border-none focus:ring-0 p-0">
                    </div>
                </div>

                <div class="hidden md:block w-px bg-gray-800 my-4"></div>

                <div class="relative flex-1 w-full bg-[#161b22]/50 hover:bg-[#161b22] border border-transparent hover:border-gray-700 rounded-2xl px-6 py-4 transition-all group/field focus-within:bg-[#161b22] focus-within:border-cyberlime/50 cursor-text" onclick="document.getElementById('search-price').focus()">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1 group-focus-within/field:text-cyberlime transition-colors">Max Budget</label>
                    <div class="flex items-center gap-3">
                        <span class="text-gray-600 font-bold group-focus-within/field:text-white transition-colors">₱</span>
                        <input type="number" id="search-price" onkeyup="window.filterFleet()" placeholder="15,000,000" class="w-full bg-transparent text-white font-bold font-mono text-sm placeholder:text-gray-700 outline-none border-none focus:ring-0 p-0 appearance-none">
                    </div>
                </div>

                <button onclick="document.getElementById('showroom').scrollIntoView({behavior: 'smooth'})" class="w-full md:w-auto bg-cyberlime hover:bg-white text-black font-black text-sm uppercase tracking-widest px-10 py-5 rounded-2xl transition-all duration-300 hover:shadow-[0_0_30px_rgba(204,255,0,0.4)] hover:scale-[1.02] flex items-center justify-center gap-3 whitespace-nowrap">
                    <span>Explore Fleet</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>

            </div>

            <div class="mt-6 flex justify-center flex-wrap gap-3">
                <span class="text-[10px] font-bold text-gray-600 uppercase tracking-widest py-1.5">Trending:</span>
                <button onclick="document.getElementById('search-make').value='Toyota'; window.filterFleet()" class="px-4 py-1.5 rounded-full border border-gray-800 bg-black/40 backdrop-blur-md text-[9px] font-bold text-gray-400 uppercase hover:border-cyberlime hover:text-white hover:bg-cyberlime/10 transition-all">Toyota</button>
                <button onclick="document.getElementById('search-make').value='Land Cruiser'; window.filterFleet()" class="px-4 py-1.5 rounded-full border border-gray-800 bg-black/40 backdrop-blur-md text-[9px] font-bold text-gray-400 uppercase hover:border-cyberlime hover:text-white hover:bg-cyberlime/10 transition-all">Land Cruiser</button>
                <button onclick="document.getElementById('search-make').value='Mustang'; window.filterFleet()" class="px-4 py-1.5 rounded-full border border-gray-800 bg-black/40 backdrop-blur-md text-[9px] font-bold text-gray-400 uppercase hover:border-cyberlime hover:text-white hover:bg-cyberlime/10 transition-all">Mustang</button>
                <button onclick="document.getElementById('search-make').value='Supercar'; window.filterFleet()" class="px-4 py-1.5 rounded-full border border-gray-800 bg-black/40 backdrop-blur-md text-[9px] font-bold text-gray-400 uppercase hover:border-cyberlime hover:text-white hover:bg-cyberlime/10 transition-all">Supercar</button>
            </div>

=======
            <div class="slide-up delay-300 max-w-4xl mx-auto relative z-30">
                <div class="bg-[#161b22]/90 backdrop-blur-xl border border-gray-700/50 p-2 rounded-2xl shadow-2xl flex flex-col md:flex-row items-center gap-2 group hover:border-cyberlime/30 transition-all duration-500 focus-within:border-cyberlime/50 focus-within:shadow-[0_0_30px_rgba(204,255,0,0.15)]">
                    <div class="flex-1 w-full px-4 py-3 relative text-left group/input">
                        <label class="text-[9px] font-bold text-gray-500 uppercase tracking-widest mb-1 ml-1 group-focus-within/input:text-cyberlime transition-colors">Target Unit</label>
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 text-gray-600 group-focus-within/input:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            <input type="text" id="search-make" onkeyup="window.filterFleet()" placeholder="e.g. Land Cruiser" class="bg-transparent text-white w-full outline-none placeholder:text-gray-700 text-sm font-bold font-mono uppercase">
                        </div>
                    </div>
                    <div class="hidden md:block w-[1px] h-12 bg-gray-700/50"></div>
                    <div class="flex-1 w-full px-4 py-3 relative text-left group/input">
                        <label class="text-[9px] font-bold text-gray-500 uppercase tracking-widest mb-1 ml-1 group-focus-within/input:text-cyberlime transition-colors">Budget Limit</label>
                        <div class="flex items-center gap-3">
                            <span class="text-gray-600 font-bold group-focus-within/input:text-white transition-colors">₱</span>
                            <input type="number" id="search-price" onkeyup="window.filterFleet()" placeholder="15,000,000" class="bg-transparent text-white w-full outline-none placeholder:text-gray-700 text-sm font-bold font-mono">
                        </div>
                    </div>
                    <button onclick="document.getElementById('showroom').scrollIntoView({behavior: 'smooth'})" class="w-full md:w-auto bg-cyberlime hover:bg-white text-black font-black px-8 py-4 rounded-xl transition-all duration-300 uppercase text-xs tracking-widest flex items-center justify-center gap-2">View Fleet</button>
                </div>
>>>>>>> origin/main
            </div>
        </div>
        <div class="absolute bottom-0 left-0 w-full h-32 bg-gradient-to-t from-black to-transparent pointer-events-none"></div>
    </header>

<<<<<<< HEAD
    <section id="showroom" class="py-24 px-6 bg-[#050505] border-t border-gray-900 relative overflow-hidden">
        
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-cyberlime/5 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto relative z-10">
            
            <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
                <div>
                    <h2 class="text-cyberlime font-bold uppercase tracking-[0.3em] text-xs mb-3">Facundo Fleet</h2>
                    <h3 class="text-4xl md:text-5xl font-black italic uppercase tracking-tighter text-white">
                        Available <span class="text-cyberlime">Units</span>
                    </h3>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="fleet-grid">
                <?php if(empty($units)): ?>
                    <div class="col-span-full text-center py-20 border border-dashed border-gray-800 rounded-3xl bg-[#0d1117]">
                        <p class="text-gray-500 uppercase tracking-widest text-xs font-bold">Inventory Currently Updating...</p>
                    </div>
                <?php else: foreach($units as $unit): ?>
                    <div class="unit-card rounded-2xl group bg-[#0d1117] border border-gray-800 overflow-hidden relative transition-all duration-500 hover:border-cyberlime/50 hover:shadow-[0_0_30px_rgba(204,255,0,0.05)] hover:-translate-y-1">
                        
                        <div class="absolute top-4 left-4 z-10">
                            <span class="bg-black/80 backdrop-blur-md <?php echo $unit['status'] == 'Reserved' ? 'text-orange-400 border-orange-400/50' : 'text-cyberlime border-cyberlime/50'; ?> text-[9px] font-bold px-3 py-1 rounded-full uppercase tracking-widest border shadow-xl">
                                <?php echo $unit['status']; ?>
                            </span>
                        </div>

                        <div class="relative aspect-[16/10] bg-black overflow-hidden">
                            <?php $imagePath = !empty($unit['image_path']) ? 'uploads/cars/' . $unit['image_path'] : 'assets/img/no-image.jpg'; ?>
                            <img src="<?php echo $imagePath; ?>" alt="Car" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 opacity-90 group-hover:opacity-100">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#0d1117] via-transparent to-transparent opacity-80"></div>
                        </div>

                        <div class="p-6 relative">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-xl font-black tracking-tight group-hover:text-cyberlime transition-all uppercase text-white unit-title">
                                    <?php echo htmlspecialchars($unit['make'] . ' ' . $unit['model']); ?>
                                </h4>
                                
                                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <div class="flex items-center gap-1 bg-black/60 border border-gray-700 rounded-lg p-1 backdrop-blur-md opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button onclick="window.openEditModal(<?php echo htmlspecialchars(json_encode($unit), ENT_QUOTES, 'UTF-8'); ?>)" class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </button>
                                        <button onclick="window.deleteUnit(<?php echo $unit['id']; ?>)" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-500/10 rounded transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="grid grid-cols-2 border-y border-gray-800 py-4 my-4 gap-4">
                                <div>
                                    <span class="block text-[9px] text-gray-500 uppercase font-bold tracking-widest mb-1">Mileage</span>
                                    <span class="font-bold text-sm text-gray-300 font-mono"><?php echo number_format($unit['mileage_km']); ?> <span class="text-gray-600 text-[10px]">KM</span></span>
                                </div>
                                <div>
                                    <span class="block text-[9px] text-gray-500 uppercase font-bold tracking-widest mb-1">Valuation</span>
                                    <span class="text-lg font-black italic text-white unit-price tracking-tighter">₱<?php echo number_format($unit['price_php']); ?></span>
                                </div>
                            </div>

                            <div class="flex gap-3 mt-6">
                                <?php if($unit['status'] !== 'Reserved'): ?>
                                    <?php if(isset($_SESSION['user_id'])): ?>
                                        <button onclick="window.openInquiry(<?php echo $unit['id']; ?>, '<?php echo addslashes($unit['make'] . ' ' . $unit['model']); ?>')" class="flex-1 bg-cyberlime text-black py-3 px-4 rounded-xl font-black uppercase tracking-widest text-[10px] hover:bg-white transition-all transform hover:scale-[1.02] shadow-[0_0_15px_rgba(204,255,0,0.1)]">
                                            Acquire Unit
                                        </button>
                                    <?php else: ?>
                                        <button onclick="window.openRegister()" class="flex-1 bg-cyberlime text-black py-3 px-4 rounded-xl font-black uppercase tracking-widest text-[10px] hover:bg-white transition-all transform hover:scale-[1.02] shadow-[0_0_15px_rgba(204,255,0,0.1)]">
                                            Acquire Unit
                                        </button>
                                    <?php endif; ?>
                                    
                                    <a href="https://wa.me/639123456789" target="_blank" class="w-12 flex items-center justify-center bg-[#161b22] border border-gray-700 text-gray-400 rounded-xl hover:text-emerald-400 hover:border-emerald-400 transition-all">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    </a>
                                <?php else: ?>
                                    <button class="w-full bg-[#161b22] border border-gray-800 text-gray-500 py-3 rounded-xl cursor-not-allowed flex items-center justify-center gap-2" disabled>
                                        <span class="font-bold uppercase tracking-widest text-[10px]">Unit Reserved</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </section>

    <?php if ($isAdmin): ?>
        <section id="stc" class="py-24 px-6 bg-black border-t border-gray-900 relative overflow-hidden">
        
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-cyberlime/5 rounded-full blur-[120px] pointer-events-none"></div>

            <div class="max-w-7xl mx-auto relative z-10">
                
                <div class="mb-12 flex items-center justify-between">
                    <div>
                        <h2 class="text-cyberlime font-bold uppercase tracking-[0.3em] text-xs mb-3">Internal Operations</h2>
                        <h3 class="text-4xl font-black italic uppercase tracking-tighter text-white">Stock <span class="text-cyberlime">Control</span></h3>
                    </div>
                    <div class="hidden md:block text-right">
                        <div class="text-[10px] text-gray-500 font-mono uppercase tracking-widest">System ID</div>
                        <div class="text-xl font-black text-white tracking-widest">STC-9000</div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                    
                    <div class="lg:col-span-8 bg-[#0d1117] border border-gray-800 rounded-3xl p-8 relative shadow-2xl group">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cyberlime via-gray-800 to-gray-800 opacity-50"></div>

                        <div class="flex items-center justify-between mb-8 pb-6 border-b border-gray-800">
                            <h4 class="text-lg font-black text-white uppercase tracking-widest flex items-center gap-3">
                                <span class="w-2 h-8 bg-cyberlime rounded-sm"></span>
                                Unit Intake Form
                            </h4>
                            <span class="px-3 py-1 bg-cyberlime/10 border border-cyberlime/30 rounded text-[9px] font-bold text-cyberlime uppercase tracking-widest">New Entry Mode</span>
                        </div>
                        
                        <form action="process_add_car.php" method="POST" enctype="multipart/form-data" class="space-y-8">
                            
                            <div>
                                <h5 class="text-[9px] text-gray-500 font-bold uppercase tracking-[0.2em] mb-4">01 // Vehicle Identity</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="relative group">
                                        <select id="make-select" name="make" class="w-full bg-[#161b22] border border-gray-800 text-white text-xs px-4 py-3 rounded-lg focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all appearance-none font-bold uppercase cursor-pointer" required>
                                            <option value="" disabled selected>Select Manufacturer</option>
                                            <option value="Toyota">Toyota</option>
                                            <option value="Honda">Honda</option>
                                            <option value="Mitsubishi">Mitsubishi</option>
                                            <option value="Nissan">Nissan</option>
                                            <option value="Hyundai">Hyundai</option>
                                            <option value="Kia">Kia</option>
                                            <option value="Mazda">Mazda</option>
                                            <option value="Subaru">Subaru</option>
                                            <option value="Isuzu">Isuzu</option>
                                            <option value="Suzuki">Suzuki</option>
                                            <option value="Ford">Ford</option>
                                            <option value="Chevrolet">Chevrolet</option>
                                            <option value="Jeep">Jeep</option>
                                            <option value="BMW">BMW</option>
                                            <option value="Mercedes-Benz">Mercedes-Benz</option>
                                            <option value="Audi">Audi</option>
                                            <option value="Lexus">Lexus</option>
                                            <option value="Porsche">Porsche</option>
                                            <option value="Land Rover">Land Rover</option>
                                        </select>
                                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">▼</div>
                                    </div>

                                    <div class="relative group">
                                        <select id="model-select" name="model" class="w-full bg-[#161b22] border border-gray-800 text-white text-xs px-4 py-3 rounded-lg focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all appearance-none font-bold uppercase cursor-pointer" required>
                                            <option value="" disabled selected>Select Model</option>
                                        </select>
                                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">▼</div>
=======
    <section id="showroom" class="py-24 px-6 max-w-7xl mx-auto border-t border-gray-900/50">
        <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
            <div>
                <h2 class="text-cyberlime font-bold uppercase tracking-[0.3em] text-xs mb-3">Facundo Fleet</h2>
                <h3 class="text-4xl md:text-5xl font-black italic uppercase tracking-tighter text-white">Available <span class="text-cyberlime">Units</span></h3>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="fleet-grid">
            <?php if(empty($units)): ?>
                <div class="col-span-full text-center py-20 border border-dashed border-gray-800 rounded-3xl">
                    <p class="text-gray-500 uppercase tracking-widest text-xs">Inventory Currently Updating...</p>
                </div>
            <?php else: foreach($units as $unit): ?>
                <div class="unit-card rounded-2xl group bg-[#161b22] border border-gray-800 overflow-hidden relative transition-facundo hover:border-gray-600">
                    
                    <div class="absolute top-4 left-4 z-10">
                        <span class="bg-black/60 backdrop-blur-md <?php echo $unit['status'] == 'Reserved' ? 'text-orange-400 border-orange-400/50' : 'text-cyberlime border-cyberlime/50'; ?> text-[9px] font-bold px-3 py-1 rounded-full uppercase tracking-widest border">
                            <?php echo $unit['status']; ?>
                        </span>
                    </div>

                    <div class="relative aspect-[16/10] bg-black overflow-hidden">
                        <?php $imagePath = !empty($unit['image_path']) ? 'uploads/cars/' . $unit['image_path'] : 'assets/img/no-image.jpg'; ?>
                        <img src="<?php echo $imagePath; ?>" alt="Car" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#161b22] via-transparent to-transparent opacity-60"></div>
                    </div>

                    <div class="p-6">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="text-xl font-black tracking-tight group-hover:text-cyberlime transition-all uppercase text-white unit-title">
                                <?php echo htmlspecialchars($unit['make'] . ' ' . $unit['model']); ?>
                            </h4>
                            
                            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <div class="flex items-center gap-1 bg-black/40 border border-gray-700 rounded-lg p-1 relative z-50 backdrop-blur-md">
                                    <button onclick="window.openEditModal(<?php echo htmlspecialchars(json_encode($unit), ENT_QUOTES, 'UTF-8'); ?>)" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded transition-all" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </button>
                                    <button onclick="window.deleteUnit(<?php echo $unit['id']; ?>)" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-500/10 rounded transition-all" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-2 border-y border-gray-800/50 py-4 my-4 gap-4">
                            <div><span class="block text-[9px] text-gray-500 uppercase font-bold">Mileage</span><span class="font-bold text-sm text-white"><?php echo number_format($unit['mileage_km']); ?> <span class="text-cyberlime text-[10px]">km</span></span></div>
                            <div><span class="block text-[9px] text-gray-500 uppercase font-bold">Price</span><span class="text-lg font-black italic text-white unit-price">₱<?php echo number_format($unit['price_php']); ?></span></div>
                        </div>

                        <div class="flex gap-2 mt-6">
                            <?php if($unit['status'] !== 'Reserved'): ?>
                                <?php if(isset($_SESSION['user_id'])): ?>
                                    <button onclick="window.openInquiry(<?php echo $unit['id']; ?>, '<?php echo addslashes($unit['make'] . ' ' . $unit['model']); ?>')" class="flex-1 bg-cyberlime text-black p-3 rounded-lg font-black uppercase tracking-tighter text-[11px] hover:bg-white transition-all transform active:scale-95">Secure Unit</button>
                                <?php else: ?>
                                    <button onclick="window.openRegister()" class="flex-1 bg-cyberlime text-black p-3 rounded-lg font-black uppercase tracking-tighter text-[11px] hover:bg-white transition-all transform active:scale-95">Secure Unit</button>
                                <?php endif; ?>
                                <a href="https://wa.me/639123456789" target="_blank" class="bg-[#25D366] text-white p-3 rounded-lg hover:opacity-80 transition-all flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                </a>
                            <?php else: ?>
                                <button class="w-full bg-gray-800 text-gray-500 p-3 rounded-lg cursor-not-allowed flex items-center justify-center gap-2" disabled>
                                    <span class="font-black uppercase tracking-tighter text-[11px]">Unit Reserved</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>a
    </section>

    <?php if ($isAdmin): ?>
        <section id="stc" class="py-24 px-6 bg-[#0d1117] border-t border-gray-900">
            <div class="max-w-7xl mx-auto">
                <div class="mb-12"><h2 class="text-cyberlime font-bold uppercase tracking-[0.3em] text-xs mb-3">Internal Operations</h2><h3 class="text-4xl font-black italic uppercase tracking-tighter text-white">Stock <span class="text-cyberlime">Control</span> (STC)</h3></div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                    <div class="lg:col-span-2 p-8 rounded-2xl bg-[#161b22] border border-gray-800 shadow-2xl">
                        <h4 class="text-lg font-bold mb-6 text-white uppercase tracking-widest">Unit Intake Form</h4>
                        
                        <form action="process_add_car.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                            
                            <div class="w-full"><label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block">Brand</label><select id="make-select" name="make" class="input-facundo"></select></div>
                            <div class="w-full"><label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block">Model</label><select id="model-select" name="model" class="input-facundo"></select></div>

                            <div class="w-full"><label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block">Transmission</label>
                                <select id="transmission-select" name="transmission" class="input-facundo"><option value="Automatic">Automatic</option><option value="Manual">Manual</option></select>
                            </div>

                            <div class="w-full"><label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block">Fuel Type</label>
                                <select id="fuel-select" name="fuel" class="input-facundo"><option value="Gasoline">Gasoline</option><option value="Diesel">Diesel</option><option value="Hybrid">Hybrid</option><option value="Electric">Electric</option></select>
                            </div>

                            <div class="w-full"><label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block">Year</label><select id="year-select" name="year" class="input-facundo"><?php for($y = date('Y')+1; $y >= 1995; $y--) echo "<option value='$y'>$y</option>"; ?></select></div>
                            <div class="w-full"><label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block">Price (PHP)</label><input type="number" name="price" class="input-facundo" placeholder="0.00" required></div>
                            <div class="w-full"><label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block">Mileage (KM)</label><input type="number" name="mileage" class="input-facundo" placeholder="0" required></div>

                            <div class="w-full md:col-span-2">
                                <label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block tracking-widest">Unit Image</label>
                                <div class="relative w-full h-32 group">
                                    <input type="file" name="car_image" id="car_image_input" class="absolute inset-0 w-full h-full opacity-0 z-20 cursor-pointer" accept="image/*" required onchange="window.previewImage(this)">
                                    <div class="w-full h-full bg-[#0b0e14] border-2 border-dashed border-gray-800 rounded-xl flex flex-col items-center justify-center">
                                        <div id="upload-placeholder" class="text-center pointer-events-none"><p class="text-[10px] text-gray-500 font-bold uppercase">Click to Upload</p></div>
                                        <img id="image-preview" src="#" class="absolute inset-0 w-full h-full object-cover rounded-xl z-10 hidden pointer-events-none">
>>>>>>> origin/main
                                    </div>
                                </div>
                            </div>

<<<<<<< HEAD
                            <div>
                                <h5 class="text-[9px] text-gray-500 font-bold uppercase tracking-[0.2em] mb-4">02 // Technical Specs</h5>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <select id="year-select" name="year" class="bg-[#161b22] border border-gray-800 text-white text-xs px-3 py-3 rounded-lg focus:outline-none focus:border-cyberlime font-bold">
                                        <?php for($y = date('Y')+1; $y >= 1995; $y--) echo "<option value='$y'>$y</option>"; ?>
                                    </select>
                                    <select name="transmission" class="bg-[#161b22] border border-gray-800 text-white text-xs px-3 py-3 rounded-lg focus:outline-none focus:border-cyberlime font-bold">
                                        <option value="Automatic">Automatic</option>
                                        <option value="Manual">Manual</option>
                                    </select>
                                    <select name="fuel" class="bg-[#161b22] border border-gray-800 text-white text-xs px-3 py-3 rounded-lg focus:outline-none focus:border-cyberlime font-bold">
                                        <option value="Diesel">Diesel</option>
                                        <option value="Gasoline">Gasoline</option>
                                        <option value="Hybrid">Hybrid</option>
                                        <option value="Electric">Electric</option>
                                    </select>
                                    <div class="relative">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[9px] text-gray-500 font-bold">KM</span>
                                        <input type="number" name="mileage" class="w-full bg-[#161b22] border border-gray-800 text-white text-xs px-3 py-3 rounded-lg focus:outline-none focus:border-cyberlime font-bold" placeholder="Mileage">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                <div>
                                    <h5 class="text-[9px] text-gray-500 font-bold uppercase tracking-[0.2em] mb-4">03 // Valuation</h5>
                                    <div class="relative group">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-cyberlime font-bold text-lg">₱</span>
                                        <input type="number" name="price" class="w-full bg-[#161b22] border border-gray-800 text-white text-lg pl-10 pr-4 py-4 rounded-xl focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all font-bold font-mono placeholder:text-gray-700" placeholder="0.00" required>
                                    </div>
                                </div>

                                <div class="row-span-2">
                                    <h5 class="text-[9px] text-gray-500 font-bold uppercase tracking-[0.2em] mb-4">04 // Visual Asset</h5>
                                    <div class="relative w-full h-32 md:h-full group">
                                        <input type="file" name="car_image" id="car_image_input" class="absolute inset-0 w-full h-full opacity-0 z-20 cursor-pointer" accept="image/*" required onchange="window.previewImage(this)">
                                        
                                        <div class="w-full h-full bg-[#161b22] border-2 border-dashed border-gray-700 group-hover:border-cyberlime group-hover:bg-cyberlime/5 rounded-xl flex flex-col items-center justify-center transition-all duration-300">
                                            <div id="upload-placeholder" class="text-center pointer-events-none group-hover:-translate-y-1 transition-transform">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-600 group-hover:text-cyberlime mx-auto mb-2 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <p class="text-[9px] text-gray-500 font-bold uppercase group-hover:text-white transition-colors">Upload Image</p>
                                            </div>
                                            <img id="image-preview" src="#" class="absolute inset-0 w-full h-full object-cover rounded-xl z-10 hidden pointer-events-none p-1 bg-[#161b22]">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <button type="submit" class="w-full bg-white text-black font-black py-4 rounded-xl uppercase tracking-[0.2em] hover:bg-cyberlime transition-all hover:scale-[1.01] hover:shadow-[0_0_20px_rgba(204,255,0,0.4)] text-xs flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                Confirm Inventory Addition
                            </button>
                        </form>
                    </div>

                    <div class="lg:col-span-4 space-y-6">
                        <?php 
                            $total_val = 0; 
                            $total_cnt = 0;
                            try {
                                if(isset($pdo)) {
                                    $q = $pdo->query("SELECT SUM(price_php) as total_value, COUNT(*) as total_units FROM vehicles WHERE status != 'Sold'");
                                    if($q) { 
                                        $stats = $q->fetch(PDO::FETCH_ASSOC); 
                                        $total_val = $stats['total_value'] ?? 0; 
                                        $total_cnt = $stats['total_units'] ?? 0; 
                                    }
                                }
                            } catch (Exception $e) {}
                        ?>

                        <div class="bg-[#0d1117] border border-gray-800 p-8 rounded-3xl relative overflow-hidden group hover:border-cyberlime/50 transition-colors">
                            <div class="absolute right-0 top-0 p-3 opacity-10 group-hover:opacity-20 transition-opacity">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-cyberlime" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            
                            <h5 class="text-gray-500 font-black uppercase tracking-widest text-xs mb-4">Total Asset Value</h5>
                            <div class="text-4xl lg:text-5xl font-black text-white tracking-tighter mb-4">
                                ₱<?php echo number_format($total_val / 1000000, 1); ?>M
                            </div>
                            <div class="w-full bg-gray-800 h-1.5 rounded-full overflow-hidden">
                                <div class="h-full bg-cyberlime w-[70%] relative">
                                    <span class="absolute right-0 top-0 h-full w-2 bg-white animate-pulse"></span>
                                </div>
                            </div>
                            <p class="text-[9px] text-cyberlime font-mono mt-2 uppercase">Financial Target: 70%</p>
                        </div>

                        <div class="bg-[#161b22] border border-gray-800 p-8 rounded-3xl flex items-center justify-between group hover:border-gray-600 transition-colors">
                            <div>
                                <h5 class="text-gray-500 font-bold uppercase tracking-widest text-[10px] mb-1">Active Units</h5>
                                <div class="text-4xl font-black text-white"><?php echo number_format($total_cnt); ?></div>
                            </div>
                            <div class="h-12 w-12 rounded-full border-2 border-dashed border-gray-700 flex items-center justify-center text-gray-500 group-hover:text-white group-hover:border-white transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            </div>
                        </div>

                        <div class="bg-cyberlime/10 border border-cyberlime/20 p-6 rounded-2xl">
                            <div class="flex gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-cyberlime flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <p class="text-[10px] text-cyberlime leading-relaxed uppercase font-bold">
                                    Ensure all assets are verified before intake. High-resolution images required for showroom display.
                                </p>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </section>

        <section id="crm" class="py-24 px-6 bg-[#050505] border-t border-gray-900 relative overflow-hidden">
        
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-cyberlime/5 rounded-full blur-[120px] pointer-events-none"></div>

            <div class="max-w-7xl mx-auto relative z-10">
                
                <div class="mb-12 flex flex-row justify-between items-end">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="w-2 h-2 rounded-full bg-cyberlime animate-ping"></span>
                            <h2 class="text-cyberlime font-bold uppercase tracking-[0.3em] text-xs">Live Feed</h2>
                        </div>
                        <h3 class="text-4xl font-black italic uppercase tracking-tighter text-white">
                            Command <span class="text-cyberlime">Center</span>
                        </h3>
                    </div>
                    
                    <button onclick="window.openStaffModal()" class="group bg-[#0d1117] border border-gray-800 hover:border-cyberlime text-white px-6 py-3 rounded-xl flex items-center gap-3 transition-all duration-300">
                        <div class="bg-gray-800 group-hover:bg-cyberlime group-hover:text-black p-1.5 rounded-lg transition-colors">
=======
                            <button type="submit" class="md:col-span-2 bg-cyberlime text-black font-black py-4 rounded-xl uppercase tracking-widest hover:bg-white transition-all text-xs">Add to Inventory</button>
                        </form>
                    </div>

                    <div class="space-y-6">
                        <?php 
                            $total_val = 0; $total_cnt = 0;
                            try {
                                if(isset($pdo)) {
                                    $q = $pdo->query("SELECT SUM(price_php) as total_value, COUNT(*) as total_units FROM vehicles WHERE status != 'Sold'");
                                    if($q) { $stats = $q->fetch(PDO::FETCH_ASSOC); $total_val = $stats['total_value'] ?? 0; $total_cnt = $stats['total_units'] ?? 0; }
                                }
                            } catch (Exception $e) {}
                        ?>
                        <div class="bg-[#161b22] p-8 rounded-xl border border-gray-800"><span class="text-gray-500 text-[10px] font-bold uppercase tracking-[0.2em]">Inventory Value</span><div class="text-3xl font-black mt-2 text-white">₱<?php echo number_format($total_val); ?></div></div>
                        <div class="bg-[#161b22] p-8 rounded-xl border border-gray-800"><span class="text-gray-500 text-[10px] font-bold uppercase tracking-[0.2em]">Active Units</span><div class="text-3xl font-black mt-2 text-white"><?php echo number_format($total_cnt); ?></div></div>
                    </div>
                </div>
            </div>
        </section>
    
        <section id="crm" class="py-24 px-6 bg-black border-t border-gray-900 relative">
            <div class="max-w-7xl mx-auto">
                
                <div class="mb-12 flex flex-row justify-between items-end">
                    <div>
                        <h2 class="text-cyberlime font-bold uppercase tracking-[0.3em] text-xs mb-3">Client Relations</h2>
                        <h3 class="text-4xl font-black italic uppercase tracking-tighter text-white">Lead <span class="text-cyberlime">Pipeline</span></h3>
                    </div>
                    <button onclick="window.openStaffModal()" class="bg-[#161b22] border border-gray-800 text-white px-5 py-3 rounded-xl flex items-center gap-2 hover:border-cyberlime hover:text-cyberlime transition-all group">
                        <div class="bg-gray-800 p-1 rounded group-hover:bg-cyberlime group-hover:text-black transition-colors">
>>>>>>> origin/main
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0 1 1 0 002 0z" />
                            </svg>
                        </div>
<<<<<<< HEAD
                        <span class="text-[10px] font-bold uppercase tracking-widest group-hover:text-cyberlime transition-colors">Recruit Officer</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <div class="lg:col-span-8 bg-[#0d1117] border border-gray-800 rounded-3xl overflow-hidden relative group">
                        <div class="bg-[#161b22] px-8 py-5 border-b border-gray-800 flex justify-between items-center">
                            <div>
                                <h4 class="text-white font-black uppercase tracking-widest text-xs">Intercepted Comms</h4>
                                <p class="text-[9px] text-gray-500 uppercase font-mono mt-1">Protocol: Secure</p>
                            </div>
                            <div class="flex gap-1">
                                <span class="w-1.5 h-1.5 bg-gray-700 rounded-full"></span>
                                <span class="w-1.5 h-1.5 bg-gray-700 rounded-full"></span>
                                <span class="w-1.5 h-1.5 bg-cyberlime rounded-full"></span>
                            </div>
                        </div>

                        <div class="p-6 space-y-4 max-h-[450px] overflow-y-auto pr-4 custom-scrollbar">
                            <?php
                                try {
                                    if(isset($pdo)) {
                                        $sql = "SELECT m.*, u.first_name, u.last_name FROM messages m LEFT JOIN users u ON m.sender_id = u.id ORDER BY m.created_at DESC LIMIT 20";
=======
                        <span class="text-[10px] font-bold uppercase tracking-widest">Add Staff</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                    
                    <div class="lg:col-span-2 bg-[#161b22] border border-gray-800 rounded-2xl p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h4 class="text-white font-black uppercase tracking-widest text-sm">Direct Comms History</h4>
                                <p class="text-[9px] text-gray-500 uppercase mt-1">Recent Transmissions</p>
                            </div>
                            <span class="text-cyberlime text-[8px] font-black uppercase animate-pulse flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-cyberlime rounded-full"></span> System Online
                            </span>
                        </div>

                        <div class="space-y-4 max-h-[350px] overflow-y-auto pr-4 custom-scrollbar">
                            <?php
                                try {
                                    // Safe Fetch: Checks if PDO exists and query succeeds
                                    if(isset($pdo)) {
                                        $sql = "SELECT m.*, u.first_name, u.last_name 
                                                FROM messages m 
                                                LEFT JOIN users u ON m.sender_id = u.id 
                                                ORDER BY m.created_at DESC 
                                                LIMIT 20";
                                        
>>>>>>> origin/main
                                        $q2 = $pdo->query($sql);
                                        $msgs = $q2 ? $q2->fetchAll(PDO::FETCH_ASSOC) : [];

                                        if(empty($msgs)): 
                            ?>
<<<<<<< HEAD
                                        <div class="flex flex-col items-center justify-center py-20 opacity-50">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                            <p class="text-gray-500 text-[10px] uppercase font-bold tracking-[0.2em]">Silence on all frequencies</p>
                                        </div>
                            <?php 
                                        else: 
                                            foreach($msgs as $m):
                                                $isSystem = ($m['sender_id'] == 0);
                                                $name = $isSystem ? 'SYSTEM ADMIN' : htmlspecialchars($m['first_name'] . ' ' . $m['last_name']);
                                                $initial = $isSystem ? 'S' : substr($name, 0, 1);
                                                $bgClass = $isSystem ? 'bg-cyberlime/10 border-cyberlime/30' : 'bg-[#161b22] border-gray-800';
                                                $textClass = $isSystem ? 'text-cyberlime' : 'text-gray-300';
                            ?>
                                        <div class="flex gap-4 p-4 rounded-xl border <?php echo $bgClass; ?> hover:bg-white/[0.02] transition-all group/card">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 rounded-lg bg-black border border-gray-700 flex items-center justify-center text-xs font-black text-gray-400 font-mono">
                                                    <?php echo $initial; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="flex-1 min-w-0">
                                                <div class="flex justify-between items-start mb-1">
                                                    <span class="text-[10px] font-black uppercase tracking-wider text-white truncate"><?php echo $name; ?></span>
                                                    <span class="text-[8px] text-gray-600 font-mono whitespace-nowrap"><?php echo date('M d, H:i', strtotime($m['created_at'])); ?></span>
                                                </div>
                                                <p class="text-xs leading-relaxed <?php echo $textClass; ?> break-words"><?php echo htmlspecialchars($m['message']); ?></p>
                                                
                                                <?php if(!$isSystem): ?>
                                                    <div class="mt-3 flex justify-end opacity-0 group-hover/card:opacity-100 transition-opacity">
                                                        <button onclick="window.openAdminReply(<?php echo $m['sender_id']; ?>, '<?php echo htmlspecialchars($name, ENT_QUOTES); ?>')" class="flex items-center gap-2 text-[9px] font-bold uppercase tracking-widest text-gray-500 hover:text-cyberlime transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                                                            Reply to User
=======
                                            <div class="text-center py-10 border border-dashed border-gray-800 rounded-xl">
                                                <p class="text-gray-600 text-[10px] uppercase font-bold tracking-[0.2em]">No transmissions intercepted.</p>
                                            </div>
                            <?php 
                                        else: 
                                            foreach($msgs as $m):
                                                $name = ($m['sender_id'] == 0) ? 'SYSTEM ADMIN' : htmlspecialchars($m['first_name'] . ' ' . $m['last_name']);
                            ?>
                                            <div class="p-4 bg-[#0d1117] border-l-2 border-cyberlime rounded-r-xl group hover:bg-white/[0.01] transition-all relative">
                                                <div class="flex justify-between mb-2">
                                                    <span class="text-[9px] font-black text-cyberlime uppercase tracking-tighter">
                                                        From: <?php echo $name; ?>
                                                    </span>
                                                    <span class="text-[8px] text-gray-600 font-mono">
                                                        <?php echo date('H:i | M d', strtotime($m['created_at'])); ?>
                                                    </span>
                                                </div>
                                                <p class="text-gray-300 text-xs leading-relaxed mb-3">
                                                    <?php echo htmlspecialchars($m['message']); ?>
                                                </p>

                                                <?php if($m['sender_id'] != 0): ?>
                                                    <div class="flex justify-end">
                                                        <button onclick="window.openAdminReply(<?php echo $m['sender_id']; ?>, '<?php echo addslashes($name); ?>')" 
                                                                class="opacity-0 group-hover:opacity-100 transition-opacity bg-cyberlime text-black px-3 py-1 rounded text-[9px] font-bold uppercase tracking-widest hover:bg-white flex items-center gap-1">
                                                            Reply <span>↵</span>
>>>>>>> origin/main
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
<<<<<<< HEAD
                                        </div>
                            <?php 
                                            endforeach; 
                                        endif; 
                                    }
                                } catch (Exception $e) {}
=======
                            <?php 
                                            endforeach; 
                                        endif;
                                    }
                                } catch (Exception $e) {
                                    // Silent fail to prevent crash
                                }
>>>>>>> origin/main
                            ?>
                        </div>
                    </div>

<<<<<<< HEAD
                    <div class="lg:col-span-4 flex flex-col gap-6">
                        
                        <div class="flex-1 bg-cyberlime p-1 rounded-3xl">
                            <div class="h-full bg-black/20 backdrop-blur-sm rounded-[20px] p-8 flex flex-col justify-between relative overflow-hidden">
                                <div class="absolute -right-10 -top-10 text-black/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-40 w-40" fill="currentColor" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                                </div>
                                
                                <div>
                                    <h5 class="text-black font-black uppercase tracking-widest text-xs mb-2">Total Transmissions</h5>
                                    <div class="text-7xl font-black text-black tracking-tighter"><?php echo count($msgs ?? []); ?></div>
                                </div>
                                
                                <div class="mt-8">
                                    <div class="w-full bg-black/10 h-1 rounded-full mb-2">
                                        <div class="w-[75%] h-full bg-black rounded-full"></div>
                                    </div>
                                    <p class="text-[9px] font-bold text-black uppercase tracking-widest opacity-60">Database Capacity: Stable</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-[#161b22] border border-gray-800 p-8 rounded-3xl relative overflow-hidden group">
                            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
                            <div class="relative z-10 flex items-center justify-between">
                                <div>
                                    <h5 class="text-white font-bold uppercase text-xs tracking-widest mb-1">System Status</h5>
                                    <p class="text-[10px] text-emerald-500 font-mono uppercase tracking-wider">All Systems Operational</p>
                                </div>
                                <div class="w-10 h-10 rounded-full border border-gray-700 flex items-center justify-center">
                                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                                </div>
                            </div>
                        </div>

=======
                    <div class="bg-cyberlime p-8 rounded-2xl flex flex-col justify-center items-center text-center shadow-xl shadow-cyberlime/10">
                        <div class="text-black">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                            <h5 class="text-xs font-black uppercase tracking-widest">Active Transmissions</h5>
                            <div class="text-6xl font-black my-2"><?php echo count($msgs ?? []); ?></div>
                            <p class="text-[9px] font-bold uppercase opacity-60">Total Fleet Inquiries</p>
                        </div>
>>>>>>> origin/main
                    </div>

                </div>
            </div>
        </section>
    <?php endif; ?>

<<<<<<< HEAD
     <section id="finance" class="py-24 px-6 bg-black border-t border-gray-900 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-cyberlime/5 rounded-full blur-[120px] pointer-events-none"></div>

            <div class="max-w-7xl mx-auto relative z-10">
                <div class="mb-16 text-center">
                    <h2 class="text-cyberlime font-bold uppercase tracking-[0.3em] text-xs mb-4">Financial Services</h2>
                    
                    <h3 class="text-4xl md:text-5xl font-black italic uppercase tracking-tighter text-white">
                        Payment <span class="text-cyberlime">Solutions</span>
                    </h3>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                    
                    <div class="lg:col-span-7 bg-[#161b22] border border-gray-800 p-8 md:p-10 rounded-3xl shadow-2xl flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-8">
                                <h4 class="text-xl font-black italic uppercase text-white">Loan Calculator</h4>
                                <span class="bg-gray-800 text-gray-400 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest">Estimate Only</span>
                            </div>
                            
                            <div class="space-y-8">
                                <div class="group">
                                    <label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block group-focus-within:text-cyberlime transition-colors">Vehicle Price (PHP)</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">₱</span>
                                        <input type="number" id="calc_price" oninput="window.calculateLoan()" placeholder="e.g. 1500000" class="w-full bg-[#0d1117] border border-gray-700 text-white pl-8 pr-4 py-4 rounded-xl font-bold text-lg focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all">
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between mb-3">
                                        <label class="text-[9px] text-gray-500 uppercase font-bold">Down Payment %</label>
                                        <div class="text-right">
                                            <span id="dp_percent_display" class="text-cyberlime font-bold text-lg">20%</span>
                                            <span class="text-gray-600 text-xs font-bold mx-2">/</span>
                                            <span id="dp_amount_display" class="text-white font-bold text-xs">₱0</span>
                                            <span class="text-[9px] text-gray-600 uppercase font-bold ml-1">Cash Out</span>
                                        </div>
                                    </div>
                                    <input type="range" id="calc_dp" min="20" max="50" value="20" step="5" oninput="window.calculateLoan()" class="w-full h-3 bg-gray-800 rounded-lg appearance-none cursor-pointer accent-[#ccff00]">
                                    <div class="flex justify-between mt-2 text-[9px] text-gray-600 font-mono uppercase">
                                        <span>Min: 20%</span>
                                        <span>Max: 50%</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block">Payment Term</label>
                                    <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                                        <button onclick="setTerm(12)" class="term-btn bg-[#0d1117] border border-gray-700 hover:border-cyberlime text-gray-400 hover:text-white py-3 rounded-lg text-xs font-bold transition-all" data-value="12">12 Mo</button>
                                        <button onclick="setTerm(24)" class="term-btn bg-[#0d1117] border border-gray-700 hover:border-cyberlime text-gray-400 hover:text-white py-3 rounded-lg text-xs font-bold transition-all" data-value="24">24 Mo</button>
                                        <button onclick="setTerm(36)" class="term-btn bg-[#0d1117] border border-gray-700 hover:border-cyberlime text-gray-400 hover:text-white py-3 rounded-lg text-xs font-bold transition-all" data-value="36">36 Mo</button>
                                        <button onclick="setTerm(48)" class="term-btn bg-[#0d1117] border border-gray-700 hover:border-cyberlime text-gray-400 hover:text-white py-3 rounded-lg text-xs font-bold transition-all" data-value="48">48 Mo</button>
                                        <button onclick="setTerm(60)" class="term-btn bg-cyberlime text-black border border-cyberlime py-3 rounded-lg text-xs font-bold transition-all shadow-[0_0_15px_rgba(204,255,0,0.3)]" data-value="60">60 Mo</button>
                                    </div>
                                    <input type="hidden" id="calc_term" value="60">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-5 flex flex-col gap-6">
                        <div class="bg-gradient-to-br from-[#1c2128] to-[#0d1117] border border-gray-800 p-10 rounded-3xl text-center relative overflow-hidden group">
                            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
                            
                            <span class="text-[10px] text-gray-500 uppercase font-bold tracking-[0.3em] relative z-10">Estimated Monthly</span>
                            <div id="monthly_result" class="text-5xl md:text-6xl font-black text-white italic my-6 tracking-tighter relative z-10 drop-shadow-[0_0_10px_rgba(255,255,255,0.1)]">₱0.00</div>
                            
                            <button onclick="window.location.href='#contact'" class="w-full bg-white hover:bg-cyberlime text-black font-black py-4 rounded-xl uppercase tracking-widest transition-all text-xs relative z-10 shadow-xl hover:shadow-2xl hover:scale-[1.02]">
                                Apply for Finance
                            </button>
                            <p class="text-[9px] text-gray-600 mt-4 relative z-10">*Subject to bank approval. Rates may vary.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-[#161b22] border border-gray-800 p-6 rounded-2xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-cyberlime mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <h5 class="text-white font-bold uppercase text-xs mb-1">Fast Approval</h5>
                                <p class="text-[10px] text-gray-500">24-48 hour processing</p>
                            </div>
                            <div class="bg-[#161b22] border border-gray-800 p-6 rounded-2xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-cyberlime mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                                <h5 class="text-white font-bold uppercase text-xs mb-1">Low Rates</h5>
                                <p class="text-[10px] text-gray-500">From 0.56% monthly</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

    <div id="login-overlay" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/90 backdrop-blur-sm hidden transition-all duration-300">
        
        <div class="bg-[#0d1117] border border-gray-800 rounded-2xl w-full max-w-sm relative overflow-hidden shadow-2xl group">
            
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-1 bg-cyberlime shadow-[0_0_20px_rgba(204,255,0,0.5)]"></div>

            <button onclick="window.closeLogin()" class="absolute top-4 right-4 text-gray-500 hover:text-white transition-colors p-2 z-10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="p-8 md:p-10 relative z-10">
                <div class="text-center mb-8">
                    <div class="w-12 h-12 bg-[#161b22] border border-gray-800 rounded-full mx-auto flex items-center justify-center text-cyberlime mb-4 shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h2 id="modal-title" class="text-white text-xs font-black uppercase tracking-[0.3em]">System Access</h2>
                    <p class="text-[9px] text-gray-500 uppercase tracking-widest mt-1">Identify Yourself</p>
                </div>

                <form action="db_connection/login_handler.php" method="POST" class="space-y-4">
                    <input type="hidden" name="auth_mode" id="auth_mode" value="login">
                    
                    <div id="registration-fields" class="hidden space-y-4 animate-in-fade">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="relative group">
                                <input type="text" name="first_name" id="reg_first" placeholder="First Name" class="w-full bg-[#161b22] border border-gray-800 text-white text-xs px-4 py-3 rounded-lg focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all placeholder:text-gray-600">
                            </div>
                            <div class="relative group">
                                <input type="text" name="last_name" id="reg_last" placeholder="Last Name" class="w-full bg-[#161b22] border border-gray-800 text-white text-xs px-4 py-3 rounded-lg focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all placeholder:text-gray-600">
                            </div>
                        </div>
                        <div class="relative group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-600 group-focus-within:text-cyberlime transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            <input type="text" name="phone" placeholder="Mobile Number" class="w-full bg-[#161b22] border border-gray-800 text-white text-xs pl-10 pr-4 py-3 rounded-lg focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all placeholder:text-gray-600">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="relative group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-600 group-focus-within:text-cyberlime transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" /></svg>
                            <input type="email" name="email" placeholder="Email Address" class="w-full bg-[#161b22] border border-gray-800 text-white text-xs pl-10 pr-4 py-3 rounded-lg focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all placeholder:text-gray-600" required>
                        </div>
                        <div class="relative group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-600 group-focus-within:text-cyberlime transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            <input type="password" name="password" placeholder="Passcode" class="w-full bg-[#161b22] border border-gray-800 text-white text-xs pl-10 pr-4 py-3 rounded-lg focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all placeholder:text-gray-600" required>
                        </div>
                    </div>

                    <button type="submit" id="submit-btn" class="w-full bg-cyberlime text-black font-black py-4 rounded-xl uppercase text-xs tracking-[0.2em] hover:bg-white hover:shadow-[0_0_20px_rgba(255,255,255,0.3)] transition-all transform active:scale-[0.98] mt-2">
                        Initialize Session
                    </button>
                </form>

                <div class="mt-6 text-center border-t border-gray-800 pt-6">
                    <button onclick="window.toggleRegister()" class="text-[9px] font-bold uppercase tracking-widest text-gray-500 hover:text-cyberlime transition-colors">
                        <span id="toggle-text">New Client? Create Profile</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 z-[500] bg-black/90 hidden items-center justify-center backdrop-blur-sm p-4 transition-all duration-300">
        
        <div class="bg-[#0d1117] border border-gray-800 w-full max-w-lg rounded-2xl relative overflow-hidden shadow-2xl">
            
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-cyberlime via-white to-cyberlime opacity-80"></div>

            <button onclick="window.closeEditModal()" class="absolute top-5 right-5 text-gray-500 hover:text-white transition-colors p-2 z-10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>

            <div class="p-8">
                <div class="mb-8 border-b border-gray-800 pb-6">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="bg-cyberlime/10 border border-cyberlime/30 p-2 rounded-lg text-cyberlime">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-black italic uppercase text-xl tracking-tighter">Modify <span class="text-cyberlime">Asset</span></h3>
                            <p class="text-[9px] text-gray-500 uppercase font-bold tracking-widest">Update Inventory Record</p>
                        </div>
                    </div>
                </div>

                <form action="process_edit_car.php" method="POST" class="space-y-6">
                    <input type="hidden" name="car_id" id="edit_id">
                    
                    <div class="space-y-2">
                        <label class="text-[9px] text-gray-500 uppercase font-bold tracking-widest ml-1">Vehicle Identity</label>
                        <div class="flex gap-3">
                            <div class="relative group flex-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-600 font-bold text-[10px]">MAKE</span>
                                <input type="text" name="make" id="edit_make" class="w-full bg-[#161b22] border border-gray-800 text-white text-xs pl-12 pr-4 py-3 rounded-lg focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all font-bold uppercase">
                            </div>
                            <div class="relative group flex-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-600 font-bold text-[10px]">MODEL</span>
                                <input type="text" name="model" id="edit_model" class="w-full bg-[#161b22] border border-gray-800 text-white text-xs pl-14 pr-4 py-3 rounded-lg focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all font-bold uppercase">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] text-gray-500 uppercase font-bold tracking-widest ml-1">Valuation & Metrics</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="relative group">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-bold">₱</span>
                                <input type="number" name="price" id="edit_price" class="w-full bg-[#161b22] border border-gray-800 text-white text-xs pl-8 pr-4 py-3 rounded-lg focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all font-mono" placeholder="0.00">
                            </div>
                            <div class="relative group">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[9px] text-gray-600 font-bold">KM</span>
                                <input type="number" name="mileage" id="edit_mileage" class="w-full bg-[#161b22] border border-gray-800 text-white text-xs pl-4 pr-10 py-3 rounded-lg focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all font-mono" placeholder="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 pt-4 border-t border-gray-800">
                        <button type="submit" class="flex-1 bg-cyberlime text-black font-black py-4 rounded-xl uppercase tracking-[0.2em] hover:bg-white hover:shadow-[0_0_15px_rgba(255,255,255,0.3)] transition-all text-[10px] flex items-center justify-center gap-2">
                            <span>Save Changes</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </button>
                        
                        <button type="button" id="modal_delete_btn" class="group bg-red-500/5 border border-red-500/30 text-red-500 px-5 rounded-xl hover:bg-red-500 hover:text-white transition-all flex items-center justify-center" title="Decommission Unit">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div id="staffModal" class="fixed inset-0 z-[700] bg-black/90 hidden items-center justify-center backdrop-blur-sm transition-all duration-300">
        
        <div class="bg-[#0d1117] border border-gray-800 p-8 rounded-2xl w-full max-w-md shadow-2xl relative overflow-hidden">
            
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-cyberlime to-transparent opacity-50"></div>

            <button onclick="window.closeStaffModal()" class="absolute top-4 right-4 text-gray-500 hover:text-white transition-colors p-2 z-10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>

            <div class="text-center mb-8">
                <div class="w-14 h-14 bg-[#161b22] border border-gray-800 rounded-2xl mx-auto flex items-center justify-center text-cyberlime mb-4 shadow-[0_0_15px_rgba(204,255,0,0.1)] rotate-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884-.95 2-1 2h3c-.5 0-1-1.116-1-2z" />
                    </svg>
                </div>
                <h3 class="text-white text-sm font-black uppercase tracking-[0.2em]">Personnel Onboarding</h3>
                <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 bg-cyberlime/10 border border-cyberlime/20 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyberlime animate-pulse"></span>
                    <span class="text-[9px] font-bold text-cyberlime uppercase tracking-wider">Granting Admin Access</span>
                </div>
            </div>

            <form id="add-staff-form" class="space-y-4">
                
                <div class="space-y-3">
                    <div class="relative group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-600 group-focus-within:text-cyberlime transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        <input type="text" name="first_name" placeholder="First Name (Officer)" required class="w-full bg-[#161b22] border border-gray-800 text-white text-xs pl-10 pr-4 py-3 rounded-lg focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all placeholder:text-gray-600">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" name="middle_name" placeholder="Middle" class="w-full bg-[#161b22] border border-gray-800 text-white text-xs px-4 py-3 rounded-lg focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all placeholder:text-gray-600">
                        <input type="text" name="last_name" placeholder="Last Name" required class="w-full bg-[#161b22] border border-gray-800 text-white text-xs px-4 py-3 rounded-lg focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all placeholder:text-gray-600">
                    </div>
                </div>

                <div class="space-y-3 pt-2">
                    <div class="relative group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-600 group-focus-within:text-cyberlime transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        <input type="email" name="email" placeholder="Corporate Email" required class="w-full bg-[#161b22] border border-gray-800 text-white text-xs pl-10 pr-4 py-3 rounded-lg focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all placeholder:text-gray-600">
                    </div>
                    
                    <div class="relative group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-600 group-focus-within:text-cyberlime transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 19l-1 1-1 1-2-2-5 5L3 12l9-9 9 9z" /></svg>
                        <input type="password" name="password" placeholder="Assign Passcode" required class="w-full bg-[#161b22] border border-gray-800 text-white text-xs pl-10 pr-4 py-3 rounded-lg focus:outline-none focus:border-cyberlime focus:ring-1 focus:ring-cyberlime transition-all placeholder:text-gray-600">
                    </div>
                </div>

                <button type="submit" class="w-full bg-cyberlime text-black font-black py-4 rounded-xl uppercase text-xs tracking-[0.2em] hover:bg-white hover:shadow-[0_0_20px_rgba(255,255,255,0.3)] transition-all transform active:scale-[0.98] mt-6 flex items-center justify-center gap-2 group">
                    <span>Authorize Officer</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </button>
=======
    <div id="login-overlay" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/95 backdrop-blur-md hidden">
        <div class="bg-[#161b22] border border-gray-800 p-10 rounded-3xl max-w-sm w-full relative">
            <h2 id="modal-title" class="text-white text-sm font-bold uppercase tracking-[0.2em] mb-8 text-center">Access Portal</h2>
            <form action="db_connection/login_handler.php" method="POST">
                <input type="hidden" name="auth_mode" id="auth_mode" value="login">
                <div id="registration-fields" class="hidden space-y-4 mb-4">
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="first_name" id="reg_first" placeholder="First Name *" class="input-facundo text-xs">
                        <input type="text" name="last_name" id="reg_last" placeholder="Last Name *" class="input-facundo text-xs">
                    </div>
                    <input type="text" name="middle_name" placeholder="Middle (Opt)" class="input-facundo text-xs">
                    <input type="text" name="phone" placeholder="Phone" class="input-facundo text-xs">
                </div>
                <div class="space-y-4">
                    <input type="email" name="email" placeholder="Email" class="input-facundo text-xs" required>
                    <input type="password" name="password" placeholder="Password" class="input-facundo text-xs" required>
                </div>
                <button type="submit" id="submit-btn" class="w-full mt-6 bg-cyberlime text-black font-black py-4 rounded-xl uppercase text-xs tracking-widest hover:bg-white transition-all">Sign In</button>
            </form>
            <button onclick="window.toggleRegister()" class="mt-6 text-[9px] text-gray-500 uppercase tracking-widest hover:text-cyberlime block w-full text-center">New Buyer? Join</button>
            <button onclick="window.closeLogin()" class="mt-4 text-gray-500 text-[9px] uppercase tracking-widest hover:text-white block w-full text-center">Cancel</button>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 bg-black/95 backdrop-blur-md z-[500] hidden items-center justify-center p-4">
        <div class="bg-[#161b22] border border-gray-800 w-full max-w-xl rounded-3xl p-8 relative">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-white font-black italic uppercase text-2xl">Edit <span class="text-cyberlime">Unit</span></h3>
                <button onclick="window.closeEditModal()" class="text-gray-500 hover:text-white text-2xl">&times;</button>
            </div>
            <form action="process_edit_car.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="car_id" id="edit_id">
                <div class="md:col-span-2 flex gap-2">
                    <input type="text" name="make" id="edit_make" class="input-facundo flex-1" placeholder="Make">
                    <input type="text" name="model" id="edit_model" class="input-facundo flex-1" placeholder="Model">
                </div>
                <input type="number" name="price" id="edit_price" class="input-facundo" placeholder="Price">
                <input type="number" name="mileage" id="edit_mileage" class="input-facundo" placeholder="Mileage">
                
                <div class="md:col-span-2 flex gap-3 mt-4">
                    <button type="submit" class="flex-1 bg-cyberlime text-black font-black py-4 rounded-xl uppercase tracking-widest hover:bg-white transition-all text-xs">Save Changes</button>
                    <button type="button" id="modal_delete_btn" class="bg-red-500/10 border border-red-500 text-red-500 px-6 rounded-xl hover:bg-red-500 hover:text-white transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="staffModal" class="fixed inset-0 z-[700] bg-black/90 hidden items-center justify-center backdrop-blur-sm">
        <div class="bg-[#161b22] border border-gray-800 p-8 rounded-2xl w-full max-w-md shadow-2xl relative">
            <button onclick="window.closeStaffModal()" class="absolute top-4 right-4 text-gray-500 hover:text-white">✕</button>
            <h3 class="text-white text-xl font-black italic uppercase tracking-tighter mb-6">Recruit <span class="text-cyberlime">Officer</span></h3>
            <form id="add-staff-form" class="space-y-4">
                <input type="text" name="first_name" placeholder="First Name" required class="input-facundo">
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="middle_name" placeholder="Middle" class="input-facundo">
                    <input type="text" name="last_name" placeholder="Last Name" required class="input-facundo">
                </div>
                <input type="email" name="email" placeholder="Email" required class="input-facundo">
                <input type="password" name="password" placeholder="Password" required class="input-facundo">
                <button type="submit" class="w-full bg-cyberlime text-black font-black py-4 rounded-xl uppercase text-xs tracking-[0.2em] hover:bg-white transition-all mt-4">Grant Access</button>
>>>>>>> origin/main
            </form>
        </div>
    </div>

    <div id="adminReplyModal" class="fixed inset-0 z-[600] bg-black/90 hidden items-center justify-center backdrop-blur-sm">
        <div class="bg-[#161b22] border border-gray-800 p-6 rounded-2xl w-full max-w-md shadow-2xl relative">
            <button onclick="window.closeAdminReply()" class="absolute top-4 right-4 text-gray-500 hover:text-white">✕</button>
            <h3 class="text-white text-sm font-bold uppercase tracking-widest mb-1">Transmitting To:</h3>
            <p id="replyTargetName" class="text-cyberlime font-black text-xl italic mb-6">User</p>
<<<<<<< HEAD
            
            <form id="admin-reply-form" class="space-y-4">
                <input type="hidden" id="reply_receiver_id" name="receiver_id">
                
                <textarea 
                    id="reply_message" 
                    name="message" 
                    rows="4" 
                    class="input-facundo resize-none" 
                    placeholder="Type response..." 
                    required></textarea>
                
=======
            <form id="admin-reply-form" class="space-y-4">
                <input type="hidden" id="reply_receiver_id" name="receiver_id">
                <textarea id="reply_message" rows="4" class="input-facundo resize-none" placeholder="Type response..." required></textarea>
>>>>>>> origin/main
                <button type="submit" class="w-full bg-cyberlime text-black font-black py-3 rounded-xl uppercase text-[10px] tracking-widest hover:bg-white transition-all">Send Transmission</button>
            </form>
        </div>
    </div>

    <div id="inquiryModal" class="fixed inset-0 z-[150] bg-black/90 backdrop-blur-sm hidden flex items-center justify-center p-6">
        <div class="bg-[#161b22] border border-gray-800 w-full max-w-md rounded-2xl p-8 relative">
            <button onclick="window.closeInquiry()" class="absolute top-4 right-4 text-gray-500 hover:text-white">✕</button>
            <h3 class="text-2xl font-black text-white italic uppercase tracking-tighter mb-1">Secure <span class="text-cyberlime">Unit</span></h3>
            <p id="unitNameDisplay" class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-6 border-b border-gray-800 pb-4"></p>
            <form action="process_inquiry.php" method="POST" class="space-y-4">
                <input type="hidden" name="vehicle_id" id="modal_vehicle_id">
                <input type="text" name="client_name" class="input-facundo" placeholder="Full Name" required>
                <input type="text" name="client_phone" class="input-facundo" placeholder="Phone Number" required>
                <button type="submit" class="w-full bg-cyberlime text-black font-black py-4 rounded-xl uppercase tracking-widest hover:bg-white transition-all text-xs mt-4">Submit Inquiry</button>
            </form>
        </div>
    </div>

    <?php if($isLoggedIn): ?>
<<<<<<< HEAD
        <div id="chat-widget" class="fixed bottom-6 right-6 z-[9000] flex flex-col items-end">
        
            <div id="chat-window" class="hidden w-80 sm:w-96 h-[500px] bg-[#0d1117]/95 backdrop-blur-xl border border-gray-800 rounded-2xl shadow-2xl flex flex-col overflow-hidden mb-4 transition-all duration-300 origin-bottom-right animate-in-fade">
                
                <div class="bg-[#161b22] p-4 border-b border-gray-800 flex justify-between items-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-cyberlime via-transparent to-transparent opacity-50"></div>
                    
                    <div class="flex items-center gap-3">
                        <div class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </div>
                        <div>
                            <h4 class="text-white text-[10px] font-black uppercase tracking-[0.2em] leading-none">Secure Uplink</h4>
                            <span class="text-[8px] text-gray-500 font-mono uppercase tracking-wider">Support Online</span>
                        </div>
                    </div>
                    
                    <button onclick="window.toggleChat()" class="text-gray-500 hover:text-white transition-colors p-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div id="message-display" class="flex-1 overflow-y-auto p-4 space-y-4 bg-black/50 custom-scrollbar relative">
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-5 pointer-events-none"></div>
                    </div>

                <div class="p-3 bg-[#161b22] border-t border-gray-800">
                    <form id="chat-form" class="flex items-center gap-2 bg-[#0d1117] border border-gray-700 rounded-xl px-2 py-2 focus-within:border-cyberlime focus-within:ring-1 focus-within:ring-cyberlime/50 transition-all">
                        <input type="text" id="chat-input" placeholder="Type transmission..." autocomplete="off" class="flex-1 bg-transparent border-none text-xs text-white placeholder-gray-600 focus:ring-0 px-2 font-medium">
                        <button type="submit" class="bg-cyberlime text-black p-2 rounded-lg hover:bg-white transition-colors shadow-[0_0_10px_rgba(204,255,0,0.2)]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            <button onclick="window.toggleChat()" class="group relative bg-cyberlime text-black w-14 h-14 rounded-full flex items-center justify-center shadow-[0_0_20px_rgba(204,255,0,0.3)] hover:shadow-[0_0_30px_rgba(204,255,0,0.6)] hover:scale-110 transition-all duration-300">
                <span class="absolute inline-flex h-full w-full rounded-full bg-cyberlime opacity-20 animate-ping group-hover:animate-none"></span>
                
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                
                <span class="absolute top-0 right-0 h-3.5 w-3.5 bg-red-500 border-2 border-black rounded-full z-20 hidden"></span>
=======
        <div id="chat-widget" class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
            <div id="chat-window" class="hidden w-80 h-[450px] bg-[#0d1117] border border-gray-800 rounded-2xl shadow-2xl flex flex-col overflow-hidden mb-4">
                <div class="bg-[#161b22] p-4 border-b border-gray-800 flex justify-between items-center">
                    <h4 class="text-white text-[10px] font-black uppercase tracking-widest">Secure Comms</h4>
                    <button onclick="window.toggleChat()" class="text-gray-500 hover:text-white">✕</button>
                </div>
                <div id="message-display" class="flex-1 overflow-y-auto p-4 space-y-4 bg-black/50"></div>
                <div class="p-4 bg-[#161b22] border-t border-gray-800">
                    <form id="chat-form" class="flex gap-2">
                        <input type="text" id="chat-input" placeholder="Type..." class="flex-1 bg-[#0d1117] border border-gray-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyberlime">
                        <button type="submit" class="bg-cyberlime text-black p-2 rounded-lg hover:bg-white"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg></button>
                    </form>
                </div>
            </div>
            <button onclick="window.toggleChat()" class="bg-cyberlime text-black w-14 h-14 rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
>>>>>>> origin/main
            </button>
        </div>
    <?php endif; ?>

<<<<<<< HEAD
    <footer class="relative pt-24 pb-10 px-6 bg-[#050505] border-t border-gray-900 overflow-hidden">
        
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-cyberlime/5 blur-[100px] rounded-full pointer-events-none"></div>

        <div class="absolute top-12 left-1/2 -translate-x-1/2 text-[20vw] font-black italic text-white/[0.02] select-none pointer-events-none leading-none">
            FACUNDO
        </div>

        <div class="max-w-7xl mx-auto relative z-10">
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-12 mb-20">
                
                <div class="md:col-span-4 space-y-8">
                    <div>
                        <div class="text-3xl font-black italic text-white tracking-tighter mb-4">FACU<span class="text-cyberlime">NDO</span></div>
                        <p class="text-gray-500 text-xs leading-relaxed uppercase tracking-wider max-w-xs">
                            Engineering the future of automotive commerce in the Philippines. Precision, performance, and premium service.
                        </p>
                    </div>
                    
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 border border-gray-800 rounded-full flex items-center justify-center text-gray-500 hover:text-black hover:bg-cyberlime hover:border-cyberlime transition-all duration-300">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 border border-gray-800 rounded-full flex items-center justify-center text-gray-500 hover:text-black hover:bg-cyberlime hover:border-cyberlime transition-all duration-300">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.36-.2 6.78-2.618 6.98-6.98.058-1.28-.072-1.689.072-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <h4 class="text-white font-bold text-[10px] uppercase tracking-[0.3em] mb-8">Navigation</h4>
                    <ul class="space-y-4 flex flex-col">
                        <a href="#showroom" class="text-[10px] text-gray-500 font-bold uppercase tracking-widest hover:text-cyberlime hover:translate-x-2 transition-all duration-300 flex items-center gap-2">
                            <span>Available Units</span>
                        </a>
                        <a href="#finance" class="text-[10px] text-gray-500 font-bold uppercase tracking-widest hover:text-cyberlime hover:translate-x-2 transition-all duration-300 flex items-center gap-2">
                            <span>Financing</span>
                        </a>
                        <a href="#crm" class="text-[10px] text-gray-500 font-bold uppercase tracking-widest hover:text-cyberlime hover:translate-x-2 transition-all duration-300 flex items-center gap-2">
                            <span>Client Portal</span>
                        </a>
                    </ul>
                </div>

                <div class="md:col-span-3">
                    <h4 class="text-white font-bold text-[10px] uppercase tracking-[0.3em] mb-8">Headquarters</h4>
                    <p class="text-gray-500 text-[10px] leading-loose uppercase font-medium mb-6">
                        1810 Sto Niño Street<br>
                        Caloocan City, Metro Manila<br>
                        Philippines, 1400
                    </p>
                    <div>
                        <span class="block text-[9px] text-gray-600 uppercase font-bold tracking-widest mb-1">Direct Line</span>
                        <div class="text-xl font-black text-white hover:text-cyberlime transition-colors cursor-pointer">+63 2 8555 9999</div>
                    </div>
                </div>

                <div class="md:col-span-3">
                    <h4 class="text-white font-bold text-[10px] uppercase tracking-[0.3em] mb-8">Stay Informed</h4>
                    <form onsubmit="event.preventDefault(); alert('Subscribed!');" class="relative group">
                        <input type="email" placeholder="ENTER EMAIL ADDRESS" class="w-full bg-transparent border-b border-gray-800 py-3 text-xs text-white placeholder-gray-700 font-bold uppercase tracking-wider focus:outline-none focus:border-cyberlime transition-colors">
                        <button type="submit" class="absolute right-0 bottom-3 text-gray-500 group-focus-within:text-cyberlime transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </form>
                    <p class="mt-4 text-[9px] text-gray-600">Receive alerts on new arrivals and price drops.</p>
                </div>

            </div>

            <div class="pt-8 border-t border-gray-900 flex flex-col md:flex-row justify-between items-center gap-6">
                
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <div class="text-[9px] text-gray-600 uppercase tracking-[0.2em] font-bold">
                        facundo automotive &copy; 2026
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <a href="#" class="text-[9px] text-gray-600 uppercase font-bold tracking-wider hover:text-white transition-colors">Privacy</a>
                    <a href="#" class="text-[9px] text-gray-600 uppercase font-bold tracking-wider hover:text-white transition-colors">Terms</a>
                    
                    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="ml-4 w-8 h-8 rounded-full border border-gray-800 flex items-center justify-center text-gray-500 hover:text-black hover:bg-cyberlime hover:border-cyberlime transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                    </button>
                </div>
=======
    <footer class="relative pt-32 pb-12 px-6 overflow-hidden border-t border-gray-900">
        <div class="absolute top-10 left-1/2 -translate-x-1/2 footer-logo-bg font-black italic select-none pointer-events-none">FACUNDO</div>
        <div class="max-w-7xl mx-auto relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-20">
                <div class="md:col-span-1">
                    <div class="text-2xl font-black italic mb-6">FACU<span class="text-cyberlime">NDO</span></div>
                    <p class="text-gray-500 text-xs leading-relaxed uppercase tracking-wider">The premium standard in Philippine automotive commerce.</p>
                </div>
                <div><h4 class="text-white font-bold text-[10px] uppercase tracking-[0.3em] mb-6">Inventory</h4><ul class="space-y-4 flex flex-col"><a href="#showroom" class="footer-link">Available Units</a><a href="#finance" class="footer-link">Financing Options</a></ul></div>
                <div><h4 class="text-white font-bold text-[10px] uppercase tracking-[0.3em] mb-6">System</h4><ul class="space-y-4 flex flex-col"><a href="#crm" class="footer-link">Lead Pipeline</a></ul></div>
                <div><h4 class="text-white font-bold text-[10px] uppercase tracking-[0.3em] mb-6">Headquarters</h4><p class="text-gray-500 text-[10px] leading-loose uppercase">1810 Sto Niño Street,<br>Caloocan City, Manila</p><div class="mt-6 text-cyberlime font-black text-sm">+63 2 8555 9999</div></div>
            </div>
            <div class="pt-8 border-t border-gray-900 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-[9px] text-gray-600 uppercase tracking-[0.4em]">© 2026 Facundo Automotive System</div>
                <button onclick="window.scrollTo(0,0)" class="px-4 py-2 rounded-full text-[9px] font-bold uppercase tracking-widest text-gray-500 hover:text-white transition">Back to Top ↑</button>
>>>>>>> origin/main
            </div>
        </div>
    </footer>

    <script>
        // ==========================================
<<<<<<< HEAD
        // 1. DATASETS (Car Models)
        // ==========================================
        const carData = {
            "Toyota": ["Vios", "Fortuner", "Innova", "Hilux", "Wigo", "Rush", "Hiace", "Land Cruiser", "Alphard", "Raize", "Veloz", "Avanza", "Camry", "Corolla Altis"],
            "Honda": ["Civic", "City", "CR-V", "HR-V", "Brio", "Accord", "BR-V", "Pilot", "Civic Type R"],
            "Mitsubishi": ["Montero Sport", "Xpander", "Mirage G4", "Mirage HB", "L300", "Strada", "Outlander", "Pajero"],
            "Ford": ["Ranger", "Everest", "Territory", "Mustang", "Explorer", "Ranger Raptor", "Bronco"],
            "Nissan": ["Navara", "Terra", "Almera", "Patrol", "Kicks", "Urvan", "370Z", "GT-R"],
            "Hyundai": ["Accent", "Tucson", "Santa Fe", "Starex", "Creta", "Staria", "Elantra", "Palisade", "Ioniq 5"],
            "Kia": ["Soluto", "Stonic", "Seltos", "Sportage", "Sorento", "Carnival", "K2500"],
            "Mazda": ["Mazda2", "Mazda3", "Mazda6", "CX-3", "CX-5", "CX-8", "CX-9", "CX-60", "MX-5"],
            "Subaru": ["Forester", "XV", "Outback", "Evoltis", "WRX", "BRZ"],
            "Isuzu": ["D-Max", "mu-X", "Traviz"],
            "Suzuki": ["Ertiga", "Jimny", "Dzire", "Swift", "S-Presso", "XL7", "Celerio"],
            "Chevrolet": ["Camaro", "Corvette", "Suburban", "Tahoe", "Trailblazer", "Tracker"],
            "BMW": ["1 Series", "3 Series", "5 Series", "7 Series", "X1", "X3", "X5", "X7", "M2", "M3", "M4", "M5", "Z4"],
            "Mercedes-Benz": ["A-Class", "C-Class", "E-Class", "S-Class", "GLA", "GLB", "GLC", "GLE", "GLS", "G-Class"],
            "Audi": ["A3", "A4", "A6", "Q2", "Q3", "Q5", "Q7", "Q8", "R8"],
            "Lexus": ["IS", "ES", "LS", "UX", "NX", "RX", "GX", "LX", "LC", "LM"],
            "Porsche": ["911", "Cayenne", "Macan", "Panamera", "Taycan", "718 Boxster", "718 Cayman"]
        };

        // Global variables
        let makeChoicesInstance, modelChoicesInstance;
        let editYearChoices, editTransChoices;

        // ==========================================
        // 2. INITIALIZATION (DOM READY)
        // ==========================================
        document.addEventListener('DOMContentLoaded', () => {
            
            // ==========================================
        // CALCULATOR LOGIC (Premium + Safe)
        // ==========================================

        // Helper: Handle the clickable Term Buttons (12mo, 24mo, etc.)
            window.setTerm = function(months) {
                const termInput = document.getElementById('calc_term');
                if (!termInput) return; // Safety check

                // Update hidden input
                termInput.value = months;

                // Update Button Visuals
                document.querySelectorAll('.term-btn').forEach(btn => {
                    // Remove active styles (reset to dark)
                    btn.classList.remove('bg-cyberlime', 'text-black', 'border-cyberlime', 'shadow-[0_0_15px_rgba(204,255,0,0.3)]');
                    btn.classList.add('bg-[#0d1117]', 'text-gray-400', 'border-gray-700');

                    // Add active styles if this is the clicked button
                    if (parseInt(btn.getAttribute('data-value')) === months) {
                        btn.classList.remove('bg-[#0d1117]', 'text-gray-400', 'border-gray-700');
                        btn.classList.add('bg-cyberlime', 'text-black', 'border-cyberlime', 'shadow-[0_0_15px_rgba(204,255,0,0.3)]');
                    }
                });

                // Recalculate the numbers
                window.calculateLoan();
            };

            // Main Calculator Function
            window.calculateLoan = function() {
                // 1. Safe Element Selection
                const priceEl = document.getElementById('calc_price');
                const dpEl = document.getElementById('calc_dp');
                const termEl = document.getElementById('calc_term');
                const resultEl = document.getElementById('monthly_result');
                const dpPercentDisplay = document.getElementById('dp_percent_display');
                const dpAmountDisplay = document.getElementById('dp_amount_display');

                // 2. Safety Check: Stop if core elements are missing (prevents crash on other pages)
                if (!priceEl || !dpEl || !termEl || !resultEl) {
                    return;
                }

                // 3. Get Values
                const p = parseFloat(priceEl.value) || 0;
                const dpPercent = parseInt(dpEl.value) || 20;
                const months = parseInt(termEl.value) || 60;

                // 4. Calculations
                // Down Payment Cash Amount (Price * Percentage)
                const dpAmount = p * (dpPercent / 100);
                
                // Loan Amount (Price - Down Payment)
                const loanAmount = p - dpAmount;
                
                // Interest Calculation (Mock Rate: 5% per year)
                const annualRate = 0.05; 
                const years = months / 12;
                const totalInterest = loanAmount * annualRate * years;
                
                // Total to Pay & Monthly Amortization
                const totalAmount = loanAmount + totalInterest;
                const monthly = totalAmount / months;

                // 5. Update UI
                // Update the text showing "20%"
                if (dpPercentDisplay) dpPercentDisplay.innerText = dpPercent + "%";
                
                // Update the text showing "₱300,000" (Cash Out)
                if (dpAmountDisplay) dpAmountDisplay.innerText = "₱" + dpAmount.toLocaleString();
                
                // Update the Big Monthly Result
                resultEl.innerText = "₱" + monthly.toLocaleString(undefined, {
                    minimumFractionDigits: 2, 
                    maximumFractionDigits: 2
                });
            };

            // --- B. STC DROPDOWN LOGIC (FIXED) ---
            const makeEl = document.getElementById('make-select');
            const modelEl = document.getElementById('model-select');

            if (makeEl && modelEl) {
                // 1. Setup Brand Dropdown
                makeChoicesInstance = new Choices(makeEl, {
                    searchEnabled: false,
                    itemSelectText: '',
                    shouldSort: false
                });

                // 2. Setup Model Dropdown
                modelChoicesInstance = new Choices(modelEl, {
                    searchEnabled: true,
                    itemSelectText: '',
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: 'Select Brand First' // Initial text
                });

                // 3. THE LOGIC: Connect Brand -> Model
                makeEl.addEventListener('change', function() {
                    
                    // SAFE VALUE RETRIEVAL: Ask the library directly for the value
                    const brand = makeChoicesInstance.getValue(true);
                    
                    // Clear the current models
                    modelChoicesInstance.clearStore();
                    modelChoicesInstance.clearInput();

                    // Check if we have models for this brand
                    if (brand && carData[brand]) {
                        const models = carData[brand];
                        
                        // Convert to format Choices.js needs: [{value: 'Vios', label: 'Vios'}, ...]
                        const choicesData = models.map(m => ({ value: m, label: m }));
                        
                        // Update the dropdown
                        modelChoicesInstance.setChoices(choicesData, 'value', 'label', true);
                        modelChoicesInstance.enable();
                    } else {
                        // Disable if no brand selected
                        modelChoicesInstance.disable();
                    }
                });
            }

            // --- C. EDIT MODAL DROPDOWNS ---
            if(document.getElementById('edit_year')) {
                editYearChoices = new Choices('#edit_year');
                editTransChoices = new Choices('#edit_transmission');
            }

            // --- D. CHAT SYSTEM ---
            if(document.getElementById('message-display')) {
                loadMessages();
                setInterval(loadMessages, 5000);
            }

            // --- E. ALERT FADE OUT ---
            const alert = document.getElementById('success-alert');
            if(alert) setTimeout(() => { alert.style.opacity = "0"; setTimeout(()=>alert.remove(), 500); }, 3000);
        });

        // ==========================================
        // 3. GLOBAL FUNCTIONS
        // ==========================================
=======
        // 1. DATA & VARIABLES
        // ==========================================
        const carData = {
            "Toyota": ["Vios", "Fortuner", "Innova", "Hilux", "Wigo", "Rush", "Hiace", "Land Cruiser", "Alphard", "Raize"],
            "Honda": ["Civic", "City", "CR-V", "HR-V", "Brio", "Accord", "BR-V"],
            "Mitsubishi": ["Montero", "Xpander", "Mirage", "L300", "Strada"],
            "Ford": ["Ranger", "Everest", "Territory", "Mustang", "Explorer"],
            "Nissan": ["Navara", "Terra", "Almera", "Patrol"],
            "BMW": ["3 Series", "5 Series", "X1", "X3", "X5", "M3"],
            "Mercedes-Benz": ["C-Class", "E-Class", "GLA", "G-Wagon"]
        };

        let makeChoices, modelChoices, editYearChoices, editTransChoices;

>>>>>>> origin/main
        const escapeHTML = (str) => {
            const p = document.createElement('p');
            p.textContent = str;
            return p.innerHTML;
        };

<<<<<<< HEAD
=======
        // ==========================================
        // 2. HELPER: FORCE POPULATE DROPDOWN
        // ==========================================
        // This fixes the issue where the dropdown appears empty
        function populateMakeSelect() {
            const makeEl = document.getElementById('make-select');
            if (makeEl) {
                makeEl.innerHTML = ''; // Clear it
                // Add placeholder
                const placeholder = document.createElement('option');
                placeholder.value = "";
                placeholder.textContent = "Select Brand";
                makeEl.appendChild(placeholder);
                // Add brands manually
                Object.keys(carData).forEach(brand => {
                    const opt = document.createElement('option');
                    opt.value = brand;
                    opt.textContent = brand;
                    makeEl.appendChild(opt);
                });
            }
        }

        // ==========================================
        // 3. GLOBAL FUNCTIONS
        // ==========================================
>>>>>>> origin/main
        window.deleteUnit = function(id) {
            if(!confirm("⚠ WARNING: Permanently delete this unit?")) return;
            const fd = new FormData();
            fd.append('id', id);
            fetch('delete_listing.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if(data.status === 'success') { alert("Deleted Successfully"); location.reload(); }
                else alert("Error: " + data.message);
            })
            .catch(e => console.error(e));
        };

        window.openEditModal = function(unit) {
            document.getElementById('edit_id').value = unit.id;
            document.getElementById('edit_make').value = unit.make;
            document.getElementById('edit_model').value = unit.model;
            document.getElementById('edit_price').value = unit.price_php;
            document.getElementById('edit_mileage').value = unit.mileage_km;
<<<<<<< HEAD
            
=======

>>>>>>> origin/main
            if(unit.year_produced && typeof editYearChoices !== 'undefined') editYearChoices.setChoiceByValue(unit.year_produced.toString());
            if(unit.transmission && typeof editTransChoices !== 'undefined') editTransChoices.setChoiceByValue(unit.transmission);

            const delBtn = document.getElementById('modal_delete_btn');
            if(delBtn) {
                delBtn.onclick = function() {
                    window.closeEditModal();
                    setTimeout(() => window.deleteUnit(unit.id), 200);
                };
            }
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editModal').classList.add('flex');
        };

        window.closeEditModal = () => { document.getElementById('editModal').classList.add('hidden'); };

        window.openInquiry = function(id, name) {
            document.getElementById('modal_vehicle_id').value = id;
            document.getElementById('unitNameDisplay').innerText = name;
            document.getElementById('inquiryModal').classList.remove('hidden');
            document.getElementById('inquiryModal').classList.add('flex');
        };
        window.closeInquiry = () => { document.getElementById('inquiryModal').classList.add('hidden'); };

<<<<<<< HEAD
        // Toggles
        window.toggleChat = () => { document.getElementById('chat-window').classList.toggle('hidden'); };
        window.openLogin = () => { document.getElementById('login-overlay').classList.remove('hidden'); };
        window.closeLogin = () => { document.getElementById('login-overlay').classList.add('hidden'); };
        
=======
        // Login / Register Toggles
        window.toggleChat = () => { document.getElementById('chat-window').classList.toggle('hidden'); };
        window.openLogin = () => { document.getElementById('login-overlay').classList.remove('hidden'); };
        window.closeLogin = () => { document.getElementById('login-overlay').classList.add('hidden'); };

>>>>>>> origin/main
        window.toggleRegister = () => {
            const mode = document.getElementById('auth_mode');
            const reg = document.getElementById('registration-fields');
            if(mode.value === 'login') {
                mode.value = 'register';
                reg.classList.remove('hidden');
                document.getElementById('modal-title').innerText = "Join Fleet";
                document.getElementById('submit-btn').innerText = "Register";
            } else {
                mode.value = 'login';
                reg.classList.add('hidden');
                document.getElementById('modal-title').innerText = "Access Portal";
                document.getElementById('submit-btn').innerText = "Sign In";
            }
        };
        window.openRegister = () => { window.openLogin(); if(document.getElementById('auth_mode').value === 'login') window.toggleRegister(); };

<<<<<<< HEAD
=======
        // Staff & Admin Modal Toggles
>>>>>>> origin/main
        window.openStaffModal = () => { const m = document.getElementById('staffModal'); if(m) { m.classList.remove('hidden'); m.classList.add('flex'); } };
        window.closeStaffModal = () => { const m = document.getElementById('staffModal'); if(m) m.classList.add('hidden'); };

        window.openAdminReply = (uid, uname) => {
            document.getElementById('reply_receiver_id').value = uid;
            document.getElementById('replyTargetName').innerText = uname;
            document.getElementById('adminReplyModal').classList.remove('hidden');
            document.getElementById('adminReplyModal').classList.add('flex');
        };
        window.closeAdminReply = () => { document.getElementById('adminReplyModal').classList.add('hidden'); };

        // Calculator & Filter
        window.calculateLoan = function() {
            const p = parseFloat(document.getElementById('calc_price').value) || 0;
            const dp = document.getElementById('calc_dp').value;
            const t = document.getElementById('calc_term').value;
            document.getElementById('dp_display').innerText = dp + "%";
            const loan = p - (p * (dp/100));
            const monthly = (loan + (loan * 0.05 * (t/12))) / t;
            document.getElementById('monthly_result').innerText = "₱" + monthly.toLocaleString(undefined, {minimumFractionDigits: 2});
        };

        window.filterFleet = function() {
            const m = document.getElementById('search-make').value.toUpperCase();
            const p = document.getElementById('search-price').value;
            document.querySelectorAll('.unit-card').forEach(c => {
                const title = c.querySelector('.unit-title').innerText.toUpperCase();
                const price = parseInt(c.querySelector('.unit-price').innerText.replace(/[^0-9]/g, ''));
                c.style.display = (title.includes(m) && (!p || price <= p)) ? "" : "none";
            });
        };

        window.previewImage = function(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('image-preview');
                    const ph = document.getElementById('upload-placeholder');
                    if(img) { img.src = e.target.result; img.classList.remove('hidden'); }
                    if(ph) ph.classList.add('opacity-0');
                }
                reader.readAsDataURL(input.files[0]);
            }
        };

        // ==========================================
<<<<<<< HEAD
        // 4. EVENT LISTENERS (FORMS)
        // ==========================================
=======
        // 4. INITIALIZATION (DOM READY)
        // ==========================================
        document.addEventListener('DOMContentLoaded', () => {
            window.calculateLoan();
            const alert = document.getElementById('success-alert');
            if(alert) setTimeout(() => { alert.style.opacity = "0"; setTimeout(()=>alert.remove(), 500); }, 3000);

            // --- FIX: MANUALLY POPULATE DROPDOWN FIRST ---
            populateMakeSelect();

            const makeEl = document.getElementById('make-select');
            if (makeEl) {
                // Initialize Choices.js AFTER populating
                makeChoices = new Choices(makeEl);
                modelChoices = new Choices('#model-select');

                // Listener for updating models
                makeEl.addEventListener('change', function(e) {
                    modelChoices.clearStore();
                    // Get value safely (works for both standard select and Choices.js event)
                    const val = e.detail ? e.detail.value : this.value;
                    const models = carData[val] || [];

                    // Update Model Dropdown
                    modelChoices.setChoices(
                        models.map(m => ({value: m, label: m})), 
                        'value', 
                        'label', 
                        true
                    );
                });
            }

            // Edit Modal Dropdowns
            if(document.getElementById('edit_year')) {
                editYearChoices = new Choices('#edit_year');
                editTransChoices = new Choices('#edit_transmission');
            }

            // Chat System
            if(document.getElementById('message-display')) {
                loadMessages();
                setInterval(loadMessages, 5000);
            }
        });

        // ==========================================
        // 5. EVENT LISTENERS (FORMS)
        // ==========================================

        // Chat Message Loader
>>>>>>> origin/main
        function loadMessages() {
            fetch('fetch_messages.php')
            .then(r=>r.json()).then(d=>{
                if(d.status!=='success') return;
                const disp = document.getElementById('message-display');
                const myId = d.current_user_id;
<<<<<<< HEAD
                let content = `<div class="flex flex-col items-start mb-4"><span class="text-[8px] text-gray-500 uppercase mb-1 ml-1">System</span><div class="bg-gray-900 border border-gray-800 text-gray-300 p-3 rounded-2xl rounded-tl-none text-xs max-w-[85%]">Welcome to Facundo Fleet.</div></div>`;
=======

                let content = `<div class="flex flex-col items-start mb-4"><span class="text-[8px] text-gray-500 uppercase mb-1 ml-1">System</span><div class="bg-gray-900 border border-gray-800 text-gray-300 p-3 rounded-2xl rounded-tl-none text-xs max-w-[85%]">Welcome to Facundo Fleet.</div></div>`;

>>>>>>> origin/main
                d.messages.forEach(m => {
                    const isMe = (m.sender_id == myId);
                    if (isMe) {
                        content += `<div class="flex flex-col items-end mb-4 animate-in-fade"><div class="bg-cyberlime text-black p-3 rounded-2xl rounded-tr-none text-xs max-w-[85%] font-bold shadow-lg">${escapeHTML(m.message)}</div><span class="text-[8px] text-gray-600 mt-1 mr-1 font-mono">Just now</span></div>`;
                    } else {
                        content += `<div class="flex flex-col items-start mb-4 animate-in-fade"><span class="text-[8px] text-cyberlime uppercase mb-1 ml-1 font-bold">Admin</span><div class="bg-gray-800 text-white p-3 rounded-2xl rounded-tl-none text-xs max-w-[85%] border border-gray-700">${escapeHTML(m.message)}</div></div>`;
                    }
                });
                disp.innerHTML = content;
            }).catch(e=>{});
        }

<<<<<<< HEAD
=======
        // Chat Submit
>>>>>>> origin/main
        const chatForm = document.getElementById('chat-form');
        if (chatForm) {
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const inp = document.getElementById('chat-input');
                const txt = inp.value.trim();
                if (txt) {
                    const fd = new FormData();
                    fd.append('message', txt);
                    fetch('send_message.php', { method: 'POST', body: fd }).then(()=>{ inp.value=""; loadMessages(); });
                }
            });
        }

<<<<<<< HEAD
=======
        // Staff Add Form
>>>>>>> origin/main
        const staffForm = document.getElementById('add-staff-form');
        if(staffForm) {
            staffForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = this.querySelector('button');
                btn.innerText = "PROCESSING..."; btn.disabled = true;
                fetch('create_staff.php', { method: 'POST', body: new FormData(this) })
                .then(r=>r.json()).then(d=>{
                    if(d.status==='success') { alert("Success"); window.closeStaffModal(); this.reset(); }
                    else alert(d.message);
                })
                .finally(()=>{ btn.innerText="Grant Access"; btn.disabled=false; });
            });
        }

<<<<<<< HEAD
=======
        // Admin Reply Form
>>>>>>> origin/main
        const arForm = document.getElementById('admin-reply-form');
        if(arForm) {
            arForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = this.querySelector('button');
                btn.innerText = "SENDING..."; btn.disabled = true;
                fetch('admin_reply.php', { method: 'POST', body: new FormData(this) })
                .then(r=>r.json()).then(d=>{
                    if(d.status==='success') { window.closeAdminReply(); alert("Sent"); location.reload(); }
                    else alert(d.message);
                })
                .finally(()=>{ btn.innerText="Send Transmission"; btn.disabled=false; });
            });
        }
<<<<<<< HEAD

        // ==========================================
    // GPS LOCATION TRACKER
    // ==========================================
    document.addEventListener("DOMContentLoaded", function() {
        // We use a free IP-API to get the city without asking for permission
        fetch('https://ipapi.co/json/')
            .then(response => response.json())
            .then(data => {
                const cityDisplay = document.getElementById('user-city');
                const locator = document.getElementById('geo-locator');
                
                if(data.city) {
                    // Update text to City, Country Code (e.g., MANILA, PH)
                    cityDisplay.innerText = `${data.city}, ${data.country_code}`;
                    
                    // Update the hover title
                    locator.setAttribute('title', `Signal Locked: ${data.ip}`);
                    
                    // Add a cool "Success" color flash
                    cityDisplay.classList.add('text-cyberlime');
                    setTimeout(() => cityDisplay.classList.remove('text-cyberlime'), 1000);
                } else {
                    cityDisplay.innerText = "UNKNOWN SECTOR";
                }
            })
            .catch(error => {
                // Fallback if ad-blocker blocks the API
                document.getElementById('user-city').innerText = "MANILA, PH"; // Default fallback
            });
    });
=======
>>>>>>> origin/main
    </script>
</body>
</html>
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
            </div>
        </div>
    </nav>

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
            </div>
        </div>
        <div class="absolute bottom-0 left-0 w-full h-32 bg-gradient-to-t from-black to-transparent pointer-events-none"></div>
    </header>

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
                                    </div>
                                </div>
                            </div>

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
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0 1 1 0 002 0z" />
                            </svg>
                        </div>
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
                                        
                                        $q2 = $pdo->query($sql);
                                        $msgs = $q2 ? $q2->fetchAll(PDO::FETCH_ASSOC) : [];

                                        if(empty($msgs)): 
                            ?>
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
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                            <?php 
                                            endforeach; 
                                        endif;
                                    }
                                } catch (Exception $e) {
                                    // Silent fail to prevent crash
                                }
                            ?>
                        </div>
                    </div>

                    <div class="bg-cyberlime p-8 rounded-2xl flex flex-col justify-center items-center text-center shadow-xl shadow-cyberlime/10">
                        <div class="text-black">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                            <h5 class="text-xs font-black uppercase tracking-widest">Active Transmissions</h5>
                            <div class="text-6xl font-black my-2"><?php echo count($msgs ?? []); ?></div>
                            <p class="text-[9px] font-bold uppercase opacity-60">Total Fleet Inquiries</p>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    <?php endif; ?>

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
            </form>
        </div>
    </div>

    <div id="adminReplyModal" class="fixed inset-0 z-[600] bg-black/90 hidden items-center justify-center backdrop-blur-sm">
        <div class="bg-[#161b22] border border-gray-800 p-6 rounded-2xl w-full max-w-md shadow-2xl relative">
            <button onclick="window.closeAdminReply()" class="absolute top-4 right-4 text-gray-500 hover:text-white">✕</button>
            <h3 class="text-white text-sm font-bold uppercase tracking-widest mb-1">Transmitting To:</h3>
            <p id="replyTargetName" class="text-cyberlime font-black text-xl italic mb-6">User</p>
            <form id="admin-reply-form" class="space-y-4">
                <input type="hidden" id="reply_receiver_id" name="receiver_id">
                <textarea id="reply_message" rows="4" class="input-facundo resize-none" placeholder="Type response..." required></textarea>
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
            </button>
        </div>
    <?php endif; ?>

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
            </div>
        </div>
    </footer>

    <script>
        // ==========================================
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

        const escapeHTML = (str) => {
            const p = document.createElement('p');
            p.textContent = str;
            return p.innerHTML;
        };

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

        // Login / Register Toggles
        window.toggleChat = () => { document.getElementById('chat-window').classList.toggle('hidden'); };
        window.openLogin = () => { document.getElementById('login-overlay').classList.remove('hidden'); };
        window.closeLogin = () => { document.getElementById('login-overlay').classList.add('hidden'); };

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

        // Staff & Admin Modal Toggles
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
        function loadMessages() {
            fetch('fetch_messages.php')
            .then(r=>r.json()).then(d=>{
                if(d.status!=='success') return;
                const disp = document.getElementById('message-display');
                const myId = d.current_user_id;

                let content = `<div class="flex flex-col items-start mb-4"><span class="text-[8px] text-gray-500 uppercase mb-1 ml-1">System</span><div class="bg-gray-900 border border-gray-800 text-gray-300 p-3 rounded-2xl rounded-tl-none text-xs max-w-[85%]">Welcome to Facundo Fleet.</div></div>`;

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

        // Chat Submit
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

        // Staff Add Form
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

        // Admin Reply Form
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
    </script>
</body>
</html>
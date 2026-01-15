<?php 
session_start(); 
require 'db_connection/db.php'; 

// Fetch all available units
$stmt = $pdo->query("SELECT * FROM vehicles WHERE status != 'Sold'");
$units = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FACUNDO | Automotive Excellence</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"/>
	<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="assets/style.css">
    
    <style>
    /* CHOICES.JS CUSTOM FACUNDO THEME */

    /* The main container */
    .choices__inner {
        background-color: var(--obsidian) !important;
        border: 1px solid #374151 !important; /* Matches .input-facundo border */
        border-radius: 8px !important;       /* Matches .input-facundo radius */
        color: #ffffff !important;
        font-size: 0.875rem !important;      /* Standard text-sm */
        padding: 0.5rem 0.75rem !important;  /* Matches your input padding */
        min-height: auto !important;
    }

    /* Change border on focus to match your shadow effect */
    .choices.is-focused .choices__inner {
        border-color: var(--cyberlime) !important;
        box-shadow: 0 0 10px rgba(204, 255, 0, 0.1) !important;
    }

    /* The dropdown menu */
    .choices__list--dropdown {
        background-color: #161b22 !important; /* Matches your .unit-card background */
        border: 1px solid #1f2937 !important;
        border-radius: 8px !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4) !important;
        margin-top: 5px !important;
        z-index: 50;
    }

    /* Individual items in the list */
    .choices__item {
        font-size: 13px !important;
        color: #9ca3af !important; /* Gray-400 */
    }

    /* Hover state for items (using Cyberlime) */
    .choices__list--dropdown .choices__item--selectable.is-highlighted {
        background-color: var(--cyberlime) !important;
        color: #000000 !important; /* Black text on lime background */
    }

    /* Selected item styling */
    .choices__list--single {
        padding: 0 !important;
        font-weight: 500;
    }

    /* The search input field inside the dropdown */
    .choices__input {
        background-color: var(--obsidian) !important;
        color: white !important;
        border-bottom: 1px solid #1f2937 !important;
    }

    /* Hide the default caret to keep it clean, or style it lime */
    .choices[data-type*="select-one"]::after {
        border-color: #6b7280 transparent transparent transparent !important;
    }

    .choices[data-type*="select-one"].is-open::after {
        border-color: transparent transparent var(--cyberlime) transparent !important;
    }
        
            .choices__list--single {
        color: #ffffff !important;
        background-color: transparent !important;
    }

    .choices__inner {
        background-color: var(--obsidian) !important;
        border: 1px solid #374151 !important;
        color: white !important;
    }
        
        
        /* MOBILE OPTIMIZATIONS FOR CHOICES.JS */

    /* Increase tap target size for mobile fingers */
    .choices__inner {
        min-height: 52px !important; /* Larger touch area */
        display: flex;
        align-items: center;
        padding: 0 12px !important;
    }

    /* Prevent the dropdown from being too small on mobile */
    .choices__list--dropdown .choices__item {
        padding: 12px !important; /* More space between options */
        font-size: 14px !important; /* Larger text for readability */
    }

    /* Ensure the form stacks perfectly on mobile */
    @media (max-width: 768px) {
        .choices {
            margin-bottom: 8px;
        }

        /* Make sure the dropdown doesn't overflow the screen width */
        .choices__list--dropdown {
            width: 100% !important;
            left: 0 !important;
        }
    }

    /* Custom scrollbar for the dropdown list */
    .choices__list--dropdown::-webkit-scrollbar {
        width: 4px;
    }
    .choices__list--dropdown::-webkit-scrollbar-thumb {
        background: var(--cyberlime);
        border-radius: 10px;
    }
        
    </style>
    
</head>
    <body>

        <div id="login-overlay" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-md <?php echo isset($_GET['error']) ? '' : 'hidden'; ?>">
            <div class="login-modal p-10 rounded-3xl max-w-sm w-full text-center bg-[#161b22] border border-gray-800">
                <div class="text-3xl font-black italic mb-8">FACU<span class="text-cyberlime">NDO</span></div>
                <h2 class="text-white text-sm font-bold uppercase tracking-widest mb-6">Internal Access Only</h2>
                
                <form action="db_connection/login_handler.php" method="POST">
                    <input type="email" name="email" placeholder="Email" class="input-facundo w-full mb-4" required>
                    <input type="password" name="password" placeholder="Password" class="input-facundo w-full mb-6" required>
                    
                    <?php if(isset($_GET['error'])): ?>
                        <p class="text-red-500 text-[10px] mb-4 uppercase font-bold tracking-widest">Invalid Credentials</p>
                    <?php endif; ?>

                    <button type="submit" class="w-full bg-cyberlime text-black font-black py-4 rounded-xl uppercase text-xs tracking-widest hover:bg-white transition">Sign In</button>
                </form>
                
                <button onclick="closeLogin()" class="mt-4 text-gray-500 text-[9px] uppercase tracking-widest hover:text-white">Cancel</button>
            </div>
        </div>

        <div class="bg-black border-b border-gray-900 px-6 py-2 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <span class="text-[9px] text-gray-500 uppercase font-bold tracking-widest">Current Session:</span>
                <div id="user-display" class="flex items-center gap-2">
                    <span class="text-xs font-bold text-white">
                        <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Guest'; ?>
                    </span>
                    <span class="role-badge <?php echo (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') ? 'bg-cyberlime text-black' : 'bg-gray-800 text-gray-400'; ?> px-2 py-0.5 rounded text-[8px] font-black uppercase">
                        <?php echo isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'Visitor'; ?>
                    </span>
                </div>
            </div>
            <div class="flex gap-4">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="text-[9px] text-red-500 uppercase font-bold tracking-widest transition underline">Logout</a>
                <?php else: ?>
                    <button onclick="openLogin()" class="text-[9px] text-cyberlime uppercase font-bold tracking-widest transition underline">Login</button>
                <?php endif; ?>
            </div>
        </div>

        

        <script>
            function openLogin() {
                document.getElementById('login-overlay').classList.remove('hidden');
            }
            function closeLogin() {
                document.getElementById('login-overlay').classList.add('hidden');
            }
            // Closes if you click the dark background
            window.onclick = function(event) {
                if (event.target == document.getElementById('login-overlay')) {
                    closeLogin();
                }
            }
        </script>

        <nav class="sticky top-0 z-50 nav-glass border-b border-gray-800 px-6 py-4">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="text-3xl font-black tracking-tighter italic text-white">
                    FACU<span class="text-cyberlime font-black">NDO</span>
                </div>

                <div class="hidden md:flex gap-10 text-[10px] font-bold uppercase tracking-[0.2em]">
                    <a href="#showroom" class="hover:text-cyberlime transition-facundo">Showroom</a>

                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <a href="#stc" class="hover:text-cyberlime transition-facundo text-cyberlime">Stock (STC)</a>
                        <a href="#crm" class="hover:text-cyberlime transition-facundo text-cyberlime">CRM Portal</a>
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

        <header class="relative min-h-[90vh] flex items-center justify-center overflow-hidden hero-gradient px-6">
            
            <div class="absolute inset-0 flex items-center justify-center opacity-[0.02] select-none pointer-events-none">
                <h1 class="text-[30vw] font-black italic">FACUNDO</h1>
            </div>

            <div class="max-w-7xl mx-auto w-full relative z-10 text-center">
                <div class="inline-block px-4 py-1 mb-6 border border-cyberlime/30 rounded-full bg-cyberlime/5">
                    <span class="text-cyberlime text-[10px] font-bold tracking-[0.3em] uppercase">Premier Auto Commerce Manila</span>
                </div>

                <h1 class="hero-title font-black italic mb-8 tracking-tighter uppercase">
                    Drive the <span class="text-cyberlime">Standard</span>
                </h1>
                
                <p class="text-gray-400 max-w-2xl mx-auto mb-12 text-sm md:text-lg tracking-wide">
                    Access the exclusive Facundo fleet. Verified units, high-performance maintenance, 
                    and transparent PHP pricing.
                </p>

                <div class="max-w-5xl mx-auto search-container p-2 rounded-2xl flex flex-col md:flex-row items-center gap-2">
                    
                    <div class="flex flex-col items-start px-4 py-2 w-full md:w-1/3 text-left">
                        <label class="text-[9px] font-bold text-gray-500 uppercase tracking-widest mb-1">Make / Model</label>
                        <input type="text" placeholder="e.g. Land Cruiser" class="bg-transparent text-white w-full outline-none placeholder:text-gray-700">
                    </div>

                    <div class="hidden md:block w-[1px] h-10 bg-gray-800"></div>

                    <div class="flex flex-col items-start px-4 py-2 w-full md:w-1/3 text-left">
                        <label class="text-[9px] font-bold text-gray-500 uppercase tracking-widest mb-1">Max Price (PHP)</label>
                        <div class="flex items-center w-full">
                            <span class="text-gray-500 mr-1">₱</span>
                            <input type="number" placeholder="5,000,000" class="bg-transparent text-white w-full outline-none placeholder:text-gray-700">
                        </div>
                    </div>

                    <div class="hidden md:block w-[1px] h-10 bg-gray-800"></div>

                    <div class="flex flex-col items-start px-4 py-2 w-full md:w-1/3 text-left">
                        <label class="text-[9px] font-bold text-gray-500 uppercase tracking-widest mb-1">Max Mileage (KM)</label>
                        <div class="flex items-center w-full">
                            <input type="number" placeholder="20,000" class="bg-transparent text-white w-full outline-none placeholder:text-gray-700">
                            <span class="text-gray-500 ml-1 text-[10px]">km</span>
                        </div>
                    </div>

                    <button class="w-full md:w-auto bg-cyberlime text-black font-black px-10 py-5 rounded-xl transition-facundo hover:bg-white uppercase text-xs tracking-widest">
                        Search
                    </button>
                </div>
            </div>
        </header>

        <section id="showroom" class="py-24 px-6 max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
                <div>
                    <h2 class="text-cyberlime font-bold uppercase tracking-[0.3em] text-xs mb-3">Facundo Fleet</h2>
                    <h3 class="text-4xl md:text-5xl font-black italic uppercase tracking-tighter text-white">Available <span class="text-cyberlime">Units</span></h3>
                </div>
                <div class="flex gap-4">
                    <button class="px-6 py-2 border border-gray-800 rounded-full text-[10px] font-bold uppercase hover:border-cyberlime transition-all text-white">Filter</button>
                    <button class="px-6 py-2 border border-gray-800 rounded-full text-[10px] font-bold uppercase hover:border-cyberlime transition-all text-white">Sort By</button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach($units as $unit): ?>
                <div class="unit-card rounded-2xl group bg-[#161b22] border border-gray-800 overflow-hidden">
                    <div class="relative aspect-[16/10] bg-black overflow-hidden">
                        <div class="absolute top-4 left-4 z-10">
                            <span class="bg-black/60 backdrop-blur-md <?php echo $unit['status'] == 'Reserved' ? 'text-orange-400' : 'text-cyberlime'; ?> text-[9px] font-bold px-3 py-1 rounded-full uppercase tracking-widest border border-white/10">
                                <?php echo $unit['status']; ?>
                            </span>
                        </div>

                        <?php 
                            // Check if image exists in folder, otherwise use a placeholder
                            $imagePath = !empty($unit['image_path']) ? 'uploads/cars/' . $unit['image_path'] : 'assets/img/no-image.jpg';
                        ?>

                        <img src="<?php echo $imagePath; ?>" 
                             alt="<?php echo $unit['make'] . ' ' . $unit['model']; ?>"
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">

                        <div class="absolute inset-0 bg-gradient-to-t from-[#161b22] via-transparent to-transparent opacity-60"></div>
                    </div>

                    <div class="p-6">
                        <h4 class="text-xl font-black tracking-tight group-hover:text-cyberlime transition-all uppercase text-white">
                            <?php echo $unit['make'] . ' ' . $unit['model']; ?>
                        </h4>
                        <p class="text-gray-500 text-[10px] uppercase font-bold tracking-widest mt-1">
                            <?php echo $unit['year_produced'] ?? $unit['year']; ?> • <?php echo $unit['transmission']; ?>
                        </p>

                        <div class="grid grid-cols-2 border-y border-gray-800/50 py-4 my-4 gap-4">
                            <div>
                                <span class="block text-[9px] text-gray-500 uppercase font-bold">Mileage</span>
                                <span class="font-bold text-sm text-white"><?php echo number_format($unit['mileage_km'] ?? $unit['mileage']); ?> <span class="text-cyberlime text-[10px]">km</span></span>
                            </div>
                            <div>
                                <span class="block text-[9px] text-gray-500 uppercase font-bold">Status</span>
                                <span class="font-bold text-sm uppercase text-white text-[10px]"><?php echo $unit['status']; ?></span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center mt-6">
                            <div>
                                <span class="block text-[9px] text-gray-500 uppercase font-bold tracking-widest">Price</span>
                                <span class="text-2xl font-black italic text-white">₱<?php echo number_format($unit['price_php'] ?? $unit['price']); ?></span>
                            </div>

                            <?php if($unit['status'] !== 'Reserved'): ?>
                                <button onclick="openInquiry(<?php echo $unit['id']; ?>, '<?php echo $unit['make'].' '.$unit['model']; ?>')" 
                                        class="bg-cyberlime text-black p-3 rounded-lg hover:bg-white transition-all transform active:scale-90">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </button>
                            <?php else: ?>
                                <button class="bg-gray-800 text-gray-500 p-3 rounded-lg cursor-not-allowed" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>

        <section id="stc" class="py-24 px-6 bg-[#0d1117] border-t border-gray-900">
            <div class="max-w-7xl mx-auto">
                <?php if(isset($_GET['success'])): ?>
                    <div id="success-alert" class="mb-10 bg-cyberlime/10 border border-cyberlime/20 p-4 rounded-xl flex items-center gap-3 animate-pulse">
                        <span class="text-cyberlime font-bold">✓</span>
                        <p class="text-cyberlime text-[10px] font-bold uppercase tracking-[0.2em]">
                            <?php 
                                if($_GET['success'] == 'unit_added') echo 'Inventory System Updated: Unit Successfully Deployed';
                                elseif($_GET['success'] == 'unit_deleted') echo 'Inventory System Updated: Unit Permanently Removed';
                                else echo 'System Update: Unit Status Modified';
                            ?>
                        </p>
                    </div>
                <?php endif; ?>

                <div class="mb-12">
                    <h2 class="text-cyberlime font-bold uppercase tracking-[0.3em] text-xs mb-3">Internal Operations</h2>
                    <h3 class="text-4xl font-black italic uppercase tracking-tighter text-white">Stock <span class="text-cyberlime">Control</span> (STC)</h3>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                    <div class="lg:col-span-2 p-8 rounded-2xl bg-[#161b22] border border-gray-800 shadow-2xl">
                        <h4 class="text-lg font-bold mb-6 text-white uppercase tracking-widest">Unit Intake Form</h4>

                        <form action="process_add_car.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">

                            <div class="w-full">
                                <label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block tracking-widest">Brand</label>
                                <select id="make-select" name="make" required></select>
                            </div>

                            <div class="w-full">
                                <label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block tracking-widest">Model</label>
                                <select id="model-select" name="model" required></select>
                            </div>

                            <div class="w-full">
                                <label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block tracking-widest">Transmission</label>
                                <select id="transmission-select" name="transmission" required>
                                    <option value="Automatic">Automatic</option>
                                    <option value="Manual">Manual</option>
                                </select>
                            </div>

                            <div class="w-full">
                                <label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block tracking-widest">Fuel Type</label>
                                <select id="fuel-select" name="fuel_type" required>
                                    <option value="Gasoline">Gasoline</option>
                                    <option value="Diesel">Diesel</option>
                                    <option value="Hybrid">Hybrid</option>
                                    <option value="Electric">Electric</option>
                                </select>
                            </div>

                            <div class="w-full">
                                <label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block tracking-widest">Year Produced</label>
                                <select id="year-select" name="year" required>
                                    <?php for($y = date('Y')+1; $y >= 1995; $y--) echo "<option value='$y'>$y</option>"; ?>
                                </select>
                            </div>

                            <div class="w-full">
                                <label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block tracking-widest">Price (PHP)</label>
                                <input type="number" name="price" placeholder="0.00" class="input-facundo w-full h-[50px]" required>
                            </div>

                            <div class="w-full">
                                <label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block tracking-widest">Mileage (KM)</label>
                                <input type="number" name="mileage" placeholder="0" class="input-facundo w-full h-[50px]" required>
                            </div>

                            <div class="md:col-span-2">
                                <label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block tracking-widest">Notes</label>
                                <textarea name="notes" rows="3" placeholder="Condition, color, etc..." class="input-facundo w-full"></textarea>
                            </div>

                            <div class="md:col-span-2">
                                <label class="text-[9px] text-gray-500 uppercase font-bold mb-2 block tracking-widest">Unit Photo</label>
                                <div class="relative border-2 border-dashed border-gray-800 rounded-xl p-4 hover:border-cyberlime transition-colors group text-center">
                                    <input type="file" name="car_image" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    <div class="text-gray-500 group-hover:text-cyberlime transition-colors">
                                        <span class="text-xs uppercase font-black">Click to Upload</span>
                                        <p class="text-[8px]">JPG, PNG or WEBP</p>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="md:col-span-2 bg-cyberlime text-black font-black py-4 rounded-xl uppercase tracking-widest hover:bg-white transition-all text-xs">
                                Add to Inventory
                            </button>
                        </form>
                    </div>

                    <div class="space-y-6">
                        <?php
                            $stc_stats = $pdo->query("SELECT SUM(price_php) as total_value, COUNT(*) as total_units FROM vehicles WHERE status != 'Sold'")->fetch();
                            $sales_stats = $pdo->query("SELECT SUM(price_php) as total_sales FROM vehicles WHERE status = 'Sold'")->fetch();
                        ?>
                        <div class="bg-[#161b22] p-8 rounded-xl border border-gray-800">
                            <span class="text-gray-500 text-[10px] font-bold uppercase tracking-[0.2em]">Live Inventory Value</span>
                            <div class="text-3xl font-black mt-2 text-white">₱<?php echo number_format($stc_stats['total_value'] ?? 0); ?></div>
                        </div>
                        <div class="bg-cyberlime/5 p-8 rounded-xl border border-cyberlime/20">
                            <span class="text-cyberlime text-[10px] font-bold uppercase tracking-[0.2em]">Total Revenue (Sold)</span>
                            <div class="text-3xl font-black mt-2 text-cyberlime">₱<?php echo number_format($sales_stats['total_sales'] ?? 0); ?></div>
                        </div>
                        <div class="bg-[#161b22] p-8 rounded-xl border border-gray-800">
                            <span class="text-gray-500 text-[10px] font-bold uppercase tracking-[0.2em]">Active Units</span>
                            <div class="text-3xl font-black mt-2 text-white"><?php echo $stc_stats['total_units'] ?? 0; ?></div>
                        </div>
                    </div>

                    <div class="lg:col-span-3 mt-4 bg-[#161b22] border border-gray-800 rounded-2xl overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left min-w-[800px]">
                                <thead>
                                    <tr class="bg-[#0b0e14] text-gray-500 text-[9px] font-bold uppercase tracking-widest">
                                        <th class="p-6">Unit Description</th>
                                        <th class="p-6">Price</th>
                                        <th class="p-6 text-center">Status</th>
                                        <th class="p-6 text-right">Quick Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-900">
                                    <?php 
                                        $all_units = $pdo->query("SELECT * FROM vehicles ORDER BY id DESC LIMIT 10")->fetchAll();
                                        foreach($all_units as $u): 
                                    ?>
                                    <tr class="hover:bg-white/[0.02] transition-colors">
                                        <td class="p-6 flex items-center gap-4">
                                            <img src="uploads/cars/<?php echo $u['image_path']; ?>" class="w-12 h-12 rounded-lg object-cover border border-gray-800" alt="Unit">
                                            <div>
                                                <span class="text-white font-bold block text-sm"><?php echo $u['year_produced'] . ' ' . $u['make'] . ' ' . $u['model']; ?></span>
                                                <span class="text-[9px] text-gray-500 uppercase tracking-tighter">Serial: #FCD-<?php echo str_pad($u['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                            </div>
                                        </td>
                                        <td class="p-6 text-gray-300 font-mono text-sm">₱<?php echo number_format($u['price_php']); ?></td>
                                        <td class="p-6 text-center">
                                            <span class="px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-widest 
                                                <?php 
                                                    if($u['status'] == 'Available') echo 'bg-cyberlime/10 text-cyberlime border border-cyberlime/20';
                                                    elseif($u['status'] == 'Reserved') echo 'bg-orange-500/10 text-orange-500 border border-orange-500/20';
                                                    else echo 'bg-gray-800 text-gray-400';
                                                ?>">
                                                <?php echo $u['status']; ?>
                                            </span>
                                        </td>
                                        <td class="p-6 text-right">
                                            <div class="flex items-center justify-end gap-3">
                                                <form action="update_status.php" method="POST" class="flex gap-2">
                                                    <input type="hidden" name="car_id" value="<?php echo $u['id']; ?>">
                                                    <select name="new_status" class="bg-black border border-gray-800 text-[10px] text-gray-400 px-2 py-1 rounded-lg outline-none">
                                                        <option value="Available" <?php echo $u['status'] == 'Available' ? 'selected' : ''; ?>>Available</option>
                                                        <option value="Reserved" <?php echo $u['status'] == 'Reserved' ? 'selected' : ''; ?>>Reserved</option>
                                                        <option value="Sold" <?php echo $u['status'] == 'Sold' ? 'selected' : ''; ?>>Sold</option>
                                                    </select>
                                                    <button type="submit" class="bg-white/5 hover:bg-cyberlime hover:text-black text-white px-3 py-1 rounded-lg text-[9px] font-bold uppercase transition-all">Apply</button>
                                                </form>
                                                <form action="delete_listing.php" method="POST" onsubmit="return confirm('Delete permanently?');" class="inline">
                                                    <input type="hidden" name="car_id" value="<?php echo $u['id']; ?>">
                                                    <button type="submit" class="text-gray-600 hover:text-red-500 transition-all p-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="crm" class="py-24 px-6 bg-black">
            <div class="max-w-7xl mx-auto">
                <div class="mb-12">
                    <h2 class="text-cyberlime font-bold uppercase tracking-[0.3em] text-xs mb-3">Client Relations</h2>
                    <h3 class="text-4xl font-black italic uppercase tracking-tighter text-white">Lead <span class="text-cyberlime">Pipeline</span></h3>
                </div>

                <div class="bg-[#161b22] border border-gray-800 rounded-2xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-[#0b0e14] text-gray-500 text-[9px] font-bold uppercase tracking-widest border-b border-gray-800">
                                    <th class="p-6">Date Received</th>
                                    <th class="p-6">Client Info</th>
                                    <th class="p-6">Unit Requested</th>
                                    <th class="p-6">Lead Status</th>
                                    <th class="p-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-900">
                                <?php 
                                    // Joining the leads table with vehicles table to see which car they want
                                    $leads = $pdo->query("SELECT leads.*, vehicles.make, vehicles.model 
                                                         FROM leads 
                                                         JOIN vehicles ON leads.vehicle_id = vehicles.id 
                                                         ORDER BY leads.created_at DESC")->fetchAll();

                                    foreach($leads as $lead): 
                                ?>
                                <tr class="hover:bg-white/[0.02] transition-colors">
                                    <td class="p-6 text-gray-500 text-xs font-mono">
                                        <?php echo date('M d, Y', strtotime($lead['created_at'])); ?>
                                    </td>
                                    <td class="p-6">
                                        <span class="text-white font-bold block"><?php echo $lead['client_name']; ?></span>
                                        <a href="tel:<?php echo $lead['client_phone']; ?>" class="text-cyberlime text-[10px] hover:underline font-bold">
                                            <?php echo $lead['client_phone']; ?>
                                        </a>
                                    </td>
                                    <td class="p-6">
                                        <span class="text-gray-300 text-xs uppercase font-bold tracking-tight">
                                            <?php echo $lead['make'] . ' ' . $lead['model']; ?>
                                         Ferry</span>
                                    </td>
                                    <td class="p-6">
                                        <span class="px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-widest 
                                            <?php 
                                                if($lead['status'] == 'New') echo 'bg-blue-500/10 text-blue-400 border border-blue-500/20';
                                                elseif($lead['status'] == 'Contacted') echo 'bg-purple-500/10 text-purple-400 border border-purple-500/20';
                                                else echo 'bg-cyberlime/10 text-cyberlime border border-cyberlime/20';
                                            ?>">
                                            <?php echo $lead['status']; ?>
                                        </span>
                                    </td>
                                    <td class="p-6 text-right">
                                        <div class="flex gap-4 justify-end items-center">
                                            <form action="update_lead.php" method="POST" class="flex items-center gap-2">
                                                <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">

                                                <button name="new_status" value="Contacted" class="text-[9px] font-black uppercase text-gray-500 hover:text-white transition-all">
                                                    Mark Contacted
                                                </button>

                                                <span class="text-gray-800">|</span>

                                                <button name="new_status" value="Sold" class="text-[9px] font-black uppercase text-cyberlime hover:text-white transition-all">
                                                    Convert to Sale
                                                </button>
                                            </form>

                                            <form action="delete_lead.php" method="POST" onsubmit="return confirm('Archive this lead? This cannot be undone.');" class="inline">
                                                <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">
                                                <button type="submit" class="text-gray-600 hover:text-red-500 transition-all">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>

                                <?php if(empty($leads)): ?>
                                    <tr>
                                        <td colspan="5" class="p-20 text-center text-gray-600 uppercase text-[10px] font-bold tracking-[0.5em]">
                                            No active leads in pipeline
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <?php endif; ?>

        <section id="buyer-dashboard" class="py-24 px-6 bg-[#0b0e14] border-t border-gray-900 hidden">
            <div class="max-w-7xl mx-auto">
                
                <div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-6">
                    <div>
                        <h2 class="text-cyberlime font-bold uppercase tracking-[0.3em] text-xs mb-3">Welcome Back, Client</h2>
                        <h3 class="text-4xl font-black italic uppercase tracking-tighter text-white">My <span class="text-cyberlime">Garage</span></h3>
                    </div>
                    <div class="flex gap-4">
                        <div class="text-right">
                            <span class="block text-[10px] text-gray-500 uppercase font-bold">Account Manager</span>
                            <span class="text-white font-bold">Facundo Concierge #04</span>
                        </div>
                        <div class="w-12 h-12 bg-gray-800 rounded-full border border-cyberlime flex items-center justify-center">
                            <span class="text-cyberlime font-black">JS</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                    
                    <div class="lg:col-span-2 space-y-6">
                        <h4 class="text-sm font-bold text-gray-400 uppercase tracking-[0.2em] mb-6">Active Inquiries</h4>
                        
                        <div class="bg-[#161b22] border border-gray-800 rounded-2xl p-8">
                            <div class="flex flex-col md:flex-row gap-6 mb-10">
                                <div class="w-full md:w-48 aspect-video bg-gray-900 rounded-xl overflow-hidden border border-gray-800 flex items-center justify-center">
                                    <span class="text-[10px] uppercase font-bold text-gray-700 italic">LC300 Photo</span>
                                </div>
                                <div>
                                    <h5 class="text-xl font-black text-white italic">TOYOTA LAND CRUISER 300</h5>
                                    <p class="text-gray-500 text-xs mb-4">Inquiry Ref: #FAC-99281</p>
                                    <span class="bg-cyberlime/10 text-cyberlime text-[9px] font-black px-3 py-1 rounded-full uppercase">Processing Documents</span>
                                </div>
                            </div>

                            <div class="flex justify-between relative">
                                <div class="status-step step-active">
                                    <div class="step-line"></div>
                                    <div class="step-circle"><span class="text-[10px] font-bold">01</span></div>
                                    <span class="text-[9px] mt-4 font-bold text-gray-500 uppercase">Inquiry</span>
                                </div>
                                <div class="status-step step-active">
                                    <div class="step-line"></div>
                                    <div class="step-circle"><span class="text-[10px] font-bold">02</span></div>
                                    <span class="text-[9px] mt-4 font-bold text-gray-500 uppercase">Viewing</span>
                                </div>
                                <div class="status-step step-active">
                                    <div class="step-line"></div>
                                    <div class="step-circle"><span class="text-[10px] font-bold">03</span></div>
                                    <span class="text-[9px] mt-4 font-bold text-gray-500 uppercase">Approval</span>
                                </div>
                                <div class="status-step">
                                    <div class="step-circle"><span class="text-[10px] font-bold">04</span></div>
                                    <span class="text-[9px] mt-4 font-bold text-gray-500 uppercase">Release</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-[#161b22] p-6 rounded-2xl border border-gray-800">
                            <h4 class="text-xs font-black text-white uppercase tracking-widest mb-4">Required Docs</h4>
                            <ul class="space-y-3">
                                <li class="flex items-center justify-between text-[10px] p-3 bg-black/40 rounded-lg">
                                    <span class="text-gray-400 font-bold uppercase">Valid ID (Primary)</span>
                                    <span class="text-cyberlime">✓ Uploaded</span>
                                </li>
                                <li class="flex items-center justify-between text-[10px] p-3 bg-black/40 rounded-lg">
                                    <span class="text-gray-400 font-bold uppercase">Proof of Income</span>
                                    <span class="text-orange-500 underline cursor-pointer">Missing</span>
                                </li>
                            </ul>
                        </div>

                        <button class="w-full bg-transparent border border-gray-700 text-white py-4 rounded-xl text-[10px] font-black uppercase tracking-widest hover:border-cyberlime transition">
                            Message Concierge
                        </button>
                    </div>

                </div>
            </div>
        </section>

        <footer class="relative pt-32 pb-12 px-6 overflow-hidden border-t border-gray-900">
    
            <div class="absolute top-10 left-1/2 -translate-x-1/2 footer-logo-bg font-black italic select-none pointer-events-none">
                FACUNDO
            </div>

            <div class="max-w-7xl mx-auto relative z-10">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-20">
                    
                    <div class="md:col-span-1">
                        <div class="text-2xl font-black italic mb-6">
                            FACU<span class="text-cyberlime">NDO</span>
                        </div>
                        <p class="text-gray-500 text-xs leading-relaxed uppercase tracking-wider">
                            The premium standard in Philippine automotive commerce. Showroom and management systems redefined.
                        </p>
                    </div>

                    <div>
                        <h4 class="text-white font-bold text-[10px] uppercase tracking-[0.3em] mb-6">Inventory</h4>
                        <ul class="space-y-4 flex flex-col">
                            <a href="#showroom" class="footer-link">Available Units</a>
                            <a href="#stc" class="footer-link">STC Management</a>
                            <a href="#finance" class="footer-link">Financing Options</a>
                            <a href="#" class="footer-link">Pre-Owned Luxury</a>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-white font-bold text-[10px] uppercase tracking-[0.3em] mb-6">System</h4>
                        <ul class="space-y-4 flex flex-col">
                            <a href="#crm" class="footer-link">Lead Pipeline</a>
                            <a href="#" class="footer-link">Sales Dashboard</a>
                            <a href="#" class="footer-link">Admin Login</a>
                            <a href="#" class="footer-link">API Documentation</a>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-white font-bold text-[10px] uppercase tracking-[0.3em] mb-6">Headquarters</h4>
                        <p class="text-gray-500 text-[10px] leading-loose uppercase">
                            1810 Sto Niño Street,<br>
                            Caloocan City, Camarin,<br>
                            Manila, Philippines 1400
                        </p>
                        <div class="mt-6 text-cyberlime font-black text-sm">
                            +63 2 8555 9999
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-gray-900 flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="text-[9px] text-gray-600 uppercase tracking-[0.4em]">
                        © 2026 Facundo Automotive System // All Rights Reserved
                    </div>
                    
                    <button onclick="window.scrollTo(0,0)" class="back-to-top px-4 py-2 rounded-full text-[9px] font-bold uppercase tracking-widest flex items-center gap-2">
                        Back to Top ↑
                    </button>

                    <div class="flex gap-6">
                        <div class="w-4 h-4 bg-gray-800 rounded-full hover:bg-cyberlime transition cursor-pointer"></div>
                        <div class="w-4 h-4 bg-gray-800 rounded-full hover:bg-cyberlime transition cursor-pointer"></div>
                        <div class="w-4 h-4 bg-gray-800 rounded-full hover:bg-cyberlime transition cursor-pointer"></div>
                    </div>
                </div>
            </div>
        </footer>
        
        <div id="inquiryModal" class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-sm hidden flex items-center justify-center p-6">
            <div class="bg-[#161b22] border border-gray-800 w-full max-w-md rounded-2xl p-8 relative">
                <button onclick="closeInquiry()" class="absolute top-4 right-4 text-gray-500 hover:text-white text-xl">✕</button>

                <h3 class="text-2xl font-black text-white italic uppercase tracking-tighter mb-1">Secure <span class="text-cyberlime">Unit</span></h3>
                <p id="unitNameDisplay" class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-6 border-b border-gray-800 pb-4"></p>

                <form action="process_inquiry.php" method="POST" class="space-y-4">
                    <input type="hidden" name="vehicle_id" id="modal_vehicle_id">

                    <div class="space-y-1">
                        <label class="text-[9px] font-bold text-gray-500 uppercase tracking-widest ml-2">Full Name</label>
                        <input type="text" name="client_name" class="w-full bg-black border border-gray-800 rounded-xl px-4 py-3 text-white focus:border-cyberlime outline-none" placeholder="Juan Dela Cruz" required>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[9px] font-bold text-gray-500 uppercase tracking-widest ml-2">Phone Number</label>
                        <input type="text" name="client_phone" class="w-full bg-black border border-gray-800 rounded-xl px-4 py-3 text-white focus:border-cyberlime outline-none" placeholder="0917 XXX XXXX" required>
                    </div>

                    <button type="submit" class="w-full bg-cyberlime text-black font-black py-4 rounded-xl uppercase tracking-widest hover:bg-white transition-all text-xs mt-4">
                        Submit Inquiry
                    </button>
                </form>
            </div>
        </div>

        <script>
        function openInquiry(id, name) {
            document.getElementById('modal_vehicle_id').value = id;
            document.getElementById('unitNameDisplay').innerText = name;
            document.getElementById('inquiryModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Stop background scrolling
        }

        function closeInquiry() {
            document.getElementById('inquiryModal').classList.add('hidden');
            document.body.style.overflow = 'auto'; // Enable scrolling again
        }
        </script>

    </body>

        <script>
            function setRole(role) {
                const userDisplay = document.getElementById('user-display');
                const stcSection = document.getElementById('stc');
                const crmSection = document.getElementById('crm');
                const buyerDash = document.getElementById('buyer-dashboard');

                if (role === 'admin') {
                    userDisplay.innerHTML = '<span class="text-xs font-bold text-white">System Admin</span> <span class="role-badge role-admin">Full Access</span>';
                    stcSection.classList.remove('hidden');
                    crmSection.classList.remove('hidden');
                    buyerDash.classList.add('hidden'); // Hide buyer dash for admin
                } else {
                    userDisplay.innerHTML = '<span class="text-xs font-bold text-white">Verified Buyer</span> <span class="role-badge role-buyer">Viewer</span>';
                    stcSection.classList.add('hidden');
                    crmSection.classList.add('hidden');
                    buyerDash.classList.remove('hidden'); // SHOW Buyer Dash
                }
            }

            function openLogin() {
                document.getElementById('login-overlay').classList.remove('hidden');
            }

            function closeLogin() {
                document.getElementById('login-overlay').classList.add('hidden');
                setRole('admin'); // Simulate successful admin login
            }
        </script>
    	
    	<script>
            document.addEventListener('DOMContentLoaded', function() {

                // --- 1. SUCCESS ALERT TIMER ---
                const alert = document.getElementById('success-alert');
                if (alert) {
                    setTimeout(() => {
                        alert.style.transition = "opacity 0.8s ease";
                        alert.style.opacity = "0";
                        setTimeout(() => { alert.remove(); }, 800);
                    }, 3000); 
                }

                if (window.history.replaceState && window.location.search.includes('success')) {
                    const url = new URL(window.location);
                    url.searchParams.delete('success');
                    window.history.replaceState({}, document.title, url);
                }

                // --- 2. CAR DATA BRAIN ---
                const carData = {
                    "Toyota": ["Vios", "Fortuner", "Innova", "Hilux", "Wigo", "Rush", "Hiace", "Corolla Cross", "Land Cruiser", "Alphard", "Raize", "Avanza"],
                    "Honda": ["Civic", "City", "CR-V", "HR-V", "Brio", "Accord", "BR-V", "Jazz"],
                    "Mitsubishi": ["Montero Sport", "Xpander", "Mirage G4", "L300", "Strada", "Pajero", "Outlander"],
                    "Mercedes-Benz": ["C-Class", "E-Class", "S-Class", "GLA", "GLC", "GLE", "GLS", "A-Class", "CLA", "G-Wagon", "V-Class"],
                    "BMW": ["3 Series", "5 Series", "7 Series", "X1", "X3", "X5", "X7", "M4", "Z4"],
                    "Ford": ["Ranger", "Everest", "Territory", "Mustang", "Explorer", "F-150", "EcoSport"],
                    "Nissan": ["Navara", "Terra", "Almera", "Urvan", "Patrol", "Kicks", "370Z"],
                    "Isuzu": ["mu-X", "D-MAX", "Traviz", "N-Series"],
                    "Hyundai": ["Stargazer", "Creta", "Tucson", "Santa Fe", "Staria", "Accent", "Kona"],
                    "Suzuki": ["Ertiga", "Jimny", "Swift", "Dzire", "S-Presso", "Vitara", "XL7"],
                    "Mazda": ["Mazda 2", "Mazda 3", "Mazda 6", "CX-3", "CX-5", "CX-9", "MX-5"],
                    "Kia": ["Seltos", "Stonic", "Sorento", "Carnival", "Soluto", "Sportage"]
                };

                // --- 3. INITIALIZE SEARCHABLE UI (CHOICES.JS) ---

                const makeChoices = new Choices('#make-select', { 
                    searchEnabled: true, 
                    itemSelectText: '',
                    placeholderValue: 'Select Brand'
                });

                const modelChoices = new Choices('#model-select', { 
                    searchEnabled: true, 
                    itemSelectText: '',
                    placeholderValue: 'Select Model'
                });

                const transChoices = new Choices('#transmission-select', { 
                    searchEnabled: false, 
                    itemSelectText: '',
                    shouldSort: false
                });

                // NEW: Initialize Fuel Dropdown
                const fuelChoices = new Choices('#fuel-select', { 
                    searchEnabled: false, 
                    itemSelectText: '',
                    shouldSort: false
                });

                // NEW: Initialize Year Dropdown
                const yearChoices = new Choices('#year-select', { 
                    searchEnabled: true, 
                    itemSelectText: ''
                });

                // --- TRANSMISSION SYNC FIX ---
                // This forces the value to update whenever you click a choice
                document.getElementById('transmission-select').addEventListener('change', function(event) {
                    console.log("Transmission ready for PHP:", event.detail.value);
                });

                // --- 4. POPULATE DROPDOWNS ---

                const makeList = Object.keys(carData).sort().map(make => ({ value: make, label: make }));
                makeChoices.setChoices(makeList, 'value', 'label', true);

                document.getElementById('make-select').addEventListener('change', function(event) {
                    const selectedMake = event.detail.value;
                    modelChoices.clearStore(); 
                    modelChoices.clearChoices();

                    if (carData[selectedMake]) {
                        const models = carData[selectedMake].sort().map(m => ({ value: m, label: m }));
                        modelChoices.setChoices(models, 'value', 'label', true);
                    }
                });

                // --- 5. IMAGE PREVIEW LOGIC ---
                const fileInput = document.querySelector('input[name="car_image"]');
                if (fileInput) {
                    fileInput.addEventListener('change', function() {
                        const file = this.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const label = fileInput.nextElementSibling;
                                if(label) label.innerHTML = `<span class="text-cyberlime font-black">IMAGE READY:</span> ${file.name}`;
                            }
                            reader.readAsDataURL(file);
                        }
                    });
                }
            });
        </script>

</html>
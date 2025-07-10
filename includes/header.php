<?php
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK MOORA - SMKS YAPRI JAKARTA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af',
                        secondary: '#64748b'
                    },
                    animation: {
                        'slide-down': 'slideDown 0.3s ease-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'fade-in': 'fadeIn 0.2s ease-out'
                    },
                    keyframes: {
                        slideDown: {
                            '0%': { transform: 'translateY(-10px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(0)', opacity: '1' },
                            '100%': { transform: 'translateY(-10px)', opacity: '0' }
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .mobile-menu-enter {
            animation: slide-down 0.3s ease-out;
        }
        .mobile-menu-exit {
            animation: slide-up 0.3s ease-out;
        }
        .dropdown-enter {
            animation: fade-in 0.2s ease-out;
        }
        /* Custom scrollbar for mobile menu */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
        }
        
        /* Print styles */
        @media print {
            body { 
                background: white !important;
                font-size: 12pt;
            }
            .no-print { 
                display: none !important; 
            }
            .container {
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            table {
                page-break-inside: avoid;
            }
            .bg-gradient-to-r {
                background: #3b82f6 !important;
                color: white !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo and Brand -->
                <div class="flex items-center space-x-4">
                    <a href="/spk_mora" class="flex items-center space-x-3 group">
                        <div class="bg-white/10 p-2 rounded-lg group-hover:bg-white/20 transition-all duration-200">
                            <i class="fas fa-chart-line text-xl text-white"></i>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="font-bold text-xl tracking-tight">SPK MOORA</h1>
                            <p class="text-xs text-blue-200 -mt-1">SMKS YAPRI JAKARTA</p>
                        </div>
                        <div class="sm:hidden">
                            <h1 class="font-bold text-lg">SPK MOORA</h1>
                        </div>
                    </a>
                </div>
                
                <?php if ($auth->isLoggedIn()): ?>
                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-1">
                    <!-- Dashboard -->
                    <a href="/spk_mora" class="flex items-center px-4 py-2 rounded-lg text-sm font-medium hover:bg-white/10 transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && !strpos($_SERVER['REQUEST_URI'], '/admin/') ? 'bg-white/20 text-white' : 'text-blue-100 hover:text-white'; ?>">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Dashboard
                    </a>
                    
                    <!-- Admin Dropdown -->
                    <?php if ($auth->isAdmin()): ?>
                    <div class="relative group">
                        <button class="flex items-center px-4 py-2 rounded-lg text-sm font-medium text-blue-100 hover:text-white hover:bg-white/10 transition-all duration-200 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/') !== false ? 'bg-white/20 text-white' : ''; ?>">
                            <i class="fas fa-cog mr-2"></i>
                            Admin
                            <i class="fas fa-chevron-down ml-2 text-xs group-hover:rotate-180 transition-transform duration-200"></i>
                        </button>
                        <div class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-xl py-2 z-50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 dropdown-enter">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Manajemen Data</p>
                            </div>
                            <a href="/spk_mora/admin/alternatif/index.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-150 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/alternatif/') !== false ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-500' : ''; ?>">
                                <div class="bg-blue-100 p-1.5 rounded-lg mr-3">
                                    <i class="fas fa-list-alt text-blue-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="font-medium">Alternatif</p>
                                    <p class="text-xs text-gray-500">Kelola metode pembelajaran</p>
                                </div>
                            </a>
                            <a href="/spk_mora/admin/kriteria/index.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 transition-colors duration-150 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/kriteria/') !== false ? 'bg-green-50 text-green-700 border-r-2 border-green-500' : ''; ?>">
                                <div class="bg-green-100 p-1.5 rounded-lg mr-3">
                                    <i class="fas fa-tasks text-green-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="font-medium">Kriteria</p>
                                    <p class="text-xs text-gray-500">Kelola kriteria penilaian</p>
                                </div>
                            </a>
                            <a href="/spk_mora/admin/penilaian/index.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors duration-150 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/penilaian/') !== false ? 'bg-purple-50 text-purple-700 border-r-2 border-purple-500' : ''; ?>">
                                <div class="bg-purple-100 p-1.5 rounded-lg mr-3">
                                    <i class="fas fa-star-half-alt text-purple-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="font-medium">Penilaian MOORA</p>
                                    <p class="text-xs text-gray-500">Hitung dan analisis</p>
                                </div>
                            </a>
                            <a href="/spk_mora/admin/siswa/index.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 transition-colors duration-150 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/siswa/') !== false ? 'bg-yellow-50 text-yellow-700 border-r-2 border-yellow-500' : ''; ?>">
                                <div class="bg-yellow-100 p-1.5 rounded-lg mr-3">
                                    <i class="fas fa-user-graduate text-yellow-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="font-medium">Data Siswa</p>
                                    <p class="text-xs text-gray-500">Kelola data siswa</p>
                                </div>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Laporan -->
                    <a href="/spk_mora/hasil.php" class="flex items-center px-4 py-2 rounded-lg text-sm font-medium hover:bg-white/10 transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'hasil.php' ? 'bg-white/20 text-white' : 'text-blue-100 hover:text-white'; ?>">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Laporan
                    </a>
                    
                    <!-- User Menu -->
                    <div class="relative group ml-4">
                        <button class="flex items-center space-x-2 px-3 py-2 rounded-lg text-sm font-medium text-blue-100 hover:text-white hover:bg-white/10 transition-all duration-200">
                            <div class="bg-white/20 p-1.5 rounded-full">
                                <i class="fas fa-user text-xs"></i>
                            </div>
                            <span class="hidden xl:block"><?php echo htmlspecialchars($auth->getCurrentUser()['username']); ?></span>
                            <i class="fas fa-chevron-down text-xs group-hover:rotate-180 transition-transform duration-200"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl py-2 z-50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($auth->getCurrentUser()['username']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo $auth->isAdmin() ? 'Administrator' : 'User'; ?></p>
                            </div>
                            <form action="/spk_mora/logout.php" method="post">
                                <button type="submit" class="flex items-center w-full px-4 py-3 text-sm text-red-700 hover:bg-red-50 transition-colors duration-150">
                                    <i class="fas fa-sign-out-alt mr-3 text-red-500"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile menu button -->
                <div class="lg:hidden flex items-center space-x-2">
                    <!-- User info for mobile -->
                    <div class="flex items-center space-x-2">
                        <div class="bg-white/20 p-1.5 rounded-full">
                            <i class="fas fa-user text-xs"></i>
                        </div>
                        <span class="text-sm font-medium hidden sm:block"><?php echo htmlspecialchars($auth->getCurrentUser()['username']); ?></span>
                    </div>
                    <button id="mobile-menu-button" class="p-2 rounded-lg text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/20 transition-all duration-200">
                        <svg id="menu-icon" class="h-6 w-6 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <svg id="close-icon" class="h-6 w-6 hidden transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Mobile menu -->
            <div id="mobile-menu" class="lg:hidden hidden">
                <div class="px-2 pt-2 pb-4 space-y-1 bg-blue-700/50 backdrop-blur-sm rounded-b-xl mt-2 custom-scrollbar max-h-96 overflow-y-auto">
                    <?php if ($auth->isLoggedIn()): ?>
                    <!-- Dashboard -->
                    <a href="/spk_mora" class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && !strpos($_SERVER['REQUEST_URI'], '/admin/') ? 'bg-white/20 text-white' : 'text-blue-100 hover:text-white hover:bg-white/10'; ?>">
                        <i class="fas fa-tachometer-alt mr-3 w-5"></i>
                        Dashboard
                    </a>
                    
                    <?php if ($auth->isAdmin()): ?>
                    <!-- Admin Section -->
                    <div class="border-t border-blue-600/50 my-3 pt-3">
                        <p class="px-4 py-1 text-xs font-semibold text-blue-200 uppercase tracking-wider">Admin Menu</p>
                        
                        <a href="/spk_mora/admin/alternatif/index.php" class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-all duration-200 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/alternatif/') !== false ? 'bg-white/20 text-white' : 'text-blue-100 hover:text-white hover:bg-white/10'; ?>">
                            <div class="bg-blue-500/30 p-1.5 rounded-lg mr-3">
                                <i class="fas fa-list-alt text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium">Alternatif</p>
                                <p class="text-xs text-blue-200">Kelola metode pembelajaran</p>
                            </div>
                        </a>
                        
                        <a href="/spk_mora/admin/kriteria/index.php" class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-all duration-200 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/kriteria/') !== false ? 'bg-white/20 text-white' : 'text-blue-100 hover:text-white hover:bg-white/10'; ?>">
                            <div class="bg-green-500/30 p-1.5 rounded-lg mr-3">
                                <i class="fas fa-tasks text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium">Kriteria</p>
                                <p class="text-xs text-blue-200">Kelola kriteria penilaian</p>
                            </div>
                        </a>
                        
                        <a href="/spk_mora/admin/penilaian/index.php" class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-all duration-200 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/penilaian/') !== false ? 'bg-white/20 text-white' : 'text-blue-100 hover:text-white hover:bg-white/10'; ?>">
                            <div class="bg-purple-500/30 p-1.5 rounded-lg mr-3">
                                <i class="fas fa-star-half-alt text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium">Penilaian MOORA</p>
                                <p class="text-xs text-blue-200">Hitung dan analisis</p>
                            </div>
                        </a>
                        
                        <a href="/spk_mora/admin/siswa/index.php" class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-all duration-200 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/siswa/') !== false ? 'bg-white/20 text-white' : 'text-blue-100 hover:text-white hover:bg-white/10'; ?>">
                            <div class="bg-yellow-500/30 p-1.5 rounded-lg mr-3">
                                <i class="fas fa-user-graduate text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium">Data Siswa</p>
                                <p class="text-xs text-blue-200">Kelola data siswa</p>
                            </div>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Laporan -->
                    <div class="border-t border-blue-600/50 my-3 pt-3">
                        <a href="/spk_mora/hasil.php" class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'hasil.php' ? 'bg-white/20 text-white' : 'text-blue-100 hover:text-white hover:bg-white/10'; ?>">
                            <i class="fas fa-chart-bar mr-3 w-5"></i>
                            Laporan
                        </a>
                    </div>
                    
                    <!-- Logout -->
                    <div class="border-t border-blue-600/50 my-3 pt-3">
                        <form action="/spk_mora/logout.php" method="post">
                            <button type="submit" class="flex items-center w-full px-4 py-3 rounded-lg text-base font-medium text-red-200 hover:text-red-100 hover:bg-red-500/20 transition-all duration-200">
                                <i class="fas fa-sign-out-alt mr-3 w-5"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">

<script>
// Enhanced Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuIcon = document.getElementById('menu-icon');
    const closeIcon = document.getElementById('close-icon');
    let isMenuOpen = false;
    
    function toggleMenu() {
        isMenuOpen = !isMenuOpen;
        
        if (isMenuOpen) {
            mobileMenu.classList.remove('hidden');
            mobileMenu.classList.add('mobile-menu-enter');
            menuIcon.classList.add('hidden');
            closeIcon.classList.remove('hidden');
            // Prevent body scroll when menu is open
            document.body.style.overflow = 'hidden';
        } else {
            mobileMenu.classList.add('mobile-menu-exit');
            menuIcon.classList.remove('hidden');
            closeIcon.classList.add('hidden');
            document.body.style.overflow = '';
            
            setTimeout(() => {
                mobileMenu.classList.add('hidden');
                mobileMenu.classList.remove('mobile-menu-enter', 'mobile-menu-exit');
            }, 300);
        }
    }
    
    mobileMenuButton.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleMenu();
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (isMenuOpen && !mobileMenu.contains(event.target) && !mobileMenuButton.contains(event.target)) {
            toggleMenu();
        }
    });
    
    // Close menu when clicking on menu links
    const menuLinks = mobileMenu.querySelectorAll('a');
    menuLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (isMenuOpen) {
                toggleMenu();
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024 && isMenuOpen) {
            toggleMenu();
        }
    });
    
    // Smooth scroll for anchor links
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
});

// Add scroll effect to navbar
window.addEventListener('scroll', function() {
    const nav = document.querySelector('nav');
    if (window.scrollY > 10) {
        nav.classList.add('shadow-2xl');
    } else {
        nav.classList.remove('shadow-2xl');
    }
});
</script>

<?php
// Prevent any whitespace output
?>

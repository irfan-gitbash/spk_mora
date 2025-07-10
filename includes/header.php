<?php
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
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
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <nav class="bg-primary text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/spk_mora" class="font-bold text-xl">SPK MOORA</a>
                </div>
                
                <!-- Mobile menu button -->
                <div class="flex md:hidden items-center">
                    <button id="mobile-menu-button" class="text-white hover:text-gray-200 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
                
                <?php if ($auth->isLoggedIn()): ?>
                <!-- Desktop menu -->
                <div class="hidden md:flex items-center space-x-4">
                    <a href="/spk_mora" class="hover:text-gray-200 px-3 py-2">Dashboard</a>
                    
                    <!-- Admin Menu -->
                    <?php if ($auth->isAdmin()): ?>
                    <div class="relative group">
                        <button class="flex items-center hover:text-gray-200 px-3 py-2">
                            <span>Admin</span>
                            <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                            <a href="/spk_mora/admin/alternatif/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/alternatif/') !== false ? 'bg-gray-100' : ''; ?>">
                                <i class="fas fa-list-alt mr-2"></i>Alternatif
                            </a>
                            <a href="/spk_mora/admin/kriteria/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/kriteria/') !== false ? 'bg-gray-100' : ''; ?>">
                                <i class="fas fa-tasks mr-2"></i>Kriteria
                            </a>
                            <a href="/spk_mora/admin/penilaian/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/penilaian/') !== false ? 'bg-gray-100' : ''; ?>">
                                <i class="fas fa-star-half-alt mr-2"></i>Penilaian
                            </a>
                            <a href="/spk_mora/admin/siswa/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/siswa/') !== false ? 'bg-gray-100' : ''; ?>">
                                <i class="fas fa-user-graduate mr-2"></i>Data Siswa
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <a href="/spk_mora/hasil.php" class="hover:text-gray-200 px-3 py-2">Laporan</a>
                    <form action="/spk_mora/logout.php" method="post" class="inline">
                        <button type="submit" class="hover:text-gray-200 px-3 py-2">Logout</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Mobile menu, show/hide based on menu state -->
            <div id="mobile-menu" class="md:hidden hidden">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <?php if ($auth->isLoggedIn()): ?>
                    <a href="/spk_mora" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700">Dashboard</a>
                    
                    <?php if ($auth->isAdmin()): ?>
                    <div class="border-t border-blue-700 my-2"></div>
                    <p class="px-3 py-1 text-sm text-gray-300">Admin Menu</p>
                    <a href="/spk_mora/admin/alternatif/index.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/alternatif/') !== false ? 'bg-blue-700' : ''; ?>">
                        <i class="fas fa-list-alt mr-2"></i>Alternatif
                    </a>
                    <a href="/spk_mora/admin/kriteria/index.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/kriteria/') !== false ? 'bg-blue-700' : ''; ?>">
                        <i class="fas fa-tasks mr-2"></i>Kriteria
                    </a>
                    <a href="/spk_mora/admin/penilaian/index.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/penilaian/') !== false ? 'bg-blue-700' : ''; ?>">
                        <i class="fas fa-star-half-alt mr-2"></i>Penilaian
                    </a>
                    <a href="/spk_mora/admin/siswa/index.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/siswa/') !== false ? 'bg-blue-700' : ''; ?>">
                        <i class="fas fa-user-graduate mr-2"></i>Data Siswa
                    </a>
                    <div class="border-t border-blue-700 my-2"></div>
                    <?php endif; ?>
                    
                    <a href="/spk_mora/hasil.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700">Hasil</a>
                    <form action="/spk_mora/logout.php" method="post">
                        <button type="submit" class="w-full text-left block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700">Logout</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <main class="max-w-7xl mx-auto px-4 py-8">

<script>
    // Toggle mobile menu
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        
        if (!mobileMenu.contains(event.target) && event.target !== mobileMenuButton && !mobileMenuButton.contains(event.target)) {
            mobileMenu.classList.add('hidden');
        }
    });
</script>
<?php
// Add this closing PHP tag to prevent any whitespace from being output
// This ensures no output is sent before header() calls
?>

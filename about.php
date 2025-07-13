<?php
require_once 'includes/auth.php';

$auth->requireLogin();

// Get user data
$user = $auth->getCurrentUser();
require_once 'includes/header.php';
?>

<!-- About Us Hero Section -->
<div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl p-6 md:p-8 mb-8 shadow-lg">
    <div class="flex flex-col md:flex-row items-center justify-between">
        <div class="mb-4 md:mb-0">
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-2">Tentang Kami</h1>
            <p class="text-blue-100 text-sm md:text-base">SMKS YAPRI JAKARTA</p>
            <p class="text-blue-200 text-xs md:text-sm mt-1">Sistem Pendukung Keputusan Multi-Objective Optimization by Ratio Analysis</p>
        </div>
        <div class="hidden md:block">
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                <i class="fas fa-school text-3xl text-blue-200"></i>
            </div>
        </div>
    </div>
</div>

<!-- School Information Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 mb-8">
    <!-- School Logo Section -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-lg p-6 text-center">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Logo Sekolah</h2>
            <div class="flex justify-center mb-4">
                <!-- School logo from images folder -->
                <div class="w-32 h-32 rounded-full overflow-hidden shadow-lg border-4 border-white">
                    <img src="images/logo.jpg" alt="Logo SMKS YAPRI Jakarta" class="w-full h-full object-cover">
                </div>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">SMKS YAPRI JAKARTA</h3>
            <p class="text-sm text-gray-600">Sekolah Menengah Kejuruan Swasta</p>
            <p class="text-xs text-gray-500 mt-1">Yayasan Pendidikan Republik Indonesia</p>
        </div>
    </div>
    
    <!-- Vision and Mission Section -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Visi & Misi Sekolah</h2>
            
            <!-- Vision -->
            <div class="mb-6">
                <div class="flex items-center mb-3">
                    <div class="bg-blue-100 p-2 rounded-lg mr-3">
                        <i class="fas fa-eye text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Visi</h3>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                    <p class="text-gray-700 leading-relaxed">
                        Menjadi sekolah kejuruan yang unggul dalam menghasilkan lulusan yang berkarakter, kompeten, dan siap bersaing di era global dengan mengedepankan nilai-nilai Pancasila dan teknologi modern.
                    </p>
                </div>
            </div>
            
            <!-- Mission -->
            <div>
                <div class="flex items-center mb-3">
                    <div class="bg-green-100 p-2 rounded-lg mr-3">
                        <i class="fas fa-bullseye text-green-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Misi</h3>
                </div>
                <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                    <ul class="text-gray-700 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 flex-shrink-0"></i>
                            <span>Menyelenggarakan pendidikan kejuruan yang berkualitas dengan kurikulum yang relevan dengan kebutuhan industri</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 flex-shrink-0"></i>
                            <span>Mengembangkan karakter siswa yang berakhlak mulia, disiplin, dan bertanggung jawab</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 flex-shrink-0"></i>
                            <span>Membekali siswa dengan keterampilan teknis dan soft skills yang dibutuhkan dunia kerja</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 flex-shrink-0"></i>
                            <span>Menjalin kerjasama yang baik dengan dunia usaha dan dunia industri (DUDI)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 flex-shrink-0"></i>
                            <span>Mengintegrasikan teknologi modern dalam proses pembelajaran</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- School Profile Section -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 mb-8">
    <!-- School Information -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Profil Sekolah</h2>
        <div class="space-y-4">
            <div class="flex items-center">
                <div class="bg-blue-100 p-2 rounded-lg mr-3">
                    <i class="fas fa-school text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Nama Sekolah</p>
                    <p class="font-semibold text-gray-800">SMKS YAPRI JAKARTA</p>
                </div>
            </div>
            <div class="flex items-center">
                <div class="bg-green-100 p-2 rounded-lg mr-3">
                    <i class="fas fa-map-marker-alt text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Alamat</p>
                    <p class="font-semibold text-gray-800">Jl. KH. Muhasyim IV No.7, RT.12/RW.6, Cilandak Bar., Kec. Cilandak,
Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12430</p>
                </div>
            </div>
            <div class="flex items-center">
                <div class="bg-purple-100 p-2 rounded-lg mr-3">
                    <i class="fas fa-certificate text-purple-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Status</p>
                    <p class="font-semibold text-gray-800">Sekolah Menengah Kejuruan Swasta</p>
                </div>
            </div>
            <div class="flex items-center">
                <div class="bg-yellow-100 p-2 rounded-lg mr-3">
                    <i class="fas fa-users text-yellow-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Yayasan</p>
                    <p class="font-semibold text-gray-800">Yayasan Pendidikan Republik Indonesia</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- SPK MOORA Information -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Tentang SPK MOORA</h2>
        <div class="space-y-4">
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-4 rounded-lg border border-blue-200">
                <h3 class="font-semibold text-gray-800 mb-2">Multi-Objective Optimization by Ratio Analysis</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Sistem Pendukung Keputusan (SPK) MOORA adalah aplikasi yang membantu dalam pengambilan keputusan dengan menganalisis berbagai kriteria secara objektif.
                </p>
            </div>
            <div class="space-y-3">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span class="text-sm text-gray-700">Analisis multi-kriteria yang akurat</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span class="text-sm text-gray-700">Proses perhitungan yang transparan</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span class="text-sm text-gray-700">Hasil ranking yang objektif</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span class="text-sm text-gray-700">Interface yang user-friendly</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Information -->
<div class="bg-white rounded-xl shadow-lg p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-6">Informasi Kontak</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="text-center">
            <div class="bg-blue-100 p-4 rounded-full w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                <i class="fas fa-phone text-blue-600 text-xl"></i>
            </div>
            <h3 class="font-semibold text-gray-800 mb-1">Telepon</h3>
            <p class="text-sm text-gray-600">(021)7511641</p>
        </div>
        <div class="text-center">
            <div class="bg-green-100 p-4 rounded-full w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                <i class="fas fa-envelope text-green-600 text-xl"></i>
            </div>
            <h3 class="font-semibold text-gray-800 mb-1">Email</h3>
            <p class="text-sm text-gray-600">smkyapri40@gmail.com</p>
        </div>
      <div class="text-center">
            <div class="bg-blue-100 p-4 rounded-full w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                <i class="fas fa-phone text-blue-600 text-xl"></i>
            </div>
            <h3 class="font-semibold text-gray-800 mb-1">Fax</h3>
            <p class="text-sm text-gray-600">(021)7511641</p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
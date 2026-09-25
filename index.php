<?php
/**
 * SISTEM PENGURUSAN STOK & PERMOHONAN INDIVIDU KEPADA STOR (KEW.PS-8)
 * PEJABAT KDYMM TUANKU SULTAN KEDAH DARUL AMAN
 * 
 * Versi PHP Asal (Native PHP) untuk Laragon / XAMPP / Apache / phpMyAdmin
 * Lokasi Fail: C:\laragon\www\kewps8\index.php
 */

if (file_exists(__DIR__ . '/db_connect.php')) {
    require_once __DIR__ . '/db_connect.php';
}

// Ambil senarai item dari jadual `senarai data stok pejabat`
$items = [];
$dbError = null;
try {
    $stmt = $pdo->query("SELECT * FROM `senarai data stok pejabat` ORDER BY nama ASC");
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    $dbError = $e->getMessage();
}

// Senarai Kategori
$categories = [
    'Semua',
    'Alat Tulis',
    'Kertas & Sampul',
    'Buku & Fail',
    'Toner Printer',
    'Barang Domestik',
    'Lain-lain'
];

// Senarai Bahagian / Sektor Pejabat KDYMM Tuanku Sultan Kedah
$departments = [
    'Sektor Protokol & Istiadat Diraja',
    'Sektor Pentadbiran & Pengurusan Sumber Manusia',
    'Sektor Kewangan & Akaun',
    'Sektor Khidmat Domestik & Sajian Istana',
    'Sektor Keselamatan & Kawalan Istana',
    'Sektor Penyelenggaraan & Pembangunan Landskap',
    'Unit Perhubungan Awam & Media',
    'Unit Audit Dalam & Integriti'
];
?>
<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Pengurusan Stok KEW.PS-8 | Pejabat KDYMM Tuanku Sultan Kedah</title>
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>
  
  <style>
    @media print {
      .no-print { display: none !important; }
      body { background: white !important; }
    }
  </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col font-sans">

  <!-- Header / Navigation Bar -->
  <header class="bg-slate-900 border-b-4 border-amber-600 text-white shadow-lg sticky top-0 z-30 no-print">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20">
        
        <!-- Logo & Tajuk Rasmi -->
        <div class="flex items-center space-x-3.5">
          <div class="w-12 h-12 rounded-xl bg-amber-500/20 border border-amber-500/50 flex items-center justify-center text-amber-400 shrink-0 shadow-inner">
            <i data-lucide="building-2" class="w-6 h-6"></i>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="text-xs font-bold uppercase tracking-widest text-amber-400">SISTEM STOR KEW.PS-8</span>
              <span class="bg-amber-500/20 text-amber-300 text-[10px] font-mono px-2 py-0.5 rounded border border-amber-500/30">PHP NATIVE</span>
            </div>
            <h1 class="text-base sm:text-lg font-black tracking-tight text-white leading-tight">
              PEJABAT KDYMM TUANKU SULTAN KEDAH
            </h1>
            <p class="text-[11px] text-slate-300 hidden sm:block">
              Pangkalan Data MySQL: <code class="text-amber-400 font-mono font-bold">senarai data stok pejabat</code>
            </p>
          </div>
        </div>

        <!-- Butang Navigasi Utama -->
        <div class="flex items-center space-x-2 sm:space-x-3">
          
          <!-- Butang Semak Status -->
          <button onclick="openModal('modalStatus')" class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-slate-200 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-xl transition border border-slate-700">
            <i data-lucide="search" class="w-3.5 h-3.5 text-amber-400"></i>
            <span class="hidden sm:inline">Semak Status</span>
          </button>

          <!-- Butang Troli Permohonan -->
          <button onclick="toggleCartDrawer()" class="relative flex items-center gap-2 px-3.5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl transition shadow-sm">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            <span>Troli</span>
            <span id="cartBadgeCount" class="w-5 h-5 bg-red-600 text-white text-[10px] font-extrabold rounded-full flex items-center justify-center">0</span>
          </button>

          <!-- Butang Admin -->
          <button onclick="openModal('modalAdminLogin')" class="flex items-center gap-1 px-3 py-2 text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-xl transition border border-slate-700">
            <i data-lucide="shield" class="w-3.5 h-3.5 text-amber-400"></i>
            <span class="hidden md:inline">Pentadbir</span>
          </button>

        </div>
      </div>
    </div>
  </header>

  <!-- Kandungan Utama (Main Container) -->
  <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Notis Status Pangkalan Data MySQL -->
    <?php if ($dbError): ?>
      <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl shadow-xs">
        <div class="flex items-start">
          <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 mr-3 mt-0.5"></i>
          <div>
            <h3 class="text-sm font-bold text-red-800">Amaran Sambungan Pangkalan Data MySQL</h3>
            <p class="text-xs text-red-700 mt-1">
              Gagal membaca jadual <code>senarai data stok pejabat</code>: <?= htmlspecialchars($dbError) ?>
            </p>
            <p class="text-[11px] text-red-600 mt-2">
              Sila pastikan Laragon (MySQL) telah dimulakan (Start All) dan pangkalan data <code>kewps8_db</code> telah diimport melalui phpMyAdmin.
            </p>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Banner Maklumat Rasmi -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-amber-950 text-white rounded-2xl p-5 sm:p-6 shadow-sm border border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-500/20 text-amber-300 border border-amber-500/30">
            Borang KEW.PS-8 Rasmi
          </span>
          <span class="text-xs text-slate-300">Pekeliling Perbendaharaan Tatacara Pengurusan Stor</span>
        </div>
        <h2 class="text-base sm:text-xl font-bold">Permohonan Stok Pejabat Secara Terus ke Stor</h2>
        <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
          Pilih kuantiti barang yang diperlukan untuk tugasan rasmi, lengkapkan butiran pemohon, dan serahkan permohonan untuk kelulusan Pegawai Stor.
        </p>
      </div>

      <div class="flex items-center gap-2 shrink-0">
        <a href="print_kewps8.php" target="_blank" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold flex items-center gap-2 border border-slate-700 transition">
          <i data-lucide="printer" class="w-4 h-4 text-amber-400"></i>
          <span>Borang Kosong</span>
        </a>
      </div>
    </div>

    <!-- Kotak Carian & Penapis Kategori -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
        
        <!-- Input Carian -->
        <div class="relative flex-1">
          <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
          <input 
            type="text" 
            id="searchInput" 
            onkeyup="filterItems()" 
            placeholder="Cari nama barang atau kod stok (cth: Ball Pen, Kertas A4, Toner)..." 
            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition"
          >
        </div>

        <!-- Penapis Stok Pantas -->
        <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl text-xs">
          <button onclick="setStockFilter('all')" id="btnStockAll" class="px-3 py-1.5 rounded-lg font-bold bg-white text-slate-900 shadow-2xs">
            Semua (<?= count($items) ?>)
          </button>
          <button onclick="setStockFilter('available')" id="btnStockAvailable" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900">
            Ada Stok
          </button>
          <button onclick="setStockFilter('zero')" id="btnStockZero" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900">
            Habis (0)
          </button>
        </div>

      </div>

      <!-- Kategori Pills -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
        <?php foreach ($categories as $index => $cat): ?>
          <button 
            onclick="setCategoryFilter('<?= $cat ?>')" 
            class="cat-filter-btn px-3.5 py-1.5 rounded-full font-medium whitespace-nowrap transition <?= $index === 0 ? 'bg-amber-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>"
            data-category="<?= $cat ?>"
          >
            <?= $cat ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Grid Paparan Item Stok -->
    <?php if (empty($items)): ?>
      <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-xs">
        <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center mx-auto mb-3 text-amber-700">
          <i data-lucide="package-x" class="w-8 h-8"></i>
        </div>
        <h3 class="text-base font-bold text-slate-800">Pangkalan Data Stok Kosong</h3>
        <p class="text-xs text-slate-500 mt-1.5 max-w-md mx-auto leading-relaxed">
          Tiada data item dalam jadual <code class="font-mono text-amber-800 font-bold bg-amber-50 px-1.5 py-0.5 rounded">senarai data stok pejabat</code>. Sila import data stok anda ke dalam pangkalan data MySQL melalui phpMyAdmin.
        </p>
      </div>
    <?php else: ?>
      <div id="itemsGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($items as $item): 
          $isZero = $item['baki_stok'] <= 0;
          $isLow = $item['baki_stok'] > 0 && $item['baki_stok'] <= $item['paras_minimum'];
        ?>
          <div 
            class="item-card bg-white rounded-2xl border transition-all duration-200 overflow-hidden flex flex-col justify-between <?= $isZero ? 'border-red-200 bg-red-50/20' : 'border-slate-200 hover:border-amber-400 hover:shadow-md' ?>"
            data-name="<?= strtolower(htmlspecialchars($item['nama'])) ?>"
            data-code="<?= strtolower(htmlspecialchars($item['kod'])) ?>"
            data-category="<?= htmlspecialchars($item['kategori']) ?>"
            data-stock="<?= (int)$item['baki_stok'] ?>"
          >
            <!-- Imej / Ikon Visual -->
            <div class="h-32 bg-slate-100 flex items-center justify-center relative p-3 border-b border-slate-100">
              <i data-lucide="package" class="w-12 h-12 text-slate-400"></i>
              <div class="absolute top-2 right-2">
                <?php if ($isZero): ?>
                  <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700 border border-red-200">
                    Baki: 0 <?= htmlspecialchars($item['unit']) ?>
                  </span>
                <?php elseif ($isLow): ?>
                  <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300">
                    Baki: <?= $item['baki_stok'] ?> <?= htmlspecialchars($item['unit']) ?> (Rendah)
                  </span>
                <?php else: ?>
                  <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Baki: <?= $item['baki_stok'] ?> <?= htmlspecialchars($item['unit']) ?>
                  </span>
                <?php endif; ?>
              </div>
            </div>

            <!-- Butiran Item -->
            <div class="p-4 flex-1 flex flex-col justify-between">
              <div>
                <div class="flex items-center justify-between gap-2 mb-1.5">
                  <span class="font-mono text-[11px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-800 border border-slate-200">
                    <?= htmlspecialchars($item['kod']) ?>
                  </span>
                  <span class="text-[10px] text-slate-500 font-medium">
                    <?= htmlspecialchars($item['kategori']) ?>
                  </span>
                </div>

                <h4 class="text-xs font-bold text-slate-900 leading-snug line-clamp-2">
                  <?= htmlspecialchars($item['nama']) ?>
                </h4>

                <?php if (!empty($item['catatan'])): ?>
                  <p class="text-[11px] text-slate-500 mt-1 line-clamp-2">
                    <?= htmlspecialchars($item['catatan']) ?>
                  </p>
                <?php endif; ?>
              </div>

              <!-- Bahagian Tindakan Bawah -->
              <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                <div class="text-[11px] text-slate-500">
                  Unit: <span class="font-semibold text-slate-700"><?= htmlspecialchars($item['unit']) ?></span>
                </div>

                <button 
                  onclick="addToCart(<?= htmlspecialchars(json_encode($item)) ?>)"
                  <?= $isZero ? 'disabled' : '' ?>
                  class="px-3 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 transition <?= $isZero ? 'bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed' : 'bg-amber-600 hover:bg-amber-700 text-white shadow-xs' ?>"
                >
                  <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                  <span><?= $isZero ? 'Habis' : 'Mohon' ?></span>
                </button>
              </div>

            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </main>

  <!-- Floating Cart Button (Bawah Kanan) -->
  <div class="fixed bottom-5 right-5 z-40 no-print">
    <button onclick="toggleCartDrawer()" class="group flex items-center gap-3 px-5 py-3.5 bg-slate-900 hover:bg-black text-white rounded-full shadow-2xl border-2 border-amber-500/80 transition-all hover:scale-105">
      <div class="relative">
        <i data-lucide="shopping-bag" class="w-5 h-5 text-amber-400"></i>
        <span id="floatingCartBadge" class="absolute -top-2 -right-2.5 w-5 h-5 bg-red-600 text-white font-extrabold text-[10px] rounded-full flex items-center justify-center">0</span>
      </div>
      <div class="text-left pr-1">
        <div class="text-xs font-bold leading-tight text-white flex items-center gap-1.5">
          <span>Buka Troli Permohonan</span>
        </div>
        <div class="text-[10px] text-slate-300">Isi Borang KEW.PS-8</div>
      </div>
    </button>
  </div>

  <!-- Drawer Troli Permohonan -->
  <div id="cartDrawer" class="fixed inset-0 z-50 overflow-hidden hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="toggleCartDrawer()"></div>
    <div class="absolute inset-y-0 right-0 max-w-full flex pl-10">
      <div class="w-screen max-w-md bg-white shadow-2xl flex flex-col">
        
        <!-- Header Drawer -->
        <div class="p-4 sm:p-5 bg-slate-900 text-white flex items-center justify-between border-b-2 border-amber-500">
          <div class="flex items-center gap-2.5">
            <i data-lucide="shopping-bag" class="w-5 h-5 text-amber-400"></i>
            <div>
              <h3 class="text-sm font-bold">Troli Permohonan Stok</h3>
              <p class="text-[10px] text-slate-300">Borang KEW.PS-8 (Individu Kepada Stor)</p>
            </div>
          </div>
          <button onclick="toggleCartDrawer()" class="p-1 rounded-lg text-slate-400 hover:text-white">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>

        <!-- Senarai Item dalam Troli -->
        <div id="cartItemsList" class="flex-1 overflow-y-auto p-4 space-y-3">
          <!-- Diisi oleh Javascript -->
        </div>

        <!-- Footer Borang Permohonan -->
        <div class="p-4 bg-slate-50 border-t border-slate-200 space-y-3">
          <button onclick="openModal('modalCheckout')" id="btnProceedCheckout" class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-md flex items-center justify-center gap-2 transition disabled:opacity-50 disabled:cursor-not-allowed">
            <span>Isi Maklumat & Hantar Permohonan</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </button>
        </div>

      </div>
    </div>
  </div>

  <!-- Modal Checkout Borang KEW.PS-8 -->
  <div id="modalCheckout" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="min-h-screen px-4 text-center flex items-center justify-center">
      <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeModal('modalCheckout')"></div>
      
      <div class="inline-block w-full max-w-xl p-6 my-8 text-left align-middle bg-white rounded-2xl shadow-2xl relative z-10">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
          <h3 class="text-base font-bold text-slate-900">Maklumat Pemohon (Borang KEW.PS-8)</h3>
          <button onclick="closeModal('modalCheckout')" class="text-slate-400 hover:text-slate-600">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>

        <form id="checkoutForm" onsubmit="submitKewps8Request(event)" class="space-y-4 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Penuh Pemohon <span class="text-red-500">*</span></label>
            <input type="text" id="reqNama" required placeholder="Cth: NURUL NADIA BINTI AHMAD" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 uppercase">
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Jawatan & Gred <span class="text-red-500">*</span></label>
              <input type="text" id="reqJawatan" required placeholder="Cth: Pembantu Tadbir (N19)" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600">
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Bahagian / Sektor <span class="text-red-500">*</span></label>
              <select id="reqBahagian" required class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 bg-white">
                <option value="">Pilih Bahagian</option>
                <?php foreach ($departments as $dept): ?>
                  <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Tujuan / Catatan Penggunaan</label>
            <textarea id="reqCatatan" rows="2" placeholder="Cth: Keperluan mesyuarat rasmi atau tugas harian pejabat..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600"></textarea>
          </div>

          <div class="bg-amber-50 p-3 rounded-xl border border-amber-200 text-amber-900">
            <p class="font-bold">Perakuan Pemohon:</p>
            <p class="mt-0.5 text-[11px] leading-relaxed">
              Saya memperakui bahawa bekalan stok yang dimohon ini adalah benar-benar diperlukan untuk kegunaan rasmi Pejabat KDYMM Tuanku Sultan Kedah.
            </p>
          </div>

          <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
            <button type="button" onclick="closeModal('modalCheckout')" class="px-4 py-2 border border-slate-300 rounded-xl font-medium text-slate-700 hover:bg-slate-100">Batal</button>
            <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold shadow-xs flex items-center gap-1.5">
              <i data-lucide="send" class="w-3.5 h-3.5"></i>
              <span>Hantar Permohonan</span>
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <!-- Modal Semak Status -->
  <div id="modalStatus" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="min-h-screen px-4 text-center flex items-center justify-center">
      <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeModal('modalStatus')"></div>
      
      <div class="inline-block w-full max-w-lg p-6 my-8 text-left align-middle bg-white rounded-2xl shadow-2xl relative z-10 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
          <h3 class="text-base font-bold text-slate-900">Semak Status Permohonan KEW.PS-8</h3>
          <button onclick="closeModal('modalStatus')" class="text-slate-400 hover:text-slate-600">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>

        <div class="flex gap-2 mb-4">
          <input type="text" id="statusQuery" placeholder="Masukkan No. BPSI (cth: BPSI/2026/0001) atau nama..." class="flex-1 px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 uppercase">
          <button onclick="checkStatusOnline()" class="px-4 py-2 bg-slate-900 hover:bg-black text-white font-bold rounded-xl transition">
            Cari
          </button>
        </div>

        <div id="statusResult" class="space-y-3">
          <p class="text-slate-500 text-center py-6">Masukkan No. BPSI anda untuk melihat status kelulusan semasa.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Log Masuk Admin -->
  <div id="modalAdminLogin" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="min-h-screen px-4 text-center flex items-center justify-center">
      <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeModal('modalAdminLogin')"></div>
      
      <div class="inline-block w-full max-w-sm p-6 my-8 text-left align-middle bg-white rounded-2xl shadow-2xl relative z-10 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
          <h3 class="text-base font-bold text-slate-900">Log Masuk Pentadbir Stor</h3>
          <button onclick="closeModal('modalAdminLogin')" class="text-slate-400 hover:text-slate-600">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>

        <form onsubmit="handleAdminLogin(event)" class="space-y-4">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Kata Laluan Pentadbir</label>
            <input type="password" id="adminPassword" required placeholder="Masukkan kata laluan..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600">
            <p class="text-[10px] text-slate-400 mt-1">Kata laluan piawai: <code>admin123</code></p>
          </div>
          <button type="submit" class="w-full py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-xs transition">
            Masuk ke Panel Pentadbir
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Javascript Utiliti & Logik Native PHP Frontend -->
  <script>
    let cart = [];
    let currentCategory = 'Semua';
    let currentStockFilter = 'all';

    // Inisialisasi ikon Lucide
    document.addEventListener('DOMContentLoaded', () => {
      lucide.createIcons();
    });

    function toggleCartDrawer() {
      const drawer = document.getElementById('cartDrawer');
      drawer.classList.toggle('hidden');
      renderCart();
    }

    function openModal(id) {
      document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
      document.getElementById(id).classList.add('hidden');
    }

    function addToCart(item) {
      const existing = cart.find(i => i.id === item.id);
      if (existing) {
        if (existing.qty < item.baki_stok) {
          existing.qty += 1;
        } else {
          alert('Kuantiti telah mencapai baki maksimum stok sedia ada.');
        }
      } else {
        cart.push({
          id: item.id,
          kod: item.kod,
          nama: item.nama,
          unit: item.unit,
          baki_stok: item.baki_stok,
          qty: 1,
          catatan: ''
        });
      }
      updateCartBadge();
      renderCart();
    }

    function updateCartBadge() {
      const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
      document.getElementById('cartBadgeCount').innerText = totalQty;
      document.getElementById('floatingCartBadge').innerText = totalQty;
    }

    function renderCart() {
      const container = document.getElementById('cartItemsList');
      if (cart.length === 0) {
        container.innerHTML = `
          <div class="text-center py-12 text-slate-400">
            <i data-lucide="package-open" class="w-12 h-12 mx-auto mb-2 text-slate-300"></i>
            <p class="font-bold text-slate-600">Troli permohonan kosong</p>
            <p class="text-[11px] mt-1">Sila klik "Mohon" pada mana-mana barang di katalog.</p>
          </div>
        `;
        document.getElementById('btnProceedCheckout').disabled = true;
        lucide.createIcons();
        return;
      }

      document.getElementById('btnProceedCheckout').disabled = false;
      let html = '';
      cart.forEach((item, index) => {
        html += `
          <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
            <div class="flex items-start justify-between gap-2">
              <div>
                <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-800">${item.kod}</span>
                <h5 class="text-xs font-bold text-slate-800 mt-1">${item.nama}</h5>
                <span class="text-[10px] text-slate-500">Baki stok stor: ${item.baki_stok} ${item.unit}</span>
              </div>
              <button onclick="removeFromCart(${index})" class="text-slate-400 hover:text-red-600 p-1">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
              </button>
            </div>

            <div class="flex items-center justify-between pt-1 border-t border-slate-200">
              <span class="text-[11px] text-slate-600">Kuantiti Dimohon:</span>
              <div class="flex items-center border border-slate-300 rounded-lg bg-white overflow-hidden">
                <button onclick="adjustQty(${index}, -1)" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-xs font-bold">-</button>
                <span class="px-3 py-1 font-mono text-xs font-bold">${item.qty}</span>
                <button onclick="adjustQty(${index}, 1)" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-xs font-bold">+</button>
              </div>
            </div>
          </div>
        `;
      });
      container.innerHTML = html;
      lucide.createIcons();
    }

    function adjustQty(index, delta) {
      const item = cart[index];
      const newQty = item.qty + delta;
      if (newQty <= 0) {
        removeFromCart(index);
      } else if (newQty > item.baki_stok) {
        alert('Kuantiti tidak boleh melebihi baki stok stor (' + item.baki_stok + ')');
      } else {
        item.qty = newQty;
        updateCartBadge();
        renderCart();
      }
    }

    function removeFromCart(index) {
      cart.splice(index, 1);
      updateCartBadge();
      renderCart();
    }

    function setCategoryFilter(cat) {
      currentCategory = cat;
      document.querySelectorAll('.cat-filter-btn').forEach(btn => {
        if (btn.getAttribute('data-category') === cat) {
          btn.className = 'cat-filter-btn px-3.5 py-1.5 rounded-full font-bold whitespace-nowrap transition bg-amber-600 text-white';
        } else {
          btn.className = 'cat-filter-btn px-3.5 py-1.5 rounded-full font-medium whitespace-nowrap transition bg-slate-100 text-slate-600 hover:bg-slate-200';
        }
      });
      filterItems();
    }

    function setStockFilter(type) {
      currentStockFilter = type;
      const bAll = document.getElementById('btnStockAll');
      const bAvail = document.getElementById('btnStockAvailable');
      const bZero = document.getElementById('btnStockZero');

      bAll.className = type === 'all' ? 'px-3 py-1.5 rounded-lg font-bold bg-white text-slate-900 shadow-2xs' : 'px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900';
      bAvail.className = type === 'available' ? 'px-3 py-1.5 rounded-lg font-bold bg-emerald-600 text-white shadow-2xs' : 'px-3 py-1.5 rounded-lg text-slate-600 hover:text-emerald-700';
      bZero.className = type === 'zero' ? 'px-3 py-1.5 rounded-lg font-bold bg-rose-600 text-white shadow-2xs' : 'px-3 py-1.5 rounded-lg text-slate-600 hover:text-rose-700';

      filterItems();
    }

    function filterItems() {
      const query = document.getElementById('searchInput').value.toLowerCase();
      const cards = document.querySelectorAll('.item-card');

      cards.forEach(card => {
        const name = card.getAttribute('data-name');
        const code = card.getAttribute('data-code');
        const cat = card.getAttribute('data-category');
        const stock = parseInt(card.getAttribute('data-stock'), 10);

        const matchSearch = name.includes(query) || code.includes(query);
        const matchCat = currentCategory === 'Semua' || cat === currentCategory;
        const matchStock = currentStockFilter === 'all' 
          ? true 
          : currentStockFilter === 'available' 
          ? stock > 0 
          : stock === 0;

        if (matchSearch && matchCat && matchStock) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      });
    }

    async function submitKewps8Request(e) {
      e.preventDefault();
      if (cart.length === 0) {
        alert('Sila pilih sekurang-kurangnya satu item untuk dimohon.');
        return;
      }

      const payload = {
        namaPemohon: document.getElementById('reqNama').value,
        jawatan: document.getElementById('reqJawatan').value,
        bahagian: document.getElementById('reqBahagian').value,
        catatanPemohon: document.getElementById('reqCatatan').value,
        items: cart.map(i => ({
          itemId: i.id,
          kod: i.kod,
          nama: i.nama,
          unit: i.unit,
          kuantitiDimohon: i.qty,
          bakiSediaAda: i.baki_stok
        }))
      };

      try {
        const res = await fetch('api_requests.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const result = await res.json();
        if (result.status === 'success') {
          alert('Permohonan Borang KEW.PS-8 berjaya dihantar!\\n\\nNo. BPSI: ' + result.noBPSI);
          cart = [];
          updateCartBadge();
          closeModal('modalCheckout');
          toggleCartDrawer();
          window.open('print_kewps8.php?bpsi=' + encodeURIComponent(result.noBPSI), '_blank');
        } else {
          alert('Ralat menghantar permohonan: ' + (result.message || 'Sila cuba lagi.'));
        }
      } catch (err) {
        alert('Gagal menghubungi pelayan pangkalan data. Sila pastikan fail api_requests.php boleh diakses.');
      }
    }

    async function checkStatusOnline() {
      const query = document.getElementById('statusQuery').value.trim();
      if (!query) return;

      const container = document.getElementById('statusResult');
      container.innerHTML = '<p class="text-slate-400 text-center py-4">Mencari dalam pangkalan data...</p>';

      try {
        const res = await fetch('api_requests.php?search=' + encodeURIComponent(query));
        const result = await res.json();
        if (result.status === 'success' && result.data.length > 0) {
          let html = '';
          result.data.forEach(req => {
            html += `
              <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5">
                <div class="flex items-center justify-between">
                  <span class="font-mono font-bold text-amber-700">${req.no_bpsi}</span>
                  <span class="px-2 py-0.5 rounded-full font-bold text-[10px] ${req.status === 'Diluluskan' ? 'bg-emerald-100 text-emerald-800' : req.status === 'Ditolak' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800'}">
                    ${req.status}
                  </span>
                </div>
                <div class="font-semibold text-slate-800">${req.nama_pemohon} (${req.jawatan})</div>
                <div class="text-slate-500 text-[11px]">${req.bahagian} • Tarikh: ${req.tarikh_mohon}</div>
                <div class="pt-2 flex justify-end">
                  <a href="print_kewps8.php?bpsi=${encodeURIComponent(req.no_bpsi)}" target="_blank" class="px-2.5 py-1 bg-slate-900 text-white rounded-lg text-[10px] font-bold flex items-center gap-1">
                    <i data-lucide="printer" class="w-3 h-3"></i> Cetak KEW.PS-8
                  </a>
                </div>
              </div>
            `;
          });
          container.innerHTML = html;
          lucide.createIcons();
        } else {
          container.innerHTML = '<p class="text-rose-600 text-center py-4">Tiada permohonan ditemui bagi carian tersebut.</p>';
        }
      } catch (e) {
        container.innerHTML = '<p class="text-slate-500 text-center py-4">Sila pastikan jadual `requests` telah diimport di MySQL.</p>';
      }
    }

    function handleAdminLogin(e) {
      e.preventDefault();
      const pwd = document.getElementById('adminPassword').value;
      if (pwd === 'admin123') {
        window.location.href = 'admin.php';
      } else {
        alert('Kata laluan tidak sah! Sila masukkan: admin123');
      }
    }
  </script>

</body>
</html>

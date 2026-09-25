<?php
/**
 * admin.php
 * Panel Pentadbir Stor KEW.PS-8 & Pengurusan Stok Pejabat
 * Pejabat KDYMM Tuanku Sultan Kedah Darul Aman
 */

require_once __DIR__ . '/db_connect.php';

$message = '';
$error = '';

// Senarai Kategori Standard
$categories = [
    'Semua',
    'Alat Tulis',
    'Kertas & Sampul',
    'Buku & Fail',
    'Toner Printer',
    'Barang Domestik',
    'Lain-lain'
];

// Proses Tindakan Kelulusan Permohonan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_request'])) {
    $reqId = $_POST['req_id'] ?? '';
    $action = $_POST['action_request'];

    if ($action === 'approve') {
        try {
            $stmt = $pdo->prepare("UPDATE `requests` SET `status` = 'Diluluskan', `tarikh_kelulusan` = CURDATE() WHERE `id` = :id");
            $stmt->execute([':id' => $reqId]);
            $message = 'Permohonan berjaya diluluskan!';
        } catch (PDOException $e) {
            $error = 'Ralat meluluskan: ' . $e->getMessage();
        }
    } elseif ($action === 'reject') {
        try {
            $stmt = $pdo->prepare("UPDATE `requests` SET `status` = 'Ditolak', `tarikh_kelulusan` = CURDATE() WHERE `id` = :id");
            $stmt->execute([':id' => $reqId]);
            $message = 'Permohonan telah ditolak.';
        } catch (PDOException $e) {
            $error = 'Ralat: ' . $e->getMessage();
        }
    }
}

// 1. Tambah (Create) Item Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add_item'])) {
    $kod = trim($_POST['kod'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $kategori = $_POST['kategori'] ?? 'Alat Tulis';
    $unit = trim($_POST['unit'] ?? 'UNIT');
    $baki = (int)($_POST['baki_stok'] ?? 0);
    $paras = (int)($_POST['paras_minimum'] ?? 5);
    $harga = (float)($_POST['harga_seunit'] ?? 0);
    $catatan = trim($_POST['catatan'] ?? '');

    if ($kod && $nama) {
        try {
            $sql = "INSERT INTO `senarai data stok pejabat` (id, kod, nama, kategori, unit, harga_seunit, baki_stok, paras_minimum, gambar, catatan)
                    VALUES (:id, :kod, :nama, :kategori, :unit, :harga, :baki, :paras, 'package', :catatan)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => 'item-' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $kod)) . '-' . time(),
                ':kod' => strtoupper($kod),
                ':nama' => strtoupper($nama),
                ':kategori' => $kategori,
                ':unit' => strtoupper($unit),
                ':harga' => $harga,
                ':baki' => $baki,
                ':paras' => $paras,
                ':catatan' => $catatan
            ]);
            $message = 'Item baru berjaya ditambah ke jadual `senarai data stok pejabat`!';
        } catch (PDOException $e) {
            $error = 'Ralat menambah item: ' . $e->getMessage();
        }
    } else {
        $error = 'Sila lengkapkan kod dan nama item.';
    }
}

// 2. Ubah (Update) Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_edit_item'])) {
    $id = trim($_POST['item_id'] ?? '');
    $kod = trim($_POST['kod'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $kategori = $_POST['kategori'] ?? 'Alat Tulis';
    $unit = trim($_POST['unit'] ?? 'UNIT');
    $baki = (int)($_POST['baki_stok'] ?? 0);
    $paras = (int)($_POST['paras_minimum'] ?? 5);
    $harga = (float)($_POST['harga_seunit'] ?? 0);
    $catatan = trim($_POST['catatan'] ?? '');

    if ($id && $kod && $nama) {
        try {
            $sql = "UPDATE `senarai data stok pejabat` 
                    SET `kod` = :kod, `nama` = :nama, `kategori` = :kategori, `unit` = :unit, 
                        `harga_seunit` = :harga, `baki_stok` = :baki, `paras_minimum` = :paras, `catatan` = :catatan
                    WHERE `id` = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':kod' => strtoupper($kod),
                ':nama' => strtoupper($nama),
                ':kategori' => $kategori,
                ':unit' => strtoupper($unit),
                ':harga' => $harga,
                ':baki' => $baki,
                ':paras' => $paras,
                ':catatan' => $catatan
            ]);
            $message = "Item $kod berjaya dikemaskini!";
        } catch (PDOException $e) {
            $error = 'Ralat mengemaskini item: ' . $e->getMessage();
        }
    } else {
        $error = 'Maklumat kemaskini item tidak lengkap.';
    }
}

// 3. Hapus (Delete) Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_delete_item'])) {
    $id = trim($_POST['item_id'] ?? '');
    if ($id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM `senarai data stok pejabat` WHERE `id` = :id");
            $stmt->execute([':id' => $id]);
            $message = 'Item berjaya dihapuskan dari pangkalan data!';
        } catch (PDOException $e) {
            $error = 'Ralat menghapuskan item: ' . $e->getMessage();
        }
    }
}

// Ambil senarai permohonan
$requests = [];
try {
    $stmtReq = $pdo->query("SELECT * FROM `requests` ORDER BY `created_at` DESC LIMIT 50");
    $requests = $stmtReq->fetchAll();
} catch (PDOException $e) {
    // Abaikan jika jadual belum diimport
}

// Ambil senarai stok dari jadual `senarai data stok pejabat`
$items = [];
try {
    $stmtItems = $pdo->query("SELECT * FROM `senarai data stok pejabat` ORDER BY `nama` ASC");
    $items = $stmtItems->fetchAll();
} catch (PDOException $e) {
    // Abaikan
}

// Penapisan Kategori & Carian untuk Paparan Item
$selectedCategory = $_GET['cat'] ?? 'Semua';
$searchQuery = trim($_GET['search'] ?? '');
$filteredItems = array_filter($items, function($item) use ($selectedCategory, $searchQuery) {
    $matchCat = ($selectedCategory === 'Semua' || $item['kategori'] === $selectedCategory);
    $matchSearch = true;
    if ($searchQuery !== '') {
        $q = strtolower($searchQuery);
        $matchSearch = (strpos(strtolower($item['nama']), $q) !== false || strpos(strtolower($item['kod']), $q) !== false);
    }
    return $matchCat && $matchSearch;
});
$filteredItems = array_values($filteredItems);

// Konfigurasi Pagination (50 item setiap halaman)
$perPage = 50;
$totalFilteredItems = count($filteredItems);
$totalPages = max(1, (int)ceil($totalFilteredItems / $perPage));
$currentPage = isset($_GET['page']) ? max(1, min($totalPages, (int)$_GET['page'])) : 1;
$offset = ($currentPage - 1) * $perPage;
$paginatedItems = array_slice($filteredItems, $offset, $perPage);

// Statistik
$totalItems = count($items);
$lowStockCount = count(array_filter($items, fn($i) => $i['baki_stok'] <= $i['paras_minimum']));
$pendingReqCount = count(array_filter($requests, fn($r) => $r['status'] === 'Menunggu Kelulusan'));
?>
<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Pentadbir Stor | KEW.PS-8</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col font-sans">

  <!-- Header Pentadbir -->
  <header class="bg-slate-900 border-b-4 border-amber-600 text-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-xl bg-amber-600 flex items-center justify-center font-bold">
          <i data-lucide="shield-check" class="w-5 h-5 text-white"></i>
        </div>
        <div>
          <h1 class="text-base font-bold">PANEL PENTADBIR STOR KEW.PS-8</h1>
          <p class="text-xs text-slate-300">Pejabat KDYMM Tuanku Sultan Kedah Darul Aman</p>
        </div>
      </div>
      <div class="flex items-center space-x-2">
        <a href="index.php" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold flex items-center gap-1.5 border border-slate-700">
          <i data-lucide="home" class="w-4 h-4 text-amber-400"></i>
          <span>Laman Utama</span>
        </a>
      </div>
    </div>
  </header>

  <!-- Mesej Status -->
  <div class="max-w-7xl mx-auto w-full px-4 pt-4">
    <?php if ($message): ?>
      <div class="p-3 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-xl text-xs font-semibold">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="p-3 bg-rose-100 border border-rose-300 text-rose-800 rounded-xl text-xs font-semibold">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Statistik Kad Ringkasan -->
  <div class="max-w-7xl mx-auto w-full px-4 py-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
      <div>
        <p class="text-xs text-slate-500 font-medium">Permohonan Menunggu</p>
        <h3 class="text-2xl font-bold text-amber-600 mt-0.5"><?= $pendingReqCount ?></h3>
      </div>
      <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
        <i data-lucide="clock" class="w-5 h-5"></i>
      </div>
    </div>

    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
      <div>
        <p class="text-xs text-slate-500 font-medium">Jumlah Item Stok Pejabat</p>
        <h3 class="text-2xl font-bold text-slate-800 mt-0.5"><?= $totalItems ?></h3>
      </div>
      <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
        <i data-lucide="package" class="w-5 h-5"></i>
      </div>
    </div>

    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
      <div>
        <p class="text-xs text-slate-500 font-medium">Stok Rendah / Habis</p>
        <h3 class="text-2xl font-bold text-rose-600 mt-0.5"><?= $lowStockCount ?></h3>
      </div>
      <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
      </div>
    </div>
  </div>

  <!-- Kandungan Tab / Meja Permohonan & Pengurusan Stok -->
  <main class="max-w-7xl mx-auto w-full px-4 pb-12 space-y-6">

    <!-- Bahagian 1: Senarai Permohonan KEW.PS-8 -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <i data-lucide="file-text" class="w-5 h-5 text-amber-600"></i>
          <h2 class="text-sm font-bold text-slate-900">Senarai Permohonan Masuk (Borang KEW.PS-8)</h2>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-100 text-slate-600 uppercase font-semibold border-b border-slate-200">
            <tr>
              <th class="py-3 px-4">No. BPSI</th>
              <th class="py-3 px-4">Nama Pemohon</th>
              <th class="py-3 px-4">Bahagian / Sektor</th>
              <th class="py-3 px-4">Tarikh</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-right">Tindakan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <?php if (empty($requests)): ?>
              <tr>
                <td colspan="6" class="text-center py-8 text-slate-400">Tiada permohonan stok dalam sistem buat masa ini.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($requests as $r): ?>
                <tr class="hover:bg-slate-50">
                  <td class="py-3 px-4 font-mono font-bold text-slate-900"><?= htmlspecialchars($r['no_bpsi']) ?></td>
                  <td class="py-3 px-4">
                    <div class="font-bold"><?= htmlspecialchars($r['nama_pemohon']) ?></div>
                    <div class="text-[10px] text-slate-500"><?= htmlspecialchars($r['jawatan']) ?></div>
                  </td>
                  <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($r['bahagian']) ?></td>
                  <td class="py-3 px-4 text-slate-600 whitespace-nowrap"><?= htmlspecialchars($r['tarikh_mohon']) ?></td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-0.5 rounded-full font-bold text-[10px] <?= $r['status'] === 'Diluluskan' ? 'bg-emerald-100 text-emerald-800' : ($r['status'] === 'Ditolak' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') ?>">
                      <?= htmlspecialchars($r['status']) ?>
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right whitespace-nowrap space-x-1">
                    <a href="print_kewps8.php?bpsi=<?= urlencode($r['no_bpsi']) ?>" target="_blank" class="px-2.5 py-1 bg-slate-800 text-white rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                      <i data-lucide="printer" class="w-3 h-3"></i> Cetak
                    </a>

                    <?php if ($r['status'] === 'Menunggu Kelulusan'): ?>
                      <form method="POST" class="inline">
                        <input type="hidden" name="req_id" value="<?= htmlspecialchars($r['id']) ?>">
                        <input type="hidden" name="action_request" value="approve">
                        <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-bold">
                          Lulus
                        </button>
                      </form>
                      <form method="POST" class="inline">
                        <input type="hidden" name="req_id" value="<?= htmlspecialchars($r['id']) ?>">
                        <input type="hidden" name="action_request" value="reject">
                        <button type="submit" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-[10px] font-bold">
                          Tolak
                        </button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Bahagian 2: Jadual Pengurusan Senarai Stok Pejabat (Lihat, Tambah, Ubah, Hapus & Pagination 50 Item) -->
    <div id="sectionStokPejabat" class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
      
      <!-- Bahagian Atas: Tajuk, Penapis Kategori, Carian & Butang Tambah Item -->
      <div class="p-4 sm:p-5 bg-slate-50 border-b border-slate-200 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-700 flex items-center justify-center font-bold">
              <i data-lucide="boxes" class="w-5 h-5"></i>
            </div>
            <div>
              <h2 class="text-sm font-bold text-slate-900">Senarai Data Stok Pejabat (`senarai data stok pejabat`)</h2>
              <p class="text-[11px] text-slate-500">
                Memaparkan <?= min($totalFilteredItems, $offset + 1) ?> - <?= min($totalFilteredItems, $offset + $perPage) ?> daripada <?= $totalFilteredItems ?> item (50 setiap halaman)
              </p>
            </div>
          </div>

          <!-- Butang Tambah Item (Membuka Pop-up Modal) -->
          <button 
            type="button" 
            onclick="openAddItemModal()" 
            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer shrink-0"
          >
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Item Baru</span>
          </button>
        </div>

        <!-- Penapis Carian & Senarai Kategori -->
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 pt-1 border-t border-slate-200/80">
          <!-- Carian -->
          <form method="GET" action="admin.php" class="relative flex-1 max-w-md">
            <input type="hidden" name="cat" value="<?= htmlspecialchars($selectedCategory) ?>">
            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input 
              type="text" 
              name="search" 
              value="<?= htmlspecialchars($searchQuery) ?>" 
              placeholder="Cari kod atau nama item..." 
              class="w-full pl-9 pr-8 py-1.5 text-xs bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600"
            >
            <?php if ($searchQuery !== ''): ?>
              <a href="admin.php?cat=<?= urlencode($selectedCategory) ?>" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs font-bold">&times;</a>
            <?php endif; ?>
          </form>

          <!-- Kategori Pills -->
          <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
            <?php foreach ($categories as $cat): ?>
              <a 
                href="admin.php?cat=<?= urlencode($cat) ?><?= $searchQuery !== '' ? '&search=' . urlencode($searchQuery) : '' ?>" 
                class="px-3 py-1 rounded-full font-medium whitespace-nowrap transition <?= $selectedCategory === $cat ? 'bg-amber-600 text-white font-bold shadow-2xs' : 'bg-slate-200/70 text-slate-600 hover:bg-slate-300' ?>"
              >
                <?= htmlspecialchars($cat) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Jadual Senarai Item Stok -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-100 text-slate-600 uppercase font-semibold border-b border-slate-200">
            <tr>
              <th class="py-3 px-4 w-12 text-center">Bil</th>
              <th class="py-3 px-4 w-24">Kod</th>
              <th class="py-3 px-4">Nama Item</th>
              <th class="py-3 px-4 w-32">Kategori</th>
              <th class="py-3 px-4 w-20 text-center">Unit</th>
              <th class="py-3 px-4 w-24 text-center">Baki Stok</th>
              <th class="py-3 px-4 w-24 text-center">Paras Min</th>
              <th class="py-3 px-4 w-24 text-right">Harga (RM)</th>
              <th class="py-3 px-4 w-36 text-center">Tindakan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <?php if (empty($paginatedItems)): ?>
              <tr>
                <td colspan="9" class="text-center py-10 text-slate-400">
                  <i data-lucide="package-x" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                  Tiada item stok ditemui bagi carian atau kategori ini.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($paginatedItems as $index => $item): 
                $itemIndex = $offset + $index + 1;
                $isZero = $item['baki_stok'] <= 0;
                $isLow = $item['baki_stok'] > 0 && $item['baki_stok'] <= $item['paras_minimum'];
                $jsonItem = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
              ?>
                <tr class="hover:bg-slate-50/80 transition">
                  <td class="py-2.5 px-4 text-center text-slate-400 font-mono"><?= $itemIndex ?></td>
                  <td class="py-2.5 px-4 font-mono font-bold text-slate-900"><?= htmlspecialchars($item['kod']) ?></td>
                  <td class="py-2.5 px-4 font-semibold text-slate-800">
                    <div><?= htmlspecialchars($item['nama']) ?></div>
                    <?php if (!empty($item['catatan'])): ?>
                      <div class="text-[10px] text-slate-500 font-normal"><?= htmlspecialchars($item['catatan']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="py-2.5 px-4 text-slate-600">
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-medium border border-slate-200">
                      <?= htmlspecialchars($item['kategori']) ?>
                    </span>
                  </td>
                  <td class="py-2.5 px-4 text-center uppercase text-slate-600 font-medium"><?= htmlspecialchars($item['unit']) ?></td>
                  <td class="py-2.5 px-4 text-center">
                    <?php if ($isZero): ?>
                      <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-red-100 text-red-700">0</span>
                    <?php elseif ($isLow): ?>
                      <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-amber-100 text-amber-800"><?= $item['baki_stok'] ?> (Rendah)</span>
                    <?php else: ?>
                      <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-emerald-100 text-emerald-800"><?= $item['baki_stok'] ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="py-2.5 px-4 text-center font-mono text-slate-600"><?= $item['paras_minimum'] ?></td>
                  <td class="py-2.5 px-4 text-right font-mono text-slate-700">RM <?= number_format((float)$item['harga_seunit'], 2) ?></td>
                  <td class="py-2.5 px-4 text-center whitespace-nowrap space-x-1">
                    
                    <!-- Icon 1: Lihat (Read) -->
                    <button 
                      type="button"
                      onclick="viewItemDetails(<?= $jsonItem ?>)" 
                      title="Lihat Butiran Item" 
                      class="p-1.5 rounded-lg text-slate-600 hover:text-amber-700 hover:bg-amber-50 border border-slate-200 transition cursor-pointer inline-flex items-center justify-center"
                    >
                      <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>

                    <!-- Icon 2: Ubah (Update) -->
                    <button 
                      type="button"
                      onclick="openEditItemModal(<?= $jsonItem ?>)" 
                      title="Ubah Item" 
                      class="p-1.5 rounded-lg text-blue-600 hover:text-blue-800 hover:bg-blue-50 border border-slate-200 transition cursor-pointer inline-flex items-center justify-center"
                    >
                      <i data-lucide="pencil" class="w-4 h-4"></i>
                    </button>

                    <!-- Icon 3: Hapus (Delete) -->
                    <button 
                      type="button"
                      onclick="confirmDeleteItem(<?= $jsonItem ?>)" 
                      title="Hapus Item" 
                      class="p-1.5 rounded-lg text-rose-600 hover:text-rose-800 hover:bg-rose-50 border border-slate-200 transition cursor-pointer inline-flex items-center justify-center"
                    >
                      <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>

                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Bahagian Pagination (50 item per page) -->
      <?php if ($totalPages > 1): ?>
        <div class="p-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
          <div class="text-slate-600 font-medium">
            Halaman <span class="font-bold text-slate-900"><?= $currentPage ?></span> daripada <span class="font-bold text-slate-900"><?= $totalPages ?></span> (Jumlah: <?= $totalFilteredItems ?> item)
          </div>

          <div class="flex items-center gap-1">
            <!-- First Page -->
            <?php if ($currentPage > 1): ?>
              <a 
                href="admin.php?cat=<?= urlencode($selectedCategory) ?><?= $searchQuery !== '' ? '&search=' . urlencode($searchQuery) : '' ?>&page=1" 
                class="px-2.5 py-1.5 rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 font-medium"
                title="Halaman Pertama"
              >
                &laquo;
              </a>
              <a 
                href="admin.php?cat=<?= urlencode($selectedCategory) ?><?= $searchQuery !== '' ? '&search=' . urlencode($searchQuery) : '' ?>&page=<?= $currentPage - 1 ?>" 
                class="px-3 py-1.5 rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 font-medium"
              >
                Sebelum
              </a>
            <?php endif; ?>

            <!-- Page Number Badges -->
            <?php
              $startPage = max(1, $currentPage - 2);
              $endPage = min($totalPages, $currentPage + 2);
              for ($p = $startPage; $p <= $endPage; $p++):
            ?>
              <a 
                href="admin.php?cat=<?= urlencode($selectedCategory) ?><?= $searchQuery !== '' ? '&search=' . urlencode($searchQuery) : '' ?>&page=<?= $p ?>" 
                class="px-3 py-1.5 rounded-lg font-bold transition <?= $p === $currentPage ? 'bg-amber-600 text-white shadow-2xs' : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-100' ?>"
              >
                <?= $p ?>
              </a>
            <?php endfor; ?>

            <!-- Next Page -->
            <?php if ($currentPage < $totalPages): ?>
              <a 
                href="admin.php?cat=<?= urlencode($selectedCategory) ?><?= $searchQuery !== '' ? '&search=' . urlencode($searchQuery) : '' ?>&page=<?= $currentPage + 1 ?>" 
                class="px-3 py-1.5 rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 font-medium"
              >
                Seterusnya
              </a>
              <a 
                href="admin.php?cat=<?= urlencode($selectedCategory) ?><?= $searchQuery !== '' ? '&search=' . urlencode($searchQuery) : '' ?>&page=<?= $totalPages ?>" 
                class="px-2.5 py-1.5 rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 font-medium"
                title="Halaman Terakhir"
              >
                &raquo;
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

    </div>

  </main>

  <!-- =================================================================== -->
  <!-- MODAL 1: Tambah Item Baharu (Pop-up Create)                         -->
  <!-- =================================================================== -->
  <div id="modalAddItem" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="min-h-screen px-4 text-center flex items-center justify-center">
      <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeModal('modalAddItem')"></div>
      
      <div class="inline-block w-full max-w-xl p-6 my-8 text-left align-middle bg-white rounded-2xl shadow-2xl relative z-10 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
          <div class="flex items-center gap-2">
            <i data-lucide="plus-circle" class="w-5 h-5 text-amber-600"></i>
            <h3 class="text-base font-bold text-slate-900">Tambah Item Baharu ke Pangkalan Data</h3>
          </div>
          <button onclick="closeModal('modalAddItem')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>

        <form method="POST" action="admin.php" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
          <input type="hidden" name="action_add_item" value="1">

          <div>
            <label class="block font-bold text-slate-700 mb-1">Kod Stok <span class="text-red-500">*</span></label>
            <input type="text" name="kod" required placeholder="Cth: B1, K4, T11" class="w-full px-3 py-2 border rounded-xl uppercase focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600">
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Item <span class="text-red-500">*</span></label>
            <input type="text" name="nama" required placeholder="Cth: BALL PEN BLACK" class="w-full px-3 py-2 border rounded-xl uppercase focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600">
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Kategori</label>
            <select name="kategori" class="w-full px-3 py-2 border rounded-xl bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600">
              <option>Alat Tulis</option>
              <option>Kertas &amp; Sampul</option>
              <option>Buku &amp; Fail</option>
              <option>Toner Printer</option>
              <option>Barang Domestik</option>
              <option>Lain-lain</option>
            </select>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Unit Pengukuran</label>
            <input type="text" name="unit" value="BATANG" placeholder="Cth: UNIT, BATANG, RIM" class="w-full px-3 py-2 border rounded-xl uppercase focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600">
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Baki Stok Sedia Ada</label>
            <input type="number" name="baki_stok" value="50" min="0" class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600">
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Paras Minimum</label>
            <input type="number" name="paras_minimum" value="10" min="0" class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600">
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Harga Seunit (RM)</label>
            <input type="number" step="0.01" name="harga_seunit" value="1.50" min="0" class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600">
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan</label>
            <input type="text" name="catatan" placeholder="Cth: Lokasi Rak A2 atau pembekal" class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600">
          </div>

          <div class="sm:col-span-2 pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
            <button type="button" onclick="closeModal('modalAddItem')" class="px-4 py-2 border border-slate-300 rounded-xl font-medium text-slate-700 hover:bg-slate-100 cursor-pointer">
              Batal
            </button>
            <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-xs cursor-pointer flex items-center gap-1.5">
              <i data-lucide="plus" class="w-4 h-4"></i>
              <span>Simpan ke Database</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- =================================================================== -->
  <!-- MODAL 2: Lihat Butiran Item (Pop-up Read)                           -->
  <!-- =================================================================== -->
  <div id="modalViewItem" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="min-h-screen px-4 text-center flex items-center justify-center">
      <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeModal('modalViewItem')"></div>
      
      <div class="inline-block w-full max-w-md p-6 my-8 text-left align-middle bg-white rounded-2xl shadow-2xl relative z-10 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
          <div class="flex items-center gap-2">
            <i data-lucide="eye" class="w-5 h-5 text-amber-600"></i>
            <h3 class="text-base font-bold text-slate-900">Butiran Item Stok</h3>
          </div>
          <button onclick="closeModal('modalViewItem')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>

        <div class="space-y-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
          <div class="flex justify-between items-center pb-2 border-b border-slate-200">
            <span class="text-slate-500 font-medium">Kod Stok:</span>
            <span id="viewKod" class="font-mono font-bold text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-200 text-sm"></span>
          </div>
          <div class="flex justify-between items-start pb-2 border-b border-slate-200">
            <span class="text-slate-500 font-medium">Nama Item:</span>
            <span id="viewNama" class="font-bold text-slate-900 text-right max-w-xs"></span>
          </div>
          <div class="flex justify-between items-center pb-2 border-b border-slate-200">
            <span class="text-slate-500 font-medium">Kategori:</span>
            <span id="viewKategori" class="font-semibold text-slate-800"></span>
          </div>
          <div class="flex justify-between items-center pb-2 border-b border-slate-200">
            <span class="text-slate-500 font-medium">Unit:</span>
            <span id="viewUnit" class="font-mono uppercase font-bold text-slate-800"></span>
          </div>
          <div class="flex justify-between items-center pb-2 border-b border-slate-200">
            <span class="text-slate-500 font-medium">Baki Stok:</span>
            <span id="viewBaki" class="font-bold text-sm"></span>
          </div>
          <div class="flex justify-between items-center pb-2 border-b border-slate-200">
            <span class="text-slate-500 font-medium">Paras Minimum:</span>
            <span id="viewParas" class="font-mono font-bold text-slate-800"></span>
          </div>
          <div class="flex justify-between items-center pb-2 border-b border-slate-200">
            <span class="text-slate-500 font-medium">Harga Seunit:</span>
            <span id="viewHarga" class="font-mono font-bold text-slate-900"></span>
          </div>
          <div class="flex justify-between items-start">
            <span class="text-slate-500 font-medium">Catatan:</span>
            <span id="viewCatatan" class="text-slate-700 italic text-right max-w-xs"></span>
          </div>
        </div>

        <div class="pt-4 flex justify-end">
          <button type="button" onclick="closeModal('modalViewItem')" class="px-4 py-2 bg-slate-900 hover:bg-black text-white font-bold rounded-xl transition cursor-pointer">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- =================================================================== -->
  <!-- MODAL 3: Ubah / Kemaskini Item (Pop-up Update)                      -->
  <!-- =================================================================== -->
  <div id="modalEditItem" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="min-h-screen px-4 text-center flex items-center justify-center">
      <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeModal('modalEditItem')"></div>
      
      <div class="inline-block w-full max-w-xl p-6 my-8 text-left align-middle bg-white rounded-2xl shadow-2xl relative z-10 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
          <div class="flex items-center gap-2">
            <i data-lucide="pencil" class="w-5 h-5 text-blue-600"></i>
            <h3 class="text-base font-bold text-slate-900">Ubah / Kemaskini Data Item Stok</h3>
          </div>
          <button onclick="closeModal('modalEditItem')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>

        <form method="POST" action="admin.php" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
          <input type="hidden" name="action_edit_item" value="1">
          <input type="hidden" id="editItemId" name="item_id" value="">

          <div>
            <label class="block font-bold text-slate-700 mb-1">Kod Stok <span class="text-red-500">*</span></label>
            <input type="text" id="editKod" name="kod" required class="w-full px-3 py-2 border rounded-xl uppercase focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Item <span class="text-red-500">*</span></label>
            <input type="text" id="editNama" name="nama" required class="w-full px-3 py-2 border rounded-xl uppercase focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Kategori</label>
            <select id="editKategori" name="kategori" class="w-full px-3 py-2 border rounded-xl bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
              <option value="Alat Tulis">Alat Tulis</option>
              <option value="Kertas & Sampul">Kertas &amp; Sampul</option>
              <option value="Buku & Fail">Buku &amp; Fail</option>
              <option value="Toner Printer">Toner Printer</option>
              <option value="Barang Domestik">Barang Domestik</option>
              <option value="Lain-lain">Lain-lain</option>
            </select>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Unit Pengukuran</label>
            <input type="text" id="editUnit" name="unit" class="w-full px-3 py-2 border rounded-xl uppercase focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Baki Stok Sedia Ada</label>
            <input type="number" id="editBaki" name="baki_stok" min="0" class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Paras Minimum</label>
            <input type="number" id="editParas" name="paras_minimum" min="0" class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Harga Seunit (RM)</label>
            <input type="number" step="0.01" id="editHarga" name="harga_seunit" min="0" class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan</label>
            <input type="text" id="editCatatan" name="catatan" class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
          </div>

          <div class="sm:col-span-2 pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
            <button type="button" onclick="closeModal('modalEditItem')" class="px-4 py-2 border border-slate-300 rounded-xl font-medium text-slate-700 hover:bg-slate-100 cursor-pointer">
              Batal
            </button>
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs cursor-pointer flex items-center gap-1.5">
              <i data-lucide="check" class="w-4 h-4"></i>
              <span>Kemaskini Perubahan</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- =================================================================== -->
  <!-- MODAL 4: Sahkan Hapus Item (Pop-up Delete Confirmation)             -->
  <!-- =================================================================== -->
  <div id="modalDeleteItem" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="min-h-screen px-4 text-center flex items-center justify-center">
      <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeModal('modalDeleteItem')"></div>
      
      <div class="inline-block w-full max-w-sm p-6 my-8 text-left align-middle bg-white rounded-2xl shadow-2xl relative z-10 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
          <div class="flex items-center gap-2 text-rose-600">
            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            <h3 class="text-base font-bold text-slate-900">Sahkan Padam Item</h3>
          </div>
          <button onclick="closeModal('modalDeleteItem')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>

        <p class="text-slate-600 mb-4 leading-relaxed">
          Adakah anda pasti mahu menghapuskan item berikut dari jadual <code class="font-bold text-slate-800">senarai data stok pejabat</code>?
        </p>

        <div class="bg-rose-50 border border-rose-200 p-3 rounded-xl mb-4">
          <div class="font-mono font-bold text-rose-700" id="deleteItemKod"></div>
          <div class="font-bold text-slate-900 mt-0.5" id="deleteItemNama"></div>
        </div>

        <form method="POST" action="admin.php" class="flex items-center justify-end gap-2">
          <input type="hidden" name="action_delete_item" value="1">
          <input type="hidden" id="deleteItemId" name="item_id" value="">
          <button type="button" onclick="closeModal('modalDeleteItem')" class="px-4 py-2 border border-slate-300 rounded-xl font-medium text-slate-700 hover:bg-slate-100 cursor-pointer">
            Batal
          </button>
          <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-xs cursor-pointer flex items-center gap-1.5">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
            <span>Hapuskan Item</span>
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Javascript Utiliti & Logik Modal -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      lucide.createIcons();
    });

    function openModal(id) {
      document.getElementById(id).classList.remove('hidden');
      lucide.createIcons();
    }

    function closeModal(id) {
      document.getElementById(id).classList.add('hidden');
    }

    function openAddItemModal() {
      openModal('modalAddItem');
    }

    function viewItemDetails(item) {
      document.getElementById('viewKod').innerText = item.kod || '-';
      document.getElementById('viewNama').innerText = item.nama || '-';
      document.getElementById('viewKategori').innerText = item.kategori || '-';
      document.getElementById('viewUnit').innerText = item.unit || '-';
      
      const bakiEl = document.getElementById('viewBaki');
      const baki = parseInt(item.baki_stok || 0, 10);
      const paras = parseInt(item.paras_minimum || 0, 10);
      if (baki <= 0) {
        bakiEl.className = 'font-bold text-sm text-red-600';
        bakiEl.innerText = '0 ' + (item.unit || '') + ' (Habis)';
      } else if (baki <= paras) {
        bakiEl.className = 'font-bold text-sm text-amber-600';
        bakiEl.innerText = baki + ' ' + (item.unit || '') + ' (Rendah)';
      } else {
        bakiEl.className = 'font-bold text-sm text-emerald-600';
        bakiEl.innerText = baki + ' ' + (item.unit || '');
      }

      document.getElementById('viewParas').innerText = paras + ' ' + (item.unit || '');
      document.getElementById('viewHarga').innerText = 'RM ' + parseFloat(item.harga_seunit || 0).toFixed(2);
      document.getElementById('viewCatatan').innerText = item.catatan || '- Tiada -';

      openModal('modalViewItem');
    }

    function openEditItemModal(item) {
      document.getElementById('editItemId').value = item.id || '';
      document.getElementById('editKod').value = item.kod || '';
      document.getElementById('editNama').value = item.nama || '';
      document.getElementById('editKategori').value = item.kategori || 'Alat Tulis';
      document.getElementById('editUnit').value = item.unit || 'UNIT';
      document.getElementById('editBaki').value = item.baki_stok ?? 0;
      document.getElementById('editParas').value = item.paras_minimum ?? 5;
      document.getElementById('editHarga').value = parseFloat(item.harga_seunit || 0).toFixed(2);
      document.getElementById('editCatatan').value = item.catatan || '';

      openModal('modalEditItem');
    }

    function confirmDeleteItem(item) {
      document.getElementById('deleteItemId').value = item.id || '';
      document.getElementById('deleteItemKod').innerText = 'Kod: ' + (item.kod || '');
      document.getElementById('deleteItemNama').innerText = item.nama || '';

      openModal('modalDeleteItem');
    }
  </script>
</body>
</html>

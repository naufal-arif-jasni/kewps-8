<?php
/**
 * admin.php
 * Panel Pentadbir Stor KEW.PS-8 & Pengurusan Stok Pejabat
 * Pejabat KDYMM Tuanku Sultan Kedah Darul Aman
 */

require_once __DIR__ . '/db_connect.php';

$message = '';
$error = '';

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

// Proses Tambah Item Baru ke `senarai data stok pejabat`
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

    <!-- Bahagian 2: Tambah Item Baharu ke `senarai data stok pejabat` -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
      <div class="flex items-center gap-2 mb-4 pb-2 border-b border-slate-200">
        <i data-lucide="plus-circle" class="w-5 h-5 text-amber-600"></i>
        <h2 class="text-sm font-bold text-slate-900">Tambah Item Baru Terus ke MySQL (`senarai data stok pejabat`)</h2>
      </div>

      <form method="POST" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
        <input type="hidden" name="action_add_item" value="1">
        <div>
          <label class="block font-bold text-slate-700 mb-1">Kod Stok <span class="text-red-500">*</span></label>
          <input type="text" name="kod" required placeholder="Cth: B1, K4, T11" class="w-full px-3 py-2 border rounded-xl uppercase">
        </div>
        <div>
          <label class="block font-bold text-slate-700 mb-1">Nama Item <span class="text-red-500">*</span></label>
          <input type="text" name="nama" required placeholder="Cth: BALL PEN BLACK" class="w-full px-3 py-2 border rounded-xl uppercase">
        </div>
        <div>
          <label class="block font-bold text-slate-700 mb-1">Kategori</label>
          <select name="kategori" class="w-full px-3 py-2 border rounded-xl bg-white">
            <option>Alat Tulis</option>
            <option>Kertas & Sampul</option>
            <option>Buku & Fail</option>
            <option>Toner Printer</option>
            <option>Barang Domestik</option>
            <option>Lain-lain</option>
          </select>
        </div>
        <div>
          <label class="block font-bold text-slate-700 mb-1">Unit</label>
          <input type="text" name="unit" value="BATANG" class="w-full px-3 py-2 border rounded-xl uppercase">
        </div>
        <div>
          <label class="block font-bold text-slate-700 mb-1">Baki Stok Sedia Ada</label>
          <input type="number" name="baki_stok" value="50" class="w-full px-3 py-2 border rounded-xl">
        </div>
        <div>
          <label class="block font-bold text-slate-700 mb-1">Paras Minimum</label>
          <input type="number" name="paras_minimum" value="10" class="w-full px-3 py-2 border rounded-xl">
        </div>
        <div>
          <label class="block font-bold text-slate-700 mb-1">Harga Seunit (RM)</label>
          <input type="number" step="0.01" name="harga_seunit" value="1.50" class="w-full px-3 py-2 border rounded-xl">
        </div>
        <div class="flex items-end">
          <button type="submit" class="w-full py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-xs">
            + Simpan ke Database
          </button>
        </div>
      </form>
    </div>

  </main>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      lucide.createIcons();
    });
  </script>
</body>
</html>

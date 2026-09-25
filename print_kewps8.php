<?php
/**
 * print_kewps8.php
 * Cetakan Borang Rasmi KEW.PS-8 (Individu Kepada Stor)
 * Pejabat KDYMM Tuanku Sultan Kedah Darul Aman
 */

require_once __DIR__ . '/db_connect.php';

$bpsi = trim($_GET['bpsi'] ?? '');
$request = null;
$items = [];

if ($bpsi !== '') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM `requests` WHERE `no_bpsi` = :bpsi LIMIT 1");
        $stmt->execute([':bpsi' => $bpsi]);
        $request = $stmt->fetch();

        if ($request) {
            $stmtItems = $pdo->prepare("SELECT * FROM `request_items` WHERE `request_id` = :id");
            $stmtItems->execute([':id' => $request['id']]);
            $items = $stmtItems->fetchAll();
        }
    } catch (PDOException $e) {
        // Abaikan
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <title>KEW.PS-8 - <?= htmlspecialchars($bpsi ?: 'Borang Kosong') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @page { size: A4 portrait; margin: 12mm 15mm; }
    body { font-family: 'Times New Roman', Times, serif; color: black; background: #f8fafc; }
    @media print {
      .no-print { display: none !important; }
      body { background: white !important; }
      .sheet { box-shadow: none !important; border: none !important; padding: 0 !important; }
    }
  </style>
</head>
<body class="p-4 sm:p-8 flex flex-col items-center">

  <!-- Butang Cetak (Atas) -->
  <div class="max-w-[210mm] w-full mb-4 flex items-center justify-between no-print">
    <a href="index.php" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-xs font-sans font-bold hover:bg-slate-700">
      &larr; Kembali ke Laman Utama
    </a>
    <button onclick="window.print()" class="px-5 py-2 bg-amber-600 text-white rounded-lg text-xs font-sans font-bold hover:bg-amber-700 shadow-md">
      Cetak Borang KEW.PS-8 (A4)
    </button>
  </div>

  <!-- Helaian Borang A4 -->
  <div class="sheet max-w-[210mm] w-full bg-white p-8 border border-slate-300 shadow-xl text-black">
    
    <!-- Kod Borang Atas Kanan -->
    <div class="text-right font-bold text-sm mb-1 tracking-wider">
      KEW.PS-8
    </div>

    <!-- Tajuk Rasmi Borang -->
    <div class="text-center mb-6">
      <h2 class="text-base font-bold uppercase tracking-tight">
        BORANG PERMOHONAN STOK (INDIVIDU KEPADA STOR)
      </h2>
      <h3 class="text-xs uppercase font-bold tracking-tight text-slate-800 mt-0.5">
        PEJABAT KDYMM TUANKU SULTAN KEDAH DARUL AMAN
      </h3>
    </div>

    <!-- Maklumat Pemohon Header -->
    <div class="flex justify-between items-start text-xs mb-4 pb-2 border-b border-black">
      <div class="space-y-1">
        <div><strong>Nama Pemohon:</strong> <?= htmlspecialchars($request['nama_pemohon'] ?? '..........................................................') ?></div>
        <div><strong>Jawatan & Gred:</strong> <?= htmlspecialchars($request['jawatan'] ?? '..........................................................') ?></div>
        <div><strong>Bahagian / Sektor:</strong> <?= htmlspecialchars($request['bahagian'] ?? '..........................................................') ?></div>
      </div>
      <div class="text-right space-y-1">
        <div><strong>No. Permohonan (BPSI):</strong> <span class="font-mono font-bold"><?= htmlspecialchars($request['no_bpsi'] ?? 'BPSI/......../........') ?></span></div>
        <div><strong>Tarikh Mohon:</strong> <?= htmlspecialchars($request['tarikh_mohon'] ?? date('d/m/Y')) ?></div>
        <div><strong>Status:</strong> <span class="uppercase font-bold"><?= htmlspecialchars($request['status'] ?? 'MENUNGGU KELULUSAN') ?></span></div>
      </div>
    </div>

    <!-- Jadual Item KEW.PS-8 Rasmi -->
    <table class="w-full text-xs border-collapse border border-black mb-6">
      <thead>
        <tr class="bg-slate-100 text-center font-bold">
          <th class="border border-black p-2 w-10">Bil.</th>
          <th class="border border-black p-2 w-24">No. Kod</th>
          <th class="border border-black p-2">Perihal Stok</th>
          <th class="border border-black p-2 w-20">Unit</th>
          <th class="border border-black p-2 w-24">Kuantiti Dimohon</th>
          <th class="border border-black p-2 w-24">Kuantiti Diluluskan</th>
          <th class="border border-black p-2 w-28">Catatan</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($items)): ?>
          <?php foreach ($items as $idx => $it): ?>
            <tr>
              <td class="border border-black p-2 text-center"><?= $idx + 1 ?></td>
              <td class="border border-black p-2 font-mono font-bold text-center"><?= htmlspecialchars($it['kod']) ?></td>
              <td class="border border-black p-2 font-semibold"><?= htmlspecialchars($it['nama']) ?></td>
              <td class="border border-black p-2 text-center uppercase"><?= htmlspecialchars($it['unit']) ?></td>
              <td class="border border-black p-2 text-center font-bold"><?= $it['kuantiti_dimohon'] ?></td>
              <td class="border border-black p-2 text-center font-bold"><?= $it['kuantiti_diluluskan'] ?? $it['kuantiti_dimohon'] ?></td>
              <td class="border border-black p-2 text-center text-[10px]"><?= htmlspecialchars($it['catatan'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <!-- Baris Kosong jika borang kosong dicetak -->
          <?php for ($i = 1; $i <= 8; $i++): ?>
            <tr class="h-8">
              <td class="border border-black p-2 text-center"><?= $i ?></td>
              <td class="border border-black p-2"></td>
              <td class="border border-black p-2"></td>
              <td class="border border-black p-2"></td>
              <td class="border border-black p-2"></td>
              <td class="border border-black p-2"></td>
              <td class="border border-black p-2"></td>
            </tr>
          <?php endfor; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Bahagian Perakuan & Tandatangan Rasmi -->
    <div class="grid grid-cols-3 gap-4 text-xs pt-4 border-t border-black">
      
      <!-- Pemohon -->
      <div class="space-y-1">
        <p class="font-bold underline">Permohonan Oleh:</p>
        <div class="h-16"></div>
        <p>Nama: <?= htmlspecialchars($request['nama_pemohon'] ?? '..................................') ?></p>
        <p>Jawatan: <?= htmlspecialchars($request['jawatan'] ?? '..................................') ?></p>
        <p>Tarikh: <?= htmlspecialchars($request['tarikh_mohon'] ?? '..................................') ?></p>
      </div>

      <!-- Pegawai Pelulus -->
      <div class="space-y-1">
        <p class="font-bold underline">Kelulusan Stor:</p>
        <div class="h-16"></div>
        <p>Nama: <?= htmlspecialchars($request['nama_pelulus'] ?? 'PEGAWAI STOR') ?></p>
        <p>Jawatan: <?= htmlspecialchars($request['jawatan_pelulus'] ?? 'Penolong Pegawai Tadbir (Stor)') ?></p>
        <p>Tarikh: <?= htmlspecialchars($request['tarikh_kelulusan'] ?? '..................................') ?></p>
      </div>

      <!-- Perakuan Penerimaan -->
      <div class="space-y-1">
        <p class="font-bold underline">Akuan Penerimaan:</p>
        <div class="h-16"></div>
        <p>Nama: ..................................</p>
        <p>Jawatan: ..................................</p>
        <p>Tarikh: ..................................</p>
      </div>

    </div>

  </div>

</body>
</html>

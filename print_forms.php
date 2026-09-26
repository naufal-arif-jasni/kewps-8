<?php
/**
 * print_forms.php
 * Paparan & Cetakan Borang-Borang Rasmi Aset & Stor Kerajaan
 * Pejabat KDYMM Tuanku Sultan Kedah Darul Aman
 */

$form = trim($_GET['form'] ?? 'KEW.PA-9');
$validForms = ['KEW.PA-9', 'KEW.PA-10', 'KEW.PA-11', 'KEW.PA-20', 'KEW.PA-32', 'KEW.PS-8'];
if (!in_array($form, $validForms)) {
    $form = 'KEW.PA-9';
}

$titles = [
    'KEW.PA-9' => 'BORANG PERMOHONAN PERGERAKAN / PINJAMAN ASET ALIH',
    'KEW.PA-10' => 'LAPORAN PEMERIKSAAN HARTA MODAL',
    'KEW.PA-11' => 'LAPORAN PEMERIKSAAN INVENTORI',
    'KEW.PA-20' => 'LAPORAN TAHUNAN PELUPUSAN ASET ALIH KERAJAAN',
    'KEW.PA-32' => 'LAPORAN TINDAKAN SURCAJ / TATATERTIB ASET ALIH KERAJAAN',
    'KEW.PS-8' => 'BORANG PERMOHONAN STOK (INDIVIDU KEPADA STOR)'
];
?>
<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($form) ?> - <?= htmlspecialchars($titles[$form] ?? 'Borang Rasmi') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @page { size: A4 landscape; margin: 10mm 12mm; }
    body { font-family: 'Times New Roman', Times, serif; color: black; background: #f8fafc; }
    @media print {
      .no-print { display: none !important; }
      body { background: white !important; }
      .sheet { box-shadow: none !important; border: none !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
    }
  </style>
  <?php if ($form === 'KEW.PA-9' || $form === 'KEW.PS-8'): ?>
  <style>
    @page { size: A4 portrait; margin: 12mm 15mm; }
  </style>
  <?php endif; ?>
</head>
<body class="p-4 sm:p-8 flex flex-col items-center">

  <!-- Butang Atas (No-Print) -->
  <div class="max-w-[280mm] w-full mb-4 flex items-center justify-between no-print">
    <a href="index.php" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-xs font-sans font-bold hover:bg-slate-700">
      &larr; Kembali ke Laman Utama
    </a>
    <div class="flex items-center gap-2">
      <button onclick="window.print()" class="px-5 py-2 bg-amber-600 text-white rounded-lg text-xs font-sans font-bold hover:bg-amber-700 shadow-md">
        Cetak / Muat Turun (PDF)
      </button>
    </div>
  </div>

  <!-- Helaian Borang -->
  <div class="sheet max-w-[280mm] w-full bg-white p-8 border border-slate-300 shadow-xl text-black">

    <?php if ($form === 'KEW.PA-9'): ?>
      <!-- ==================== KEW.PA-9 ==================== -->
      <div class="flex justify-between items-center text-xs mb-2">
        <span>Pekeliling Perbendaharaan Malaysia</span>
        <span>AM 2.4 Lampiran A</span>
      </div>
      <div class="text-right font-bold text-sm mb-2">KEW.PA-9</div>
      <div class="text-right text-xs mb-4">No. Permohonan : ......................</div>

      <div class="text-center font-bold text-base mb-6 uppercase tracking-tight">
        BORANG PERMOHONAN PERGERAKAN/ PINJAMAN ASET ALIH
      </div>

      <table class="w-full text-xs border border-black mb-4">
        <tr>
          <td class="p-2 border border-black font-semibold w-1/4">Nama Pemohon:</td>
          <td class="p-2 border border-black w-1/4"></td>
          <td class="p-2 border border-black font-semibold w-1/4">Tujuan:</td>
          <td class="p-2 border border-black w-1/4"></td>
        </tr>
        <tr>
          <td class="p-2 border border-black font-semibold">Jawatan:</td>
          <td class="p-2 border border-black"></td>
          <td class="p-2 border border-black font-semibold">Tempat Digunakan:</td>
          <td class="p-2 border border-black"></td>
        </tr>
        <tr>
          <td class="p-2 border border-black font-semibold">Bahagian:</td>
          <td class="p-2 border border-black"></td>
          <td class="p-2 border border-black font-semibold">Nama Pengeluar:</td>
          <td class="p-2 border border-black"></td>
        </tr>
      </table>

      <table class="w-full text-xs border-collapse border border-black mb-6">
        <thead>
          <tr class="bg-slate-50 text-center font-bold">
            <th rowspan="2" class="border border-black p-2 w-10">Bil.</th>
            <th rowspan="2" class="border border-black p-2 w-36">No. Siri Pendaftaran</th>
            <th rowspan="2" class="border border-black p-2">Keterangan Aset</th>
            <th colspan="2" class="border border-black p-2">Tarikh</th>
            <th rowspan="2" class="border border-black p-2 w-20">Lulus / Tidak Lulus</th>
            <th colspan="2" class="border border-black p-2">Tarikh</th>
            <th rowspan="2" class="border border-black p-2 w-28">Catatan</th>
          </tr>
          <tr class="bg-slate-50 text-center font-bold">
            <th class="border border-black p-1 w-20">Dipinjam</th>
            <th class="border border-black p-1 w-20">Dijangka Pulang</th>
            <th class="border border-black p-1 w-20">Dipulangkan</th>
            <th class="border border-black p-1 w-20">Diterima</th>
          </tr>
        </thead>
        <tbody>
          <?php for ($i = 1; $i <= 8; $i++): ?>
            <tr class="h-9">
              <td class="border border-black p-1 text-center"><?= $i ?></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
            </tr>
          <?php endfor; ?>
        </tbody>
      </table>

      <div class="grid grid-cols-2 gap-8 text-xs mb-8">
        <div>
          <p class="font-bold">..................................................</p>
          <p class="font-bold">(Tandatangan Peminjam)</p>
          <p class="mt-2">Nama :</p>
          <p>Jawatan :</p>
          <p>Tarikh :</p>
        </div>
        <div>
          <p class="font-bold">..................................................</p>
          <p class="font-bold">(Tandatangan Pelulus)</p>
          <p class="mt-2">Nama :</p>
          <p>Jawatan :</p>
          <p>Tarikh :</p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-8 text-xs border-t border-black pt-4">
        <div>
          <p class="font-bold">..................................................</p>
          <p class="font-bold">(Tandatangan Pemulang)</p>
          <p class="mt-2">Nama :</p>
          <p>Jawatan :</p>
          <p>Tarikh :</p>
        </div>
        <div>
          <p class="font-bold">..................................................</p>
          <p class="font-bold">(Tandatangan Penerima)</p>
          <p class="mt-2">Nama :</p>
          <p>Jawatan :</p>
          <p>Tarikh :</p>
        </div>
      </div>

      <div class="text-right text-[11px] mt-6">M.S. 1/1</div>

    <?php elseif ($form === 'KEW.PA-10'): ?>
      <!-- ==================== KEW.PA-10 ==================== -->
      <div class="text-right font-bold text-sm mb-2">KEW.PA-10</div>
      <div class="text-center font-bold text-base mb-1 uppercase tracking-tight">
        LAPORAN PEMERIKSAAN HARTA MODAL
      </div>
      <div class="text-center text-xs mb-4">(diisi oleh Pegawai Pemeriksa)</div>

      <div class="text-xs mb-4 space-y-1">
        <div><strong>Kementerian/Jabatan :</strong> Pengajian Tinggi Malaysia</div>
        <div><strong>Bahagian :</strong> .....................................................................................</div>
      </div>

      <table class="w-full text-xs border-collapse border border-black mb-6">
        <thead>
          <tr class="bg-slate-50 text-center font-bold">
            <th rowspan="2" class="border border-black p-2 w-10">BIL</th>
            <th rowspan="2" class="border border-black p-2 w-32">No. Siri Pendaftaran</th>
            <th rowspan="2" class="border border-black p-2 w-48">Jenis Harta Modal</th>
            <th colspan="2" class="border border-black p-2">Lokasi</th>
            <th colspan="4" class="border border-black p-2">Daftar (KEW.PA-2)</th>
            <th rowspan="2" class="border border-black p-2 w-32">Keadaan Harta Modal</th>
            <th rowspan="2" class="border border-black p-2">Catatan</th>
          </tr>
          <tr class="bg-slate-50 text-center font-bold">
            <th class="border border-black p-1 w-24">Mengikut Rekod</th>
            <th class="border border-black p-1 w-24">Sebenar</th>
            <th class="border border-black p-1 w-12">Lengkap (Ya)</th>
            <th class="border border-black p-1 w-12">Lengkap (Tidak)</th>
            <th class="border border-black p-1 w-12">Kemaskini (Ya)</th>
            <th class="border border-black p-1 w-12">Kemaskini (Tidak)</th>
          </tr>
        </thead>
        <tbody>
          <?php for ($i = 1; $i <= 8; $i++): ?>
            <tr class="h-8">
              <td class="border border-black p-1 text-center"><?= $i ?></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
            </tr>
          <?php endfor; ?>
        </tbody>
      </table>

      <div class="grid grid-cols-3 gap-6 text-xs items-start">
        <div>
          <p>....................................................................</p>
          <p class="font-bold">(Tandatangan)</p>
          <p class="mt-4">....................................................................</p>
          <p>(Nama Pegawai Pemeriksa 1)</p>
          <p class="mt-4">....................................................................</p>
          <p>(Jawatan)</p>
          <p class="mt-4">....................................................................</p>
          <p>(Tarikh Pemeriksaan)</p>
        </div>
        <div>
          <p>....................................................................</p>
          <p class="font-bold">(Tandatangan)</p>
          <p class="mt-4">....................................................................</p>
          <p>(Nama Pegawai Pemeriksa 2)</p>
          <p class="mt-4">....................................................................</p>
          <p>(Jawatan)</p>
          <p class="mt-4">....................................................................</p>
          <p>(Tarikh Pemeriksaan)</p>
        </div>
        <div class="border border-black p-3 text-[11px] leading-relaxed">
          <p class="font-bold underline mb-1">Nota :</p>
          <p><strong>Lokasi :</strong> Nyatakan lokasi harta modal mengikut rekod dan lokasi harta modal semasa pemeriksaan.</p>
          <p class="mt-1"><strong>Daftar :</strong> Tandakan &radic; pada yang berkenaan.</p>
          <p class="mt-1"><strong>Keadaan Harta Modal :</strong> Nyatakan samada sedang digunakan atau tidak digunakan.</p>
          <p class="mt-1"><strong>Catatan :</strong> Penjelasan kepada penemuan pemeriksaan.</p>
        </div>
      </div>

    <?php elseif ($form === 'KEW.PA-11'): ?>
      <!-- ==================== KEW.PA-11 ==================== -->
      <div class="text-right font-bold text-sm mb-2">KEW.PA-11</div>
      <div class="text-center font-bold text-base mb-1 uppercase tracking-tight">
        LAPORAN PEMERIKSAAN INVENTORI
      </div>
      <div class="text-center text-xs mb-4">(diisi oleh Pegawai Pemeriksa)</div>

      <div class="text-xs mb-4 space-y-1">
        <div><strong>Kementerian/Jabatan :</strong> Pengajian Tinggi Malaysia</div>
        <div><strong>Bahagian :</strong> .....................................................................................</div>
      </div>

      <table class="w-full text-xs border-collapse border border-black mb-6">
        <thead>
          <tr class="bg-slate-50 text-center font-bold">
            <th rowspan="2" class="border border-black p-2 w-10">BIL</th>
            <th rowspan="2" class="border border-black p-2 w-48">Jenis Inventori</th>
            <th colspan="4" class="border border-black p-2">Daftar</th>
            <th colspan="2" class="border border-black p-2">Lokasi</th>
            <th colspan="2" class="border border-black p-2">Kuantiti</th>
            <th rowspan="2" class="border border-black p-2 w-28">Keadaan Inventori</th>
            <th rowspan="2" class="border border-black p-2">Catatan</th>
          </tr>
          <tr class="bg-slate-50 text-center font-bold">
            <th class="border border-black p-1 w-12">Lengkap (Ya)</th>
            <th class="border border-black p-1 w-12">Lengkap (Tidak)</th>
            <th class="border border-black p-1 w-12">Kemaskini (Ya)</th>
            <th class="border border-black p-1 w-12">Kemaskini (Tidak)</th>
            <th class="border border-black p-1 w-24">Mengikut Rekod</th>
            <th class="border border-black p-1 w-24">Sebenar</th>
            <th class="border border-black p-1 w-20">Mengikut Rekod</th>
            <th class="border border-black p-1 w-20">Sebenar</th>
          </tr>
        </thead>
        <tbody>
          <?php for ($i = 1; $i <= 8; $i++): ?>
            <tr class="h-8">
              <td class="border border-black p-1 text-center"><?= $i ?></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
            </tr>
          <?php endfor; ?>
        </tbody>
      </table>

      <div class="grid grid-cols-3 gap-6 text-xs items-start">
        <div>
          <p>....................................................................</p>
          <p class="font-bold">(Tandatangan)</p>
          <p class="mt-4">....................................................................</p>
          <p>(Nama Pegawai Pemeriksa 1)</p>
          <p class="mt-4">....................................................................</p>
          <p>(Jawatan)</p>
          <p class="mt-4">....................................................................</p>
          <p>(Tarikh Pemeriksaan)</p>
        </div>
        <div>
          <p>....................................................................</p>
          <p class="font-bold">(Tandatangan)</p>
          <p class="mt-4">....................................................................</p>
          <p>(Nama Pegawai Pemeriksa 2)</p>
          <p class="mt-4">....................................................................</p>
          <p>(Jawatan)</p>
          <p class="mt-4">....................................................................</p>
          <p>(Tarikh Pemeriksaan)</p>
        </div>
        <div class="border border-black p-3 text-[11px] leading-relaxed">
          <p class="font-bold underline mb-1">Nota :</p>
          <p><strong>Daftar :</strong> Tandakan &radic; pada yang berkenaan.</p>
          <p class="mt-1"><strong>Lokasi :</strong> Nyatakan lokasi inventori mengikut rekod dan lokasi inventori semasa pemeriksaan.</p>
          <p class="mt-1"><strong>Keadaan Inventori :</strong> Nyatakan samada sedang digunakan atau tidak digunakan.</p>
          <p class="mt-1"><strong>Catatan :</strong> Penjelasan kepada penemuan pemeriksaan.</p>
        </div>
      </div>

    <?php elseif ($form === 'KEW.PA-20'): ?>
      <!-- ==================== KEW.PA-20 ==================== -->
      <div class="text-right font-bold text-sm mb-2">KEW.PA-20</div>
      <div class="text-center font-bold text-base mb-1 uppercase tracking-tight">
        LAPORAN TAHUNAN PELUPUSAN ASET ALIH KERAJAAN
      </div>
      <div class="text-center text-xs mb-4 font-bold">SUKU PERTAMA (JANUARI - MAC TAHUN 2010)</div>

      <div class="text-xs mb-4">
        <strong>BAHAGIAN/ JABATAN :</strong> .....................................................................................
      </div>

      <table class="w-full text-xs border-collapse border border-black mb-4">
        <thead>
          <tr class="bg-slate-50 text-center font-bold">
            <th rowspan="2" class="border border-black p-2 w-10">BIL</th>
            <th rowspan="2" class="border border-black p-2 w-56">KEMENTERIAN/ JABATAN</th>
            <th rowspan="2" class="border border-black p-2 w-36">JUMLAH NILAI PEROLEHAN ASAL (RM)</th>
            <th rowspan="2" class="border border-black p-2 w-36">HASIL PELUPUSAN (RM)</th>
            <th colspan="4" class="border border-black p-2">JUMLAH NILAI PEROLEHAN ASAL ASET SECARA (RM)</th>
          </tr>
          <tr class="bg-slate-50 text-center font-bold">
            <th class="border border-black p-1 w-24">JUALAN</th>
            <th class="border border-black p-1 w-24">PINDAHAN</th>
            <th class="border border-black p-1 w-24">MUSNAH</th>
            <th class="border border-black p-1 w-24">KAEDAH LAIN</th>
          </tr>
        </thead>
        <tbody>
          <?php for ($i = 1; $i <= 8; $i++): ?>
            <tr class="h-8">
              <td class="border border-black p-1 text-center"><?= $i ?></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
            </tr>
          <?php endfor; ?>
        </tbody>
      </table>

      <p class="text-[11px] italic mb-6">
        Nota : Laporan ini hendaklah dihantar ke Kementerian Pengajian Tinggi sebelum 15 bulan berikutnya.
      </p>

      <div class="flex justify-between items-start text-xs pt-2">
        <div class="space-y-2">
          <p>Tandatangan Ketua Jabatan : .......................................................</p>
          <p>Nama : .......................................................</p>
          <p>Jawatan : .......................................................</p>
          <p>Tarikh : .......................................................</p>
        </div>
        <div>
          <p>Cop Jabatan :</p>
          <div class="w-32 h-20 border border-dashed border-slate-400 mt-2 flex items-center justify-center text-slate-400 text-[10px]">
            Ruang Cop
          </div>
        </div>
      </div>

    <?php elseif ($form === 'KEW.PA-32'): ?>
      <!-- ==================== KEW.PA-32 ==================== -->
      <div class="text-right font-bold text-sm mb-2">KEW.PA-32</div>
      <div class="text-center font-bold text-base mb-1 uppercase tracking-tight">
        LAPORAN TINDAKAN SURCAJ/ TATATERTIB ASET ALIH KERAJAAN
      </div>
      <div class="text-center text-xs mb-4 font-bold">SUKU PERTAMA (JANUARI - MAC TAHUN 2010)</div>

      <div class="text-xs mb-4">
        <strong>BAHAGIAN/ JABATAN :</strong> .....................................................................................
      </div>

      <table class="w-full text-xs border-collapse border border-black mb-4">
        <thead>
          <tr class="bg-slate-50 text-center font-bold">
            <th rowspan="2" class="border border-black p-2 w-10">Bil</th>
            <th rowspan="2" class="border border-black p-2 w-32">Ruj. Kelulusan Perbendaharaan</th>
            <th rowspan="2" class="border border-black p-2 w-36">Kementerian/ Jabatan</th>
            <th rowspan="2" class="border border-black p-2 w-28">Jenis Aset</th>
            <th rowspan="2" class="border border-black p-2 w-28">Punca Kehilangan</th>
            <th rowspan="2" class="border border-black p-2 w-28">Nilai Perolehan Asal (RM)</th>
            <th rowspan="2" class="border border-black p-2 w-28">Nilai Semasa (RM)</th>
            <th colspan="2" class="border border-black p-2">Tindakan Surcaj</th>
            <th colspan="2" class="border border-black p-2">Tindakan Tatatertib</th>
          </tr>
          <tr class="bg-slate-50 text-center font-bold">
            <th class="border border-black p-1 w-24">Amaun Surcaj (RM)</th>
            <th class="border border-black p-1 w-24">Tarikh Dikenakan</th>
            <th class="border border-black p-1 w-24">Jenis Hukuman</th>
            <th class="border border-black p-1 w-24">Tarikh Dikenakan</th>
          </tr>
        </thead>
        <tbody>
          <?php for ($i = 1; $i <= 7; $i++): ?>
            <tr class="h-8">
              <td class="border border-black p-1 text-center"><?= $i ?></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
              <td class="border border-black p-1"></td>
            </tr>
          <?php endfor; ?>
          <tr class="font-bold bg-slate-50">
            <td colspan="5" class="border border-black p-2 text-center">JUMLAH</td>
            <td class="border border-black p-2"></td>
            <td class="border border-black p-2"></td>
            <td class="border border-black p-2"></td>
            <td class="border border-black p-2"></td>
            <td class="border border-black p-2"></td>
            <td class="border border-black p-2"></td>
          </tr>
        </tbody>
      </table>

      <div class="flex justify-between items-start text-xs pt-4">
        <div class="space-y-2">
          <p>Tandatangan Ketua Jabatan : .......................................................</p>
          <p>Nama : .......................................................</p>
          <p>Jawatan : .......................................................</p>
          <p>Tarikh : .......................................................</p>
        </div>
        <div>
          <p>Cop Jabatan :</p>
          <div class="w-32 h-20 border border-dashed border-slate-400 mt-2 flex items-center justify-center text-slate-400 text-[10px]">
            Ruang Cop
          </div>
        </div>
      </div>

    <?php endif; ?>

  </div>

</body>
</html>

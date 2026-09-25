<?php
/**
 * api_requests.php
 * Endpoint REST API Pengurusan Permohonan Borang KEW.PS-8
 * Pejabat KDYMM Tuanku Sultan Kedah
 */

require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: Carian permohonan atau senarai semua permohonan
if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');
    try {
        if ($search !== '') {
            $stmt = $pdo->prepare("SELECT * FROM `requests` WHERE `no_bpsi` LIKE :q OR `nama_pemohon` LIKE :q ORDER BY `created_at` DESC LIMIT 10");
            $stmt->execute([':q' => "%$search%"]);
        } else {
            $stmt = $pdo->query("SELECT * FROM `requests` ORDER BY `created_at` DESC LIMIT 50");
        }
        $requests = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $requests]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// POST: Hantar Permohonan Baru KEW.PS-8
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['namaPemohon']) || empty($input['items'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Sila lengkapkan nama pemohon dan senarai item.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Jana No. BPSI format: BPSI/TAHUN/NOMBOR
        $year = date('Y');
        $stmtSeq = $pdo->query("SELECT COUNT(*) AS total FROM `requests` WHERE YEAR(`tarikh_mohon`) = $year");
        $count = (int)$stmtSeq->fetch()['total'] + 1;
        $noBPSI = sprintf("BPSI/%s/%04d", $year, $count);
        $reqId = 'req-' . uniqid();

        // Masukkan rekod permohonan utama
        $sql = "INSERT INTO `requests` 
                (id, no_bpsi, nama_pemohon, jawatan, bahagian, tarikh_mohon, status, catatan_pemohon, nama_pelulus, jawatan_pelulus, created_at)
                VALUES 
                (:id, :bpsi, :nama, :jawatan, :bahagian, :tarikh, 'Menunggu Kelulusan', :catatan, 'PEGAWAI STOR PEJABAT KDYMM TUANKU SULTAN KEDAH', 'Penolong Pegawai Tadbir (Stor)', NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $reqId,
            ':bpsi' => $noBPSI,
            ':nama' => strtoupper($input['namaPemohon']),
            ':jawatan' => $input['jawatan'] ?? '',
            ':bahagian' => $input['bahagian'] ?? '',
            ':tarikh' => date('Y-m-d'),
            ':catatan' => $input['catatanPemohon'] ?? ''
        ]);

        // Masukkan item permohonan ke jadual `request_items`
        $sqlItem = "INSERT INTO `request_items` (request_id, item_id, kod, nama, unit, kuantiti_dimohon, kuantiti_diluluskan, baki_sedia_ada)
                    VALUES (:req_id, :item_id, :kod, :nama, :unit, :qty, :qty_lulus, :baki)";
        $stmtItem = $pdo->prepare($sqlItem);

        foreach ($input['items'] as $item) {
            $stmtItem->execute([
                ':req_id' => $reqId,
                ':item_id' => $item['itemId'] ?? '',
                ':kod' => $item['kod'] ?? '',
                ':nama' => $item['nama'] ?? '',
                ':unit' => $item['unit'] ?? 'UNIT',
                ':qty' => (int)($item['kuantitiDimohon'] ?? 1),
                ':qty_lulus' => (int)($item['kuantitiDimohon'] ?? 1),
                ':baki' => (int)($item['bakiSediaAda'] ?? 0)
            ]);
        }

        $pdo->commit();
        echo json_encode([
            'status' => 'success',
            'noBPSI' => $noBPSI,
            'id' => $reqId,
            'message' => 'Permohonan berjaya dihantar ke pangkalan data MySQL!'
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Ralat pangkalan data: ' . $e->getMessage()]);
    }
    exit;
}

// PUT: Kemaskini status permohonan (Kelulusan)
if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['id']) || empty($input['status'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID dan status diperlukan.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE `requests` SET `status` = :st, `tarikh_kelulusan` = CURDATE() WHERE `id` = :id");
        $stmt->execute([
            ':st' => $input['status'],
            ':id' => $input['id']
        ]);
        echo json_encode(['status' => 'success', 'message' => 'Status berjaya dikemaskini.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Kaedah HTTP tidak dibenarkan.']);
?>

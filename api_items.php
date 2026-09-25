<?php
/**
 * api_items.php
 * Endpoint JSON REST API untuk Senarai Barang Stok Pejabat
 * Jadual MySQL: `senarai data stok pejabat`
 */

require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM `senarai data stok pejabat` ORDER BY nama ASC");
        $rawItems = $stmt->fetchAll();

        $items = array_map(function($row) {
            return [
                'id' => (string)$row['id'],
                'kod' => (string)$row['kod'],
                'nama' => (string)$row['nama'],
                'kategori' => (string)($row['kategori'] ?? 'Alat Tulis'),
                'unit' => (string)($row['unit'] ?? 'UNIT'),
                'hargaSeunit' => (float)($row['harga_seunit'] ?? 0),
                'bakiStok' => (int)($row['baki_stok'] ?? 0),
                'parasMinimum' => (int)($row['paras_minimum'] ?? 5),
                'gambar' => !empty($row['gambar']) ? $row['gambar'] : 'package',
                'catatan' => (string)($row['catatan'] ?? '')
            ];
        }, $rawItems);

        echo json_encode([
            'status' => 'success',
            'table' => 'senarai data stok pejabat',
            'total' => count($items),
            'data' => $items
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Ralat membaca jadual `senarai data stok pejabat`: ' . $e->getMessage()
        ]);
    }
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['nama']) || empty($input['kod'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Kod dan nama item diperlukan']);
        exit;
    }

    try {
        $sql = "INSERT INTO `senarai data stok pejabat` (id, kod, nama, kategori, unit, harga_seunit, baki_stok, paras_minimum, gambar, catatan) 
                VALUES (:id, :kod, :nama, :kategori, :unit, :harga, :baki, :paras, :gambar, :catatan)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $input['id'] ?? ('item-' . time()),
            ':kod' => strtoupper($input['kod']),
            ':nama' => strtoupper($input['nama']),
            ':kategori' => $input['kategori'] ?? 'Alat Tulis',
            ':unit' => strtoupper($input['unit'] ?? 'UNIT'),
            ':harga' => (float)($input['hargaSeunit'] ?? 0),
            ':baki' => (int)($input['bakiStok'] ?? 0),
            ':paras' => (int)($input['parasMinimum'] ?? 5),
            ':gambar' => $input['gambar'] ?? 'package',
            ':catatan' => $input['catatan'] ?? ''
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Item berjaya disimpan ke `senarai data stok pejabat`!']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Kaedah tidak disokong']);
?>

<?php
include 'config/koneksi.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// ==========================
// HAPUS TRANSAKSI TAGIHAN
// ==========================
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];

    $stmt = $conn->prepare("SELECT p.*, b.tagihan FROM tagihan p 
                            JOIN pelanggan b ON p.id_pelanggan = b.id 
                            WHERE p.id = ?");
    $stmt->bind_param("i", $id_hapus);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $data = $result->fetch_assoc()) {
        $id_pelanggan = $data['id_pelanggan'];
        $jumlah = $data['jumlah'];
        $transaksi = $data['transaksi'];
        $tagihan_sekarang = $data['tagihan'];

        $tagihan_baru = $transaksi === 'debit'
            ? $tagihan_sekarang - $jumlah
            : $tagihan_sekarang + $jumlah;

        mysqli_begin_transaction($conn);
        try {
            $stmt1 = $conn->prepare("UPDATE pelanggan SET tagihan = ? WHERE id = ?");
            $stmt1->bind_param("ii", $tagihan_baru, $id_pelanggan);
            $stmt1->execute();
            $stmt1->close();

            $stmt2 = $conn->prepare("DELETE FROM tagihan WHERE id = ?");
            $stmt2->bind_param("i", $id_hapus);
            $stmt2->execute();
            $stmt2->close();

            mysqli_commit($conn);
            $_SESSION['success'] = "Transaksi berhasil dihapus.";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $_SESSION['error'] = "Gagal menghapus transaksi: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Transaksi tidak ditemukan.";
    }

    $stmt->close();
    header('Location: index.php?page=transaksi');
    exit;
}

// ==========================
// AMBIL DATA TAGIHAN
// ==========================
// Pagination setup
$limit = 10; // jumlah data per halaman
$page = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total data
$total_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM tagihan");
$total_row = mysqli_fetch_assoc($total_result);
$total_data = $total_row['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data sesuai pagination
$query = "SELECT t.id, t.jumlah, t.keterangan, t.tgl_transaksi, t.transaksi, 
                 p.nama_pelanggan, p.kode_pelanggan 
          FROM tagihan t 
          JOIN pelanggan p ON t.id_pelanggan = p.id 
          ORDER BY t.tgl_transaksi DESC
          LIMIT $limit OFFSET $offset";
$tagihan = mysqli_query($conn, $query);
?>
<div class="py-4">
    <div class="card p-4">
        <div class="card-header">
            <h2 class="text-center">Riwayat Transaksi</h2>
        </div>
        <div class="card-body p-0">
            <form id="filterForm" class="row g-3 mb-4">
                <div class="col-md-6">
                    <input type="text" id="q" class="form-control" placeholder="Cari nama / kode pelanggan">
                </div>
                <div class="col-md-3">
                    <input type="date" id="tgl_mulai" class="form-control">
                </div>
                <div class="col-md-3">
                    <input type="date" id="tgl_selesai" class="form-control">
                </div>
            </form>


            <div id="tabelHasil">
                <div class="text-center p-4">Loading data transaksi...</div>
            </div>
            <a href="excel_transaksi.php?q=<?= $_GET['q'] ?? '' ?>&tgl_mulai=<?= $_GET['tgl_mulai'] ?? '' ?>&tgl_selesai=<?= $_GET['tgl_selesai'] ?? '' ?>" class="float-end btn btn-success mb-3" target="_blank">Export ke Excel</a>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const q = document.getElementById('q');
        const tglMulai = document.getElementById('tgl_mulai');
        const tglSelesai = document.getElementById('tgl_selesai');
        let currentPage = 1;

        function fetchData(page = 1) {
            currentPage = page;
            const params = new URLSearchParams({
                q: q.value,
                tgl_mulai: tglMulai.value,
                tgl_selesai: tglSelesai.value,
                hal: page
            });

            fetch('ajax_search_transaksi.php?' + params.toString())
                .then(res => res.text())
                .then(html => {
                    document.getElementById('tabelHasil').innerHTML = html;
                });
            const newUrl = new URL(window.location.href);
            newUrl.searchParams.set('hal', page);
            window.history.replaceState({}, '', newUrl);
        }

        // Search listener
        [q, tglMulai, tglSelesai].forEach(input => {
            input.addEventListener('input', () => fetchData(1)); // balik ke page 1 saat filter baru
        });

        // Pagination listener (delegasi)
        document.addEventListener('click', function(e) {
            const link = e.target.closest('.pagination-link');
            if (link) {
                e.preventDefault();
                const page = parseInt(link.dataset.page);
                if (!isNaN(page)) {
                    fetchData(page);
                }
            }
        });

        const urlParams = new URLSearchParams(window.location.search);
        const startPage = parseInt(urlParams.get('hal')) || 1;

        // initial load
        fetchData(startPage);
    });
</script>
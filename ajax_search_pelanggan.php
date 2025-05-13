<?php
include 'config/koneksi.php';

$keyword = $_GET['keyword'] ?? '';
$page = isset($_GET['hal']) ? max(1, intval($_GET['hal'])) : 1;
$limit = 5;
$start = ($page - 1) * $limit;

$where = '';
if (!empty($keyword)) {
    $escaped = $conn->real_escape_string($keyword);
    $where = "WHERE nama_pelanggan LIKE '%$escaped%' OR kode_pelanggan LIKE '%$escaped%'";
}

$count_q = $conn->query("SELECT COUNT(*) as total FROM pelanggan $where");
$total_data = $count_q->fetch_assoc()['total'] ?? 0;
$total_page = ceil($total_data / $limit);

$query = "SELECT * FROM pelanggan $where ORDER BY nama_pelanggan ASC LIMIT $start, $limit";
$pelanggan = $conn->query($query);
?>

<div class="table-responsive">
    <table class="table table-bordered table-hover table-striped text-nowrap align-middle">
        <thead class="table-dark text-center">
            <tr>
                <th>No</th>
                <th>Kode Pelanggan</th>
                <th>Nama Pelanggan</th>
                <th>Alamat</th>
                <th>No HP</th>
                <th>Tagihan</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($pelanggan->num_rows): ?>
                <?php $no = $start + 1; ?>
                <?php while ($p = $pelanggan->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($p['kode_pelanggan']) ?></td>
                        <td>
                            <a href="index.php?page=detail_pelanggan&id=<?= $p['id'] ?>">
                                <?= htmlspecialchars($p['nama_pelanggan']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($p['alamat']) ?></td>
                        <td><?= htmlspecialchars($p['no_hp']) ?></td>
                        <td class="text-end">
                            <?= isset($p['tagihan']) ? number_format($p['tagihan'], 0, ',', '.') : '-' ?>
                        </td>
                        <td>
                            <span class="badge bg-info text-dark">Pelanggan</span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">Data tidak ditemukan</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
    <div class="text-muted">
        <i class="fas fa-database me-2"></i>Total Data: <?= $total_data ?>
    </div>
    <div>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a href="?page=master_pelanggan&hal=<?= $page - 1 ?>" class="page-link"><i class="fas fa-chevron-left"></i></a></li>
                <?php endif; ?>
                <li class="page-item disabled"><span class="page-link">Halaman <?= $page ?> dari <?= $total_page ?></span></li>
                <?php if ($page < $total_page): ?>
                    <li class="page-item"><a href="?page=master_pelanggan&hal=<?= $page + 1 ?>" class="page-link"><i class="fas fa-chevron-right"></i></a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>
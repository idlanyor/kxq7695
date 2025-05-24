<?php
// Koneksi ke database
include 'config/koneksi.php';

// Tentukan jumlah data per halaman
$per_page = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $per_page;

$today = date('Y-m-d');
$latest_tagihan_query = "
    SELECT MAX(t.id) as latest_id
    FROM tagihan t
    JOIN pelanggan p ON t.id_pelanggan = p.id
    WHERE t.tgl_jt < '$today' AND p.tagihan > 0
    GROUP BY p.kode_pelanggan
";
$query = "
    SELECT 
        t.id, 
        p.kode_pelanggan, 
        p.nama_pelanggan, 
        p.alamat, 
        p.no_hp,
        p.tagihan as saldo_tagihan,
        t.tgl_jt
    FROM tagihan t
    JOIN pelanggan p ON t.id_pelanggan = p.id
    JOIN ($latest_tagihan_query) latest ON t.id = latest.latest_id 
    WHERE t.tgl_jt < '$today' AND p.tagihan > 0
    ORDER BY p.kode_pelanggan, t.tgl_jt DESC
    LIMIT $offset, $per_page
";

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

$total_query = "
    SELECT COUNT(*) as total
    FROM ($latest_tagihan_query) latest
";
$total_result = mysqli_query($conn, $total_query);
if (!$total_result) {
    die("Query total gagal: " . mysqli_error($conn));
}
$total_row = mysqli_fetch_assoc($total_result);
$total_data = $total_row['total'];
$total_pages = ceil($total_data / $per_page);
?>

<div class="card shadow-sm border mb-4">
    <div class="card-body">
        <h2 class="text-center mb-4">Daftar Lewat Jatuh Tempo</h2>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle text-nowrap">
                <thead class="table-dark text-center">
                    <tr>
                        <th>No</th>
                        <th>Kode Pelanggan</th>
                        <th>Nama Pelanggan</th>
                        <th>Saldo Tagihan</th>
                        <th>Tanggal Jatuh Tempo</th>
                        <th>Alamat</th>
                        <th>No. HP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $no = $offset + 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['kode_pelanggan']) ?></td>
                                <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                                <td class="text-end"><?= number_format($row['saldo_tagihan'], 0, ',', '.') ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_jt'])) ?></td>
                                <td><?= htmlspecialchars($row['alamat']) ?></td>
                                <td><?= htmlspecialchars($row['no_hp']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada pelanggan yang lewat jatuh tempo.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-3">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>">Sebelumnya</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>">Berikutnya</a>
                </li>
            </ul>
        </nav>
    </div>
</div>


<?php mysqli_close($conn); ?>
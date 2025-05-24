<?php
include 'config/koneksi.php';

$q       = $_GET['q'] ?? '';
$tgl1    = $_GET['tgl_mulai'] ?? '';
$tgl2    = $_GET['tgl_selesai'] ?? '';
$page    = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
$limit   = 10;
$offset  = ($page - 1) * $limit;

$filters = [];
function formatTanggalIndonesia($tanggal)
{
    // Pastikan hanya mengambil bagian tanggal saja (jika ada jam)
    $tanggal = substr($tanggal, 0, 10);

    $bulan = array(
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );

    $pecahkan = explode('-', $tanggal);
    if (count($pecahkan) == 3) {
        return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
    }
    return $tanggal;
}

if (!empty($q)) {
    $safe_q = $conn->real_escape_string($q);
    $filters[] = "(p.nama_pelanggan LIKE '%$safe_q%' OR p.kode_pelanggan LIKE '%$safe_q%')";
}
if (!empty($tgl1)) {
    $safe_tgl1 = $conn->real_escape_string($tgl1);
    $filters[] = "t.tgl_transaksi >= '$safe_tgl1'";
}
if (!empty($tgl2)) {
    $safe_tgl2 = $conn->real_escape_string($tgl2);
    $filters[] = "t.tgl_transaksi <= '$safe_tgl2'";
}

$where = count($filters) ? "WHERE " . implode(" AND ", $filters) : "";

// Hitung total data setelah filter
$total_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM tagihan t JOIN pelanggan p ON t.id_pelanggan = p.id $where");
$total_row = mysqli_fetch_assoc($total_result);
$total_data = $total_row['total'];
$total_pages = ceil($total_data / $limit);
$page = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
// Query data sesuai filter dan pagination
$query = "SELECT t.id, t.jumlah, t.keterangan, t.tgl_transaksi, t.transaksi, 
                 p.nama_pelanggan, p.kode_pelanggan 
          FROM tagihan t 
          JOIN pelanggan p ON t.id_pelanggan = p.id 
          $where
          ORDER BY t.tgl_transaksi DESC
          LIMIT $limit OFFSET $offset";
$tagihan = mysqli_query($conn, $query);
?>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>No</th>
            <th>Kode Pelanggan</th>
            <th>Nama Pelanggan</th>
            <th>Tgl Transaksi</th>
            <th>Transaksi (D/K)</th>
            <th class="text-end">Jumlah</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = $offset + 1;
        $total_d = 0;
        $total_k = 0;
        $total_all_query = "SELECT 
                        SUM(CASE WHEN t.transaksi = 'debit' THEN t.jumlah ELSE 0 END) as total_debit_all,
                        SUM(CASE WHEN t.transaksi = 'kredit' THEN t.jumlah ELSE 0 END) as total_kredit_all
                    FROM tagihan t
                    JOIN pelanggan p ON t.id_pelanggan = p.id
                    $where";
        $total_all_result = mysqli_query($conn, $total_all_query);
        $total_all = mysqli_fetch_assoc($total_all_result);

        $total_debit_all = $total_all['total_debit_all'];
        $total_kredit_all = $total_all['total_kredit_all'];
        while ($row = mysqli_fetch_assoc($tagihan)) {
            if ($row['transaksi'] == 'debit') $total_d += $row['jumlah'];
            else if ($row['transaksi'] == 'kredit') $total_k += $row['jumlah'];
        ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $row['kode_pelanggan'] ?></td>
                <td><?= $row['nama_pelanggan'] ?></td>
                <td><?= formatTanggalIndonesia($row['tgl_transaksi']) ?></td>
                <td><?= ucfirst($row['transaksi']) ?></td>
                <td class="text-end"><?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                <td><?= $row['keterangan'] ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr class="table-success">
            <th colspan="6">Total Debit (Halaman ini)</th>
            <th class="text-end"><?= number_format($total_d, 0, ',', '.') ?></th>
        </tr>
        <tr class="table-danger">
            <th colspan="6">Total Kredit (Halaman ini)</th>
            <th class="text-end"><?= number_format($total_k, 0, ',', '.') ?></th>
        </tr>
        <tr class="table-primary">
            <th colspan="6">Total Tagihan Halaman Ini (D - K)</th>
            <th class="text-end"><?= number_format($total_d - $total_k, 0, ',', '.') ?></th>
        </tr>
        <tr class="table-success">
            <th colspan="6">Total Debit (Keseluruhan)</th>
            <th class="text-end"><?= number_format($total_debit_all, 0, ',', '.') ?></th>
        </tr>
        <tr class="table-danger">
            <th colspan="6">Total Kredit (Keseluruhan)</th>
            <th class="text-end"><?= number_format($total_kredit_all, 0, ',', '.') ?></th>
        </tr>
        <tr class="table-primary">
            <th colspan="6">Total Tagihan Keseluruhan (D - K)</th>
            <th class="text-end"><?= number_format($total_debit_all - $total_kredit_all, 0, ',', '.') ?></th>
        </tr>
    </tfoot>

</table>

<!-- Pagination -->
<nav aria-label="Page navigation example">
    <ul class="pagination justify-content-center mt-3">
        <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link pagination-link" href="#" data-page="<?= $page - 1 ?>">«</a>
            </li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                <a class="page-link pagination-link" href="#" data-page="<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link pagination-link" href="#" data-page="<?= $page + 1 ?>">»</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
<?php
include 'config/koneksi.php';

$id = $_GET['id'] ?? 0;

// Cek data pelanggan
$pelanggan = $conn->query("SELECT * FROM pelanggan WHERE id = $id");
$data = $pelanggan->fetch_assoc();

// Ambil riwayat transaksi (asumsikan ada tabel `transaksi`)
$transaksi = $conn->query("SELECT * FROM tagihan WHERE id_pelanggan = $id ORDER BY tgl_transaksi DESC");
?>


<div class="container py-4">
    <h3 class="mb-4">
        <i class="fas fa-user-tag me-2"></i>Detail Pelanggan
    </h3>

    <div class="card mb-4">
        <div class="card-body p-4">
            <table class="table table-bordered mb-0">
                <tr>
                    <th style="width: 180px;">Kode</th>
                    <td><?= htmlspecialchars($data['kode_pelanggan']) ?></td>
                </tr>
                <tr>
                    <th>Nama</th>
                    <td><?= htmlspecialchars($data['nama_pelanggan']) ?></td>
                </tr>
                <tr>
                    <th>Alamat</th>
                    <td><?= htmlspecialchars($data['alamat']) ?></td>
                </tr>
                <tr>
                    <th>No HP</th>
                    <td><?= htmlspecialchars($data['no_hp']) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <h4 class="mb-3">
        <i class="fas fa-history me-2"></i>Riwayat Transaksi
    </h4>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Transaksi</th>
                        <th>Jumlah</th>
                        <th>Jatuh Tempo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($transaksi->num_rows): ?>
                        <?php while ($row = $transaksi->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['tgl_transaksi']) ?></td>
                                <td><?= htmlspecialchars($row['transaksi']) ?></td>
                                <td><?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                                <td class="text-center"><?= date('d-m-Y', strtotime($row['tgl_jt'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                <i class="fas fa-receipt fa-lg me-2"></i>Belum ada transaksi.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <a href="index.php?page=master_pelanggan" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>
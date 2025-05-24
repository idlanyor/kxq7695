<?php
include 'config/koneksi.php';

// Handle export request
if (isset($_GET['export'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="dashboard_export_' . date('Y-m-d') . '.xls"');

    $export_pelanggan = $conn->query("SELECT * FROM pelanggan");
    $export_transaksi = $conn->query("
        SELECT p.*, b.nama_pelanggan, b.kode_pelanggan 
        FROM tagihan p 
        JOIN pelanggan b ON p.id_pelanggan = b.id 
        ORDER BY p.tgl_transaksi DESC
    ");

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style>
            table { border-collapse: collapse; width: 100%; }
            th { background-color: #4e73df; color: white; font-weight: bold; text-align: center; padding: 8px; }
            td { border: 1px solid #ddd; padding: 8px; text-align: center; }
            .header { background-color: #4e73df; color: white; font-weight: bold; text-align: center; padding: 8px; }
        </style>
    </head>
    <body>';

    // Data Pelanggan
    echo '<table>
        <tr><th colspan="5" class="header">DATA PELANGGAN</th></tr>
        <tr>
            <th>Kode Pelanggan</th>
            <th>Nama Pelanggan</th>
            <th>Alamat</th>
            <th>No HP</th>
            <th>Tagihan</th>
            <th>Keterangan</th>
        </tr>';

    while ($row = $export_pelanggan->fetch_assoc()) {
        echo '<tr>
            <td>' . htmlspecialchars($row['kode_pelanggan']) . '</td>
            <td>' . htmlspecialchars($row['nama_pelanggan']) . '</td>
            <td>' . htmlspecialchars($row['alamat']) . '</td>
            <td>' . htmlspecialchars($row['no_hp']) . '</td>
            <td>' . $row['tagihan'] . '</td>
            <td>' . htmlspecialchars($row['keterangan']) . '</td>
        </tr>';
    }

    echo '</table><br><br>';

    // Data Transaksi
    echo '<table>
        <tr><th colspan="6" class="header">DATA TRANSAKSI</th></tr>
        <tr>
            <th>Tanggal</th>
            <th>Kode Pelanggan</th>
            <th>Nama Pelanggan</th>
            <th>Jumlah</th>
            <th>Transaksi</th>
            <th>Keterangan</th>
            <th>Jatuh Tempo</th>
        </tr>';

    while ($row = $export_transaksi->fetch_assoc()) {
        echo '<tr>
            <td>' . date('d/m/Y', strtotime($row['tgl_transaksi'])) . '</td>
            <td>' . htmlspecialchars($row['kode_pelanggan']) . '</td>
            <td>' . htmlspecialchars($row['nama_pelanggan']) . '</td>
            <td>' . $row['jumlah'] . '</td>
            <td>' . ucfirst($row['transaksi']) . '</td>
            <td>' . htmlspecialchars($row['keterangan']) . '</td>
            <td>' . date('d/m/Y', strtotime($row['tgl_jt'])) . '</td>
        </tr>';
    }

    echo '</table></body></html>';
    exit;
}

$today = date('Y-m-d');
$end_date = date('Y-m-d', strtotime('+15 days')); // 15 hari ke depan

$total_all_query = "SELECT 
    SUM(CASE WHEN t.transaksi = 'debit' THEN t.jumlah ELSE 0 END) as total_debit_all,
    SUM(CASE WHEN t.transaksi = 'kredit' THEN t.jumlah ELSE 0 END) as total_kredit_all
FROM tagihan t
JOIN pelanggan p ON t.id_pelanggan = p.id
";
$total_pelanggan = $conn->query("SELECT COUNT(*) as total FROM pelanggan")->fetch_assoc()['total'];
$total_all_result = mysqli_query($conn, $total_all_query);
$total_all = mysqli_fetch_assoc($total_all_result);

$selisih_keseluruhan = $total_all['total_debit_all'] - $total_all['total_kredit_all'];
$jatuh_tempo = $conn->query("SELECT COUNT(*) as total FROM tagihan WHERE tgl_jt <= '$today' AND jumlah > 0")->fetch_assoc()['total'];

// Query untuk pelanggan yang akan jatuh tempo dalam 15 hari ke depan
$akan_jt = $conn->query("SELECT COUNT(*) as total FROM tagihan WHERE tgl_jt >= '$today' AND tgl_jt <= '$end_date' AND jumlah > 0")->fetch_assoc()['total'];

$recent_transaksi = $conn->query("
    SELECT p.*, b.nama_pelanggan, b.kode_pelanggan 
    FROM tagihan p 
    JOIN pelanggan b ON p.id_pelanggan = b.id 
    ORDER BY p.tgl_transaksi DESC 
    LIMIT 5
");

// Query untuk pelanggan yang akan jatuh tempo dalam 15 hari ke depan (untuk ditampilkan di tabel)
$akan_jt_list = $conn->query("
    SELECT t.id, p.kode_pelanggan, p.nama_pelanggan, t.jumlah, t.tgl_jt, p.alamat, p.no_hp
    FROM tagihan t
    JOIN pelanggan p ON t.id_pelanggan = p.id
     WHERE t.tgl_jt >= '$today' AND t.tgl_jt <= '$end_date' AND t.jumlah > 0
    ORDER BY t.tgl_jt ASC
    LIMIT 5
");

$transaksi_status = $conn->query("
   SELECT 
        COUNT(CASE WHEN tgl_jt <= '$today' AND jumlah > 0 THEN 1 END) as perlu_remind,
        COUNT(CASE WHEN (tgl_jt > '$today' OR jumlah <= 0) THEN 1 END) as aman
    FROM tagihan
")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Piutang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --secondary-color: #858796;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--light-color);
        }

        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            min-height: 100vh;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 1rem;
            font-weight: 600;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }

        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }

        .stat-card {
            border-left: 0.25rem solid;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.primary {
            border-left-color: var(--primary-color);
        }

        .stat-card.success {
            border-left-color: var(--success-color);
        }

        .stat-card.info {
            border-left-color: var(--info-color);
        }

        .stat-card.warning {
            border-left-color: var(--warning-color);
        }

        .stat-card.orange {
            border-left-color: var(--warning-color);
        }

        .stat-card .stat-icon {
            color: rgba(0, 0, 0, 0.15);
            font-size: 2rem;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--dark-color);
        }

        .badge {
            padding: 0.5em 0.75em;
            font-weight: 600;
        }

        .btn-export {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .btn-export:hover {
            background-color: #2e59d9;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">

            <!-- Main Content -->
            <div class="col-md-12 col-lg-12 ms-auto px-4 py-3">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>

                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card primary h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Pelanggan
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($total_pelanggan) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-boxes stat-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card success h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Tagihan
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            Rp <?= number_format($selisih_keseluruhan, 0, ',', '.') ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exchange-alt stat-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card warning h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Perlu Reminder
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($jatuh_tempo) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-triangle stat-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card orange h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Jatuh Tempo 15 Hari
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($akan_jt) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock stat-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Tables -->
                <div class="row">
                    <!-- Recent Transactions -->
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Transaksi Terakhir</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Nama Pelanggan</th>
                                                <th>Kode Pelanggan</th>
                                                <th>Jumlah</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($t = $recent_transaksi->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y', strtotime($t['tgl_transaksi'])) ?></td>
                                                    <td><?= htmlspecialchars($t['nama_pelanggan']) ?></td>
                                                    <td><?= htmlspecialchars($t['kode_pelanggan']) ?></td>
                                                    <td><?= $t['jumlah'] ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $t['transaksi'] == 'debit' ? 'success' : 'danger' ?>">
                                                            <?= ucfirst($t['transaksi']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Status -->
                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-header">Status Tagihan</div>
                            <div class="card-body">
                                <canvas id="tagihanChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pelanggan Jatuh Tempo 15 Hari Ke Depan -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-warning">Pelanggan Jatuh Tempo 15 Hari Ke Depan</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Kode Pelanggan</th>
                                                <th>Nama Pelanggan</th>
                                                <th>Jumlah Tagihan</th>
                                                <th>Tanggal Jatuh Tempo</th>
                                                <th>Alamat</th>
                                                <th>No. HP</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (mysqli_num_rows($akan_jt_list) > 0): ?>
                                                <?php while ($row = $akan_jt_list->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($row['kode_pelanggan']) ?></td>
                                                        <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                                                        <td class="text-end"><?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                                                        <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_jt'])) ?></td>
                                                        <td><?= htmlspecialchars($row['alamat']) ?></td>
                                                        <td><?= htmlspecialchars($row['no_hp']) ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">Tidak ada pelanggan yang jatuh tempo dalam 15 hari ke depan.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="index.php?page=akan_jt" class="btn btn-warning">Lihat Semua</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('tagihanChart').getContext('2d');
            const tagihanChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Perlu Reminder', 'Aman'],
                    datasets: [{
                        label: 'Status Tagihan',
                        data: [<?= $transaksi_status['perlu_remind'] ?>, <?= $transaksi_status['aman'] ?>],
                        backgroundColor: [
                            '#f6c23e', // kuning buat "Perlu Reminder"
                            '#1cc88a' // hijau buat "Aman"
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        </script>
    </div>
</body>

</html>
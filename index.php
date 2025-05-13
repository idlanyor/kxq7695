<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get settings from database
include 'config/koneksi.php';
$settings_query = "SELECT * FROM settings WHERE id = 1";
$settings_result = mysqli_query($conn, $settings_query);
$settings = mysqli_fetch_assoc($settings_result);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?= $settings['nama_toko'] ?? 'TOKO NOVI' ?></title>

    <!-- Custom fonts for this template-->
    <!-- <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- <link href="css/custom.css" rel="stylesheet"> -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-store"></i>
                </div>
                <div class="sidebar-brand-text mx-3"><?= $settings['nama_toko'] ?? 'Toko Novi' ?></div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item <?= !isset($_GET['page']) ? 'active' : '' ?>">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Nav Item - Master Barang -->
            <li class="nav-item <?= ($_GET['page'] ?? '') == 'master_pelanggan' ? 'active' : '' ?>">
                <a class="nav-link" href="index.php?page=master_pelanggan">
                    <i class="fas fa-fw fa-boxes-stacked"></i>
                    <span>Master Pelanggan</span>
                </a>
            </li>

            <!-- Nav Item - Form Barang -->
            <li class="nav-item <?= ($_GET['page'] ?? '') == 'form_pelanggan' ? 'active' : '' ?>">
                <a class="nav-link" href="index.php?page=form_pelanggan">
                    <i class="fas fa-fw fa-align-left"></i>
                    <span>Form Pelanggan</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Nav Item - Transaksi -->
            <li class="nav-item <?= ($_GET['page'] ?? '') == 'transaksi' ? 'active' : '' ?>">
                <a class="nav-link" href="index.php?page=transaksi">
                    <i class="fas fa-fw fa-money-bill-transfer"></i>
                    <span>Transaksi</span>
                </a>
            </li>
            <!-- Nav Item - Riwayat Transaksi -->
            <li class="nav-item <?= ($_GET['page'] ?? '') == 'riwayat_transaksi' ? 'active' : '' ?>">
                <a class="nav-link" href="index.php?page=riwayat_transaksi">
                    <i class="fas fa-fw fa-clock-rotate-left"></i>
                    <span>Riwayat Transaksi</span>
                </a>
            </li>

            <!-- Nav Item - Stok Limit -->
            <li class="nav-item <?= ($_GET['page'] ?? '') == 'jatuh_tempo' ? 'active' : '' ?>">
                <a class="nav-link" href="index.php?page=jatuh_tempo">
                    <i class="fas fa-fw fa-cart-flatbed"></i>
                    <span>Jatuh Tempo</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Nav Item - Logout -->
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-fw fa-sign-out"></i>
                    <span>Log Out</span>
                </a>
            </li>

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
               
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?php
                    $page = $_GET['page'] ?? 'dashboard';

                    switch ($page) {
                        case 'dashboard':
                            include 'dashboard.php';
                            break;
                        case 'master_pelanggan':
                            include 'master_pelanggan.php';
                            break;
                        case 'form_pelanggan':
                            include 'form_pelanggan.php';
                            break;
                        case 'transaksi':
                            include 'transaksi.php';
                            break;
                        case 'riwayat_transaksi':
                            include 'riwayat_transaksi.php';
                            break;
                        case 'detail_pelanggan':
                            include 'detail_pelanggan.php';
                            break;
                        case 'jatuh_tempo':
                            include 'jatuh_tempo.php';
                            break;
                        default:
                            echo "<h4>Halaman tidak ditemukan!</h4>";
                            break;
                    }
                    ?>
                </div>
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; <?= $settings['nama_toko'] ?? 'TOKO NOVI' ?> 2025</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js" integrity="sha512-L0Shl7nXXzIlBSUUPpxrokqq4ojqgZFQczTYlGjzONGTDAcLremjwaWv5A+EDLnxhQzY5xUZPWLOLqYRkY0Cbw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"
        integrity="sha512-0QbL0ph8Tc8g5bLhfVzSqxe9GERORsKhIn1IrpxDAgUsbBGz/V7iSav2zzW325XGd1OMLdL4UiqRJj702IeqnQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js" integrity="sha512-RtZU3AyMVArmHLiW0suEZ9McadTdegwbgtiQl5Qqo9kunkVg1ofwueXD8/8wv3Af8jkME3DDe3yLfR8HSJfT2g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js" integrity="sha512-wV7Yj1alIZDqZFCUQJy85VN+qvEIly93fIQAN7iqDFCPEucLCeNFz4r35FCo9s6WrpdDQPi80xbljXB8Bjtvcg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="./js/sb-admin-2.min.js"></script>
</body>

</html>
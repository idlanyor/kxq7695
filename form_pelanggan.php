<?php
include 'config/koneksi.php';

$notif = '';
$notif_class = '';

// Simpan Data
if (isset($_POST['save'])) {
    $kode_pelanggan = $_POST['kode_pelanggan'];
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];
    $keterangan = $_POST['keterangan'];

    $sql = "INSERT INTO pelanggan (kode_pelanggan, nama_pelanggan, alamat, no_hp,  keterangan)
            VALUES ('$kode_pelanggan', '$nama_pelanggan', '$alamat', '$no_hp',  '$keterangan')";

    if ($conn->query($sql) === TRUE) {
        $notif = "Data berhasil disimpan.";
        $notif_class = "success";
    } else {
        $notif = "Gagal menyimpan data: " . $conn->error;
        $notif_class = "error";
    }
    echo "<script>window.location.href='index.php?page=form_pelanggan';</script>";
    exit;
}

// Update Data
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $kode_pelanggan = $_POST['kode_pelanggan'];
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];
    $keterangan = $_POST['keterangan'];

    $sql = "UPDATE pelanggan SET 
                kode_pelanggan='$kode_pelanggan', 
                nama_pelanggan='$nama_pelanggan', 
                alamat='$alamat', 
                no_hp='$no_hp', 
                keterangan='$keterangan'
            WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: index.php?page=form_pelanggan&sukses=simpan");
        exit;
    } else {
        header("Location: index.php?page=form_pelanggan&error=" . urlencode("Gagal menyimpan data: " . $conn->error));
        exit;
    }
}

// Ambil data untuk Edit
$row = [];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM pelanggan WHERE id=$id");
    $row = $result->fetch_assoc();
}

// Hapus Data
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($conn->query("DELETE FROM pelanggan WHERE id=$id") === TRUE) {
        $notif = "Data berhasil dihapus.";
        $notif_class = "success";
    } else {
        $notif = "Gagal menghapus data: " . $conn->error;
        $notif_class = "error";
    }
}

// Pagination
$limit = 10;
$page = isset($_GET['halaman']) ? (int) $_GET['halaman'] : 1;
$offset = ($page - 1) * $limit;

$sql_count = "SELECT COUNT(*) as total FROM pelanggan";
$result_count = $conn->query($sql_count);
$total_data = $result_count->fetch_assoc()['total'];
$total_halaman = ceil($total_data / $limit);

$sql_data = "SELECT * FROM pelanggan ORDER BY kode_pelanggan ASC LIMIT $limit OFFSET $offset";
$data_pelanggan = $conn->query($sql_data);
?>

<style>
    .form-control-sm-custom {
        max-width: 400px; /* Lebar input */
        padding: 4px 8px; /* Padding kecil */
        font-size: 14px;  /* Ukuran font kecil */
    }

    label.form-label {
        font-size: 14px;
    }

    button.btn {
        font-size: 14px;
        padding: 6px 12px;
    }
</style>

<div>
    <div class="card shadow-sm border rounded-3 p-4 mb-5 mx-auto" style="max-width: 500px;">
        <h2 class="mb-4 fs-5">FORM PELANGGAN</h2>
        <!-- Notifikasi -->
        <?php if (!empty($notif)): ?>
            <div class="alert <?= $notif_class ?> alert-dismissible fade show" role="alert">
                <?= $notif ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="form_pelanggan.php" method="POST">
            <input type="hidden" name="id" value="<?= $row['id'] ?? '' ?>">

            <div class="mb-2">
                <label for="kode_pelanggan" class="form-label">Kode Pelanggan:</label>
                <input type="text" class="form-control form-control-sm-custom" name="kode_pelanggan" required value="<?= $row['kode_pelanggan'] ?? '' ?>">
            </div>

            <div class="mb-2">
                <label for="nama_pelanggan" class="form-label">Nama Pelanggan:</label>
                <input type="text" class="form-control form-control-sm-custom" name="nama_pelanggan" required value="<?= $row['nama_pelanggan'] ?? '' ?>">
            </div>

            <div class="mb-2">
                <label for="alamat" class="form-label">Alamat:</label>
                <input type="text" class="form-control form-control-sm-custom" name="alamat" required value="<?= $row['alamat'] ?? '' ?>">
            </div>

            <div class="mb-2">
                <label for="no_hp" class="form-label">No. HP:</label>
                <input type="text" class="form-control form-control-sm-custom" name="no_hp" required value="<?= $row['no_hp'] ?? '' ?>">
            </div>

            <div class="mb-2">
                <label for="keterangan" class="form-label">Keterangan:</label>
                <input type="text" class="form-control form-control-sm-custom" name="keterangan" value="<?= $row['keterangan'] ?? '' ?>">
            </div>

            <?php if (isset($row['id'])): ?>
                <button type="submit" name="update" class="btn btn-warning btn-sm">Update</button>
            <?php else: ?>
                <button type="submit" name="save" class="btn btn-primary btn-sm">Simpan</button>
            <?php endif; ?>
        </form>
    </div>    
    <div class="card shadow-sm border rounded-3 p-4">
        <h2 class="mb-4">DAFTAR PELANGGAN</h2>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Kode Pelanggan</th>
                        <th>Nama Pelanggan</th>
                        <th>Alamat</th>
                        <th>No HP</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $data_pelanggan->fetch_assoc()): ?>
                        <tr>
                            <td><?= $item['id'] ?></td>
                            <td><?= $item['kode_pelanggan'] ?></td>
                            <td><?= $item['nama_pelanggan'] ?></td>
                            <td><?= $item['alamat'] ?></td>
                            <td><?= $item['no_hp'] ?></td>
                            <td><?= $item['keterangan'] ?></td>
                            <td>
                                <a href="index.php?page=form_pelanggan&edit=<?= $item['id'] ?>" class="btn btn-sm btn-info text-white">Edit</a>
                                <a href="index.php?page=form_pelanggan&delete=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin hapus?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Navigasi Halaman -->
        <nav>
            <ul class="pagination">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="index.php?page=form_pelanggan&halaman=<?= $page - 1 ?>">Sebelumnya</a>
                </li>
                <li class="page-item disabled">
                    <span class="page-link">Halaman <?= $page ?> dari <?= $total_halaman ?></span>
                </li>
                <li class="page-item <?= $page >= $total_halaman ? 'disabled' : '' ?>">
                    <a class="page-link" href="index.php?page=form_pelanggan&halaman=<?= $page + 1 ?>">Berikutnya</a>
                </li>
            </ul>
        </nav>
    </div>

    <?php $conn->close(); ?>
</div>

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
// TAMBAH TRANSAKSI TAGIHAN
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_tagihan'])) {
    $id_pelanggan = intval($_POST['id_pelanggan']);
    $transaksi = $_POST['transaksi'];
    $jumlah = intval($_POST['jumlah']);
    $keterangan = $conn->real_escape_string($_POST['keterangan']);
    $tgl_jt = $transaksi === 'kredit' ? null : $_POST['tgl_jt'];

    if ($id_pelanggan && in_array($transaksi, ['debit', 'kredit']) && $jumlah > 0 && ($transaksi === 'kredit' || strtotime($tgl_jt))) {
        mysqli_begin_transaction($conn);

        try {
            $stmt = $conn->prepare("INSERT INTO tagihan (id_pelanggan, transaksi, jumlah, keterangan, tgl_jt) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isiss", $id_pelanggan, $transaksi, $jumlah, $keterangan, $tgl_jt);
            $stmt->execute();
            $stmt->close();

            $update_stmt = $transaksi === 'debit'
                ? $conn->prepare("UPDATE pelanggan SET tagihan = tagihan + ? WHERE id = ?")
                : $conn->prepare("UPDATE pelanggan SET tagihan = tagihan - ? WHERE id = ?");
            $update_stmt->bind_param("ii", $jumlah, $id_pelanggan);
            $update_stmt->execute();
            $update_stmt->close();

            mysqli_commit($conn);
            $_SESSION['success'] = "Transaksi berhasil disimpan.";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $_SESSION['error'] = "Gagal menyimpan transaksi: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Data tidak valid.";
    }

    echo "<script>window.location.href='index.php?page=transaksi';</script>";
    exit;
}

?>



<div class="py-4">
    <div class="card  mb-5">
        <div class="card-header">
            <h2 class="text-center">Transaksi Tagihan</h2>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'];
                                                unset($_SESSION['success']); ?></div>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'];
                                            unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="card-body">
            <form method="POST" class="row g-3 mb-4">
                <input type="hidden" name="submit_tagihan" value="1">
                <div class="col-md-3">
                    <label class="form-label">Pelanggan</label>
                    <select name="id_pelanggan" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <?php
                        $pelanggan = mysqli_query($conn, "SELECT id, nama_pelanggan FROM pelanggan");
                        while ($p = mysqli_fetch_assoc($pelanggan)) {
                            echo "<option value='{$p['id']}'>{$p['nama_pelanggan']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Transaksi</label>
                    <select name="transaksi" class="form-select" required>
                        <option value="debit">Debit</option>
                        <option value="kredit">Kredit</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Jumlah</label>
                    <input type="number" name="jumlah" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Jatuh Tempo</label>
                    <input type="date" name="tgl_jt" class="form-control">
                </div>
                <div class="col-12 text-end">
                    <button class="btn btn-primary" type="submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const transaksiSelect = document.querySelector('select[name="transaksi"]');
        const tglJtInput = document.querySelector('input[name="tgl_jt"]');

        function toggleJatuhTempo() {
            if (transaksiSelect.value === 'kredit') {
                tglJtInput.required = false;
                tglJtInput.value = '';
                tglJtInput.disabled = true;
            } else {
                tglJtInput.required = true;
                tglJtInput.disabled = false;
            }
        }

        transaksiSelect.addEventListener('change', toggleJatuhTempo);
        toggleJatuhTempo();

    });
</script>
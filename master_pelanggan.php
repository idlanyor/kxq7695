<?php
// Koneksi ke database

include 'config/koneksi.php';

// Get initial data
$keyword = $_GET['keyword'] ?? '';
$page = isset($_GET['hal']) ? max(1, intval($_GET['hal'])) : 1;
$limit = 5; // Ganti limit jadi 5
$start = ($page - 1) * $limit;

// Build where clause
$where = '';
if (!empty($keyword)) {
    $escaped = $conn->real_escape_string($keyword);
    $where = "WHERE nama_pelanggan LIKE '%$escaped%' OR kode_pelanggan LIKE '%$escaped%'";
}


$count_q = $conn->query("SELECT COUNT(*) as total FROM pelanggan $where");
$total_data = $count_q->fetch_assoc()['total'] ?? 0;
$total_page = ceil($total_data / $limit); // Ubah kalkulasi total halaman


$query = "SELECT * FROM pelanggan $where ORDER BY nama_pelanggan ASC LIMIT $start, $limit";
$pelanggan = $conn->query($query);
?>


<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">
                        <i class="fas fa-boxes me-2"></i>Master Pelanggan
                    </h4>
                </div>
                <div class="col-md-6">
                    <div class=" row">
                        <div class="col-md-9">
                            <div class="input-group flex-grow-1">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" id="searchInput" class="form-control"
                                    placeholder="Cari nama atau kode pelanggan..."
                                    value="<?= htmlspecialchars($keyword) ?>">
                            </div>
                        </div>
                        <div class="col-md-3"> <a href="export_pelanggan.php" class="btn btn-success">
                                <i class="fas fa-file-excel me-1"></i>Export
                            </a></div>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-body">
            <div id="tableContainer">
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
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let ajaxRequest = null;
    let debounceTimer = null;

    function loadData(page = 1) {
        if (ajaxRequest) {
            ajaxRequest.abort();
        }

        $('#tableContainer').addClass('loading');

        // Make new AJAX request
        ajaxRequest = $.ajax({
            url: 'ajax_search_pelanggan.php',
            type: 'GET',
            data: {
                keyword: $('#searchInput').val(),
                hal: new URLSearchParams(window.location.search).get('hal') // Ambil nilai 'hal' dari URL
            },
            success: function(response) {
                $('#tableContainer').html(response);
            },
            error: function(xhr, status, error) {
                if (status !== 'abort') {
                    console.error('Error:', error);
                }
            },
            complete: function() {
                $('#tableContainer').removeClass('loading');
                ajaxRequest = null;
            }
        });

    }

    $(document).ready(function() {
        // Handle input changes with debounce
        $('#searchInput').on('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(loadData, 300); // Call loadData after typing stops for 300ms
        });


        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();

            const page = new URL($(this).attr('href'), window.location.href).searchParams.get('hal');

            loadData(page);
            updateUrlWithHal(page);
        });
    });

    function loadData(page = 1) {
        if (ajaxRequest) {
            ajaxRequest.abort();
        }

        $('#tableContainer').addClass('loading');


        ajaxRequest = $.ajax({
            url: 'ajax_search_pelanggan.php',
            type: 'GET',
            data: {
                keyword: $('#searchInput').val(),
                hal: page
            },
            success: function(response) {
                $('#tableContainer').html(response);
            },
            error: function(xhr, status, error) {
                if (status !== 'abort') {
                    console.error('Error:', error);
                }
            },
            complete: function() {
                $('#tableContainer').removeClass('loading');
                ajaxRequest = null;
            }
        });
    }

    function updateUrlWithHal(page) {
        const url = new URL(window.location);
        url.searchParams.set('hal', page); // Update 'hal' parameter in URL
        history.pushState({}, '', url); // Update the URL without reloading the page
    }
</script>
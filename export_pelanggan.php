<?php
include 'config/koneksi.php';

header("Content-Type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=daftar_pelanggan.xls");

// Ambil data pelanggan dari database
$query = "SELECT * FROM pelanggan ORDER BY nama_pelanggan ASC";
$result = $conn->query($query);

echo "<table border='1' cellpadding='5'>";
echo "<tr style='background-color:#f2f2f2'>
    <th>No</th>
    <th>Kode Pelanggan</th>
    <th>Nama Pelanggan</th>
    <th>Alamat</th>
    <th>No HP</th>
    <th>Tagihan</th>
    <th>Keterangan</th>
</tr>";

$no = 1;
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $no++ . "</td>";
    echo "<td>" . htmlspecialchars($row['kode_pelanggan']) . "</td>";
    echo "<td>" . htmlspecialchars($row['nama_pelanggan']) . "</td>";
    echo "<td>" . htmlspecialchars($row['alamat']) . "</td>";
    echo "<td>" . htmlspecialchars($row['no_hp']) . "</td>";
    echo "<td style='text-align:right'>" . number_format($row['tagihan'], 0, ',', '.') . "</td>";
    echo "<td>Pelanggan</td>";
    echo "</tr>";
}

echo "</table>";

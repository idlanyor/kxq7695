<?php
include 'config/koneksi.php';

header("Content-Type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=riwayat_transaksi.xls");


$query = "SELECT t.id, t.jumlah, t.keterangan, t.tgl_transaksi, t.transaksi, 
                 p.nama_pelanggan, p.kode_pelanggan 
          FROM tagihan t 
          JOIN pelanggan p ON t.id_pelanggan = p.id 
          ORDER BY t.tgl_transaksi DESC";
$result = mysqli_query($conn, $query);

echo "<table border='1' cellpadding='5'>";
echo "<tr>
  <th>No</th>
  <th>Kode Pelanggan</th>
  <th>Nama Pelanggan</th>
  <th>Tanggal Transaksi</th>
  <th>Transaksi (D/K)</th>
  <th>Jumlah</th>
  <th>Keterangan</th>
</tr>";
$no = 1;
$total_d = 0;
$total_k = 0;
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $no++ . "</td>";
    echo "<td>" . $row['kode_pelanggan'] . "</td>";
    echo "<td>" . $row['nama_pelanggan'] . "</td>";
    echo "<td>" . $row['tgl_transaksi'] . "</td>";
    echo "<td>" . ucfirst($row['transaksi']) . "</td>";
    echo "<td style='text-align:right'>" . number_format($row['jumlah'], 0, ',', '.') . "</td>";
    echo "<td>" . $row['keterangan'] . "</td>";
    echo "</tr>";
    if (strtolower($row['transaksi']) == 'debit') {
        $total_d += $row['jumlah'];
    } else if (strtolower($row['transaksi']) == 'kredit') {
        $total_k += $row['jumlah'];
    }
}


$selisih = $total_d - $total_k;

echo "<tr>
  <td colspan='5'><strong>Total Debit</strong></td>
  <td align='right'><strong>" . number_format($total_d, 0, ',', '.') . "</strong></td>
  <td></td>
</tr>";
echo "<tr>
  <td colspan='5'><strong>Total Kredit</strong></td>
  <td align='right'><strong>" . number_format($total_k, 0, ',', '.') . "</strong></td>
  <td></td>
</tr>";
echo "<tr>
  <td colspan='5'><strong>Total Tagihan (D - K)</strong></td>
  <td align='right'><strong>" . number_format($selisih, 0, ',', '.') . "</strong></td>
  <td></td>
</tr>";
echo "</table>";

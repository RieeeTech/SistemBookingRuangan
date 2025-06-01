<?php

require_once("config.php");
$conn = connectDB();

$hari_ini = date("l"); // misalnya: Monday
$tanggal_sekarang = date("Y-m-d");

// Ambil semua ruangan aktif
$ruangan = $conn->query("SELECT * FROM tb_ruangan WHERE status = 'aktif'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Ruangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4">Jadwal Ruangan Hari Ini (<?= $hari_ini ?>)</h2>

    <?php while($r = $ruangan->fetch_assoc()): ?>
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <?= $r['nama'] ?> (<?= $r['tipe'] ?>) - <?= $r['lokasi'] ?>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $jadwal = $conn->prepare("SELECT * FROM tb_jadwal 
                                                     WHERE id_ruangan = ? 
                                                     AND (hari = ? )
                                                     ORDER BY jam_mulai");
                        $jadwal->bind_param("ss", $r['id'], $hari_ini);
                        $jadwal->execute();
                        $result = $jadwal->get_result();

                        if ($result->num_rows > 0) {
                            while ($j = $result->fetch_assoc()) {
                                $warna = $j['status'] === 'kosong' ? 'success' : ($j['status'] === 'sementara' ? 'warning' : 'danger');
                                echo "<tr class='table-$warna'>
                                        <td>{$j['jam_mulai']}</td>
                                        <td>{$j['jam_selesai']}</td>
                                        <td>{$j['status']}</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>Tidak ada jadwal</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <a href="booking.php?ruangan=<?= $r['id'] ?>" class="btn btn-outline-primary">Booking Ruangan Ini</a>
            </div>
        </div>
    <?php endwhile; ?>
</div>
</body>
</html>

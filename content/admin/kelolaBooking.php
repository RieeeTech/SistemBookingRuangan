<?php


$conn = connectDB();

// Ambil data booking terbaru
$query_recent_bookings = "SELECT tb.*, tm.nama AS nama_lengkap, tr.nama, tj.hari, tj.jam_mulai, tj.jam_selesai 
FROM tb_booking tb
JOIN tb_user tu ON tb.id_user = tu.id_user
JOIN tb_mahasiswa tm ON tu.nim = tm.nim

JOIN tb_jadwal tj ON tb.id_jadwal = tj.id
JOIN tb_ruangan tr ON tj.id_ruangan = tr.id
ORDER BY tb.created_at DESC";
$result_recent_bookings = $conn->query($query_recent_bookings);

?>



<div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Data Booking</h6>
                
            </div>
            <div class="card-body">
                <?php if ($result_recent_bookings && $result_recent_bookings->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Nama</th>
                                    <th>Ruangan</th>
                                    <th>Waktu</th>
                                    <th>Waktu Dibuat</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result_recent_bookings->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['id']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        
                                        <td><?= formatWaktu($row['jam_mulai']) . ' - ' . formatWaktu($row['jam_selesai']); ?></td>
                                        <td><?= date('H:i:s', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            switch ($row['status']) {
                                                case 'pending':
                                                    $badge_class = 'badge bg-warning text-dark';
                                                    break;
                                                case 'disetujui':
                                                    $badge_class = 'badge bg-success';
                                                    break;
                                                case 'ditolak':
                                                    $badge_class = 'badge bg-danger';
                                                    break;
                                                case 'selesai':
                                                    $badge_class = 'badge bg-secondary';
                                                    break;
                                                default:
                                                    $badge_class = 'badge bg-light text-dark';
                                            }
                                            ?>
                                            <span class="<?php echo $badge_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                                        </td>
                                        <td>
                                            <a href="detail_booking.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($row['status'] == 'pending'): ?>
                                                <a href="approve_booking.php?id=<?php echo $row['id']; ?>&action=approve" class="btn btn-sm btn-success" title="Setujui" onclick="return confirm('Setujui booking ini?')">
                                                    <i class="bi bi-check-lg"></i>
                                                </a>
                                                <a href="approve_booking.php?id=<?php echo $row['id']; ?>&action=reject" class="btn btn-sm btn-danger" title="Tolak" onclick="return confirm('Tolak booking ini?')">
                                                    <i class="bi bi-x-lg"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x mb-3 text-gray-300"></i>
                        <p class="text-muted">Belum ada data booking.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
<?php
// riwayat_booking.php - Halaman untuk melihat riwayat booking (untuk mahasiswa)
require_once 'header.php';

// Pastikan user sudah login dan adalah mahasiswa
if (!isLoggedIn() || !isMahasiswa()) {
    redirect('login.php');
}

$conn = connectDB();
$user_id = $_SESSION['user_id'];

// Pesan sukses dari halaman booking
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = 'Booking berhasil dibuat! Menunggu persetujuan admin.';
}

// Filter status
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_clause = '';

if (!empty($filter_status)) {
    $filter_clause = " AND b.status = '" . $conn->real_escape_string($filter_status) . "'";
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Hitung total data
$query_count = "SELECT COUNT(*) as total 
                FROM booking b
                WHERE b.id_user = ?$filter_clause";
$stmt_count = $conn->prepare($query_count);
$stmt_count->bind_param("i", $user_id);
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$total_records = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_records / $per_page);

// Query untuk mengambil riwayat booking
$query = "SELECT b.*, r.nama_ruangan, j.tanggal, j.jam_mulai, j.jam_selesai 
          FROM booking b
          JOIN jadwal j ON b.id_jadwal = j.id
          JOIN ruangan r ON j.id_ruangan = r.id
          WHERE b.id_user = ?$filter_clause
          ORDER BY j.tanggal DESC, j.jam_mulai DESC
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Tampilan Riwayat Booking -->
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Riwayat Booking Saya</h1>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Status -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Status</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <select class="form-control" name="status">
                            <option value="" <?php if ($filter_status == '') echo 'selected'; ?>>Semua Status</option>
                            <option value="pending" <?php if ($filter_status == 'pending') echo 'selected'; ?>>Menunggu Persetujuan</option>
                            <option value="disetujui" <?php if ($filter_status == 'disetujui') echo 'selected'; ?>>Disetujui</option>
                            <option value="ditolak" <?php if ($filter_status == 'ditolak') echo 'selected'; ?>>Ditolak</option>
                            <option value="selesai" <?php if ($filter_status == 'selesai') echo 'selected'; ?>>Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="booking.php" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i> Booking Baru
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Riwayat Booking -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Booking Saya</h6>
        </div>
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Ruangan</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Keperluan</th>
                                <th>Jumlah Peserta</th>
                                <th>Status</th>
                                <th>Tanggal Booking</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = $offset + 1;
                            while ($row = $result->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo $row['nama_ruangan']; ?></td>
                                    <td><?php echo formatTanggal($row['tanggal']); ?></td>
                                    <td><?php echo formatWaktu($row['jam_mulai']) . ' - ' . formatWaktu($row['jam_selesai']); ?></td>
                                    <td><?php echo substr($row['keperluan'], 0, 50) . (strlen($row['keperluan']) > 50 ? '...' : ''); ?></td>
                                    <td><?php echo $row['jumlah_peserta']; ?> orang</td>
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
                                        }
                                        ?>
                                        <span class="<?php echo $badge_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                                    </td>
                                    <td><?php echo formatTanggal($row['created_at']); ?></td>
                                    <td>
                                        <a href="detail_booking_mahasiswa.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <a href="batalkan_booking.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Apakah Anda yakin ingin membatalkan booking ini?');">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1<?php echo !empty($filter_status) ? '&status=' . $filter_status : ''; ?>" aria-label="First">
                                            <span aria-hidden="true">&laquo;&laquo;</span>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($filter_status) ? '&status=' . $filter_status : ''; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($filter_status) ? '&status=' . $filter_status : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($filter_status) ? '&status=' . $filter_status : ''; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo !empty($filter_status) ? '&status=' . $filter_status : ''; ?>" aria-label="Last">
                                            <span aria-hidden="true">&raquo;&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-calendar-times fa-3x mb-3 text-gray-300"></i>
                    <p>Anda belum memiliki riwayat booking<?php echo !empty($filter_status) ? ' dengan status ' . ucfirst($filter_status) : ''; ?>.</p>
                    <a href="booking.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Booking Sekarang
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'footer.php';
?>
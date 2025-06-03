<?php

$conn = connectDB();

$user_id = $_SESSION['user_id'];

// Pagination settings
$records_per_page = 5;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $records_per_page;

// Get total records count
$count_query = "SELECT COUNT(*) as total 
                FROM tb_booking tb
                JOIN tb_jadwal tj ON tb.id_jadwal = tj.id
                JOIN tb_ruangan tr ON tj.id_ruangan = tr.id
                WHERE tb.id_user = ?";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get records for current page - menggunakan prepared statement yang benar
$query_my_bookings = "SELECT tb.*, tr.nama, tj.tanggal, tj.jam_mulai, tj.jam_selesai, tj.hari 
                     FROM tb_booking tb
                     JOIN tb_jadwal tj ON tb.id_jadwal = tj.id
                     JOIN tb_ruangan tr ON tj.id_ruangan = tr.id
                     WHERE tb.id_user = ?
                     ORDER BY tj.tanggal DESC, tj.jam_mulai DESC
                     LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query_my_bookings);
$stmt->bind_param("iii", $user_id, $records_per_page, $offset);
$stmt->execute();
$result_my_bookings = $stmt->get_result();


if(isset($_POST['batal'])){
  $id_booking = $_POST['id_booking'];

  $setBatalBooking = $conn->prepare("UPDATE tb_booking SET status = 'dibatalkan' WHERE id = ?");
  $setBatalBooking->bind_param('s', $id_booking);

  if($setBatalBooking->execute()){
    echo "<script>alert('Berhasil Membatalkan Booking!!');
    document.location='index.php?page=myBooking';
    </script>";
  } else{
    echo "<script>alert('Gagal Membatalkan Booking!!');
    document.location='index.php?page=myBooking';
    </script>";
  }
}


?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Riwayat Booking Saya</h6>
        <?php if ($total_records > 0): ?>
            <small class="text-muted">
                <?php if ($total_records > 5): ?>
                    Halaman <?= $current_page ?> dari <?= $total_pages ?> (<?= $total_records ?> total data)
                <?php else: ?>
                    <?= $total_records ?> data ditemukan
                <?php endif; ?>
            </small>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($result_my_bookings && $result_my_bookings->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Ruangan</th>
                            <th>Waktu</th>
                            <th>Keperluan</th>
                            <th>Diajukan Pada</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_my_bookings->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']); ?></td>
                                <td><?= htmlspecialchars($row['nama']); ?></td>
                                <td><?= formatWaktu($row['jam_mulai']) . ' - ' . formatWaktu($row['jam_selesai']); ?></td>
                                <td>
                                    <?php 
                                    $keperluan = htmlspecialchars($row['keperluan']);
                                    echo strlen($keperluan) > 50 ? substr($keperluan, 0, 50) . '...' : $keperluan;
                                    ?>
                                </td>
                                <td><?= date('d-m-Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <?php
                                    $badge_class = '';
                                    $imgStatus = '';
                                    switch ($row['status']) {
                                        case 'pending':
                                            $badge_class = 'badge bg-warning text-dark';
                                            $imgStatus = 'bi bi-clock-fill text-warning';
                                            break;
                                        case 'disetujui':
                                            $badge_class = 'badge bg-success';
                                            $imgStatus = 'bi bi-check-circle-fill text-success';
                                            break;
                                        case 'ditolak':
                                            $badge_class = 'badge bg-danger';
                                            $imgStatus = 'bi bi-x-circle-fill text-danger';
                                            break;
                                        case 'selesai':
                                            $badge_class = 'badge bg-info';
                                            $imgStatus = 'bi bi-hand-thumbs-up-fill text-info';
                                            break;
                                        case 'dibatalkan':
                                            $badge_class = 'badge bg-light text-danger border border-danger';
                                            $imgStatus = 'bi bi-x-circle text-danger';
                                            break;
                                        default:
                                            $badge_class = 'badge bg-light text-dark';
                                    }
                                    ?>
                                    <span class="<?= $badge_class; ?>"><?= ucfirst($row['status']); ?></span>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalDetailBoMhs<?= $row['id']?>" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <?php if ($row['status'] == 'pending'): ?>
                                        <a href="#" class="btn btn-sm btn-danger" title="Batalkan" data-bs-toggle="modal" data-bs-target="#modalBatalBoMhs<?= $row['id']?>">
                                            <i class="bi bi-x-lg"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>


<!-- Modal Detail -->
<div class="modal fade" id="modalDetailBoMhs<?= $row['id']?>" tabindex="-1" aria-labelledby="bookingPendingLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4">
      <div class="modal-body text-center p-4">
        <!-- Gambar ikon pending dengan efek abu -->

        
          
          <i class="<?= $imgStatus; ?>" style="font-size: 80px;"></i>

        <h4 class="modal-title mb-3" id="bookingPendingLabel"><?= ucfirst($row['status']); ?></h4>

        <div class="text-start">
          <p><strong>Ruangan:</strong> <?= $row['nama']?></p>
          <p><strong>Hari:</strong> <?= $row['hari']?></p>
          <p><strong>Jam:</strong> <?= formatWaktu($row['jam_mulai']) . ' - ' . formatWaktu($row['jam_selesai']); ?> </p>
          <p><strong>Tanggal Diajukan:</strong> <?= date('d-m-Y', strtotime($row['created_at'])); ?></p>
          <p><strong>Waktu Diajukan:</strong> <?= date('H:i', strtotime($row['created_at'])); ?></p>
          <p><strong>Status:</strong> <span class="<?= $badge_class; ?>"><?= ucfirst($row['status']); ?></span></p>
        </div>

        <div class="mt-4">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
</div>



<!-- Modal Batalkan Booking -->
<div class="modal fade" id="modalBatalBoMhs<?= $row['id']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5 text-danger" id="exampleModalLabel">Batalkan Booking</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <form action="" method="post">
          <input type="hidden" name="id_booking" value="<?= $row['id']?>">
          <div class="modal-body">
              
              <h5 class="text-center">Apa anda yakin ingin Membatalkan Booking Ini?<br>
                  
              </h5>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keluar</button>
            <button type="submit" name="batal" class="btn btn-danger">Ya, Batalkan</button>
          </div>
      </form>
      </div>
      
    </div>
  </div>
</div>

                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            

            
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-calendar-times fa-3x mb-3 text-gray-300"></i>
                <p class="text-muted">Anda belum memiliki booking ruangan.</p>
                <a href="?page=booking" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Booking Sekarang
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>






<?php

$conn = connectDB();

$no = 1;
$query_ruangan = "SELECT tj.*, tr.nama FROM tb_jadwal tj JOIN tb_ruangan tr ON tj.id_ruangan = tr.id ORDER BY tj.hari DESC";
$ruangan = $conn->query($query_ruangan);





if(isset($_POST["ubah"])){

  $id = $_POST['id_jadwal'];
  $id_ruangan = $_POST['id_ruangan'];
  $hari = $_POST['hari'];
  $jam_mulai = $_POST['jam_mulai'];
  $jam_selesai = $_POST['jam_selesai'];
  $keterangan = $_POST['keterangan'];


  $stmt_ubah = $conn->prepare("UPDATE tb_jadwal SET id_ruangan = '$id_ruangan', hari = '$hari', jam_mulai = '$jam_mulai', jam_selesai = '$jam_selesai', keterangan = '$keterangan' WHERE id = ?");
  $stmt_ubah->bind_param("i", $id);
  $stmt_ubah->execute();


  if($stmt_ubah){
    echo "<script>alert('Berhasil Mengubah Data!!');
    document.location='index.php?page=dataJadwal'
    </script>";
  } else {
    echo "<script>alert('Gagal Mengubah Data!!');
    document.location='index.php?page=dataJadwal'
    </script>";
  }
}



// CRUD hapus
if(isset($_POST["hapus"])){

  $id = $_POST['id'];

  $stmt_hapus = $conn->prepare("DELETE FROM tb_jadwal WHERE id = ?");
  $stmt_hapus->bind_param("i", $id);
  $stmt_hapus->execute();

  if($stmt_hapus){
    echo "<script>alert('Berhasil Menghapus Data!!');
    document.location='index.php?page=dataJadwal';
    </script>";
  } else {
    echo "<script>alert('Gagal Menghapus Data!!');
    document.location='index.php?page=dataJadwal';
    </script>";
  }
}
?>
            <div class="card shadow mb-4">
              <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Data Jadwal</h6>
                  <a href="?page=tambahDataJadwal" class="btn btn-sm btn-primary shadow-sm">
                    <i class="bi bi-plus-lg text-white"></i> Tambah Jadwal
                  </a>
              </div>
              <div class="card-body">
                <?php if ($ruangan && $ruangan->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Nama Ruangan</th>
                                    <th class="text-center">Hari</th>
                                    <th>Jam</th>
                                    <th class="text-center">Kelas</th>
                                    <th>Keterangan</th>
                                    <th class="text-center">Status</th>
                                    
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody >
                                
                                <?php while ($data = $ruangan->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>

                                        <td><?= $data['nama']; ?></td>

                                        <td><?= $data['hari']; ?></td>

                                        <td><?= date("H:i", strtotime($data['jam_mulai'])) . ' - ' . date("H:i", strtotime($data['jam_selesai'])) ?></td>


                                        <td><?= $data['kelas']; ?></td>

                                        <td><?= $data['keterangan'] === null ? '-' : $data['keterangan']; ?></td>
                                        
                                        <td class="text-center">
                                            <?php
                                            $badge_class = '';
                                            switch ($data['status']) {
                                                case 'Kosong':
                                                    $badge_class = 'badge bg-secondary text-light';
                                                    break;
                                                case 'Tetap':
                                                    $badge_class = 'badge bg-success';
                                                    break;
                                                case 'Sementara':
                                                    $badge_class = 'badge bg-warning text-dark';
                                                    break;
                                                
                                                default:
                                                    $badge_class = 'badge bg-light text-dark';
                                            }
                                            ?>
                                            <span class="<?php echo $badge_class; ?>"><?php echo ucfirst($data['status']); ?></span>
                                        </td>

                                        <td class="text-center">
                                            
                                            <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $no?>">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalHapus<?= $no?>">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            
                                        </td>
                                    </tr>


<!-- Modal Edit -->
<div class="modal fade" id="modalEdit<?= $no?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Jadwal</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="" method="post">
          <input type="hidden" name="id_jadwal" value="<?= $data['id']?>">
          <div class="modal-body">
              <div class="mb-2">
                <label class="form-label">Ruangan</label>
                <select name="id_ruangan" id="" class="form-control" required>
                  <option value="<?= $data['id_ruangan']?>">-- Pilih Satu --</option>
                <?php 

                $query_ruangan1 = "SELECT * FROM tb_ruangan";
                $ruangan1 = $conn->query($query_ruangan1);
                
                while ($data1 = $ruangan1->fetch_assoc()){ ?>
                  <option value="<?= $data1['id']; ?>"><?= $data1['id'] . ' - ' . $data1['nama']; ?></option>
                <?php } ?>

                </select>
              </div>
              <div class="mb-2">
                <label class="form-label">Hari</label>
                <select name="hari" id="" class="form-control">
                  <option value="<?= $data['hari']?>">-- Pilih Hari --</option>
                  <option value="Senin">Senin</option>
                  <option value="Selasa">Selasa</option>
                  <option value="Rabu">Rabu</option>
                  <option value="Kamis">Kamis</option>
                  <option value="Jumat">Jumat</option>
                  <option value="Sabtu">Sabtu</option>
                </select>
              </div>
              
              
              <div class="mb-2">
                <label class="form-label">Jam Mulai</label>
                <input name="jam_mulai" type="text"  class="form-control" value="<?= date("H:i", strtotime($data['jam_mulai']))?>">
              </div>
              <div class="mb-2">
                <label class="form-label">Jam Selesai</label>
                <input name="jam_selesai" type="text"  class="form-control" value="<?= date("H:i", strtotime($data['jam_selesai'])) ?>">
              </div>
              <div class="mb-2">
                <label class="form-label">Keterangan (Kelas)</label>
                <input name="keterangan" type="text" class="form-control" value="<?= $data['keterangan']?>">
                <div class="form-text">Perhatian: Kosongkan Jika Bukan Untuk Jadwal Tetap!!</div>
              </div>
             
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keluar</button>
            <button type="submit" name="ubah" class="btn btn-warning">Edit</button>
          </div>
        </form>
      </div>
      
    </div>
  </div>
</div>


<!-- Modal Hapus -->
<div class="modal fade" id="modalHapus<?= $no?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <form action="" method="post">
          <input type="hidden" name="id" value="<?= $data['id']?>">
          <div class="modal-body">
              
              <h5 class="text-center">Apa anda yakin ingin menghapus Jadwal ini?<br><br>
                  <span class="text-danger"><?= $data['nama']?></span>
                  <p><span class="text-danger"><?= $data['hari']?> : <?= date("H:i", strtotime($data['jam_mulai'])) . ' - ' . date("H:i", strtotime($data['jam_selesai'])) ?> </span></p>
              </h5>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keluar</button>
            <button type="submir" name="hapus" class="btn btn-danger">Hapus</button>
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
                        <i class="fas fa-inbox fa-3x mb-3 text-gray-300"></i>
                        <p class="text-muted">Belum ada data booking.</p>
                    </div>
                <?php endif; ?>
            </div>
          </div>




          
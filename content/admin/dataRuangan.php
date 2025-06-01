<?php

$conn = connectDB();

$no = 1;
$query_ruangan = "SELECT * FROM tb_ruangan ORDER BY nama ASC";
$ruangan = $conn->query($query_ruangan);


if(isset($_POST["ubah"])){

  $id = $_POST['id_ruangan'];
  $nama_ruangan = $_POST['namaRuangan'];
  $tipe = $_POST['tipe'];
  $lokasi = $_POST['lokasi'];
  $fasilitas = $_POST['fasilitas'];


  $stmt_ubah = $conn->prepare("UPDATE tb_ruangan SET nama = '$nama_ruangan', tipe = '$tipe', lokasi = '$lokasi', fasilitas = '$fasilitas' WHERE id = ?");
  $stmt_ubah->bind_param("i", $id);
  $stmt_ubah->execute();


  if($stmt_ubah){
    echo "<script>alert('Berhasil Mengubah Data!!');
    document.location='index.php?page=dataRuangan'
    </script>";
  } else {
    echo "<script>alert('Gagal Mengubah Data!!');
    document.location='index.php?page=dataRuangan'
    </script>";
  }
}



// CRUD hapus
if(isset($_POST["hapus"])){

  $id = $_POST['id_ruangan'];

  $stmt_hapus = $conn->prepare("DELETE FROM tb_ruangan WHERE id = ?");
  $stmt_hapus->bind_param("i", $id);
  $stmt_hapus->execute();

  if($stmt_hapus){
    echo "<script>alert('Berhasil Menghapus Data!!');
    document.location='index.php?page=dataRuangan';
    </script>";
  } else {
    echo "<script>alert('Gagal Menghapus Data!!');
    document.location='index.php?page=dataRuangan';
    </script>";
  }
}
?>
            <div class="card shadow mb-4">
              <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Data Ruangan</h6>
                  <a href="?page=tambahDataRuangan" class="btn btn-sm btn-primary shadow-sm">
                    <i class="bi bi-plus-lg text-white"></i> Tambah Ruangan
                  </a>
              </div>
              <div class="card-body">
                <?php if ($ruangan && $ruangan->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Id</th>
                                    <th>Nama Ruangan</th>
                                    <th>Tipe</th>
                                    <th style="width: 130px;">Lokasi</th>
                                    <th style="width: 250px;">Fasilitas</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody >
                                
                                <?php while ($data = $ruangan->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td><?= $data['id']; ?></td>
                                        <td><?= $data['nama']; ?></td>
                                        <td><?= $data['tipe']; ?></td>
                                        <td class="text-wrap"><?= $data['lokasi']; ?></td>
                                        <td class="text-wrap"><?= $data['fasilitas']; ?></td>
                                        
                                        <td class="text-center">
                                            <?php
                                            $badge_class = '';
                                            switch ($data['status']) {
                                                case 'aktif':
                                                    $badge_class = 'badge bg-warning text-dark';
                                                    break;
                                                case 'non-aktif':
                                                    $badge_class = 'badge bg-success';
                                                    break;
                                                
                                                default:
                                                    $badge_class = 'badge bg-light text-dark';
                                            }
                                            ?>
                                            <span class="<?php echo $badge_class; ?>"><?php echo ucfirst($data['status']); ?></span>
                                        </td>
                                        <td class="text-center">
                                            
                                            <a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $no?>" title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $no?>" title="Edit Data">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalHapus<?= $no?>" title="Hapus Data">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            
                                        </td>
                                    </tr>


<!-- Modal Edit -->
<div class="modal fade" id="modalEdit<?= $no?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="" method="post">
          <input type="hidden" name="id_ruangan" value="<?= $data['id']?>">
          <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Nama Ruangan</label>
                <input name="namaRuangan" type="text" class="form-control" value="<?= $data['nama']?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Tipe Ruangan</label>
                <select name="tipe" id="" class="form-control">
                  <option value="<?= $data['tipe']?>">--Pilih Satu--</option>
                  <option value="Teori">Teori</option>
                  <option value="Lab">Lab</option>
                  <option value="Aula">Aula</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Lokasi</label>
                <input name="lokasi" type="text" class="form-control" value="<?= $data['lokasi']?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Fasilitas</label>
                <input name="fasilitas" type="text" class="form-control" value="<?= $data['fasilitas']?>">
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
          <input type="hidden" name="id_ruangan" value="<?= $data['id']?>">
          <div class="modal-body">
              
              <h5 class="text-center">Apa anda yakin ingin menghapus data Ruangan ini?<br>
                  <span class="text-danger"><?= $data['nama']?> - <?= $data['lokasi']?></span>
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
                        <p class="text-muted">Belum ada data Ruangan.</p>
                    </div>
                <?php endif; ?>
            </div>
          </div>



          <!-- Modal Trigger -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
  Buka Form
</button>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg"> <!-- modal-lg agar cukup lebar -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Form Dua Kolom</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="row">
            <!-- Kolom kiri -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="input1" class="form-label">Input 1</label>
                <input type="text" class="form-control" id="input1">
              </div>
              <div class="mb-3">
                <label for="input2" class="form-label">Input 2</label>
                <input type="text" class="form-control" id="input2">
              </div>
              <div class="mb-3">
                <label for="input3" class="form-label">Input 3</label>
                <input type="text" class="form-control" id="input3">
              </div>
            </div>

            <!-- Kolom kanan -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="input4" class="form-label">Input 4</label>
                <input type="text" class="form-control" id="input4">
              </div>
              <div class="mb-3">
                <label for="input5" class="form-label">Input 5</label>
                <input type="text" class="form-control" id="input5">
              </div>
              <div class="mb-3">
                <label for="input6" class="form-label">Input 6</label>
                <input type="text" class="form-control" id="input6">
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </div>
  </div>
</div>




          
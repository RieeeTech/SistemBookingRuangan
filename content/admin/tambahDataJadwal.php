<?php

$conn = connectDB();
$message = '';
$error = '';

$query_ruangan = "SELECT * FROM tb_ruangan";
$ruangan = $conn->query($query_ruangan);

if(isset($_POST['tambah'])){


  $id_ruangan = $_POST['id_ruangan'];
  $hari = $_POST['hari'];
  $jam_mulai = $_POST['jam_mulai'];
  $jam_selesai = $_POST['jam_selesai'];
  $keterangan = $_POST['keterangan'];
  $status = $_POST['status'];
  $kelas = $_POST['kelas'];
  

  // $stmt_insert = $conn->prepare("INSERT INTO tb_jadwal (id_ruangan, hari, jam_mulai, jam_selesai, keterangan) VALUES (?, ?, ?, ?, ?)");
  // $stmt_insert->bind_param("sssss", $id_ruangan,$hari, $jam_mulai, $jam_selesai, $keterangan);
  // $stmt_insert->execute();


  // if($stmt_insert){
  //   echo "<script>alert('Berhasil Menambahkan Data!!');
  //   location.href='index.php?page=dataJadwal';
  //   </script>";
  // } else {
  //   echo "<script>alert('Gagal Menambahkan Data!!');
  //   location.href='index.php?page=dataJadwal';
  //   </script>";
  // }
  $sql = "SELECT * FROM tb_jadwal 
        WHERE id_ruangan = ? 
          AND hari = ? 
          AND (jam_mulai < ? AND jam_selesai > ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $id_ruangan, $hari, $jam_selesai, $jam_mulai);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<script>alert('Jadwal Bertabrakan!!');
          location.href='index.php?page=tambahDataJadwal';
          </script>";
} else {
    // Tidak ada konflik → simpan jadwal baru
    $insert = $conn->prepare("INSERT INTO tb_jadwal (id_ruangan, hari, jam_mulai, jam_selesai, keterangan, status, kelas) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insert->bind_param("sssssss", $id_ruangan, $hari, $jam_mulai, $jam_selesai, $keterangan, $status, $kelas);
    
    if ($insert->execute()) {
        echo "<script>alert('Jadwal Berhasil Disimpan!!');
              location.href='index.php?page=dataJadwal';
              </script>";
    } else {
      echo "<script>alert('Gagal Menyimpan Jadwal!!');
            location.href='index.php?page=dataJadwal';
            </script>";
    }
}

}



?>



  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-primary">
      <h6 class="m-0 font-weight-bold text-light">Tambah Jadwal</h6>
    </div>
    <form action="" method="POST">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <div class="mb-2">
              <label class="form-label">Ruangan</label>
              <select name="id_ruangan" id="" class="form-control" required>

                <option value="">-- Pilih Ruangan --</option>
              <?php while ($data = $ruangan->fetch_assoc()): ?>
                <option value="<?= $data['id']; ?>"><?= $data['id'] . ' - ' . $data['nama']; ?></option>
              <?php endwhile; ?>

              </select>
            </div>

            <div class="mb-2">
              <label class="form-label">Hari</label>
              <select name="hari" id="" class="form-control">
                <option value="">-- Pilih Hari --</option>
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
              <input name="jam_mulai" type="text"  class="form-control">
            </div>
            <div class="mb-2">
              <label class="form-label">Jam Selesai</label>
              <input name="jam_selesai" type="text"  class="form-control">
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-2">
              <label class="form-label">Status Jadwal</label>
              <select name="status" id="" class="form-control">
                <option value="Kosong">-- Pilih --</option>
                <option value="Kosong">Kosong</option>
                <option value="Tetap">Tetap</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Kelas</label>
              <input name="kelas" type="text"  class="form-control">
            </div>
            <div class="mb-2">
              <label class="form-label">Keterangan</label>
              <input name="keterangan" type="text" class="form-control">
              <div class="form-text">Perhatian: Kosongkan Jika Bukan Untuk Jadwal Tetap!!</div>
            </div>
          </div>
        </div>
        
        
        
        
      </div>
      <div class="card-footer py-3 d-flex flex-row align-items-center justify-content-end gap-2">
        <a href="?page=dataJadwal" class="btn btn-secondary">Kembali</a>
        <button type="submit" name="tambah" class="btn btn-primary">Tambah Data</button>
      </div>
    </form>
  </div>


  
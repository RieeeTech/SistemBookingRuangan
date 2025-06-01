<?php

$conn = connectDB();
$message = '';
$error = '';

if(isset($_POST['tambah'])){


  $id = $_POST['id'];
  $nama_ruangan = $_POST['namaRuangan'];
  $tipe = $_POST['tipe'];
  $lokasi = $_POST['lokasi'];
  $fasilitas = $_POST['fasilitas'];
  

  $stmt_insert = $conn->prepare("INSERT INTO tb_ruangan (id, nama, tipe, lokasi, fasilitas) VALUES (?, ?, ?, ?, ?)");
  $stmt_insert->bind_param("sssss", $id,$nama_ruangan, $tipe, $lokasi, $fasilitas);
  $stmt_insert->execute();


  if($stmt_insert){
    echo "<script>alert('Berhasil Menambahkan Data!!');
    location.href='index.php?page=dataRuangan';
    </script>";
  } else {
    echo "<script>alert('Gagal Menambahkan Data!!');
    location.href='index.php?page=dataRuangan';
    </script>";
  }

}



?>



  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-primary">
      <h6 class="m-0 font-weight-bold text-light">Tambah Ruangan</h6>
    </div>
    <form action="" method="POST">
      <div class="card-body">
        <div class="mb-2">
          <label class="form-label">Id Ruangan</label>
          <input name="id" type="text" class="form-control">
        </div>
        <div class="mb-2">
          <label class="form-label">Nama Ruangan</label>
          <input name="namaRuangan" type="text" class="form-control">
        </div>
        <div class="mb-2">
          <label class="form-label">Tipe Ruangan</label>
          <select name="tipe" id="" class="form-control">
            <option value="">--Pilih Satu--</option>
            <option value="Teori">Teori</option>
            <option value="Lab">Lab</option>
            <option value="Aula">Aula</option>
          </select>
        </div>
        
        <div class="mb-2">
          <label class="form-label">Lokasi</label>
          <input name="lokasi" type="text" class="form-control">
        </div>
        <div class="mb-2">
          <label class="form-label">Fasilitas</label>
          <input name="fasilitas" type="text" class="form-control">
        </div>
        
        
      </div>
      <div class="card-footer py-3 d-flex flex-row align-items-center justify-content-end gap-2">
        <a href="?page=dataRuangan" class="btn btn-secondary">Kembali</a>
        <button type="submit" name="tambah" class="btn btn-primary">Tambah Data</button>
      </div>
    </form>
  </div>


  
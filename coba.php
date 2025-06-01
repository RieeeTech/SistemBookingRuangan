<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Form Jurusan, Semester, Kelas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <div class="container">
    <h4>Form Input</h4>
    <form action="proses.php" method="POST">
      <!-- Jurusan -->
      <div class="mb-3">
        <label for="jurusan" class="form-label">Jurusan</label>
        <select id="jurusan" name="jurusan" class="form-select" onchange="updateKelas()">
          <option value="">-- Pilih Jurusan --</option>
          <option value="si">Sistem Informasi</option>
          <option value="ti">Teknik Informatika</option>
        </select>
      </div>

      <!-- Semester -->
      <div class="mb-3">
        <label for="semester" class="form-label">Semester</label>
        <select id="semester" name="semester" class="form-select" onchange="updateKelas()">
          <option value="">-- Pilih Semester --</option>
          <option value="2">Semester 2</option>
          <option value="4">Semester 4</option>
          <option value="6">Semester 6</option>
        </select>
      </div>

      <!-- Kelas -->
      <div class="mb-3">
        <label for="kelas" class="form-label">Kelas</label>
        <select id="kelas" name="kelas" class="form-select">
          <option value="">-- Pilih Kelas --</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">Kirim</button>
    </form>
  </div>

  <script>
    const dataKelas = {
      si: {
        2: ["SI-2A", "SI-2B"],
        4: ["SI-4A", "SI-4B"],
        6: ["SI-6A"]
      },
      ti: {
        2: ["TI-2A"],
        4: ["TI-4A", "TI-4B"],
        6: ["TI-6A"]
      }
    };

    function updateKelas() {
      const jurusan = document.getElementById("jurusan").value;
      const semester = document.getElementById("semester").value;
      const kelasSelect = document.getElementById("kelas");

      // Reset opsi kelas
      kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';

      if (jurusan && semester && dataKelas[jurusan] && dataKelas[jurusan][semester]) {
        dataKelas[jurusan][semester].forEach(kelas => {
          const option = document.createElement("option");
          option.value = kelas;
          option.text = kelas;
          kelasSelect.appendChild(option);
        });
      }
    }
  </script>
</body>
</html>

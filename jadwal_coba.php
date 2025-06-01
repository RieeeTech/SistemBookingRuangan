<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Booking Ruangan</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .schedule-grid {
            font-size: 0.85rem;
        }
        .time-slot {
            min-height: 40px;
            border: 1px solid #dee2e6;
        }
        .slot-occupied {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .slot-empty {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }
        .slot-conflict {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .day-header {
            background-color: #495057;
            color: white;
            font-weight: bold;
        }
        .time-header {
            background-color: #6c757d;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <h2 class="mb-4"><i class="fas fa-door-open"></i> Sistem Booking Ruangan</h2>
        
        <!-- Alert Area -->
        <div id="alertArea"></div>
        
        <div class="row">
            <!-- Form Input -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-plus"></i> Tambah/Edit Jadwal</h5>
                    </div>
                    <div class="card-body">
                        <form id="scheduleForm">
                            <input type="hidden" id="editId" value="">
                            
                            <div class="mb-3">
                                <label for="ruangan" class="form-label">Ruangan</label>
                                <select class="form-select" id="ruangan" required>
                                    <option value="">Pilih Ruangan</option>
                                    <option value="R001">Lab Komputer 1</option>
                                    <option value="R002">Lab Komputer 2</option>
                                    <option value="R003">Ruang Kelas A</option>
                                    <option value="R004">Ruang Kelas B</option>
                                    <option value="R005">Aula</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="hari" class="form-label">Hari</label>
                                <select class="form-select" id="hari" required>
                                    <option value="">Pilih Hari</option>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="jamMulai" class="form-label">Jam Mulai</label>
                                        <input type="time" class="form-control" id="jamMulai" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="jamSelesai" class="form-label">Jam Selesai</label>
                                        <input type="time" class="form-control" id="jamSelesai" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="kelas" class="form-label">Kelas/Kegiatan</label>
                                <input type="text" class="form-control" id="kelas" placeholder="Kosongkan jika slot kosong">
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" required>
                                    <option value="tetap">Jadwal Tetap</option>
                                    <option value="sementara">Jadwal Sementara</option>
                                    <option value="kosong">Slot Kosong</option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan Jadwal
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                    <i class="fas fa-refresh"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Ruangan Filter -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6><i class="fas fa-filter"></i> Filter Ruangan</h6>
                    </div>
                    <div class="card-body">
                        <select class="form-select" id="filterRuangan" onchange="loadSchedule()">
                            <option value="">Semua Ruangan</option>
                            <option value="R001">Lab Komputer 1</option>
                            <option value="R002">Lab Komputer 2</option>
                            <option value="R003">Ruang Kelas A</option>
                            <option value="R004">Ruang Kelas B</option>
                            <option value="R005">Aula</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Schedule Grid -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-calendar"></i> Jadwal Mingguan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered schedule-grid" id="scheduleTable">
                                <thead>
                                    <tr>
                                        <th class="time-header">Waktu</th>
                                        <th class="day-header">Senin</th>
                                        <th class="day-header">Selasa</th>
                                        <th class="day-header">Rabu</th>
                                        <th class="day-header">Kamis</th>
                                        <th class="day-header">Jumat</th>
                                        <th class="day-header">Sabtu</th>
                                    </tr>
                                </thead>
                                <tbody id="scheduleBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Data List -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Daftar Jadwal</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="dataTable">
                                <thead>
                                    <tr>
                                        <th>Ruangan</th>
                                        <th>Hari</th>
                                        <th>Waktu</th>
                                        <th>Kelas/Kegiatan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="dataBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Data storage (simulasi database)
        let schedules = [
            {id: 1, ruangan: 'R001', hari: 'Senin', jamMulai: '08:00', jamSelesai: '10:00', kelas: 'Pemrograman Web', status: 'tetap'},
            {id: 2, ruangan: 'R001', hari: 'Senin', jamMulai: '10:30', jamSelesai: '12:00', kelas: '', status: 'kosong'},
            {id: 3, ruangan: 'R002', hari: 'Selasa', jamMulai: '08:00', jamSelesai: '09:30', kelas: 'Database', status: 'tetap'},
            {id: 4, ruangan: 'R003', hari: 'Rabu', jamMulai: '13:00', jamSelesai: '15:00', kelas: 'Algoritma', status: 'sementara'}
        ];
        
        let nextId = 5;
        
        // Time slots untuk grid
        const timeSlots = [
            '07:00-08:00', '08:00-09:00', '09:00-10:00', '10:00-11:00', '11:00-12:00',
            '12:00-13:00', '13:00-14:00', '14:00-15:00', '15:00-16:00', '16:00-17:00'
        ];
        
        const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        
        function showAlert(message, type = 'success') {
            const alertArea = document.getElementById('alertArea');
            alertArea.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            setTimeout(() => {
                alertArea.innerHTML = '';
            }, 5000);
        }
        
        function timeToMinutes(time) {
            const [hours, minutes] = time.split(':').map(Number);
            return hours * 60 + minutes;
        }
        
        function checkTimeConflict(newSchedule, existingSchedules, editId = null) {
            const conflicts = [];
            
            for (let schedule of existingSchedules) {
                // Skip jika sedang edit data yang sama
                if (editId && schedule.id == editId) continue;
                
                // Cek konflik: ruangan sama, hari sama, waktu overlap
                if (schedule.ruangan === newSchedule.ruangan && schedule.hari === newSchedule.hari) {
                    const newStart = timeToMinutes(newSchedule.jamMulai);
                    const newEnd = timeToMinutes(newSchedule.jamSelesai);
                    const existingStart = timeToMinutes(schedule.jamMulai);
                    const existingEnd = timeToMinutes(schedule.jamSelesai);
                    
                    // Cek overlap: (start1 < end2) && (start2 < end1)
                    if (newStart < existingEnd && existingStart < newEnd) {
                        conflicts.push(schedule);
                    }
                }
            }
            
            return conflicts;
        }
        
        function validateForm() {
            const ruangan = document.getElementById('ruangan').value;
            const hari = document.getElementById('hari').value;
            const jamMulai = document.getElementById('jamMulai').value;
            const jamSelesai = document.getElementById('jamSelesai').value;
            const kelas = document.getElementById('kelas').value;
            const status = document.getElementById('status').value;
            const editId = document.getElementById('editId').value;
            
            // Validasi basic
            if (!ruangan || !hari || !jamMulai || !jamSelesai || !status) {
                showAlert('Semua field wajib diisi!', 'danger');
                return false;
            }
            
            // Validasi waktu
            if (timeToMinutes(jamMulai) >= timeToMinutes(jamSelesai)) {
                showAlert('Jam mulai harus lebih awal dari jam selesai!', 'danger');
                return false;
            }
            
            // Cek konflik jadwal
            const newSchedule = { ruangan, hari, jamMulai, jamSelesai, kelas, status };
            const conflicts = checkTimeConflict(newSchedule, schedules, editId);
            
            if (conflicts.length > 0) {
                let conflictMessage = 'Jadwal bentrok dengan:<br>';
                conflicts.forEach(conflict => {
                    const kelasInfo = conflict.kelas || 'Slot Kosong';
                    conflictMessage += `• ${conflict.hari} ${conflict.jamMulai}-${conflict.jamSelesai} (${kelasInfo})<br>`;
                });
                showAlert(conflictMessage, 'danger');
                return false;
            }
            
            return true;
        }
        
        document.getElementById('scheduleForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateForm()) return;
            
            const ruangan = document.getElementById('ruangan').value;
            const hari = document.getElementById('hari').value;
            const jamMulai = document.getElementById('jamMulai').value;
            const jamSelesai = document.getElementById('jamSelesai').value;
            const kelas = document.getElementById('kelas').value;
            const status = document.getElementById('status').value;
            const editId = document.getElementById('editId').value;
            
            if (editId) {
                // Edit existing
                const index = schedules.findIndex(s => s.id == editId);
                if (index !== -1) {
                    schedules[index] = {
                        id: parseInt(editId),
                        ruangan, hari, jamMulai, jamSelesai, kelas, status
                    };
                    showAlert('Jadwal berhasil diupdate!');
                }
            } else {
                // Add new
                schedules.push({
                    id: nextId++,
                    ruangan, hari, jamMulai, jamSelesai, kelas, status
                });
                showAlert('Jadwal berhasil ditambahkan!');
            }
            
            resetForm();
            loadData();
            loadSchedule();
        });
        
        function resetForm() {
            document.getElementById('scheduleForm').reset();
            document.getElementById('editId').value = '';
        }
        
        function editSchedule(id) {
            const schedule = schedules.find(s => s.id === id);
            if (schedule) {
                document.getElementById('editId').value = schedule.id;
                document.getElementById('ruangan').value = schedule.ruangan;
                document.getElementById('hari').value = schedule.hari;
                document.getElementById('jamMulai').value = schedule.jamMulai;
                document.getElementById('jamSelesai').value = schedule.jamSelesai;
                document.getElementById('kelas').value = schedule.kelas;
                document.getElementById('status').value = schedule.status;
            }
        }
        
        function deleteSchedule(id) {
            if (confirm('Yakin ingin menghapus jadwal ini?')) {
                schedules = schedules.filter(s => s.id !== id);
                showAlert('Jadwal berhasil dihapus!');
                loadData();
                loadSchedule();
            }
        }
        
        function loadData() {
            const tbody = document.getElementById('dataBody');
            const filterRuangan = document.getElementById('filterRuangan').value;
            
            let filteredSchedules = schedules;
            if (filterRuangan) {
                filteredSchedules = schedules.filter(s => s.ruangan === filterRuangan);
            }
            
            tbody.innerHTML = '';
            
            filteredSchedules.forEach(schedule => {
                const kelasDisplay = schedule.kelas || '<em>Slot Kosong</em>';
                const statusBadge = {
                    'tetap': 'bg-success',
                    'sementara': 'bg-warning',
                    'kosong': 'bg-secondary'
                };
                
                tbody.innerHTML += `
                    <tr>
                        <td>${schedule.ruangan}</td>
                        <td>${schedule.hari}</td>
                        <td>${schedule.jamMulai} - ${schedule.jamSelesai}</td>
                        <td>${kelasDisplay}</td>
                        <td><span class="badge ${statusBadge[schedule.status]}">${schedule.status}</span></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editSchedule(${schedule.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteSchedule(${schedule.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        
        function loadSchedule() {
            const tbody = document.getElementById('scheduleBody');
            const filterRuangan = document.getElementById('filterRuangan').value;
            
            let filteredSchedules = schedules;
            if (filterRuangan) {
                filteredSchedules = schedules.filter(s => s.ruangan === filterRuangan);
            }
            
            tbody.innerHTML = '';
            
            timeSlots.forEach(timeSlot => {
                const [startTime] = timeSlot.split('-');
                let row = `<tr><td class="time-header">${timeSlot}</td>`;
                
                days.forEach(day => {
                    const daySchedules = filteredSchedules.filter(s => {
                        if (s.hari !== day) return false;
                        
                        const scheduleStart = timeToMinutes(s.jamMulai);
                        const scheduleEnd = timeToMinutes(s.jamSelesai);
                        const slotStart = timeToMinutes(startTime);
                        const slotEnd = slotStart + 60; // 1 jam slot
                        
                        // Cek apakah schedule overlap dengan slot ini
                        return scheduleStart < slotEnd && slotStart < scheduleEnd;
                    });
                    
                    if (daySchedules.length > 0) {
                        const schedule = daySchedules[0];
                        const kelasInfo = schedule.kelas || 'Kosong';
                        const cellClass = schedule.status === 'kosong' ? 'slot-empty' : 'slot-occupied';
                        row += `<td class="time-slot ${cellClass}">
                            <small><strong>${kelasInfo}</strong><br>
                            ${schedule.jamMulai}-${schedule.jamSelesai}<br>
                            <em>${schedule.ruangan}</em></small>
                        </td>`;
                    } else {
                        row += `<td class="time-slot slot-empty"></td>`;
                    }
                });
                
                row += '</tr>';
                tbody.innerHTML += row;
            });
        }
        
        // Load initial data
        loadData();
        loadSchedule();
    </script>
</body>
</html>
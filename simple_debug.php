<?php
// simple_debug.php - Debug login tanpa config.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Koneksi database langsung
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "booking_ruangan"; // Ganti dengan nama database Anda

// Fungsi clean input sederhana
function cleanInput($data) {
    if ($data === null || $data === '') {
        return '';
    }
    return trim(stripslashes(htmlspecialchars($data)));
}

$debug_info = [];
$conn = null;

try {
    // Koneksi ke database
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $debug_info[] = "✅ Koneksi database berhasil";
    
    // Cek tabel users
    $sql = "SHOW TABLES LIKE 'users'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $debug_info[] = "✅ Tabel 'users' ditemukan";
    } else {
        $debug_info[] = "❌ Tabel 'users' tidak ditemukan";
    }
    
    // Cek jumlah users
    $sql = "SELECT COUNT(*) as total FROM users";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $debug_info[] = "📊 Total users: " . $row['total'];
    }
    
    // Tampilkan daftar username
    $sql = "SELECT id, username, nama_lengkap, role FROM users LIMIT 10";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $debug_info[] = "👥 Daftar users:";
        while ($row = $result->fetch_assoc()) {
            $debug_info[] = "   - Username: '{$row['username']}', Nama: {$row['nama_lengkap']}, Role: {$row['role']}";
        }
    } else {
        $debug_info[] = "❌ Tidak ada data user";
    }
    
} catch (Exception $e) {
    $debug_info[] = "❌ Error: " . $e->getMessage();
}

// Test login jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["test_username"]) && isset($_POST["test_password"])) {
    $test_username = cleanInput($_POST["test_username"]);
    $test_password = $_POST["test_password"];
    
    $debug_info[] = "\n🔍 Testing login dengan username: '$test_username'";
    
    if ($conn) {
        $sql = "SELECT id, username, password, nama_lengkap, role FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $test_username);
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {
                    $debug_info[] = "✅ Username ditemukan";
                    
                    $stmt->bind_result($id, $username, $hashed_password, $nama_lengkap, $role);
                    $stmt->fetch();
                    
                    $debug_info[] = "📝 User ID: $id, Nama: '$nama_lengkap', Role: '$role'";
                    $debug_info[] = "🔐 Password hash: " . substr($hashed_password, 0, 50) . "...";
                    
                    // Test password
                    if (password_verify($test_password, $hashed_password)) {
                        $debug_info[] = "✅ Password COCOK! Login seharusnya berhasil";
                    } else {
                        $debug_info[] = "❌ Password TIDAK COCOK";
                        
                        // Cek apakah password plain text
                        if ($test_password === $hashed_password) {
                            $debug_info[] = "⚠️  Password di database adalah plain text (tidak di-hash)";
                        } else {
                            $debug_info[] = "💡 Password di database sudah di-hash, tapi tidak cocok dengan input";
                        }
                    }
                } else {
                    $debug_info[] = "❌ Username '$test_username' tidak ditemukan";
                }
            }
            $stmt->close();
        }
    }
}

// Buat user admin default jika diminta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_admin"])) {
    if ($conn) {
        $admin_username = "admin";
        $admin_password = "password"; // Password default
        $admin_hash = password_hash($admin_password, PASSWORD_DEFAULT);
        
        // Cek apakah admin sudah ada
        $check_sql = "SELECT id FROM users WHERE username = ?";
        if ($check_stmt = $conn->prepare($check_sql)) {
            $check_stmt->bind_param("s", $admin_username);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows == 0) {
                // Admin belum ada, buat baru
                $sql = "INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $nama_admin = "Administrator";
                    $role_admin = "admin";
                    $stmt->bind_param("ssss", $admin_username, $admin_hash, $nama_admin, $role_admin);
                    
                    if ($stmt->execute()) {
                        $debug_info[] = "✅ User admin berhasil dibuat!";
                        $debug_info[] = "   Username: admin";
                        $debug_info[] = "   Password: password";
                    } else {
                        $debug_info[] = "❌ Gagal membuat user admin: " . $stmt->error;
                    }
                    $stmt->close();
                }
            } else {
                $debug_info[] = "⚠️  User admin sudah ada";
            }
            $check_stmt->close();
        }
    }
}

// Buat user mahasiswa default jika diminta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_mahasiswa"])) {
    if ($conn) {
        $mhs_username = "mahasiswa";
        $mhs_password = "mahasiswa123"; // Password default
        $mhs_hash = password_hash($mhs_password, PASSWORD_DEFAULT);
        
        // Cek apakah mahasiswa sudah ada
        $check_sql = "SELECT id FROM users WHERE username = ?";
        if ($check_stmt = $conn->prepare($check_sql)) {
            $check_stmt->bind_param("s", $mhs_username);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows == 0) {
                // Mahasiswa belum ada, buat baru
                $sql = "INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $nama_mhs = "Mahasiswa Test";
                    $role_mhs = "mahasiswa";
                    $stmt->bind_param("ssss", $mhs_username, $mhs_hash, $nama_mhs, $role_mhs);
                    
                    if ($stmt->execute()) {
                        $debug_info[] = "✅ User mahasiswa berhasil dibuat!";
                        $debug_info[] = "   Username: mahasiswa";
                        $debug_info[] = "   Password: mahasiswa123";
                    } else {
                        $debug_info[] = "❌ Gagal membuat user mahasiswa: " . $stmt->error;
                    }
                    $stmt->close();
                }
            } else {
                $debug_info[] = "⚠️  User mahasiswa sudah ada";
            }
            $check_stmt->close();
        }
    }
}

// Buat user custom jika diminta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_custom"])) {
    if ($conn && !empty($_POST["custom_username"]) && !empty($_POST["custom_password"])) {
        $custom_username = cleanInput($_POST["custom_username"]);
        $custom_password = $_POST["custom_password"];
        $custom_nama = cleanInput($_POST["custom_nama"]);
        $custom_role = $_POST["custom_role"];
        
        $custom_hash = password_hash($custom_password, PASSWORD_DEFAULT);
        
        // Cek apakah username sudah ada
        $check_sql = "SELECT id FROM users WHERE username = ?";
        if ($check_stmt = $conn->prepare($check_sql)) {
            $check_stmt->bind_param("s", $custom_username);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows == 0) {
                // Username belum ada, buat baru
                $sql = "INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ssss", $custom_username, $custom_hash, $custom_nama, $custom_role);
                    
                    if ($stmt->execute()) {
                        $debug_info[] = "✅ User '$custom_username' berhasil dibuat!";
                        $debug_info[] = "   Username: $custom_username";
                        $debug_info[] = "   Password: $custom_password";
                        $debug_info[] = "   Nama: $custom_nama";
                        $debug_info[] = "   Role: $custom_role";
                    } else {
                        $debug_info[] = "❌ Gagal membuat user: " . $stmt->error;
                    }
                    $stmt->close();
                }
            } else {
                $debug_info[] = "⚠️  Username '$custom_username' sudah ada";
            }
            $check_stmt->close();
        }
    } else {
        $debug_info[] = "❌ Username dan password tidak boleh kosong";
    }
}

if ($conn) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Login Simple</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        .debug-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            font-family: 'Courier New', monospace;
            white-space: pre-line;
            font-size: 14px;
        }
        .alert-success { color: #155724; background-color: #d4edda; }
        .alert-danger { color: #721c24; background-color: #f8d7da; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">🔍 Debug Login System</h1>
        
        <div class="row">
            <div class="col-md-8">
                <h3>Debug Information</h3>
                <div class="debug-box">
<?php 
foreach ($debug_info as $info) {
    echo htmlspecialchars($info) . "\n";
}
?>
                </div>
                
                <div class="alert alert-info">
                    <h5>📋 Checklist:</h5>
                    <ul>
                        <li>Pastikan database name benar di line 7</li>
                        <li>Cek apakah ada data users di database</li>
                        <li>Test login dengan username dan password yang ada</li>
                        <li>Jika belum ada user, buat admin default</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Test Login -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>🧪 Test Login</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="test_username" class="form-label">Username:</label>
                                <input type="text" class="form-control" id="test_username" name="test_username" 
                                       value="<?php echo isset($_POST['test_username']) ? htmlspecialchars($_POST['test_username']) : 'admin'; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="test_password" class="form-label">Password:</label>
                                <input type="text" class="form-control" id="test_password" name="test_password" 
                                       value="<?php echo isset($_POST['test_password']) ? htmlspecialchars($_POST['test_password']) : 'password'; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Test Login</button>
                        </form>
                    </div>
                </div>
                
                <!-- Create Admin -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>👤 Buat Admin Default</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            Username: admin<br>
                            Password: password
                        </p>
                        <form method="post">
                            <button type="submit" name="create_admin" class="btn btn-success w-100">
                                Buat User Admin
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Create Mahasiswa -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>🎓 Buat Mahasiswa Default</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            Username: mahasiswa<br>
                            Password: mahasiswa123
                        </p>
                        <form method="post">
                            <button type="submit" name="create_mahasiswa" class="btn btn-info w-100">
                                Buat User Mahasiswa
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Create Custom User -->
                <div class="card">
                    <div class="card-header">
                        <h5>➕ Buat User Custom</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="custom_username" class="form-label">Username:</label>
                                <input type="text" class="form-control" id="custom_username" name="custom_username" required>
                            </div>
                            <div class="mb-3">
                                <label for="custom_password" class="form-label">Password:</label>
                                <input type="password" class="form-control" id="custom_password" name="custom_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="custom_nama" class="form-label">Nama Lengkap:</label>
                                <input type="text" class="form-control" id="custom_nama" name="custom_nama" required>
                            </div>
                            <div class="mb-3">
                                <label for="custom_role" class="form-label">Role:</label>
                                <select class="form-select" id="custom_role" name="custom_role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="mahasiswa">Mahasiswa</option>
                                </select>
                            </div>
                            <button type="submit" name="create_custom" class="btn btn-primary w-100">
                                Buat User
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
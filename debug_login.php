<?php
// debug_login.php - File untuk debug masalah login
session_start();
require_once 'config.php';

// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$debug_info = [];

// Test koneksi database
$conn = connectDB();
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
$debug_info[] = "✅ Koneksi database berhasil";

// Cek apakah tabel users ada
$sql = "SHOW TABLES LIKE 'users'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $debug_info[] = "✅ Tabel 'users' ditemukan";
} else {
    $debug_info[] = "❌ Tabel 'users' tidak ditemukan";
}

// Cek struktur tabel users
$sql = "DESCRIBE users";
$result = $conn->query($sql);
if ($result) {
    $debug_info[] = "✅ Struktur tabel users:";
    while ($row = $result->fetch_assoc()) {
        $debug_info[] = "   - " . $row['Field'] . " (" . $row['Type'] . ")";
    }
}

// Cek jumlah data user
$sql = "SELECT COUNT(*) as total FROM users";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$debug_info[] = "📊 Total users di database: " . $row['total'];

// Tampilkan semua username yang ada (tanpa password)
$sql = "SELECT id, username, nama_lengkap, role FROM users";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $debug_info[] = "👥 Daftar users:";
    while ($row = $result->fetch_assoc()) {
        $debug_info[] = "   - ID: {$row['id']}, Username: '{$row['username']}', Nama: {$row['nama_lengkap']}, Role: {$row['role']}";
    }
}

// Form untuk test login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $test_username = cleanInput($_POST["test_username"]);
    $test_password = $_POST["test_password"];
    
    $debug_info[] = "🔍 Testing login dengan username: '$test_username'";
    
    // Cari user
    $sql = "SELECT id, username, password, nama_lengkap, role FROM users WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $test_username);
        
        if ($stmt->execute()) {
            $stmt->store_result();
            
            if ($stmt->num_rows == 1) {
                $debug_info[] = "✅ Username ditemukan di database";
                
                $stmt->bind_result($id, $username, $hashed_password, $nama_lengkap, $role);
                $stmt->fetch();
                
                $debug_info[] = "📝 Data user: ID=$id, Username='$username', Nama='$nama_lengkap', Role='$role'";
                $debug_info[] = "🔐 Password hash di database: " . substr($hashed_password, 0, 50) . "...";
                
                // Test password verification
                if (password_verify($test_password, $hashed_password)) {
                    $debug_info[] = "✅ Password COCOK - Login seharusnya berhasil!";
                } else {
                    $debug_info[] = "❌ Password TIDAK COCOK";
                    $debug_info[] = "💡 Kemungkinan password di database tidak di-hash dengan benar";
                    
                    // Test apakah password di database adalah plain text
                    if ($test_password === $hashed_password) {
                        $debug_info[] = "⚠️  Password di database adalah PLAIN TEXT (tidak di-hash)";
                        $debug_info[] = "🔧 Solusi: Hash password dengan password_hash()";
                    }
                }
            } else {
                $debug_info[] = "❌ Username '$test_username' TIDAK DITEMUKAN di database";
                $debug_info[] = "💡 Periksa ejaan username atau buat user baru";
            }
        }
        $stmt->close();
    }
}

// Fungsi untuk membuat user baru dengan password yang benar
if (isset($_POST['create_user'])) {
    $new_username = cleanInput($_POST['new_username']);
    $new_password = $_POST['new_password'];
    $new_nama = cleanInput($_POST['new_nama']);
    $new_role = $_POST['new_role'];
    
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssss", $new_username, $hashed_password, $new_nama, $new_role);
        
        if ($stmt->execute()) {
            $debug_info[] = "✅ User baru berhasil dibuat: '$new_username'";
        } else {
            $debug_info[] = "❌ Gagal membuat user: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        .debug-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            font-family: monospace;
            white-space: pre-line;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Debug Login System</h1>
        
        <div class="row">
            <div class="col-md-8">
                <h3>Debug Information</h3>
                <div class="debug-info">
<?php 
foreach ($debug_info as $info) {
    echo htmlspecialchars($info) . "\n";
}
?>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Test Login Form -->
                <h3>Test Login</h3>
                <form method="post" class="mb-4">
                    <div class="mb-3">
                        <label for="test_username" class="form-label">Username:</label>
                        <input type="text" class="form-control" id="test_username" name="test_username" required>
                    </div>
                    <div class="mb-3">
                        <label for="test_password" class="form-label">Password:</label>
                        <input type="password" class="form-control" id="test_password" name="test_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Test Login</button>
                </form>
                
                <!-- Create User Form -->
                <h3>Buat User Baru</h3>
                <form method="post">
                    <div class="mb-3">
                        <label for="new_username" class="form-label">Username:</label>
                        <input type="text" class="form-control" id="new_username" name="new_username" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Password:</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_nama" class="form-label">Nama Lengkap:</label>
                        <input type="text" class="form-control" id="new_nama" name="new_nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_role" class="form-label">Role:</label>
                        <select class="form-control" id="new_role" name="new_role" required>
                            <option value="admin">Admin</option>
                            <option value="mahasiswa">Mahasiswa</option>
                        </select>
                    </div>
                    <button type="submit" name="create_user" class="btn btn-success">Buat User</button>
                </form>
            </div>
        </div>
        
        <div class="mt-4">
            <h3>Kemungkinan Solusi</h3>
            <div class="alert alert-info">
                <h5>Jika username tidak ditemukan:</h5>
                <ul>
                    <li>Periksa ejaan username</li>
                    <li>Buat user baru menggunakan form di atas</li>
                    <li>Cek apakah ada spasi atau karakter khusus</li>
                </ul>
                
                <h5>Jika password tidak cocok:</h5>
                <ul>
                    <li>Password di database mungkin plain text (tidak di-hash)</li>
                    <li>Gunakan script ini untuk membuat user baru dengan password yang benar</li>
                    <li>Atau update password yang ada dengan hash yang benar</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
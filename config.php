


<?php
// config.php - File konfigurasi sistem
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'booking_ruangan');

// Menghubungkan ke database
function connectDB() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Periksa koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    // Set karakter encoding
    $conn->set_charset("utf8");
    
    return $conn;
}

// Fungsi untuk membersihkan input
function cleanInput($data) {
  if ($data === null || $data === '') {
      return '';
  }
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

// Fungsi untuk mengecek apakah pengguna sudah login
function isLoggedIn() {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
        return true;
    }
    return false;
}

// Fungsi untuk mengecek apakah pengguna adalah admin
function isAdmin() {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        return true;
    }
    return false;
}

// Fungsi untuk mengecek apakah pengguna adalah mahasiswa
function isMahasiswa() {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'mahasiswa') {
        return true;
    }
    return false;
}

// Fungsi untuk redirect jika tidak memiliki akses
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Fungsi untuk redirect jika bukan admin
function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header("Location: index.php");
        exit;
    }
}

function redirect($url) {
  header("Location: $url");
  exit();
}

// Fungsi untuk redirect jika bukan mahasiswa
function redirectIfNotMahasiswa() {
    if (!isMahasiswa()) {
        header("Location: index.php");
        exit;
    }
}

// Fungsi untuk menampilkan pesan
function showMessage($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

// Fungsi untuk mendapatkan pesan dan menghapusnya
function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'];
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Fungsi untuk mendapatkan data user berdasarkan id
function getUserById($id) {
    $conn = connectDB();
    $id = (int)$id;
    
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $user;
}

// Fungsi untuk mendapatkan nama ruangan berdasarkan id
function getRuanganById($id) {
    $conn = connectDB();
    $id = (int)$id;
    
    $query = "SELECT * FROM ruangan WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $ruangan = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $ruangan;
}

// Format tanggal Indonesia
function formatTanggal($date) {
    $bulan = array (
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    
}

// Format waktu 24 jam ke format jam:menit
function formatWaktu($time) {
    return date('H:i', strtotime($time));
}
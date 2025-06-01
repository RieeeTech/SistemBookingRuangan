<?php
// login.php - Halaman login untuk admin dan mahasiswa
session_start();
require_once 'config.php';

// Fungsi untuk membersihkan input


// Jika sudah login, redirect ke halaman utama
if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';

// Proses login saat form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = cleanInput($_POST["username"]);
    $password = $_POST["password"];
    
    if (empty($username) || empty($password)) {
        $error = "Username dan password tidak boleh kosong";
    } else {
        $conn = connectDB();
        
        // Ambil data user berdasarkan username
        $sql = "SELECT tu.id_user, tu.username, tu.password, tu.role, tu.nim, tm.nama 
                FROM tb_user tu
                LEFT JOIN tb_mahasiswa tm ON tu.nim = tm.nim
                WHERE tu.username = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $username_db, $password_db, $role, $nim, $nama_lengkap);
                    
                    if ($stmt->fetch()) {
                        // Password tidak di-hash, jadi dibandingkan langsung
                        if ($password === $password_db) {
                            // Login sukses
                            $_SESSION["user_id"] = $id;
                            $_SESSION["username"] = $username_db;
                            $_SESSION["role"] = $role;
                            $_SESSION["nama_lengkap"] = $nama_lengkap ?? 'Admin';
                            $_SESSION["nim"] = $nim;
                            
                            header("Location: index.php");
                            exit;
                        } else {
                            $error = "Username atau password salah";
                        }
                    }
                } else {
                    $error = "Username atau password salah";
                }
            } else {
                $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
            }
            $stmt->close();
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Booking Ruangan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h2 {
            color: #343a40;
            font-weight: 600;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            width: 100%;
            padding: 10px;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0b5ed7;
        }
        .form-label {
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h2>Sistem Booking Ruangan</h2>
                <p class="text-muted">Silakan login untuk melanjutkan</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
                
                <div class="mt-3 text-center">
                    <p class="text-muted">
                        <small>© <?php echo date("Y"); ?> Sistem Booking Ruangan</small>
                    </p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
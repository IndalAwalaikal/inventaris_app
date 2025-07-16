<?php
session_start();
require_once 'config/db.php';

$error_message = '';
$success_message = '';

// Inisialisasi variabel default
$current_username = $_SESSION['admin_username'] ?? 'Unknown';
$current_profile_picture = 'default.jpg'; // Default gambar jika tidak ada

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: /inventaris_app/login.php");
    exit();
}

$user_id = $_SESSION['admin_id'];

// Debugging: Periksa ID pengguna
if (empty($user_id)) {
    error_log("User ID is empty or not set in session.");
    $error_message = "Sesi pengguna tidak valid. Silakan login ulang.";
} else {
    // Ambil data pengguna termasuk foto profil
    try {
        $stmt = $pdo->prepare("SELECT username, profile_picture FROM users WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("Query result: " . print_r($user, true)); // Debugging
        if ($user) {
            $current_username = $user['username'];
            $current_profile_picture = $user['profile_picture'] ?: 'default.jpg'; // Override dengan data dari DB
        } else {
            error_log("No user found with ID: $user_id");
            $error_message = "Data pengguna tidak ditemukan.";
        }
    } catch (PDOException $e) {
        error_log("Fetch user error: " . $e->getMessage());
        $error_message = "Terjadi kesalahan saat mengambil data pengguna: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['new_username'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $profile_picture = $_FILES['profile_picture'] ?? null;

    if (empty($new_username) || empty($new_password) || empty($confirm_password)) {
        $error_message = "Semua kolom harus diisi.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Password dan konfirmasi password tidak cocok.";
    } else {
        try {
            // Proses upload foto
            $profile_picture_name = $current_profile_picture;
            if ($profile_picture && $profile_picture['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!in_array($profile_picture['type'], $allowed_types)) {
                    $error_message = "Hanya file JPG, JPEG, atau PNG yang diperbolehkan.";
                } elseif ($profile_picture['size'] > 10 * 1024 * 1024) { // Maks 10MB
                    $error_message = "Ukuran file tidak boleh lebih dari 10MB.";
                } else {
                    $upload_dir = 'assets/images/profiles/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $profile_picture_name = uniqid() . '_' . basename($profile_picture['name']);
                    $target_file = $upload_dir . $profile_picture_name;
                    if (!move_uploaded_file($profile_picture['tmp_name'], $target_file)) {
                        $error_message = "Gagal mengunggah foto. Periksa izin direktori atau path.";
                    }
                }
            }

            if (empty($error_message)) {
                // Password disimpan tanpa hash
                $stmt = $pdo->prepare("UPDATE users SET username = :username, password = :password, profile_picture = :profile_picture WHERE id = :id");
                $stmt->bindParam(':username', $new_username, PDO::PARAM_STR);
                $stmt->bindParam(':password', $new_password, PDO::PARAM_STR);
                $stmt->bindParam(':profile_picture', $profile_picture_name, PDO::PARAM_STR);
                $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                $stmt->execute();
                error_log("Update executed for user ID: $user_id"); // Debugging

                // Perbarui sesi dengan username baru
                $_SESSION['admin_username'] = $new_username;
                $success_message = "Username, password, dan foto profil berhasil diperbarui.";
            }
        } catch (PDOException $e) {
            error_log("Update profile error: " . $e->getMessage());
            $error_message = "Terjadi kesalahan saat memperbarui data: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - Inventaris App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.6.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        .profile-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .form-control {
            border-radius: 8px;
        }
        .btn-primary {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            display: block;
            margin: 0 auto 20px;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'includes/header.php'; ?>
    <div class="profile-container">
        <h2><i class="fas fa-user-edit me-2"></i>Edit Profil</h2>
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <img src="assets/images/profiles/<?php echo htmlspecialchars($current_profile_picture); ?>" alt="Profile Picture" class="profile-picture">
        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="new_username" class="form-label">Username Baru</label>
                <input type="text" class="form-control" id="new_username" name="new_username" value="<?php echo htmlspecialchars($current_username); ?>" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">Password Baru</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="mb-3">
                <label for="profile_picture" class="form-label">Foto Profil</label>
                <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/jpg">
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
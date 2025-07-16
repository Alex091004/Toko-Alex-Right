<?php
include 'init.php';
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

// Jika permintaan POST untuk membatalkan pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $user_id = $_SESSION['user']['id'];

    // Periksa status pesanan dan kepemilikan oleh user
    $stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        $status = strtolower($order['status']);

        // Hanya bisa dibatalkan jika masih pending atau diproses
        if (in_array($status, ['pending', 'diproses'])) {
            $update = $conn->prepare("UPDATE orders SET status = 'dibatalkan', updated_at = NOW() WHERE id = ?");
            $update->bind_param("i", $order_id);
            $update->execute();
        }
    }

    // Redirect ke halaman pesanan setelah membatalkan
    header('Location: pesanan.php');
    exit;
}

// Jika akses langsung tanpa POST
header('Location: index.php');
exit;

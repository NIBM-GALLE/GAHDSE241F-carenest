<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = $_SESSION['user_id'] ?? null;
    if (!$staff_id) {
        die('Unauthorized: Please login.');
    }

    $child_id = $_POST['child_id'] ?? null;
    $description = $_POST['description'] ?? null;
    $date = $_POST['date'] ?? null;

    if (!$child_id || !$description || !$date) {
        die('Please fill in all required fields.');
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $tmp_path = $_FILES['image']['tmp_name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            die('Error: Invalid image file type.');
        }

        $upload_dir = 'uploads/activities/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $new_filename = uniqid('activity_', true) . '.' . $ext;
        $destination = $upload_dir . $new_filename;

        if (move_uploaded_file($tmp_path, $destination)) {
            $sql = "INSERT INTO activities (child_id, staff_id, description, date, image_url) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iisss', $child_id, $staff_id, $description, $date, $destination);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Activity added successfully.";
                header('Location: add_activity.html');
                exit();
            } else {
                die('Database error: ' . $conn->error);
            }
        } else {
            die('Failed to upload image.');
        }
    } else {
        die('Please upload an image file.');
    }
} else {
    die('Invalid request method.');
}
?>

<?php
// Include database connection
include 'db_connection.php';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure all form fields are set before accessing them
    $child_name = isset($_POST['child_name']) ? $_POST['child_name'] : '';
    $dob = isset($_POST['dob']) ? $_POST['dob'] : '';
    $allergies = isset($_POST['allergies']) ? $_POST['allergies'] : '';
    $medications = isset($_POST['medications']) ? $_POST['medications'] : '';
    $medical_conditions = isset($_POST['medical_conditions']) ? $_POST['medical_conditions'] : '';

    $parent_name = isset($_POST['parent_name']) ? $_POST['parent_name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $emergency_contact_name = isset($_POST['emergency_contact_name']) ? $_POST['emergency_contact_name'] : '';
    $emergency_contact_phone = isset($_POST['emergency_contact_phone']) ? $_POST['emergency_contact_phone'] : '';
    $staff_id = isset($_POST['staff_id']) ? $_POST['staff_id'] : '';

    // Check if Parent Exists
    $checkParentQuery = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($checkParentQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $parent_id = null;

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $parent_id = $row['id']; // Use existing parent
    } else {
        // Insert Parent
        if (!empty($parent_name)) {
            $username = strtolower(str_replace(' ', '', $parent_name)) . rand(100, 999);
            $password = password_hash("default123", PASSWORD_DEFAULT); // Default password
            $insertParentQuery = "INSERT INTO users (name, email, phone, role, username, password) VALUES (?, ?, ?, 'parent', ?, ?)";
            $stmt = $conn->prepare($insertParentQuery);
            $stmt->bind_param("sssss", $parent_name, $email, $phone, $username, $password);

            if ($stmt->execute()) {
                $parent_id = $stmt->insert_id;
            } else {
                die("Error registering parent: " . $conn->error);
            }
        } else {
            die("Parent name is required.");
        }
    }

    // Insert Child
    if (!empty($child_name)) {
        $insertChildQuery = "INSERT INTO children (name, date_of_birth, parent_id, assigned_staff_id, allergies, medications, medical_conditions, emergency_contact_name, emergency_contact_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertChildQuery);
        $stmt->bind_param("ssissssss", $child_name, $dob, $parent_id, $staff_id, $allergies, $medications, $medical_conditions, $emergency_contact_name, $emergency_contact_phone);

        if ($stmt->execute()) {
            echo "Child registered successfully!";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Child name is required.";
    }
}

$conn->close();
?>


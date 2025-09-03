<?php
session_start();
include('config/config.php');
$admin_id = $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aPurpose = trim($_POST['aPurpose']);
    $amount = trim($_POST['amount']);
    $note = !empty($_POST['note']) ? trim($_POST['note']) : null;

    // Validate inputs
    if (empty($aPurpose) || empty($amount) || !is_numeric($amount)) {
        echo "warning"; // If required fields are not filled or invalid
        exit();
    }

    // Handle file upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = "../assets/img/expense/";
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));

        // Allowed file types
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        // Validate file type
        if (!in_array($imageFileType, $allowedTypes)) {
            echo "error: Only JPG, JPEG, PNG, and GIF files are allowed.";
            exit();
        }

        // Validate file size (max 5MB)
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            echo "error: File size exceeds the maximum limit of 5MB.";
            exit();
        }

        // Generate a unique filename
        $uniqueFileName = uniqid() . '.' . $imageFileType;
        $targetFile = $targetDir . $uniqueFileName;

        // Check if the file is an actual image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            // Move uploaded file
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                $image = $uniqueFileName; // Store only the unique file name in the database
            } else {
                echo "error: Failed to upload the image.";
                exit();
            }
        } else {
            echo "error: The uploaded file is not a valid image.";
            exit();
        }
    }

    // Database insertion
    try {
        // Begin a transaction
        $mysqli->autocommit(false);

        $postQuery = "INSERT INTO expense (salesman_id, purpose, amount, note, image, creation_date) VALUES (?, ?, ?, ?, ?, NOW())";
        $postStmt = $mysqli->prepare($postQuery);

        if ($postStmt) {
            // Bind parameters
            $postStmt->bind_param('ssdss', $admin_id,$aPurpose, $amount, $note, $image);

            // Execute the statement
            if ($postStmt->execute()) {
                // Commit the transaction
                $mysqli->commit();
                echo "success";
            } else {
                // Rollback if the insertion fails
                $mysqli->rollback();
                echo "error: Failed to insert expense entry.";
            }
        } else {
            echo "error: Failed to prepare the SQL statement.";
        }
    } catch (Exception $e) {
        // Rollback if any exception occurs
        $mysqli->rollback();
        echo "error: " . $e->getMessage();
    } finally {
        $mysqli->autocommit(true); // Re-enable autocommit
    }
}
?>

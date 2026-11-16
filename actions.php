<?php
require_once 'config.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $deadline = mysqli_real_escape_string($conn, $_POST['deadline']);
    $category = isset($_POST['category']) && $_POST['category'] == 'Shared' ? 'Shared' : 'Personal';

    $image_name = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        if (in_array($file_ext, $allowed)) {
            $image_name = time() . "_" . uniqid() . "." . $file_ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
        }
    }

    $query = "INSERT INTO todos (title, description, deadline, category, image, completed) VALUES ('$title', '$description', '$deadline', '$category', '$image_name', 0)";
    mysqli_query($conn, $query);

    header("Location: index.php?category=" . urlencode($category));
    exit;
}

if ($action == 'toggle' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $category = isset($_GET['category']) ? $_GET['category'] : 'Personal';

    mysqli_query($conn, "UPDATE todos SET completed = IF(completed=1, 0, 1) WHERE id = $id");

    header("Location: index.php?category=" . urlencode($category));
    exit;
}

if ($action == 'edit' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $deadline = mysqli_real_escape_string($conn, $_POST['deadline']);
    $category = isset($_POST['category']) && $_POST['category'] == 'Shared' ? 'Shared' : 'Personal';

    $query = "UPDATE todos SET title = '$title', description = '$description', deadline = '$deadline', category = '$category'";

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        if (in_array($file_ext, $allowed)) {
            $res = mysqli_query($conn, "SELECT image FROM todos WHERE id = $id");
            if ($row = mysqli_fetch_assoc($res)) {
                if (!empty($row['image']) && file_exists($upload_dir . $row['image'])) {
                    unlink($upload_dir . $row['image']);
                }
            }
            $image_name = time() . "_" . uniqid() . "." . $file_ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
            $query .= ", image = '$image_name'";
        }
    }

    $query .= " WHERE id = $id";
    mysqli_query($conn, $query);

    header("Location: index.php?category=" . urlencode($category));
    exit;
}

if ($action == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $category = isset($_GET['category']) ? $_GET['category'] : 'Personal';

    $res = mysqli_query($conn, "SELECT image FROM todos WHERE id = $id");
    if ($row = mysqli_fetch_assoc($res)) {
        if (!empty($row['image']) && file_exists("uploads/" . $row['image'])) {
            unlink("uploads/" . $row['image']);
        }
    }

    mysqli_query($conn, "DELETE FROM todos WHERE id = $id");

    header("Location: index.php?category=" . urlencode($category));
    exit;
}

header("Location: index.php");
exit;
?>

<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-API-KEY");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'OPTIONS') {
    http_response_code(200);
    exit;
}

$api_key = "";
if (isset($_SERVER['HTTP_X_API_KEY'])) {
    $api_key = $_SERVER['HTTP_X_API_KEY'];
} else if (isset($_GET['api_key'])) {
    $api_key = $_GET['api_key'];
}

if ($api_key !== "portal123") {
    http_response_code(401);
    echo json_encode(array(
        "status" => "error",
        "message" => "Unauthorized: Invalid or missing API Key"
    ));
    exit;
}

require_once 'config.php';

if ($method == 'GET') {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $res = mysqli_query($conn, "SELECT * FROM todos WHERE id = $id");
        if ($res && mysqli_num_rows($res) > 0) {
            $task = mysqli_fetch_assoc($res);
            http_response_code(200);
            echo json_encode(array(
                "status" => "success",
                "data" => $task
            ));
        } else {
            http_response_code(404);
            echo json_encode(array(
                "status" => "error",
                "message" => "Task not found"
            ));
        }
    } else {
        $category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
        if (!empty($category)) {
            $query = "SELECT * FROM todos WHERE category = '$category' ORDER BY id DESC";
        } else {
            $query = "SELECT * FROM todos ORDER BY id DESC";
        }
        $res = mysqli_query($conn, $query);
        $tasks = array();
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $tasks[] = $row;
            }
        }
        http_response_code(200);
        echo json_encode(array(
            "status" => "success",
            "total" => count($tasks),
            "data" => $tasks
        ));
    }
    exit;
}

if ($method == 'POST') {
    $input_data = json_decode(file_get_contents("php://input"), true);
    if (!$input_data) {
        $input_data = $_POST;
    }

    $title = isset($input_data['title']) ? trim($input_data['title']) : '';
    $description = isset($input_data['description']) ? trim($input_data['description']) : '';
    $deadline = isset($input_data['deadline']) ? trim($input_data['deadline']) : '';
    $category = isset($input_data['category']) && $input_data['category'] == 'Shared' ? 'Shared' : 'Personal';

    if (empty($title) || empty($description) || empty($deadline)) {
        http_response_code(400);
        echo json_encode(array(
            "status" => "error",
            "message" => "Title, description, and deadline are required"
        ));
        exit;
    }

    $title = mysqli_real_escape_string($conn, $title);
    $description = mysqli_real_escape_string($conn, $description);
    $deadline = mysqli_real_escape_string($conn, $deadline);

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
    if (mysqli_query($conn, $query)) {
        $new_id = mysqli_insert_id($conn);
        http_response_code(201);
        echo json_encode(array(
            "status" => "success",
            "message" => "Task created successfully",
            "data" => array(
                "id" => $new_id,
                "title" => $title,
                "description" => $description,
                "deadline" => $deadline,
                "category" => $category,
                "image" => $image_name,
                "completed" => 0
            )
        ));
    } else {
        http_response_code(500);
        echo json_encode(array(
            "status" => "error",
            "message" => "Database query failed"
        ));
    }
    exit;
}

if ($method == 'PUT' || $method == 'PATCH') {
    $raw_input = file_get_contents("php://input");
    $input_data = json_decode($raw_input, true);
    if (!$input_data) {
        parse_str($raw_input, $input_data);
    }

    $id = 0;
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
    } else if (isset($input_data['id'])) {
        $id = intval($input_data['id']);
    }

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(array(
            "status" => "error",
            "message" => "Valid task ID is required"
        ));
        exit;
    }

    $check = mysqli_query($conn, "SELECT * FROM todos WHERE id = $id");
    if (!$check || mysqli_num_rows($check) == 0) {
        http_response_code(404);
        echo json_encode(array(
            "status" => "error",
            "message" => "Task not found"
        ));
        exit;
    }

    $existing = mysqli_fetch_assoc($check);
    $title = isset($input_data['title']) ? mysqli_real_escape_string($conn, trim($input_data['title'])) : $existing['title'];
    $description = isset($input_data['description']) ? mysqli_real_escape_string($conn, trim($input_data['description'])) : $existing['description'];
    $deadline = isset($input_data['deadline']) ? mysqli_real_escape_string($conn, trim($input_data['deadline'])) : $existing['deadline'];
    $category = isset($input_data['category']) ? ($input_data['category'] == 'Shared' ? 'Shared' : 'Personal') : $existing['category'];
    $completed = isset($input_data['completed']) ? intval($input_data['completed']) : $existing['completed'];

    if (empty($title) || empty($description) || empty($deadline)) {
        http_response_code(400);
        echo json_encode(array(
            "status" => "error",
            "message" => "Title, description, and deadline cannot be empty"
        ));
        exit;
    }

    $query = "UPDATE todos SET title = '$title', description = '$description', deadline = '$deadline', category = '$category', completed = $completed WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        http_response_code(200);
        echo json_encode(array(
            "status" => "success",
            "message" => "Task updated successfully",
            "data" => array(
                "id" => $id,
                "title" => $title,
                "description" => $description,
                "deadline" => $deadline,
                "category" => $category,
                "completed" => $completed
            )
        ));
    } else {
        http_response_code(500);
        echo json_encode(array(
            "status" => "error",
            "message" => "Database query failed"
        ));
    }
    exit;
}

if ($method == 'DELETE') {
    $raw_input = file_get_contents("php://input");
    $input_data = json_decode($raw_input, true);
    if (!$input_data) {
        parse_str($raw_input, $input_data);
    }

    $id = 0;
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
    } else if (isset($input_data['id'])) {
        $id = intval($input_data['id']);
    }

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(array(
            "status" => "error",
            "message" => "Valid task ID is required"
        ));
        exit;
    }

    $check = mysqli_query($conn, "SELECT * FROM todos WHERE id = $id");
    if (!$check || mysqli_num_rows($check) == 0) {
        http_response_code(404);
        echo json_encode(array(
            "status" => "error",
            "message" => "Task not found"
        ));
        exit;
    }

    $row = mysqli_fetch_assoc($check);
    if (!empty($row['image']) && file_exists("uploads/" . $row['image'])) {
        unlink("uploads/" . $row['image']);
    }

    if (mysqli_query($conn, "DELETE FROM todos WHERE id = $id")) {
        http_response_code(200);
        echo json_encode(array(
            "status" => "success",
            "message" => "Task deleted successfully"
        ));
    } else {
        http_response_code(500);
        echo json_encode(array(
            "status" => "error",
            "message" => "Database query failed"
        ));
    }
    exit;
}

http_response_code(405);
echo json_encode(array(
    "status" => "error",
    "message" => "Method not allowed"
));
exit;

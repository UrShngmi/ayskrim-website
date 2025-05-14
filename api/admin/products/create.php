<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/functions.php';

header('Content-Type: application/json');
startSession();

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Get form data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
$categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
$stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
$availabilityStatus = isset($_POST['availability_status']) ? $_POST['availability_status'] : 'Available';
$flavorProfile = isset($_POST['flavor_profile']) ? trim($_POST['flavor_profile']) : '';
$ingredients = isset($_POST['ingredients']) ? trim($_POST['ingredients']) : '';
$dietaryType = isset($_POST['dietary_type']) ? $_POST['dietary_type'] : 'Regular';

// Validate required fields
if (empty($name) || $price <= 0 || $categoryId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Required fields are missing or invalid']);
    exit;
}

try {
    $pdo = DB::getConnection();
    
    // Handle image upload
    $imageUrl = 'default.jpg'; // Default image if none provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../../assets/images/products/';
        $fileName = basename($_FILES['image']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Generate unique filename
        $newFileName = uniqid('product_') . '.' . $fileExt;
        $uploadFile = $uploadDir . $newFileName;
        
        // Check file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($fileExt, $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.');
        }
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $imageUrl = $newFileName;
        } else {
            throw new Exception('Failed to upload image.');
        }
    }
    
    // Insert new product
    $stmt = $pdo->prepare('
        INSERT INTO products (
            name,
            description,
            price,
            category_id,
            stock,
            availability_status,
            image_url,
            flavor_profile,
            ingredients,
            dietary_type,
            created_at,
            updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ');
    
    $result = $stmt->execute([
        $name,
        $description,
        $price,
        $categoryId,
        $stock,
        $availabilityStatus,
        $imageUrl,
        $flavorProfile,
        $ingredients,
        $dietaryType
    ]);
    
    if (!$result) {
        throw new Exception('Failed to create product.');
    }
    
    $productId = $pdo->lastInsertId();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Product created successfully',
        'product_id' => $productId
    ]);

} catch (Exception $e) {
    error_log('admin/products/create.php Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while creating product',
        'details' => $e->getMessage()
    ]);
}
?>

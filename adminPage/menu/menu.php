<?php
// Admin menu management
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/db.php';

// Ensure user is admin
requireAdmin();

// Get current admin user
$admin = getCurrentUser();

// Get database connection
$pdo = DB::getConnection();

// Handle form submissions
$message = '';
$messageType = '';

// Handle product deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $productId = (int)$_GET['id'];

    try {
        // Soft delete the product
        $stmt = $pdo->prepare("UPDATE products SET is_deleted = 1 WHERE id = ?");
        $stmt->execute([$productId]);

        $message = "Product successfully deleted.";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Error deleting product: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Get all active products
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_deleted = 0
    ORDER BY p.category_id, p.name
");
$products = $stmt->fetchAll();

// Get all categories for the filter and form
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Page-specific scripts
$pageScripts = ['/ayskrimWebsite/adminPage/menu/menu.js'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management | Ayskrim Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/ayskrimWebsite/adminPage/shared/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Include sidebar/navbar -->
        <?php include_once(__DIR__ . '/../shared/navbar.php'); ?>

        <div class="admin-main">
            <!-- Include header -->
            <?php include_once(__DIR__ . '/../shared/header.php'); ?>

            <div class="admin-content">
                <!-- Page header with actions -->
                <div class="page-header mb-6">
                    <div>
                        <h1 class="text-2xl font-semibold mb-2">Menu Management</h1>
                        <p class="text-gray-500">Manage your ice cream products and categories</p>
                    </div>
                    <div class="page-actions">
                        <button class="btn btn-primary" data-modal-target="addProductModal">
                            <i class="fas fa-plus btn-icon"></i>
                            Add New Product
                        </button>
                    </div>
                </div>

                <!-- Display messages if any -->
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> mb-6" data-auto-close="5000">
                        <div class="alert-content">
                            <div class="alert-title"><?php echo ucfirst($messageType); ?></div>
                            <p><?php echo $message; ?></p>
                        </div>
                        <button class="alert-close">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- Filters and search -->
                <div class="card mb-6">
                    <div class="card-body">
                        <div class="filters d-flex gap-4">
                            <div class="form-group mb-0">
                                <label for="categoryFilter" class="form-label">Category</label>
                                <select id="categoryFilter" class="form-select">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group mb-0">
                                <label for="availabilityFilter" class="form-label">Availability</label>
                                <select id="availabilityFilter" class="form-select">
                                    <option value="">All</option>
                                    <option value="Available">Available</option>
                                    <option value="Out of Stock">Out of Stock</option>
                                    <option value="Seasonal">Seasonal</option>
                                </select>
                            </div>

                            <div class="form-group mb-0 flex-grow-1">
                                <label for="searchProducts" class="form-label">Search</label>
                                <input type="text" id="searchProducts" class="form-input" placeholder="Search products...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Products</h3>
                        <div class="card-actions">
                            <button class="btn btn-secondary" id="exportProducts">
                                <i class="fas fa-download btn-icon"></i>
                                Export
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-container">
                            <table class="admin-table sortable" id="productsTable">
                                <thead>
                                    <tr>
                                        <th data-sort="id">ID</th>
                                        <th>Image</th>
                                        <th data-sort="name">Product Name</th>
                                        <th data-sort="category">Category</th>
                                        <th data-sort="price">Price</th>
                                        <th data-sort="stock">Stock</th>
                                        <th data-sort="status">Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($products)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">No products found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($products as $product): ?>
                                            <tr data-category="<?php echo $product['category_id']; ?>" data-availability="<?php echo $product['availability_status']; ?>">
                                                <td data-column="id">#<?php echo $product['id']; ?></td>
                                                <td>
                                                    <img src="/ayskrimWebsite/assets/images/products/<?php echo htmlspecialchars($product['image_url']); ?>"
                                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                         class="product-thumbnail">
                                                </td>
                                                <td data-column="name"><?php echo htmlspecialchars($product['name']); ?></td>
                                                <td data-column="category"><?php echo htmlspecialchars($product['category_name']); ?></td>
                                                <td data-column="price">₱<?php echo number_format($product['price'], 2); ?></td>
                                                <td data-column="stock">
                                                    <span class="<?php echo $product['stock'] < 10 ? 'text-danger' : ''; ?>">
                                                        <?php echo $product['stock']; ?> units
                                                    </span>
                                                </td>
                                                <td data-column="status">
                                                    <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $product['availability_status'])); ?>">
                                                        <?php echo $product['availability_status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="actions">
                                                        <button class="btn btn-sm btn-secondary edit-product" data-id="<?php echo $product['id']; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger delete-product" data-id="<?php echo $product['id']; ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Include footer -->
            <?php include_once(__DIR__ . '/../shared/footer.php'); ?>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal-backdrop" id="addProductModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Add New Product</h3>
                <button class="modal-close" data-modal-close>&times;</button>
            </div>
            <form action="/ayskrimWebsite/api/admin/products/create.php" method="post" enctype="multipart/form-data" class="needs-validation">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="productName" class="form-label">Product Name</label>
                        <input type="text" id="productName" name="name" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="productDescription" class="form-label">Description</label>
                        <textarea id="productDescription" name="description" class="form-textarea"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="productPrice" class="form-label">Price (₱)</label>
                            <input type="number" id="productPrice" name="price" class="form-input" step="0.01" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="productCategory" class="form-label">Category</label>
                            <select id="productCategory" name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="productStock" class="form-label">Stock</label>
                            <input type="number" id="productStock" name="stock" class="form-input" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="productAvailability" class="form-label">Availability</label>
                            <select id="productAvailability" name="availability_status" class="form-select" required>
                                <option value="Available">Available</option>
                                <option value="Out of Stock">Out of Stock</option>
                                <option value="Seasonal">Seasonal</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="productImage" class="form-label">Product Image</label>
                        <input type="file" id="productImage" name="image" class="form-input" accept="image/*">
                        <div class="image-preview-container mt-2">
                            <img id="imagePreview" class="image-preview" style="display: none; max-width: 100%; max-height: 200px;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="productFlavor" class="form-label">Flavor Profile</label>
                        <input type="text" id="productFlavor" name="flavor_profile" class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="productIngredients" class="form-label">Ingredients</label>
                        <textarea id="productIngredients" name="ingredients" class="form-textarea"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="productDietary" class="form-label">Dietary Type</label>
                        <select id="productDietary" name="dietary_type" class="form-select">
                            <option value="Regular">Regular</option>
                            <option value="Sugar-Free">Sugar-Free</option>
                            <option value="Dairy-Free">Dairy-Free</option>
                            <option value="Vegan">Vegan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal (will be populated dynamically) -->
    <div class="modal-backdrop" id="editProductModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Edit Product</h3>
                <button class="modal-close" data-modal-close>&times;</button>
            </div>
            <form action="/ayskrimWebsite/api/admin/products/update.php" method="post" enctype="multipart/form-data" class="needs-validation">
                <input type="hidden" id="editProductId" name="id">
                <div class="modal-body">
                    <!-- Form fields will be populated dynamically -->
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i> Loading product data...
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

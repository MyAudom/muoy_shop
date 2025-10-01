<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}
include '../config.php';

$success_msg = '';
$error_msg = '';

// Create product
if (isset($_POST['add_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = (int)$_POST['category_id'];
    
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $category = $conn->query("SELECT name FROM categories WHERE id = $category_id")->fetch_assoc()['name'];
        $image = $_FILES['image']['name'];
        $target_dir = "../img/" . strtolower(str_replace(' ', '_', $category)) . "/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target = $target_dir . basename($image);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_path = "img/" . strtolower(str_replace(' ', '_', $category)) . "/" . basename($image);
        }
    }
    
    $sql = "INSERT INTO products (name, description, price, category_id, image) VALUES ('$name', '$description', $price, $category_id, '$image_path')";
    if ($conn->query($sql)) {
        $success_msg = "Product added successfully!";
    } else {
        $error_msg = "Error adding product.";
    }
}

// Update product
if (isset($_POST['update_product'])) {
    $id = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = (int)$_POST['category_id'];
    
    $image_sql = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $category = $conn->query("SELECT name FROM categories WHERE id = $category_id")->fetch_assoc()['name'];
        $image = $_FILES['image']['name'];
        $target_dir = "../img/" . strtolower(str_replace(' ', '_', $category)) . "/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target = $target_dir . basename($image);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_path = "img/" . strtolower(str_replace(' ', '_', $category)) . "/" . basename($image);
            $image_sql = ", image='$image_path'";
            
            // Delete old image if it exists
            $old_product = $conn->query("SELECT image FROM products WHERE id = $id")->fetch_assoc();
            if ($old_product['image'] && file_exists("../" . $old_product['image'])) {
                unlink("../" . $old_product['image']);
            }
        }
    }
    
    $sql = "UPDATE products SET name='$name', description='$description', price=$price, category_id=$category_id $image_sql WHERE id=$id";
    if ($conn->query($sql)) {
        $success_msg = "Product updated successfully!";
    } else {
        $error_msg = "Error updating product.";
    }
}

// Delete product
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $product = $conn->query("SELECT image FROM products WHERE id=$id")->fetch_assoc();
    $sql = "DELETE FROM products WHERE id=$id";
    if ($conn->query($sql)) {
        if ($product['image'] && file_exists("../" . $product['image'])) {
            unlink("../" . $product['image']);
        }
        $success_msg = "Product deleted successfully!";
    } else {
        $error_msg = "Error deleting product.";
    }
}

// Read products with category names
$products = $conn->query("SELECT p.*, c.name as category FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$categories = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link href="../css/admin_css/styles.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../img/icon.ico" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1>📦 Muoy Shop</h1>
            <p>Welcome, <?php echo isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Admin'; ?></p>
        </div>
        
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>
            
            <a href="categories.php" class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                Categories
            </a>
            
            <a href="products.php" class="nav-item active">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Products
            </a>

            <a href="users.php" class="nav-item" style="margin-top: auto;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-8 0v2M12 11a4 4 0 100-8 4 4 0 000 8zm8 8v-2a4 4 0 00-3-3.87"/>
                </svg>
                Users
            </a>
            
            <a href="logout.php" class="nav-item" style="margin-top: auto;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header">
            <button class="hamburger" onclick="toggleSidebar(event)">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                </svg>
            </button>
            <h2>Manage Products</h2>
            <div class="header-actions">
                <button onclick="openModal()" class="btn btn-primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Product
                </button>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if ($success_msg): ?>
        <div class="alert alert-success">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <?php echo $success_msg; ?>
        </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
        <div class="alert alert-danger">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <?php echo $error_msg; ?>
        </div>
        <?php endif; ?>

        <!-- Products List -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">All Products</h3>
                <div class="search-bar">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" id="searchInput" class="form-input" placeholder="Search products..." onkeyup="searchTable()">
                </div>
            </div>

            <?php if ($products->num_rows > 0): ?>
            <div class="table-container">
                <table class="table" id="productTable">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $products->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if ($row['image']): ?>
                                    <img src="../<?php echo htmlspecialchars($row['image']); ?>" width="50" height="50" alt="">
                                <?php else: ?>
                                    <div style="width:50px;height:50px;background:#e5e7eb;border-radius:8px;"></div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td>$<?php echo number_format($row['price'], 2); ?></td>
                            <td><span class="badge badge-primary"><?php echo htmlspecialchars($row['category']); ?></span></td>
                            <td>
                                <div class="table-actions">
                                    <button onclick='editProduct(<?php echo json_encode($row); ?>)' class="btn btn-secondary btn-sm">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    <button onclick="deleteProduct(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>')" class="btn btn-danger btn-sm">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <h3>No Products Yet</h3>
                <p>Start adding products to your inventory</p>
                <button onclick="openModal()" class="btn btn-primary" style="margin-top: 1rem;">Add First Product</button>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Add/Edit Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add Product</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="productForm">
                <input type="hidden" name="id" id="productId">
                
                <div class="form-group">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" id="productName" class="form-input" placeholder="Enter product name" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="productDescription" class="form-textarea" placeholder="Enter product description"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Price *</label>
                        <input type="number" name="price" id="productPrice" class="form-input" placeholder="0.00" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category_id" id="productCategory" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php 
                            $categories->data_seek(0);
                            while($cat = $categories->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image" id="productImage" class="form-input" accept="image/*" onchange="previewImage(event)">
                    <div id="imagePreview" style="margin-top: 1rem; display: none;">
                        <img id="preview" style="max-width: 200px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" name="add_product" id="submitBtn" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        function toggleSidebar(event) {
            event.stopPropagation(); // Prevent event from bubbling up
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('active');
            } else {
                console.error('Sidebar element not found');
            }
        }

        function openModal() {
            document.getElementById('productModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Add Product';
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            document.getElementById('submitBtn').name = 'add_product';
            document.getElementById('submitBtn').textContent = 'Add Product';
            document.getElementById('imagePreview').style.display = 'none';
        }

        function closeModal() {
            document.getElementById('productModal').classList.remove('active');
        }

        function editProduct(product) {
            document.getElementById('productModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Edit Product';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productDescription').value = product.description;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productCategory').value = product.category_id;
            document.getElementById('submitBtn').name = 'update_product';
            document.getElementById('submitBtn').textContent = 'Update Product';
            
            if (product.image) {
                document.getElementById('imagePreview').style.display = 'block';
                document.getElementById('preview').src = '../' + product.image;
            }
        }

        function deleteProduct(id, name) {
            if (confirm('Are you sure you want to delete "' + name + '"?')) {
                window.location.href = '?delete=' + id;
            }
        }

        function previewImage(event) {
            const preview = document.getElementById('preview');
            const previewDiv = document.getElementById('imagePreview');
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewDiv.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        }

        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('productTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const tdName = tr[i].getElementsByTagName('td')[1];
                const tdDesc = tr[i].getElementsByTagName('td')[2];
                const tdCategory = tr[i].getElementsByTagName('td')[4];
                
                if (tdName || tdDesc || tdCategory) {
                    const nameText = tdName ? (tdName.textContent || tdName.innerText) : '';
                    const descText = tdDesc ? (tdDesc.textContent || tdDesc.innerText) : '';
                    const catText = tdCategory ? (tdCategory.textContent || tdCategory.innerText) : '';
                    
                    const combinedText = nameText + descText + catText;
                    tr[i].style.display = combinedText.toUpperCase().indexOf(filter) > -1 ? '' : 'none';
                }
            }
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const hamburger = document.querySelector('.hamburger');
            if (window.innerWidth <= 768 && sidebar.classList.contains('active') && !sidebar.contains(event.target) && !hamburger.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>
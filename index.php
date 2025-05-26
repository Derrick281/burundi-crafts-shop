<?php
$db = new SQLite3('shop.db');

// Create categories table
$db->exec("CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL
)");

// Create products table with category_id
$db->exec("CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    price TEXT,
    description TEXT,
    image TEXT,
    category_id INTEGER,
    FOREIGN KEY(category_id) REFERENCES categories(id)
)");

// Insert some default categories if none exist
$catCount = $db->querySingle("SELECT COUNT(*) FROM categories");
if ($catCount == 0) {
    $db->exec("INSERT INTO categories (name) VALUES ('Basketry'), ('Textiles'), ('Jewelry'), ('Woodwork')");
}

// Handle Add Product
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $desc = $_POST['description'];
    $category_id = $_POST['category_id'];
    $image = '';

    if (!empty($_FILES['image']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if (in_array($fileType, $allowedTypes)) {
            $imagePath = 'uploads/' . time() . '_' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                $image = $imagePath;
            }
        } else {
            echo "<p style='color:red;'>Invalid image format. Only JPG, PNG, GIF allowed.</p>";
        }
    }

    $stmt = $db->prepare("INSERT INTO products (name, price, description, image, category_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bindValue(1, $name);
    $stmt->bindValue(2, $price);
    $stmt->bindValue(3, $desc);
    $stmt->bindValue(4, $image);
    $stmt->bindValue(5, $category_id, SQLITE3_INTEGER);
    $stmt->execute();
    header("Location: index.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $product = $db->querySingle("SELECT image FROM products WHERE id = $id", true);
    if ($product && file_exists($product['image'])) {
        unlink($product['image']);
    }
    $db->exec("DELETE FROM products WHERE id = $id");
    header("Location: index.php");
    exit;
}

// Handle Edit Fetch
$edit = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $edit = $db->querySingle("SELECT * FROM products WHERE id = $id", true);
}

// Handle Update
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $price = $_POST['price'];
    $desc = $_POST['description'];
    $category_id = $_POST['category_id'];
    $image = $_POST['old_image'];

    if (!empty($_FILES['image']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if (in_array($fileType, $allowedTypes)) {
            $imagePath = 'uploads/' . time() . '_' . basename($_FILES['image']['tmp_name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                if (file_exists($image)) unlink($image);
                $image = $imagePath;
            }
        } else {
            echo "<p style='color:red;'>Invalid image format. Only JPG, PNG, GIF allowed.</p>";
        }
    }

    $stmt = $db->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ?, category_id = ? WHERE id = ?");
    $stmt->bindValue(1, $name);
    $stmt->bindValue(2, $price);
    $stmt->bindValue(3, $desc);
    $stmt->bindValue(4, $image);
    $stmt->bindValue(5, $category_id, SQLITE3_INTEGER);
    $stmt->bindValue(6, $id, SQLITE3_INTEGER);
    $stmt->execute();
    header("Location: index.php");
    exit;
}

// Handle Search and Pagination
$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$itemsPerPage = 5;
$offset = ($page - 1) * $itemsPerPage;

$params = [];
$whereSQL = '';
if ($search !== '') {
    $whereSQL = "WHERE name LIKE :search OR description LIKE :search";
    $params[':search'] = "%$search%";
}

// Count total products for pagination
$countQuery = "SELECT COUNT(*) FROM products $whereSQL";
$stmtCount = $db->prepare($countQuery);
if ($search !== '') {
    $stmtCount->bindValue(':search', $params[':search'], SQLITE3_TEXT);
}
$totalItems = $stmtCount->execute()->fetchArray()[0];
$totalPages = ceil($totalItems / $itemsPerPage);

// Fetch products with category names
$query = "
    SELECT products.*, categories.name AS category_name
    FROM products
    LEFT JOIN categories ON products.category_id = categories.id
    $whereSQL
    ORDER BY products.id DESC
    LIMIT $itemsPerPage OFFSET $offset
";

$stmt = $db->prepare($query);
if ($search !== '') {
    $stmt->bindValue(':search', $params[':search'], SQLITE3_TEXT);
}
$results = $stmt->execute();

// Fetch all categories for dropdown
$cats = $db->query("SELECT * FROM categories ORDER BY name ASC");
$categories = [];
while ($c = $cats->fetchArray(SQLITE3_ASSOC)) {
    $categories[$c['id']] = $c['name'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Burundi Crafts Shop with Categories, Search, and Pagination</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        h1 { background: #228B22; color: white; padding: 15px; text-align: center; }
        .container { max-width: 900px; margin: auto; padding: 20px; }
        form { background: white; padding: 20px; margin-bottom: 30px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        input, select, textarea { width: 100%; padding: 10px; margin-top: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; }
        input[type="submit"], button { width: auto; background: #228B22; color: white; border: none; cursor: pointer; padding: 10px 20px; border-radius: 5px; }
        .product { background: white; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 8px; display: flex; gap: 15px; align-items: flex-start; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .product img { width: 100px; height: auto; border-radius: 5px; object-fit: cover; }
        .actions a { margin-right: 15px; text-decoration: none; font-weight: bold; }
        .actions a:hover { text-decoration: underline; }
        .actions a.delete { color: red; }
        .actions a.edit { color: green; }
        .pagination { text-align: center; margin-top: 20px; }
        .pagination a {
            margin: 0 5px; text-decoration: none; color: #228B22; font-weight: bold; padding: 5px 10px; border: 1px solid #228B22; border-radius: 4px;
        }
        .pagination a.active, .pagination a:hover {
            background: #228B22; color: white;
        }
        .search-form { margin-bottom: 30px; }
    </style>
</head>
<body>
    <h1>Burundi Crafts Shop</h1>
    <div class="container">

        <!-- Search Form -->
        <form class="search-form" method="GET">
            <input type="text" name="search" placeholder="Search products by name or description..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>

        <!-- Add/Edit Product Form -->
        <form method="POST" enctype="multipart/form-data">
            <h2><?= $edit ? "Edit Product" : "Add Product" ?></h2>
            <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
            <input type="hidden" name="old_image" value="<?= $edit['image'] ?? '' ?>">
            <label>Name:</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($edit['name'] ?? '') ?>">

            <label>Price (BIF):</label>
            <input type="text" name="price" required value="<?= htmlspecialchars($edit['price'] ?? '') ?>">

            <label>Description:</label>
            <textarea name="description"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>

            <label>Category:</label>
            <select name="category_id" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cid => $cname): ?>
                    <option value="<?= $cid ?>" <?= (isset($edit['category_id']) && $edit['category_id'] == $cid) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cname) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Image:</label>
            <input type="file" name="image">
            <input type="submit" name="<?= $edit ? 'update' : 'add' ?>" value="<?= $edit ? 'Update' : 'Add' ?>">
            <?php if($edit): ?>
                <a href="index.php" style="margin-left:10px;">Cancel Edit</a>
            <?php endif; ?>
        </form>

        <!-- Products List -->
        <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)): ?>
            <div class="product">
                <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
                    <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                <?php else: ?>
                    <img src="placeholder.jpg" alt="No image">
                <?php endif; ?>
                <div>
                    <h3>
                        <?= htmlspecialchars($row['name']) ?> — <?= htmlspecialchars($row['price']) ?> BIF
                    </h3>
                    <p><strong>Category:</strong> <?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?></p>
                    <p><?= nl2br(htmlspecialchars($row['description'])) ?></p>
                    <div class="actions">
                        <a class="edit" href="?edit=<?= $row['id'] ?>">Edit</a>
                        <a class="delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure to delete this product?')">Delete</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>

<!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">&laquo; Prev</a>
                <?php endif; ?>

                <?php
                // Show max 5 page links centered around current page
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);

                for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"
                       class="<?= ($i == $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>

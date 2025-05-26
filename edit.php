<?php
$db = new SQLite3('shop.db');
$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("UPDATE products SET name = ?, price = ?, description = ? WHERE id = ?");
    $stmt->bindValue(1, $_POST['name']);
    $stmt->bindValue(2, $_POST['price']);
    $stmt->bindValue(3, $_POST['description']);
    $stmt->bindValue(4, $id);
    $stmt->execute();
    header("Location: admin.php");
    exit;
}

$product = $db->query("SELECT * FROM products WHERE id = $id")->fetchArray();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Product</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
  <h1>Edit Product</h1>
  <form method="post">
    <input type="text" name="name" value="<?= $product['name'] ?>" class="form-control mb-2" required>
    <input type="text" name="price" value="<?= $product['price'] ?>" class="form-control mb-2" required>
    <textarea name="description" class="form-control mb-2"><?= $product['description'] ?></textarea>
    <button type="submit" class="btn btn-success">Update</button>
    <a href="admin.php" class="btn btn-secondary">Cancel</a>
  </form>
</body>
</html>

<?php
$db = new SQLite3('shop.db');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    // Handle image upload
    $imageName = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = basename($_FILES['image']['name']);
        $target = "uploads/" . $imageName;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    }

    $stmt = $db->prepare("INSERT INTO products (name, price, description, image) VALUES (:name, :price, :description, :image)");
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':price', $price);
    $stmt->bindValue(':description', $description);
    $stmt->bindValue(':image', $imageName);
    $stmt->execute();

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        input, textarea { width: 100%; padding: 8px; margin-bottom: 10px; }
        .button { padding: 10px 15px; background: #28a745; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>

<h2>Add Product</h2>
<form action="" method="POST" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Product name" required>
    <input type="text" name="price" placeholder="Price in BIF" required>
    <textarea name="description" placeholder="Description"></textarea>
    <input type="file" name="image" accept="image/*">
    <button type="submit" class="button">Add</button>
</form>

<a href="index.php">Back to Home</a>

</body>
</html>

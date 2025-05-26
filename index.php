<?php
$db = new SQLite3('shop.db');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    // Handle image upload
    $imageName = basename($_FILES["image"]["name"]);
    $targetDir = "uploads/";
    $targetFile = $targetDir . $imageName;

    if (!file_exists($targetDir)) {
        mkdir($targetDir);
    }

    move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);

    $db->exec("INSERT INTO products (name, price, description, image) 
               VALUES ('$name', '$price', '$description', '$imageName')");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Burundi Crafts Shop</title>
</head>
<body>
    <h1>Burundi Crafts Shop</h1>

    <form action="index.php" method="post" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Product Name" required><br>
        <input type="text" name="price" placeholder="Price (e.g., 20000 BIF)" required><br>
        <input type="text" name="description" placeholder="Description" required><br>
        <input type="file" name="image" accept="image/*" required><br>
        <button type="submit">Add Product</button>
    </form>

    <hr>

    <?php
    $results = $db->query("SELECT * FROM products");
    while ($row = $results->fetchArray()) {
        echo "<h2>" . htmlspecialchars($row['name']) . ": " . htmlspecialchars($row['price']) . "</h2>";
        echo "<p>" . htmlspecialchars($row['description']) . "</p>";
        if (!empty($row['image'])) {
            echo "<img src='uploads/" . htmlspecialchars($row['image']) . "' width='200'><br><br>";
        }
    }
    ?>
</body>
</html>

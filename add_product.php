<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $products = json_decode(file_get_contents('products.json'), true);

    $newProduct = [
        "name" => $_POST["name"],
        "price" => $_POST["price"],
        "image" => $_POST["image"]
    ];

    $products[] = $newProduct;
    file_put_contents('products.json', json_encode($products, JSON_PRETTY_PRINT));
    echo "Product added successfully. <a href='index.php'>View Products</a>";
    exit;
}
?>

<form method="POST">
    <h2>Add New Craft</h2>
    Name: <input type="text" name="name" required><br><br>
    Price (BIF): <input type="number" name="price" required><br><br>
    Image URL: <input type="text" name="image" required><br><br>
    <button type="submit">Add Product</button>
</form>

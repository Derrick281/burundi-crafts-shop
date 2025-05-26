<?php
$name = $_POST['name'];
$price = $_POST['price'];
$description = $_POST['description'];

$product = "$name | $price | $description\n";

file_put_contents("products.txt", $product, FILE_APPEND);
echo "Product saved! <a href='add_product.php'>Add another</a> | <a href='products.php'>View All</a>";
?>

<?php
echo "<h2>Burundi Crafts Products</h2>";
$products = file("products.txt");
foreach ($products as $product) {
    echo "<p>$product</p>";
}
?>

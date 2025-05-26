<?php
$db = new SQLite3('shop.db');
$results = $db->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Burundi Crafts</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
  <h1>Manage Products</h1>
  <a href="index.php" class="btn btn-primary mb-3">Back to Shop</a>
  <table class="table table-bordered">
    <thead><tr><th>Name</th><th>Price</th><th>Description</th><th>Actions</th></tr></thead>
    <tbody>
      <?php while ($row = $results->fetchArray()): ?>
        <tr>
          <td><?= $row['name'] ?></td>
          <td><?= $row['price'] ?></td>
          <td><?= $row['description'] ?></td>
          <td>
            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
            <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>
</html>

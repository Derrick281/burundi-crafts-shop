<?php
$db = new SQLite3('shop.db');

// Check if category_id exists
$columns = $db->query("PRAGMA table_info(products)");
$hasCategoryId = false;
while ($col = $columns->fetchArray(SQLITE3_ASSOC)) {
    if ($col['name'] === 'category_id') {
        $hasCategoryId = true;
        break;
    }
}

if (!$hasCategoryId) {
    // Add category_id column
    $db->exec("ALTER TABLE products ADD COLUMN category_id INTEGER");
    echo "Added category_id column to products table.\n";
} else {
    echo "category_id column already exists.\n";
}
?>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';

// Hämta alla kategorier och deras hierarki
try {
    $categorySql = "SELECT * FROM categories";
    $categoryResult = $pdo->query($categorySql);
    $categories = $categoryResult->fetchAll(PDO::FETCH_ASSOC);

    // Bygg en hierarki av kategorier
    $categoryTree = [];
    foreach ($categories as $category) {
        $categoryTree[$category['id']] = $category;
        $categoryTree[$category['id']]['children'] = [];
    }

    foreach ($categories as $category) {
        if ($category['parent_id']) {
            $categoryTree[$category['parent_id']]['children'][] = &$categoryTree[$category['id']];
        }
    }

    // Hämta de tre huvudkategorierna
    $mainCategories = array_filter($categoryTree, function($category) {
        return $category['parent_id'] === null;
    });
    $mainCategories = array_slice($mainCategories, 0, 3);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Visa Kategorier</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 600px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .category {
            cursor: pointer;
            padding: 5px 0;
        }
        .category > label {
            font-weight: bold;
        }
        .children {
            display: none;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Visa Kategorier</h1>
        <div id="categories"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categories = <?php echo json_encode($categoryTree); ?>;
            const mainCategories = <?php echo json_encode($mainCategories); ?>;
            const container = document.getElementById('categories');

            const buildList = (categories, parent, level = 0) => {
                for (let id in categories) {
                    const category = categories[id];
                    const div = document.createElement('div');
                    div.className = 'category';

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.id = 'category_' + category.id;

                    const label = document.createElement('label');
                    label.textContent = category.name;
                    label.htmlFor = checkbox.id;
                    label.style.marginLeft = '5px';
                    label.addEventListener('click', () => {
                        const childrenDiv = div.querySelector('.children');
                        if (childrenDiv) {
                            childrenDiv.style.display = childrenDiv.style.display === 'none' ? 'block' : 'none';
                        }
                    });

                    div.appendChild(checkbox);
                    div.appendChild(label);
                    parent.appendChild(div);

                    if (category.children && Object.keys(category.children).length > 0) {
                        const subDiv = document.createElement('div');
                        subDiv.className = 'children';
                        buildList(category.children, subDiv, level + 1);
                        div.appendChild(subDiv);
                    }
                }
            };

            buildList(mainCategories, container);
        });
    </script>
</body>
</html>6

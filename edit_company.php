<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';

$companyId = isset($_GET['company_id']) ? (int)$_GET['company_id'] : 0;
if ($companyId <= 0) {
    die("Företags-ID saknas eller är ogiltigt.");
}

try {
    // Hämta företagsinformation
    $companySql = "SELECT name FROM companies WHERE id = :companyId";
    $stmt = $pdo->prepare($companySql);
    $stmt->execute(['companyId' => $companyId]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$company) {
        die("Företag hittades inte.");
    }

    // Hämta alla kategorier
    $categorySql = "SELECT id, name, parent_id FROM categories ORDER BY parent_id, name";
    $categoryResult = $pdo->query($categorySql);
    $categories = $categoryResult->fetchAll(PDO::FETCH_ASSOC);

    // Hämta de kategorier som redan är valda för företaget
    $selectedCategorySql = "SELECT service_id FROM company_services WHERE company_id = :companyId";
    $stmt = $pdo->prepare($selectedCategorySql);
    $stmt->execute(['companyId' => $companyId]);
    $selectedCategories = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Funktion för att bygga trädstruktur
function buildTree(array &$elements, $parentId = null) {
    $branch = array();
    foreach ($elements as &$element) {
        if ($element['parent_id'] == $parentId) {
            $children = buildTree($elements, $element['id']);
            if ($children) {
                $element['children'] = $children;
            }
            $branch[] = $element;
            unset($element);
        }
    }
    return $branch;
}

$categoryTree = buildTree($categories);
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redigera <?php echo htmlspecialchars($company['name']); ?></title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 600px;
            text-align: center;
        }
        .form-container {
            margin-top: 20px;
        }
        .form-container div {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="email"], input[type="tel"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .tree ul {
            list-style-type: none;
            padding-left: 20px;
        }
        .tree label {
            cursor: pointer;
        }
        .tree input[type="checkbox"] {
            display: none;
        }
        .tree input[type="checkbox"] + ul {
            display: none;
        }
        .tree input[type="checkbox"]:checked + ul {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Redigera <?php echo htmlspecialchars($company['name']); ?></h1>
        <form action="update_company.php" method="POST">
            <input type="hidden" name="company_id" value="<?php echo $companyId; ?>">
            <div class="form-container">
                <h2>Koppla Kategorier</h2>
                <div class="tree">
                    <?php
                    function renderTree($tree, $selectedCategories) {
                        echo '<ul>';
                        foreach ($tree as $node) {
                            echo '<li>';
                            echo '<input type="checkbox" id="category_' . $node['id'] . '" name="categories[]" value="' . $node['id'] . '" ' . (in_array($node['id'], $selectedCategories) ? 'checked' : '') . '>';
                            echo '<label for="category_' . $node['id'] . '">' . htmlspecialchars($node['name']) . '</label>';
                            if (!empty($node['children'])) {
                                renderTree($node['children'], $selectedCategories);
                            }
                            echo '</li>';
                        }
                        echo '</ul>';
                    }

                    renderTree($categoryTree, $selectedCategories);
                    ?>
                </div>
                <button type="submit">Spara Ändringar</button>
            </div>
        </form>
    </div>
</body>
</html>

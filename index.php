<!DOCTYPE html>
<html>
<head>
    <title>Admin - Kategorier</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        .sub-category {
            margin-left: 20px; /* Indragning för subkategorier */
        }
        .sub-sub-category {
            margin-left: 40px; /* Ytterligare indragning för subsubkategorier */
        }
    </style>
</head>
<body>
    <h1>Lägg till Kategori</h1>
    <form method="POST" action="add_category.php">
        <label for="name">Namn:</label>
        <input type="text" id="name" name="name" required>
        <br>
        <label for="parent_id">Förälder Kategori:</label>
        <select id="parent_id" name="parent_id">
            <option value="0">Ingen (Huvudkategori)</option>
        </select>
        <br>
        <button type="submit">Lägg till</button>
    </form>

    <h1>Alla Kategorier</h1>
    <ul id="categories"></ul>

    <script>
        // Hämta kategorier och fyll i dropdown-menyn och trädlistan
        fetch('get_categories.php')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('parent_id');
                const ul = document.getElementById('categories');

                const buildList = (categories, parent, level = 0) => {
                    categories.forEach(category => {
                        const li = document.createElement('li');
                        li.textContent = category.name;
                        if(level === 1) {
                            li.classList.add('sub-category');
                        } else if(level === 2) {
                            li.classList.add('sub-sub-category');
                        }
                        parent.appendChild(li);

                        // Lägg till i dropdown-menyn
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = '-'.repeat(level) + ' ' + category.name;
                        select.appendChild(option);

                        if (category.subcategories && category.subcategories.length > 0) {
                            buildList(category.subcategories, parent, level + 1);
                        }
                    });
                };
                buildList(data, ul);
            })
            .catch(error => console.error('Error fetching categories:', error));
    </script>
</body>
</html>

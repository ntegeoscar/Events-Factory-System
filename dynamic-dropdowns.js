// When a category is selected, fetch the subcategories
document.getElementById('itemCategory').addEventListener('change', function() {
    var categoryId = this.value;

    if (categoryId) {
        fetchSubcategories(categoryId);
    } else {
        document.getElementById('itemSubCategory').innerHTML = '<option value="">Select a Subcategory</option>';
        document.getElementById('itemGroup').innerHTML = '<option value="">Select a Group</option>';
    }
});

// When a subcategory is selected, fetch the groups
document.getElementById('itemSubCategory').addEventListener('change', function() {
    var subcategoryId = this.value;

    if (subcategoryId) {
        fetchGroups(subcategoryId);
    } else {
        document.getElementById('itemGroup').innerHTML = '<option value="">Select a Group</option>';
    }
});

// Function to fetch subcategories via AJAX
function fetchSubcategories(categoryId) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'subcategories.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        document.getElementById('itemSubCategory').innerHTML = this.responseText;
    };
    xhr.send('category_id=' + categoryId);
}

// Function to fetch groups via AJAX
function fetchGroups(subcategoryId) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'groups.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        document.getElementById('itemGroup').innerHTML = this.responseText;
    };
    xhr.send('sub_category_id=' + subcategoryId);
}

// Initial population of categories
window.onload = function() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'categories.php', true);
    xhr.onload = function() {
        document.getElementById('itemCategory').innerHTML = document.getElementById('itemCategory').innerHTML + this.responseText;
    };
    xhr.send();
};

// Fetch Subcategories
function fetchSubcategories() {
    const category = document.getElementById("category").value;
    const subcategorySelect = document.getElementById("subcategory");

    if (category) {
        fetch(`filter.php?action=subcategories&category_id=${category}`)
            .then(response => response.json())
            .then(data => {
                subcategorySelect.innerHTML = '<option value="">Select a subcategory</option>';
                data.forEach(subcat => {
                    subcategorySelect.innerHTML += `<option value="${subcat.sub_category_id}">${subcat.sub_category_name}</option>`;
                });
                subcategorySelect.disabled = false;
            })
            .catch(error => console.error("Error fetching subcategories:", error));
    } else {
        subcategorySelect.innerHTML = '<option value="">Select a subcategory</option>';
        subcategorySelect.disabled = true;
    }
}

// Fetch Groups
function fetchGroups() {
    const subcategory = document.getElementById("subcategory").value;
    const groupSelect = document.getElementById("group");

    if (subcategory) {
        fetch(`filter.php?action=groups&subcategory_id=${subcategory}`)
            .then(response => response.json())
            .then(data => {
                groupSelect.innerHTML = '<option value="">Select a group</option>';
                data.forEach(group => {
                    groupSelect.innerHTML += `<option value="${group.group_id}">${group.group_name}</option>`;
                });
                groupSelect.disabled = false;
            })
            .catch(error => console.error("Error fetching groups:", error));
    } else {
        groupSelect.innerHTML = '<option value="">Select a group</option>';
        groupSelect.disabled = true;
    }
}

// Fetch Items
function fetchItems() {
    const group = document.getElementById("group").value;
    const itemSelect = document.getElementById("manualItem");

    if (group) {
        fetch(`filter.php?action=items&group_id=${group}&filter=available`)
            .then(response => response.json())
            .then(data => {
                itemSelect.innerHTML = '<option value="">Select Item</option>';
                data.forEach(item => {
                    itemSelect.innerHTML += `<option value="${item.item_id}">${item.item_name} - ${item.model} (SN: ${item.serial_number})</option>`;
                });
                itemSelect.disabled = false;
            })
            .catch(error => console.error("Error fetching items:", error));
    } else {
        itemSelect.innerHTML = '<option value="">Select Item</option>';
        itemSelect.disabled = true;
    }
}

// Add Item to Requisition List
function addItem() {
    const itemSelect = document.getElementById("manualItem");
    const selectedOption = itemSelect.options[itemSelect.selectedIndex];

    if (selectedOption.value) {
        const [itemName, model] = selectedOption.text.split(' - ');
        const serialNumberMatch = selectedOption.text.match(/\(SN: (.+?)\)/);
        const serialNumber = serialNumberMatch ? serialNumberMatch[1] : "";

        const table = document.getElementById("orderTable").getElementsByTagName('tbody')[0];

        // Check if the item already exists
        const existingRows = table.querySelectorAll("tr");
        for (let row of existingRows) {
            if (row.cells[0].textContent.trim() === itemName.trim()) {
                alert("This item is already in the list!");
                return; // Stop execution if duplicate is found
            }
        }

        // Add new row if no duplicate is found
        const row = table.insertRow();
        row.innerHTML = `
            <td><input type="hidden" name="items[]" value="${selectedOption.value}">${itemName}</td>
            <td>${model}</td>
            <td>${serialNumber}</td>
            <td><button type="button" onclick="removeItem(this)">Remove</button></td>
        `;

        // Clear selection after adding
        itemSelect.value = "";
    }
}


// Remove Item from Requisition List
function removeItem(button) {
    const row = button.parentNode.parentNode;
    row.parentNode.removeChild(row);
}


// Automatically Select Items Based on Input
function autoSelectItems() {
    const category = document.getElementById("category").value;
    const subcategory = document.getElementById("subcategory").value;
    const group = document.getElementById("group").value;
    const itemCount = document.getElementById("itemCount").value;

    if (itemCount > 0 && (category || subcategory || group)) {
        fetch(`filter.php?action=auto_select&group_id=${group}&count=${itemCount}`)
        .then(response => response.json())
        .then(data => {
            console.log("Data received from server:", data);
            const table = document.getElementById("orderTable").getElementsByTagName('tbody')[0];

            data.forEach(item => {
                // Check if the item already exists in the table
                let exists = false;
                const existingRows = table.querySelectorAll("tr");
                
                existingRows.forEach(row => {
                    const existingItemId = row.querySelector("input[name='items[]']").value;
                    if (existingItemId === item.item_id) {
                        exists = true;
                    }
                });

                // If the item doesn't exist, add it
                if (!exists) {
                    const row = table.insertRow();
                    row.innerHTML = `
                        <td><input type="hidden" name="items[]" value="${item.item_id}">${item.item_name}</td>
                        <td>${item.model}</td>
                        <td>${item.serial_number}</td>
                        <td><button type="button" onclick="removeItem(this)">Remove</button></td>
                    `;
                }
            });
        })
        .catch(error => console.error("Error auto-selecting items:", error));
    } else {
        alert("Please fill in all fields and specify a valid number of items.");
    }
}





// Handles active state switching for inventory links
document.querySelectorAll('.inventory-link').forEach(link => {
    link.addEventListener('click', function (e) {
        e.preventDefault(); // Prevent default anchor behavior

        // Remove active state from the previously active link
        document.querySelector('.inventory-link.active-link')?.classList.remove('active-link');
        
        // Add active state to the clicked link
        this.classList.add('active-link');

        // Hide all table containers
        document.querySelectorAll('.table-container').forEach(table => {
            table.style.display = 'none';
        });

        // Show the table container corresponding to the clicked link
        const tableId = this.id.replace('link', 'table');
        document.getElementById(tableId).style.display = 'block';
    });
});

// Handles active state switching for content links
document.querySelectorAll('.content-link').forEach(link => {
    link.addEventListener('click', function (e) {
        e.preventDefault(); // Prevent default anchor behavior

        // Remove active state from the previously active link
        document.querySelector('.content-link.active-link')?.classList.remove('active-link');
        
        // Add active state to the clicked link
        this.classList.add('active-link');

        // Hide all table containers
        document.querySelectorAll('.table-container').forEach(table => {
            table.style.display = 'none';
        });

        // Show the corresponding section/table
        const sectionId = this.id.replace('link', 'table');  // Replaces "link" in ID with "table"
        document.getElementById(sectionId).style.display = 'block';

        // Add vector under the active link
        document.querySelectorAll('.vector').forEach(vector => {
            vector.style.display = 'none'; // Hide all vectors
        });
        
        // Make sure the clicked link has the vector
        const vector = this.querySelector('.vector');
        if (vector) {
            vector.style.display = 'block'; // Show vector under active link
        }
    });
});

// Make the first table/content visible on load (adjust for your specific IDs)
window.addEventListener('DOMContentLoaded', () => {
    // Set the first link as active for inventory links
    const firstInventoryLink = document.querySelector('.inventory-link');
    if (firstInventoryLink) {
        firstInventoryLink.classList.add('active-link');
    }

    // Show the first table container for inventory page
    const firstInventoryTable = document.getElementById('table1');
    if (firstInventoryTable) {
        firstInventoryTable.style.display = 'block';
    }

    // Set the first link as active for content links
    const firstContentLink = document.querySelector('.content-link');
    if (firstContentLink) {
        firstContentLink.classList.add('active-link');
    }

});


// --- Search Bar for Active Table ---
function searchActiveTable() {
    const query = document.getElementById('searchBar').value.toLowerCase();
    const activeTableContainer = [...document.querySelectorAll('.table-container')].find(
        container => container.style.display !== 'none'
    );
    const table = activeTableContainer.querySelector('table');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) { // Skip the header row
        const cells = rows[i].getElementsByTagName('td');
        const rowText = [...cells].map(cell => cell.textContent.toLowerCase()).join(' ');
        rows[i].style.display = rowText.includes(query) ? '' : 'none';
    }
}

// --- Initialization on Page Load ---
window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('searchBar').addEventListener('input', searchActiveTable);

    // Set up pagination and filters for each table
    document.querySelectorAll('.table-container table').forEach((table, index) => {
        const tableId = `table${index + 1}`;
        paginateTable(tableId, 10); // Set rows per page to 10
        setupColumnFilters(tableId);
    });

    document.getElementById('downloadButton').addEventListener('click', downloadActiveTableAsPDF);
});




// pagination.js

function setupPagination(table, paginationContainer) {
    const rowsPerPage = 10; // Number of rows per page
    const rows = table.querySelectorAll('tbody tr');
    const totalRows = rows.length;
    const totalPages = Math.ceil(totalRows / rowsPerPage);

    let currentPage = 1;

    function renderTable() {
        rows.forEach((row, index) => {
            row.style.display = (index >= (currentPage - 1) * rowsPerPage && index < currentPage * rowsPerPage) ? '' : 'none';
        });
    }

    function renderPagination() {
        paginationContainer.innerHTML = '';
        const maxVisibleButtons = 7; // Number of page buttons to show at a time
    
        const createButton = (text, page) => {
            const button = document.createElement('button');
            button.textContent = text;
            button.className = page === currentPage ? 'active' : '';
            button.addEventListener('click', () => {
                currentPage = page;
                renderTable();
                renderPagination();
            });
            return button;
        };
    
        // Previous button
        if (currentPage > 1) {
            paginationContainer.appendChild(createButton('Previous', currentPage - 1));
        }
    
        // Page number buttons
        let startPage = Math.max(1, currentPage - Math.floor(maxVisibleButtons / 2));
        let endPage = Math.min(totalPages, startPage + maxVisibleButtons - 1);
    
        // Adjust if we're near the start or end
        if (endPage - startPage + 1 < maxVisibleButtons) {
            startPage = Math.max(1, endPage - maxVisibleButtons + 1);
        }
    
        for (let i = startPage; i <= endPage; i++) {
            paginationContainer.appendChild(createButton(i, i));
        }
    
        // Next button
        if (currentPage < totalPages) {
            paginationContainer.appendChild(createButton('Next', currentPage + 1));
        }
    }
    

    // Initial render
    renderTable();
    renderPagination();
}


document.addEventListener("DOMContentLoaded", function () {
    // Table 1 filters
    const categoryFilter1 = document.getElementById("categoryFilter1");
    const subcategoryFilter1 = document.getElementById("subcategoryFilter1");
    const table1Body = document.querySelector("#table1 tbody");

    // Table 2 filters
    const categoryFilter2 = document.getElementById("categoryFilter2");
    const subcategoryFilter2 = document.getElementById("subcategoryFilter2");
    const groupFilter2 = document.getElementById("groupFilter2");
    const table2Body = document.querySelector("#table2 tbody");

    /** ----------- Table 1 Logic (Category -> Subcategory -> Groups) ----------- **/
    categoryFilter1.addEventListener("change", function () {
        const categoryId = this.value;
        subcategoryFilter1.innerHTML = '<option value="">Select Subcategory</option>';
        subcategoryFilter1.disabled = true;
        table1Body.innerHTML = ""; // Clear table

        if (categoryId) {
            fetch(`fetch_data.php?action=get_subcategories&category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    // console.log("Table 1 - Subcategories:", data);
                    data.forEach(sub => {
                        subcategoryFilter1.innerHTML += `<option value="${sub.sub_category_id}">${sub.sub_category_name}</option>`;
                    });
                    subcategoryFilter1.disabled = false;
                })
                .catch(error => console.error("Error fetching subcategories for Table 1:", error));
        }
    });

    subcategoryFilter1.addEventListener("change", function () {
        const subcategoryId = this.value;
        table1Body.innerHTML = ""; // Clear table

        if (subcategoryId) {
            fetch(`fetch_data.php?action=get_groups&subcategory_id=${subcategoryId}`)
                .then(response => response.json())
                .then(data => {
                    // console.log("Table 1 - Groups:", data);
                    data.forEach(group => {
                        table1Body.innerHTML += `
                            <tr onclick="location.href='group_details.php?id=${group.group_id}'">
                                <td>${group.group_id}</td>
                                <td>${group.group_name}</td>
                                <td>${group.total_items}</td> 
                                <td>${group.available}</td>
                                <td>${group.rented}</td>
                                <td>${group.damaged}</td>
                            </tr>`;
                    });
                    setupPagination(document.getElementById('table1'), document.getElementById('table1Pagination'));
                })
                .catch(error => console.error("Error fetching groups for Table 1:", error));
        }
    });

    /** ----------- Table 2 Logic (Category -> Subcategory -> Group -> Items) ----------- **/
    categoryFilter2.addEventListener("change", function () {
        const categoryId = this.value;
        subcategoryFilter2.innerHTML = '<option value="">Select Subcategory</option>';
        groupFilter2.innerHTML = '<option value="">Select Group</option>';
        subcategoryFilter2.disabled = true;
        groupFilter2.disabled = true;
        table2Body.innerHTML = ""; // Clear table

        if (categoryId) {
            fetch(`fetch_data.php?action=get_subcategories&category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    // console.log("Table 2 - Subcategories:", data);
                    data.forEach(sub => {
                        subcategoryFilter2.innerHTML += `<option value="${sub.sub_category_id}">${sub.sub_category_name}</option>`;
                    });
                    subcategoryFilter2.disabled = false;
                })
                .catch(error => console.error("Error fetching subcategories for Table 2:", error));
        }
    });

    subcategoryFilter2.addEventListener("change", function () {
        const subcategoryId = this.value;
        groupFilter2.innerHTML = '<option value="">Select Group</option>';
        groupFilter2.disabled = true;
        table2Body.innerHTML = ""; // Clear table

        if (subcategoryId) {
            fetch(`fetch_data.php?action=get_groups&subcategory_id=${subcategoryId}`)
                .then(response => response.json())
                .then(data => {
                    // console.log("Table 2 - Groups:", data);
                    data.forEach(group => {
                        groupFilter2.innerHTML += `<option value="${group.group_id}">${group.group_name}</option>`;
                    });
                    groupFilter2.disabled = false;
                })
                .catch(error => console.error("Error fetching groups for Table 2:", error));
        }
    });

    groupFilter2.addEventListener("change", function () {
        const groupId = this.value;
        table2Body.innerHTML = ""; // Clear table

        if (groupId) {
            fetch(`fetch_data.php?action=get_items&group_id=${groupId}`)
                .then(response => response.json())
                .then(data => {
                    // console.log("Table 2 - Items:", data);
                    data.forEach(item => {
                        table2Body.innerHTML += `
                            <tr onclick="location.href='item_details.php?id=${item.item_id}'">
                                <td>${item.item_id}</td>
                                <td>${item.item_name}</td>
                                <td>${item.availability}</td>
                            </tr>`;
                    });
                    setupPagination(document.getElementById('table2'), document.getElementById('table2Pagination'));
                })
                .catch(error => console.error("Error fetching items for Table 2:", error));
        }
    });
});


document.getElementById("Order_status_Filter").addEventListener("change", function() {
    let selectedStatus = this.value;
    window.location.href = `orders.php?status=${selectedStatus}`;
});



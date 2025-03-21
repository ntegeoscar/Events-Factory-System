
document.addEventListener("DOMContentLoaded", function () {
   
    // Table 2 filters
    const categoryFilter2 = document.getElementById("categoryFilter2");
    const subcategoryFilter2 = document.getElementById("subcategoryFilter2");
    const groupFilter2 = document.getElementById("groupFilter2");
    const table1Body = document.querySelector("#table1 tbody");

    
    /** ----------- Table 2 Logic (Category -> Subcategory -> Group -> Items) ----------- **/
    categoryFilter2.addEventListener("change", function () {
        const categoryId = this.value;
        subcategoryFilter2.innerHTML = '<option value="">Select Subcategory</option>';
        groupFilter2.innerHTML = '<option value="">Select Group</option>';
        subcategoryFilter2.disabled = true;
        groupFilter2.disabled = true;
        table1Body.innerHTML = ""; // Clear table

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
        table1Body.innerHTML = ""; // Clear table

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
        table1Body.innerHTML = ""; // Clear table

        if (groupId) {
            fetch(`fetch_data.php?action=get_items&group_id=${groupId}`)
                .then(response => response.json())
                .then(data => {
                    // console.log("Table 2 - Items:", data);
                    data.forEach(item => {
                        table1Body.innerHTML += `
                            <tr onclick="location.href='item_details.php?id=${item.item_id}'">
                                <td>${item.item_id}</td>
                                <td>${item.item_name}</td>
                                <td>${item.availability}</td>
                                <td><a href="Update_item.php?id=${item.item_id}" class="btn"><button type="button">Update</button></a></td>
                                <td><a href="Delete_item.php?id=${item.item_id}" class="btn"><button type="button">Delete</button></a></td>                                
                            </tr>`;
                    });
                    setupPagination(document.getElementById('table1'), document.getElementById('table1Pagination'));
                })
                .catch(error => console.error("Error fetching items for Table 2:", error));
        }
    });
});

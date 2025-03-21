<?php

require 'session_check.php';

// Only allow Operator & Super Admin
if ($user_role != 1 && $user_role != 2) {
    echo "❌ Access Denied!";
    exit;
}

// Include database connection
include 'db.php';

// Handle incoming requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    if ($action === 'create') {
        $name = $_POST['name'];
        $phone_no = $_POST['phone_no'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role_id = $_POST['role_id'];

        $stmt = $conn->prepare("INSERT INTO users (name, phone_no, username, email, password, role_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $name, $phone_no, $username, $email, $password, $role_id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "User created successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create user", "error" => $stmt->error]);
        }
        exit;
    }

    if ($action === 'delete') {
        $user_id = $_POST['user_id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "User deleted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete user", "error" => $stmt->error]);
        }
        exit;
    }

    if ($action === 'update') {
        $user_id = $_POST['user_id'];
        $name = $_POST['name'];
        $phone_no = $_POST['phone_no'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $role_id = $_POST['role_id'];

        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name=?, phone_no=?, username=?, email=?, password=?, role_id=? WHERE user_id=?");
            $stmt->bind_param("ssssssi", $name, $phone_no, $username, $email, $password, $role_id, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, phone_no=?, username=?, email=?, role_id=? WHERE user_id=?");
            $stmt->bind_param("ssssii", $name, $phone_no, $username, $email, $role_id, $user_id);
        }

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "User updated successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update user", "error" => $stmt->error]);
        }
        exit;
    }

    if ($action === 'getUsers') {
        $result = $conn->query("SELECT * FROM users");
        $users = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(["status" => "success", "data" => $users]);
        exit;
    }
}

// Fetch all users for frontend
$result = $conn->query("SELECT * FROM users");
$users = $result->fetch_all(MYSQLI_ASSOC);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
</head>
<body>

<div class="sidebar">
    <img src="red_logo.png" alt="Logo" class="logo">
    <div class="nav-links">
        <a href="dashboard.php" class="nav-link"><div class="nav-item"><i class="icon"></i><span>Dashboard</span></div></a>
        <a href="inventory.php" class="nav-link"><div class="nav-item"><i class="icon"></i><span>Inventory</span></div></a>
        <a href="reports.php" class="nav-link"><div class="nav-item "><i class="icon"></i><span>Reports</span></div></a>
        <a href="requisitions.php" class="nav-link"><div class="nav-item"><i class="icon"></i><span>Requisitions</span></div></a>
        <a href="orders.php" class="nav-link"><div class="nav-item"><i class="icon"></i><span>Orders</span></div></a>
        <a href="Manage_inventory.php" class="nav-link"><div class="nav-item"><i class="icon"></i><span>Manage Store</span></div></a>
        <a href="settings.php" class="nav-link"><div class="nav-item active"><i class="icon"></i><span>Settings</span></div></a>
                <a href="logout.php" class="nav-link">
          <div class="nav-item">
            <i class="icon"></i>
            <span>Log Out</span>
          </div>
        </a>
    </div>
</div>

    <div class="main-content">
        <div class="container">
            <h2>User Management</h2>
            
            <!-- Create User Form -->
            <div class="card">
                <h4>Create New User</h4>
                <form action="" method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number:</label>
                        <input type="text" name="phone_no" required>
                    </div>
                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Role:</label>
                        <select name="role_id" required>
                            <option value="1">Super Admin</option>
                            <option value="2">Operator</option>
                            <option value="3">Store Keeper</option>
                        </select>
                    </div>
                    <button type="submit" class="button-primary">Create</button>
                </form>
            </div>

                <br>

            <!-- Users List -->
            <div class="card">
                <h4>Existing Users</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone_no']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <?php
                                $role_names = [
                                    1 => 'Super Admin',
                                    2 => 'Operator',
                                    3 => 'Store Keeper'
                                ];
                                ?>
                            <td><?php echo htmlspecialchars($role_names[$user['role_id']] ?? 'Unknown Role'); ?></td>
                            <td>
                                <button class="btn btn-primary edit-user" data-id="<?php echo $user['user_id']; ?>" data-name="<?php echo htmlspecialchars($user['name']); ?>" data-phone="<?php echo htmlspecialchars($user['phone_no']); ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>" data-email="<?php echo htmlspecialchars($user['email']); ?>" data-role="<?php echo $user['role_id']; ?>">Edit</button>
                                <button id="deleteButton" class="btn btn-danger delete-user" data-user-id="<?php echo $user['user_id']; ?>">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

                <br>
        
            <!-- Edit User Modal -->
            <div class="card">
                <div class="modal" id="editUserModal">
                    <div class="modal-content">
                        <h4>Edit User</h4>
                        <form action="" method="POST">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="user_id" id="edit_user_id">
                            <div class="form-group">
                                <label>Name:</label>
                                <input type="text" name="name" id="edit_name" required>
                            </div>
                            <div class="form-group">
                                <label>Phone Number:</label>
                                <input type="text" name="phone_no" id="edit_phone_no" required>
                            </div>
                            <div class="form-group">
                                <label>Username:</label>
                                <input type="text" name="username" id="edit_username" required>
                            </div>
                            <div class="form-group">
                                <label>Email:</label>
                                <input type="email" name="email" id="edit_email" required>
                            </div>
                            <div class="form-group">
                                <label>Password (leave blank to keep current):</label>
                                <input type="password" name="password" id="edit_password">
                            </div>
                            <div class="form-group">
                                <label>Role:</label>
                                <select name="role_id" id="edit_role_id" required>
                                    <option value="1">Super Admin</option>
                                    <option value="2">Operator</option>
                                    <option value="3">Store Keeper</option>
                                </select>
                            </div>
                            <button type="submit" class="button-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>              
        </div>

 
    </div>

<script src="settings.js"></script> 

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".delete-user").forEach(button => {
            button.addEventListener("click", function () {
                let user_id = this.getAttribute("data-user-id");
                console.log("This is the " + user_id + " that was deleted");
                Swal.fire({
                    title: "Are you sure?",
                    text: "This user will be permanently deleted!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Delete",
                    cancelButtonText: "Cancel",
                    confirmButtonColor: "#FF4444",
                    cancelButtonColor: "#3085d6"
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("delete_user.php", {
                            method: "POST",
                            body: JSON.stringify({ user_id: user_id }),
                            headers: { "Content-Type": "application/json" }                        
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log(data);
                            if (data.status === "success") {
                                Swal.fire("Deleted!", data.message, "success").then(() => {
                                    location.reload(); // Refresh to update user list
                                });
                            } else {
                                Swal.fire("Error!", data.message, "error");
                            }
                        })
                        .catch(error => console.error("Error:", error));
                    }
                });
            });
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Listen for the "Edit" button click
        document.querySelectorAll(".edit-user").forEach(button => {
            button.addEventListener("click", function () {
                // Retrieve the data attributes of the clicked "Edit" button
                const userId = this.getAttribute("data-id");
                const userName = this.getAttribute("data-name");
                const userPhone = this.getAttribute("data-phone");
                const userUsername = this.getAttribute("data-username");
                const userEmail = this.getAttribute("data-email");
                const userRole = this.getAttribute("data-role");

                // Populate the modal form with the user data
                document.getElementById("edit_user_id").value = userId;
                document.getElementById("edit_name").value = userName;
                document.getElementById("edit_phone_no").value = userPhone;
                document.getElementById("edit_username").value = userUsername;
                document.getElementById("edit_email").value = userEmail;
                document.getElementById("edit_role_id").value = userRole;

                // Open the modal (if necessary, you can show it programmatically here)
                document.getElementById("editUserModal").style.display = "block";
            });
        });
    });
</script>

</body>
</html>

<?php
require 'session_check.php';

// Only allow Operator & Super Admin
if ($user_role != 1 && $user_role != 2) {
    echo "❌ Access Denied!";
    exit;
}

// Include the database connection file
include 'db.php';

$order_id = $_GET['order_id']; // Get the order ID from the URL

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Return Items for Order #<?php echo $order_id; ?></title>
</head>
<body>

<h2>Return Items for Order #<?php echo $order_id; ?></h2>

<form method="post" action="process_return.php">
    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Model</th>
                <th>Serial Number</th>
                <th>Condition</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT item_id,item_name, model, serial_number FROM item WHERE current_order_id = '$order_id'";
            $result = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$row['item_name']}</td>
                        <td>{$row['model']}</td>
                        <td>{$row['serial_number']}</td>
                        <td>
                            <select name='status[{$row['item_id']}]'>
                                <option value='Available'>Good Condition</option>
                                <option value='Damaged'>Damaged</option>
                            </select>
                        </td>
                        <td><textarea type='text' name='remarks[{$row['item_id']}]'></textarea></td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>

    <button type="submit">Complete Return</button>
</form>

</body>
</html>

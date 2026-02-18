<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: Index.php');
    exit();
}
include 'Connection.php';

$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'asc';

$sql = "SELECT u.U_Name, u.U_Email, u.Phone_number, COUNT(b.id) AS total_booked_slots 
        FROM users u 
        LEFT JOIN bookings b ON u.U_Name = b.user_name 
        GROUP BY u.U_Name, u.U_Email, u.Phone_number
        ORDER BY total_booked_slots " . ($sort_order === 'desc' ? 'DESC' : 'ASC');

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <link rel='shortcut icon' type='image/x-icon' href='../Car/Images/Main_Image.png'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>View All Users</title>
    <link rel='stylesheet' href='./Styles.css'>
    <link rel='stylesheet' href='./bootstrap.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'>
    <style>
        .sidebar {
            height: 100vh;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
        }

        .d-flex.flex-column span {
            font-size: 18px;
        }

        #btn {
            font-size: 20px;
            padding: 8px 12px;
            height: auto;
            border-radius: 8px;
        }

        .sort-btn {
            padding: 5px 10px;
            margin: 0 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .sort-btn:hover {
            transform: translateY(-2px);
        }

        .sort-btn.active {
            background-color: #007bff;
            color: white;
        }

        .sort-container {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class='bg-content'>
    <div class='sidebar'>
        <?php include './Includes/sidebar.php';
        ?>
    </div>
    <div class='main-content'>
        <div class='container col-11'>
            <h2 class='text-center'>User Details</h2>
            <hr class='w-25 mx-auto border-dark'>

            <div class='sort-container'>
                <span class='mr-2'>Sort by Total Bookings:</span>
                <a href='?sort=asc' class="sort-btn btn <?php echo $sort_order === 'asc' ? 'btn-primary' : 'btn-secondary'; ?>">
                    <i class='fas fa-sort-amount-up-alt'></i> Ascending
                </a>
                <a href='?sort=desc' class="sort-btn btn <?php echo $sort_order === 'desc' ? 'btn-primary' : 'btn-secondary'; ?>">
                    <i class='fas fa-sort-amount-down-alt'></i> Descending
                </a>
            </div>

            <table class='table table-striped h5 mt-3'>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Contact No</th>
                        <th>Total Booked Slots</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                        <tr>
                            <td><?php echo $i++;
                                ?></td>
                            <td class='pt-4'><?php echo htmlspecialchars($row['U_Name']);
                                                ?></td>
                            <td class='pt-4'><?php echo htmlspecialchars($row['U_Email']);
                                                ?></td>
                            <td><?php echo htmlspecialchars($row['Phone_number'] ?: 'N/A');
                                ?></td>
                            <td class='pt-4'><?php echo htmlspecialchars($row['total_booked_slots']);
                                                ?></td>
                        </tr>
                    <?php }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src='https://code.jquery.com/jquery-3.2.1.slim.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js'></script>
    <script src='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js'></script>
</body>

</html>
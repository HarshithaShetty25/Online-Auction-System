<?php
require_once('connection.php');
include_once('navigation.php');

// Check if user is logged in
if (!isset($_COOKIE['user_id'])) {
    header('location:login.php');
    exit;
}

$user_id = $_COOKIE['user_id'];

$query = "SELECT ai.item_name, ai.image, ai.due_date, ai.item_id, ab.bid_amount
          FROM auction_bids ab
          JOIN auction_item ai ON ab.item_id = ai.item_id
          WHERE ab.user_id = $user_id
          ORDER BY ai.due_date DESC";

$result = $conn->query($query);
?>

<html>
<head>
    <title>My Bids</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <style>
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
<div class="container">
    <h3 class="mt-4 mb-3">My Bids</h3>
    <div class="row row-cols-1 row-cols-md-3">
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $status = (date("Y-m-d") <= $row['due_date']) ? "Active" : "Expired";
                echo '<div class="col mb-4">
                    <div class="card h-100">
                        <img src="' . htmlspecialchars($row['image']) . '" class="card-img-top" alt="' . htmlspecialchars($row['item_name']) . '">
                        <div class="card-body">
                            <h5 class="card-title">' . htmlspecialchars($row['item_name']) . '</h5>
                            <p class="card-text">Your Bid: Rs. ' . htmlspecialchars($row['bid_amount']) . '</p>
                            <span class="badge badge-' . ($status == "Active" ? "success" : "danger") . '">' . $status . '</span>
                            <a href="singleItem.php?itemId=' . $row['item_id'] . '&itemName=' . urlencode($row['item_name']) . '" class="btn btn-sm btn-outline-primary mt-2">View Item</a>
                        </div>
                    </div>
                </div>';
            }
        } else {
            echo "<p>You have not placed any bids yet.</p>";
        }
        ?>
    </div>
</div>
</body>
</html>

<?php
    require_once('connection.php');
    include_once('navigation.php');
    $CURRENTDATE = date("Y-m-d");
?>

<html>
<head>
    <title>Auction - Home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sulphur+Point:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/master.css">
    <style>
        .countdown { font-size: 14px; color: #555; margin-top: 5px; }
    </style>
</head>
<body>
<div class="container">
    <div class="body">
        <div class="row row-cols-1 row-cols-md-4">
            <?php
                $query = "SELECT `image`, `item_name`, `due_date`, `item_id`, `init_bid`, `current_bid` FROM `auction_item`";
                $result = $conn->query($query);
                $rows = $result->num_rows;
                if ($rows >= 1) {
                    while ($data = $result->fetch_assoc()) {
                        $name = $data['item_name'];
                        $image = $data['image'];
                        $due_date = $data['due_date'];
                        $price = ($data['current_bid'] != 0) ? $data['current_bid'] : $data['init_bid'];
                        $id = $data['item_id'];
                        $encodedName = urlencode($name);
                        $dueDateTime = strtotime($due_date . " 23:59:59") * 1000;

                        // Fetch bid count
                        $bidCountQuery = "SELECT COUNT(*) AS bid_count FROM auction_bids WHERE item_id = '$id'";
                        $bidCountResult = $conn->query($bidCountQuery);
                        $bidCountRow = $bidCountResult->fetch_assoc();
                        $bidCount = $bidCountRow['bid_count'];

                        echo '<div class="col mb-4">
                            <div class="card h-100">
                                <img src="' . $image . '" class="card-img-top" alt="' . $name . '" height="300">
                                <div class="card-body">
                                    <h5 class="card-title">' . $name . '</h5>';

                        if ($CURRENTDATE <= $due_date) {
                            echo '<font color="green">Active</font>';
                        } else {
                            echo '<font color="red">Expired</font>';
                        }

                        echo '<h5 style="float:right;">Rs. ' . $price . '</h5>';

                        echo '<p class="mt-2 text-muted">Bids: ' . $bidCount . '</p>';

                        if ($CURRENTDATE <= $due_date) {
                            echo '<div class="countdown" id="countdown-' . $id . '">Loading timer...</div>';
                            echo '<script>
                                const countdown' . $id . ' = document.getElementById("countdown-' . $id . '");
                                const endTime' . $id . ' = new Date(' . $dueDateTime . ').getTime();
                                const interval' . $id . ' = setInterval(function() {
                                    const now = new Date().getTime();
                                    const distance = endTime' . $id . ' - now;
                                    if (distance < 0) {
                                        countdown' . $id . '.innerHTML = "Expired";
                                        clearInterval(interval' . $id . ');
                                    } else {
                                        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                        countdown' . $id . '.innerHTML = "Ends in: " + days + "d " + hours + "h " + minutes + "m " + seconds + "s";
                                    }
                                }, 1000);
                            </script>';
                        }

                        echo '<br><a href="singleItem.php?itemId=' . $id . '&itemName=' . $encodedName . '" class="btn btn-sm btn-outline-primary mt-3">Place Bid</a>
                                </div>
                            </div>
                        </div>';
                    }
                }
            ?>
        </div>
    </div>
</div>
</body>
</html>

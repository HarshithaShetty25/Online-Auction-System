<?php
require_once('connection.php');
include_once('navigation.php');

$CURRENTDATE = date('Y-m-d');
$item_id = $_GET['itemId'];
$item_name = $_GET['itemName'];

$query = "SELECT * FROM `auction_item` WHERE `item_id` = '$item_id'";
$result = $conn->query($query);
$rows = $result->num_rows;

if ($rows == 1) {
    $data = $result->fetch_assoc();
    $image = $data['image'];
    $init_bid = $data['init_bid'];
    $current_bid = $data['current_bid'];
    $due_date = $data['due_date'];
    $post_date = $data['post_date'];
    $category = $data['category'];
    $item_condition = $data['item_condition'];
    $location = $data['location'];
    $description = $data['description'];
    $poster_id = $data['item_poster'];
    $highest_bidder = $data['highest_bidder'];
    $poster_name = '';
    $winnerEmail = '';
    $price = ($current_bid > 0) ? $current_bid : $init_bid;

    $query = "SELECT `user_first_name`, `user_last_name`, `user_email` FROM `auction_user` WHERE `user_id` = '$poster_id'";
    $result = $conn->query($query);
    if ($result->num_rows == 1) {
        $data = $result->fetch_assoc();
        $poster_name = $data['user_first_name'] . ' ' . $data['user_last_name'];
        $winnerEmail = $data['user_email'];
    } else {
        echo 'An error occurred fetching user\'s name';
    }
} else {
    header("Location: singleItem.php?itemId=$item_id&itemName=$item_name");
    exit();
}
?>

<html>
<head>
    <title><?php echo $item_name; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sulphur+Point:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/master.css">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col col-4">
            <img src="<?php echo $image ?>" class="img-fluid pl-2" alt="Item image">
            <h3>Posted By: <?php echo $poster_name ?></h3>
            <h5>Condition: <?php echo $item_condition ?></h5>
            <h5>Initial Bid: <?php echo $init_bid ?></h5>
            <h6>Posted On: <?php echo $post_date ?></h6>
            <h6>Active till: <?php echo $due_date ?></h6>
        </div>

        <div class="col col-4">
            <?php
            $user_id = $_COOKIE['user_id'] ?? '';

            if ($CURRENTDATE >= $due_date) {
                echo '<font color="red">Auction Ended</font>';
                echo "<font color='green'>Won by $winnerEmail</font>";
            } elseif ($user_id == $highest_bidder) {
                echo '
                    <div class="alert alert-success" role="alert">
                        <center>You\'ve outbid everyone!</center>
                    </div>
                ';
            } elseif ($poster_id == $user_id) {
                echo '
                    <div class="alert alert-secondary" role="alert">
                        <center>Can\'t place bid on your product!</center>
                    </div>
                ';
            } else {
                echo '
                    <form method="post">
                        <div class="input-group">
                            <input class="form-control form-control-lg" type="number" name="bid" required placeholder="Place your bid">
                            <input type="submit" value="BID" class="btn btn-outline-primary btn-lg ml-4 pl-4 pr-4" name="bid_placement">
                        </div>
                    </form>
                ';
            }

            if (isset($_POST['bid_placement'])) {
                $bid = $_POST['bid'];
                if ($bid > $price) {
                    $user_name = $_COOKIE['user_first_name'] . ' ' . $_COOKIE['user_last_name'];

                    // Update current bid and highest bidder
                    $query = "UPDATE `auction_item` SET `current_bid`='$bid', `highest_bidder`='$user_id' WHERE `item_id`='$item_id'";
                    $conn->query($query);

                    // Insert or update the user's bid
                    $query = "INSERT INTO `auction_bids` (`item_id`, `user_id`, `user_name`, `user_bid`)
                              VALUES ('$item_id', '$user_id', '$user_name', '$bid')
                              ON DUPLICATE KEY UPDATE user_bid = '$bid'";
                    $conn->query($query);

                    header("Location: singleItem.php?itemId=$item_id&itemName=$item_name");
                    exit();
                } else {
                    echo "<font color='red'><center>Please Bid Higher than the current bid</center></font>";
                }
            }
            ?>
            <br>
            <div class="border pl-3 pr-3" style="text-align:justify;">
                <?php echo $description ?>
            </div>
        </div>

        <div class="col col-4">
            <h5 style='float:left;'>Current <br> Highest</h5>
            <h1 style='float:right;'>Rs. <?php echo $price ?></h1>
            <h1>&nbsp;BID</h1>
            <?php
            if ($current_bid == 0) {
                echo 'Be the first one to bid';
            } else {
                $query = "SELECT `user_name`, `user_bid`, `user_id` FROM `auction_bids` 
                          WHERE `item_id` = $item_id ORDER BY `user_bid` DESC";
                $result = $conn->query($query);
                while ($data = $result->fetch_assoc()) {
                    $user_name = $data['user_name'];
                    $user_bid = $data['user_bid'];
                    $css_class = ($_COOKIE['user_id'] == $data['user_id']) ? 'border-success' : 'border-primary';

                    echo "
                        <div class='card mt-3'>
                            <div class='card-header $css_class'>
                                $user_name
                                <span style='float:right'>Rs. $user_bid</span>
                            </div>
                        </div>
                    ";
                }
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>

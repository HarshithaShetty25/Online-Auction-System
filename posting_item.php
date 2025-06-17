<?php
require_once('connection.php');
require_once('navigation.php');
$userID = $_COOKIE['user_id'];
$userName = $_COOKIE['user_first_name'] . ' ' . $_COOKIE['user_last_name'];
?>
<html>
<head>
    <title>Post an Item</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sulphur+Point:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/master.css">
</head>
<body>
<div class="container">
    <form method="POST" enctype="multipart/form-data">
        <div class="input-group">
            <input type="text" name="item_name" required placeholder='Item Name' class='form-control'>
            <select name="condition" class='form-control ml-5 mr-5'>
                <option value="old">Old</option>
                <option value="new">New</option>
            </select>
            <input type="text" name="item_category" required placeholder='Category' class='form-control'>
        </div>
        <div class="mt-2 input-group">
            <input type="number" name="initbid" required placeholder='Initial Price' class='form-control'>
            <input type="text" name="location" required placeholder='Location' class='form-control ml-5 mr-5'>
            <input type="number" name="duration" placeholder='Duration of the Auction (in days)' class = 'form-control'>
        </div>
        <br>
        <label for='description'>Please Provide Detailed Description of your Product</label>
        <textarea rows='3' id='description' name="description"  class="mt-3 form-control"></textarea>
        <br>
        <div class="form-group col-3" style='float:right;'>
            <label for="image">Choose Product Image</label>
            <input type="file" id='image' name='fileToUpload' required>
        </div>
        <br>
        <input type="submit" name='postitem' value="Post Item" class='btn btn-lg btn-primary'>
    </form>
</div>
</body>
</html>

<?php
if (isset($_POST['postitem'])) {
    $date = date("Y-m-d");

    // Escape all inputs to avoid SQL errors
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $init_bid = mysqli_real_escape_string($conn, $_POST['initbid']);
    $number = mysqli_real_escape_string($conn, $_POST['duration']);
    $category = mysqli_real_escape_string($conn, $_POST['item_category']);
    $item_condition = mysqli_real_escape_string($conn, $_POST['condition']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $dueDate = date("Y-m-d", strtotime($date . " + $number days"));
    $image = '';

    $file = $_FILES['fileToUpload'];
    $fileName = $file['name'];
    $fileTempName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];

    $fileExt = explode('.', $fileName);
    $fileActualExtension = strtolower(end($fileExt));
    $allowed = array('jpg', 'png', 'jpeg');

    if (in_array($fileActualExtension, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 500000) {
                $fileNameNew = uniqid('', true) . '.' . $fileActualExtension;
                $fileDestination = 'uploads/' . $fileNameNew;
                move_uploaded_file($fileTempName, $fileDestination);
                $image = mysqli_real_escape_string($conn, $fileDestination);

                // Insert item
                $query = "INSERT INTO `auction_item` (`item_name`, `init_bid`, `current_bid`, `due_date`, `post_date`, `category`, `item_condition`, `location`, `description`, `image`, `item_poster`, `highest_bidder`) 
                          VALUES ('$item_name','$init_bid','0','$dueDate','$date','$category','$item_condition','$location','$description','$image','$userID','$userID')";
                $result = $conn->query($query);
                $id = $conn->insert_id;

                // Insert initial bid
                $query = "INSERT INTO `auction_bids` (`item_id`, `user_id`, `user_name`, `user_bid`) 
                          VALUES('$id','$userID','$userName','$init_bid')";
                $result = $conn->query($query);

                header('Location: index.php');
                exit;
            } else {
                echo "<script>alert('Image file is too large.');</script>";
            }
        } else {
            echo "<script>alert('Error uploading file.');</script>";
        }
    } else {
        echo "<script>alert('Invalid image format. Only JPG, PNG, JPEG allowed.');</script>";
    }
}
?>

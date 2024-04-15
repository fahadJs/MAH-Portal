<?php

require_once('../db/db.php');

$query = "SELECT * FROM deals";
$result = mysqli_query($connection, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP MySQL Project</title>
</head>
<body>
    <h3>Deals List</h3>
    <ul>
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <li><?php echo $row['deal_name']; ?> - <?php echo $row['retail_price']; ?></li>
        <?php endwhile; ?>
    </ul>
</body>
</html>

<?php mysqli_close($connection); ?>

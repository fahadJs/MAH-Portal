<?php
require_once('../db/db.php'); // Include your database connection file

$tomorrow = date('Y-m-d', strtotime('+1 day'));
$tomorrowDay = date('l, Y-m-d', strtotime('+1 day'));
// $tomorrow = date('l, Y-m-d');
// Send WhatsApp message
$message = "BREAKFAST ORDERS FOR " . $tomorrowDay . ":\n\n";
$query = "SELECT * FROM orders_breakfast WHERE date = '$tomorrow'";
$result = mysqli_query($connection, $query);


// Initialize an empty array to store dish counts
$dishCounts = array();
// Initialize Roti count
$totalRotiCount = 0;

// Loop through the database results
while ($row = mysqli_fetch_assoc($result)) {
    // Split the dish string into individual dishes
    $dishes = explode(',', $row['dish']);

    // Extract the number of persons for this row
    $persons = intval($row['persons']);

    // Loop through each dish in the row
    foreach ($dishes as $dish) {
        // Trim whitespace from the dish name
        $dish = trim($dish);

        // Check if Roti count is specified separately
        $rotiCount = 0;
        if (preg_match('/(\d+)\s*Roti/i', $dish, $matches)) {
            $rotiCount = intval($matches[1]);
            $totalRotiCount += $rotiCount * $persons; // Update total Roti count
            $dish = preg_replace('/(\d+)\s*Roti/i', 'Roti', $dish); // Remove the number before Roti
        }

        // If the dish is not empty
        if (!empty($dish)) {
            // Calculate the count for this dish considering the number of persons
            $dishCount = $persons;

            // If the dish is already counted, increment its count
            if (isset($dishCounts[$dish])) {
                $dishCounts[$dish] += $dishCount;
            } else {
                // If the dish is not counted yet, initialize its count
                $dishCounts[$dish] = $dishCount;
            }
        }
    }
}

// Initialize the message string
$message = "BREAKFAST ORDERS COUNT FOR " . $tomorrowDay . ":\n\n";

// Loop through the aggregated dish counts
foreach ($dishCounts as $dish => $count) {
    // Append the dish and its count to the message
    if ($dish != "Roti") {
        $message .= $dish . " - " . $count;
    } else if ($dish == "Roti") {
        // If total Roti count is greater than 0, add it to the message
        if ($totalRotiCount > 0) {
            $message .= " Roti - " . $totalRotiCount;
        }
    }

    $message .= "\n";
}


$curl = curl_init();

$message = rawurlencode($message);
$contact = '923152368494';

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://anunzio0786.website:8443/api/send/' . $message . '/' . urlencode($contact),
    CURLOPT_RETURNTRANSFER => true, // Return the response instead of outputting it
    CURLOPT_SSL_VERIFYHOST => true, // Disable SSL host verification
    CURLOPT_SSL_VERIFYPEER => false, // Disable SSL peer verification
));

// Execute cURL request
$response = curl_exec($curl);
curl_close($curl);

// Redirect back to the page after insertion
header("Location: ../public/orders_breakfast.php?success=true");
// header("Location: ../process/order_count.php");
exit();

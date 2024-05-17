<?php
require_once('../db/db.php'); // Include your database connection file


$tomorrow = date('Y-m-d', strtotime('+1 day'));
$tomorrowDay = date('l, Y-m-d', strtotime('+1 day'));
// $tomorrow = date('l, Y-m-d');
// Send WhatsApp message
$message = "ORDERS FOR " . $tomorrowDay . ":\n\n";
$query = "SELECT * FROM orders WHERE date = '$tomorrow'";
$result = mysqli_query($connection, $query);







while ($row = mysqli_fetch_assoc($result)) {
    if (!empty($row['dish'])) {
        $orderInfo = "*" . $row['cust_number'] . ": (" . $row['persons'] . " Person)*\n" . str_replace(',', ",\n", $row['dish']) . "\n------------------------\n";
        if (!empty($row['additional'])) {
            $orderInfo .= "(" . $row['additional'] . ") \n\n";
        } else {
            $orderInfo .= "\n";
        }
        $message .= $orderInfo;
    }
}

























// while ($row = mysqli_fetch_assoc($result)) {
//     // Check if the dish information is not empty
//     if (!empty($row['dish'])) {
//         // Check if the dish contains "(LUNCH)" and "(DINNER)" sections
//         if (strpos($row['dish'], "(LUNCH):") !== false) {
//             // Split the string into "Lunch" and "Dinner" sections
//             $sections = explode("(LUNCH):", $row['dish']);

//             // Initialize variable to store the order info
//             $orderInfo = "";

//             // Check if lunch section exists
//             if (isset($sections[1])) {
//                 // Extract lunch section
//                 $lunchSection = explode("(DINNER):", $sections[1]);
//                 $lunch = trim($lunchSection[0]);

//                 // Construct the order info with lunch section
//                 $orderInfo = "*" . $row['cust_number'] . ": (" . $row['persons'] . " Person)*\n" . str_replace(',', ",\n", $lunch) . "\n------------------------\n";

//                 // Check if additional information exists and append it
//                 if (!empty($row['additional'])) {
//                     $orderInfo .= "(" . $row['additional'] . ") \n\n";
//                 } else {
//                     $orderInfo .= "\n";
//                 }

//                 // Append order info to the message
//                 $message .= $orderInfo;
//             }
//         } else {
//             // Process other items normally
//             $orderInfo = "*" . $row['cust_number'] . ": (" . $row['persons'] . " Person)*\n" . str_replace(',', ",\n", $row['dish']) . "\n------------------------\n";

//             // Check if additional information exists and append it
//             if (!empty($row['additional'])) {
//                 $orderInfo .= "(" . $row['additional'] . ") \n\n";
//             } else {
//                 $orderInfo .= "\n";
//             }

//             // Append order info to the message
//             $message .= $orderInfo;
//         }
//     }
// }


























// $dishCounts = [];
// $totalRotiCount = 0;
// $isLunch = false; // Initialize lunch flag

// // Query your database and get the result set ($result)

// while ($row = mysqli_fetch_assoc($result)) {
//     // Check if the row contains the lunch section
//     if (strpos($row['dish'], '(LUNCH):') !== false) {
//         $isLunch = true; // Set the flag to true to indicate lunch section
//         continue; // Skip to the next row
//     }

//     // Check if the row contains the dinner section
//     if (strpos($row['dish'], '(DINNER):') !== false) {
//         $isLunch = false; // Set the flag to false to indicate dinner section
//         continue; // Skip to the next row
//     }

//     // Skip processing if it's not within lunch section
//     if (!$isLunch) {
//         continue;
//     }

//     // Split the dish string into individual dishes
//     $dishes = explode(',', $row['dish']);

//     // Extract the number of persons for this row
//     $persons = intval($row['persons']);

//     // Loop through each dish in the row
//     foreach ($dishes as $dish) {
//         // Trim whitespace from the dish name
//         $dish = trim($dish);

//         // Check if Roti count is specified separately
//         $rotiCount = 0;
//         if (preg_match('/(\d+)\s*Roti/i', $dish, $matches)) {
//             $rotiCount = intval($matches[1]);
//             $totalRotiCount += $rotiCount * $persons; // Update total Roti count
//             $dish = preg_replace('/(\d+)\s*Roti/i', 'Roti', $dish); // Remove the number before Roti
//         }

//         // If the dish is not empty
//         if (!empty($dish)) {
//             // Calculate the count for this dish considering the number of persons
//             $dishCount = $persons;

//             // If the dish is already counted, increment its count
//             if (isset($dishCounts[$dish])) {
//                 $dishCounts[$dish] += $dishCount;
//             } else {
//                 // If the dish is not counted yet, initialize its count
//                 $dishCounts[$dish] = $dishCount;
//             }
//         }
//     }
// }

// // Initialize the message string
// $message = "ORDERS FOR " . $tomorrowDay . ":\n\n";

// // Loop through the aggregated dish counts
// foreach ($dishCounts as $dish => $count) {
//     // Append the dish and its count to the message
//     if ($dish != "Roti") {
//         $message .= $dish . " - " . $count;
//     } else if ($dish == "Roti") {
//         // If total Roti count is greater than 0, add it to the message
//         if ($totalRotiCount > 0) {
//             $message .= " Roti - " . $totalRotiCount;
//         }
//     }

//     $message .= "\n";
// }


echo $message;


$curl = curl_init();

$message = rawurlencode($message);
$contact = '923331233774';

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://anunzio0786.website:8443/api/send/' . $message . '/' . urlencode($contact),
    CURLOPT_RETURNTRANSFER => true, // Return the response instead of outputting it
    CURLOPT_SSL_VERIFYHOST => true, // Disable SSL host verification
    CURLOPT_SSL_VERIFYPEER => false, // Disable SSL peer verification
));

// Execute cURL request
$response = curl_exec($curl);
curl_close($curl);

// while ($row = mysqli_fetch_assoc($result)) {
//     if (!empty($row['dish'])) {
//         $orderInfo = "*" . $row['cust_number'] . ":* \n" . $row['dish'] . " - (" . $row['persons'] . " - Person)\n";
//         if (!empty($row['additional'])) {
//             $orderInfo .= "(" . $row['additional'] . ") \n\n";
//         } else {
//             $orderInfo .= "\n";
//         }
//         $message .= $orderInfo;
//     }
// }




echo $message;

<?php

session_start();

include "../db/db.php";

if (isset($_POST['username']) && isset($_POST['password'])) {

    function validate($data)
    {

        $data = trim($data);

        $data = stripslashes($data);

        $data = htmlspecialchars($data);

        return $data;
    }

    $uname = validate($_POST['username']);

    $pass = validate($_POST['password']);

    if (empty($uname)) {

        header("Location: /mah-portal/public/login.php?error=User Name is required");

        exit();
    } else if (empty($pass)) {

        header("Location: /mah-portal/public/login.php?error=Password is required");

        exit();
    } else {

        $sql = "SELECT * FROM admin WHERE username='$uname' AND password='$pass'";

        $result = mysqli_query($connection, $sql);

        if (mysqli_num_rows($result) === 1) {

            $row = mysqli_fetch_assoc($result);

            if ($row['username'] === $uname && $row['password'] === $pass) {

                // echo "Logged in!";

                $_SESSION['username'] = $row['username'];

                // $_SESSION['name'] = $row['name'];

                $_SESSION['admin_user_id'] = $row['id'];

                // Get the user's IP address
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                }

                // Store IP address in session
                $_SESSION['ip_address'] = $ip_address;

                // echo $ip_address;

                // Function to get the client's IP address
                // function get_client_ip()
                // {
                //     $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
                //     foreach ($ip_keys as $key) {
                //         if (array_key_exists($key, $_SERVER)) {
                //             $ips = explode(',', $_SERVER[$key]);
                //             foreach ($ips as $ip) {
                //                 $ip = trim($ip);
                //                 if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                //                     return $ip;
                //                 }
                //             }
                //         }
                //     }
                //     // return '127.0.0.1'; // Default to local IPv4 address
                // }

                // $ip_address = get_client_ip();

                // Store IP address in session
                $_SESSION['ip_address'] = $ip_address;

                header("Location: /mah-portal/public/index.php");

                exit();
            } else {

                header("Location: /mah-portal/public/login.php?error=Incorect User name or password");

                exit();
            }
        } else {

            header("Location: /mah-portal/public/login.php?error=Incorect User name or password");

            exit();
        }
    }
} else {

    header("Location: index.php");

    exit();
}

<?php 

session_start(); 

include "../db/db.php";

if (isset($_POST['username']) && isset($_POST['password'])) {

    function validate($data){

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

    }else if(empty($pass)){

        header("Location: /mah-portal/public/login.php?error=Password is required");

        exit();

    }else{

        $sql = "SELECT * FROM admin WHERE username='$uname' AND password='$pass'";

        $result = mysqli_query($connection, $sql);

        if (mysqli_num_rows($result) === 1) {

            $row = mysqli_fetch_assoc($result);

            if ($row['username'] === $uname && $row['password'] === $pass) {

                echo "Logged in!";

                $_SESSION['username'] = $row['username'];

                // $_SESSION['name'] = $row['name'];

                $_SESSION['user_id'] = $row['id'];

                header("Location: /mah-portal/public/index.php");

                exit();

            }else{

                header("Location: /mah-portal/public/login.php?error=Incorect User name or password");

                exit();

            }

        }else{

            header("Location: /mah-portal/public/login.php?error=Incorect User name or password");

            exit();

        }

    }

}else{

    header("Location: index.php");

    exit();

}
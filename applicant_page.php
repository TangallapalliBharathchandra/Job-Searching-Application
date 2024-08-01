<?php
session_set_cookie_params(24*60*60);
session_start();

require_once('model/pdo.php');

$action = isset($_POST['action']) ? $_POST['action'] : "login";

if(isset($_SESSION['user'])){
    $action = $_GET['action']??"login";
}

// print_r($_SESSION);
// echo("<br>");
// print_r($_COOKIE);
//echo($action);


// Perform the specified action
switch($action) {
    case 'login':
        if(!isset($_SESSION['message'])){
            $_SESSION['message'] = 'You must login to view this page.';
        }
        include('applicant/applicant_login.php');
        $pdo = Database::getDB();

        if(isset($_POST['email']) && isset($_POST['password'])){

            $email = $_POST['email'];
            $password = $_POST['password'];
            
            $sql1 = "SELECT * FROM applicant WHERE email = :user";

            $stmt1 = $pdo->prepare($sql1);
            //echo("<pre>\n".$sql1."\n</pre>\n");
            $stmt1 ->execute(array(
                ':user' => $email )
            );
            if ($stmt1->rowCount() > 0) {
                $row = $stmt1->fetch(PDO::FETCH_ASSOC);
                $storedPasswordHash = $row["password"];

                if (password_verify($password, $storedPasswordHash)) {
                    $_SESSION['user'] = $email;
                    $_SESSION['applicant_id'] = $row['applicant_id'];
                    if (!isset($_COOKIE['applicant_visit'])) {
                        $visitCount = 1;
                        setcookie('applicant_visit', $visitCount, time() + 24 * 60 * 60);
                    } else {
                        $visitCount = $_COOKIE['applicant_visit'] + 1;
                        setcookie('applicant_visit', $visitCount);
                    }
                    header("Location:applicant_page.php?action=applicant_ui");
                    exit();
                } else {
                    $_SESSION['message'] = "Invalid Credentials";
                    header("Location:applicant_page.php?action=login");
                    exit();
                }
            }
            else {
                $_SESSION['message'] =  "Invalid Credentials";
                header("Location:applicant_page.php?action=login");
                exit();
            }
        }
        break;
    case 'register':
        include('applicant/applicant_register.php');
        break;
    case 'menu':
        include('index.php');
        break;
    case 'applicant_ui':
        include('applicant/applicant_ui.php');
        break;
    case 'logout':
        session_destroy();
        $_SESSION['message'] = 'You have been logged out.';
        include('applicant/applicant_login.php');
        break;
}
?>
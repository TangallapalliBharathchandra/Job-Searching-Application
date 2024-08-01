<?php
session_set_cookie_params(24*60*60);
session_start();

require_once('model/pdo.php');

$action = isset($_POST['action']) ? $_POST['action'] : "login";

if(isset($_SESSION['company'])){
    $action = $_GET['action']??"login";
}

switch($action) {
    case 'login':

        if(!isset($_SESSION['message'])){
            $_SESSION['message'] = 'You must login to view this page.';
        }
        include('company/company_login.php');
        $pdo = Database::getDB();

        if(isset($_POST['email']) && isset($_POST['password'])){

            $email = $_POST['email'];
            $password = $_POST['password'];
            $sql1 = "SELECT * FROM company WHERE email = :user";

            $stmt1 = $pdo->prepare($sql1);

            $stmt1 ->execute(array(
                ':user' => $email )
            );
            if ($stmt1->rowCount() > 0) {
                $row = $stmt1->fetch(PDO::FETCH_ASSOC);
                $storedPasswordHash = $row["password"];

                if (password_verify($password, $storedPasswordHash)) {
                    $_SESSION['company'] = $email;
                    $_SESSION['company_id'] = $row['company_id'];
                    if (!isset($_COOKIE['company_visit'])) {
                        $visitCount = 1;
                        setcookie('company_visit', $visitCount, time() + 24 * 60 * 60);
                    } else {
                        $visitCount = $_COOKIE['company_visit'] + 1;
                        setcookie('company_visit', $visitCount);
                    }
                    header("Location:company_page.php?action=company_ui");
                    exit();
                } else {
                    $_SESSION['message']  = "Invalid Credentials";
                    header("Location:company_page.php?action=login");
                    exit();
                }
            } 
            else {
                $_SESSION['message']  =  "Invalid Credentials";
                header("Location:company_page.php?action=login");
                exit();
            }
        }
        break;
    case 'register':
        include('company/company_register.php');
        break;
    case 'menu':
        include('index.php');
        break;
    case 'company_ui':
        include('company/company_ui.php');
        break;
    case 'logout':
        session_destroy();
        $_SESSION['message'] = 'You have been logged out.';
        include('company/company_login.php');
        break;
}
?>
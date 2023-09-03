<?php

session_start();

if ((!isset($_POST['login'])) || (!isset($_POST['haslo']))) {
    header('Location: index.php');
    exit();
}

require_once "database.php";

// $polaczenie = @new mysqli($host, $db_user, $db_password, $db_name);

// if ($polaczenie->connect_errno != 0) {
//     echo "Error: " . $polaczenie->connect_errno;
// } else {
$login = $_POST['login'];
// $haslo = $_POST['haslo'];

$login = htmlentities($login, ENT_QUOTES, "UTF-8");
$haslo = filter_input(INPUT_POST, 'haslo');

// $sql = "SELECT * FROM uzytkownicy WHERE user='$login'";
$userQuery = $db->prepare('SELECT * FROM uzytkownicy WHERE user = :login');
$userQuery->bindValue(':login', $login, PDO::PARAM_STR);
$userQuery->execute();

$user = $userQuery->fetch();

// if ($rezultat = @$polaczenie->query($sql)) {
// $ilu_userow = $rezultat->num_rows;
// if ($ilu_userow > 0) {
//     $wiersz = $rezultat->fetch_assoc();
//     if (password_verify($haslo, $wiersz['pass'])) {
//         $_SESSION['zalogowany'] = true;
//         $_SESSION['id'] = $wiersz['id'];
//         $_SESSION['user'] = $wiersz['user'];
//         $_SESSION['drewno'] = $wiersz['drewno'];
//         $_SESSION['kamien'] = $wiersz['kamien'];
//         $_SESSION['zboze'] = $wiersz['zboze'];
//         $_SESSION['email'] = $wiersz['email'];
//         $_SESSION['dnipremium'] = $wiersz['dnipremium'];

//         unset($_SESSION['blad']);
//         $rezultat->free_result();
//         header('Location:gra.php');
//     } else {

//         $_SESSION['blad'] = '<span style="color:red">Nieprawidłowy login lub hasło!</span>';
//         header('Location: index.php');
//     }
// } else {

//     $_SESSION['blad'] = '<span style="color:red">Nieprawidłowy login lub hasło!</span>';
//     header('Location: index.php');
// }
// }

// $polaczenie->close();
// }
if ($user && password_verify($haslo, $user['pass'])) {
    $_SESSION['zalogowany'] = true;
    $_SESSION['id'] = $user['id'];
    $_SESSION['user'] = $user['user'];
    $_SESSION['drewno'] = $user['drewno'];
    $_SESSION['kamien'] = $user['kamien'];
    $_SESSION['zboze'] = $user['zboze'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['dnipremium'] = $user['dnipremium'];

    unset($_SESSION['blad']);
    header('Location:gra.php');
} else {
    $_SESSION['blad'] = true;
    header('Location: index.php');
    exit();
}

?>
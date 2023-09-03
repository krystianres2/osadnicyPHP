<?php
session_start();

if (isset($_POST['email'])) {
    // Successful validation flag
    $wszystko_OK = true;

    $nick = $_POST['nick'];

    if (strlen($nick) < 3 || strlen($nick) > 20) {
        $wszystko_OK = false;
        $_SESSION['e_nick'] = "Nick musi posiadać od 3 do 20 znaków!";
    }

    if (!ctype_alnum($nick)) {
        $wszystko_OK = false;
        $_SESSION['e_nick'] = "Nick może składać się tylko z liter i cyfr (bez polskich znaków)";
    }

    $email = $_POST['email'];
    $emailB = filter_var($email, FILTER_SANITIZE_EMAIL);

    if (filter_var($emailB, FILTER_VALIDATE_EMAIL) === false || $emailB !== $email) {
        $wszystko_OK = false;
        $_SESSION['e_email'] = "Podaj poprawny adres e-mail";
    }

    $haslo1 = $_POST['haslo1'];
    $haslo2 = $_POST['haslo2'];

    if (strlen($haslo1) < 8 || strlen($haslo1) > 20) {
        $wszystko_OK = false;
        $_SESSION['e_haslo'] = "Hasło musi posiadać od 8 do 20 znaków";
    }

    if ($haslo1 !== $haslo2) {
        $wszystko_OK = false;
        $_SESSION['e_haslo'] = "Hasła nie są identyczne";
    }

    $haslo_hash = password_hash($haslo1, PASSWORD_DEFAULT);

    if (!isset($_POST['regulamin'])) {
        $wszystko_OK = false;
        $_SESSION['e_regulamin'] = "Potwierdź akceptację regulaminu";
    }

    require_once "database.php"; // Assuming this file contains your PDO database connection.

    try {
        $userQuery = $db->prepare('SELECT id FROM uzytkownicy WHERE email = :email');
        $userQuery->bindValue(':email', $email, PDO::PARAM_STR);
        $userQuery->execute();
        $rezultat = $userQuery->fetch();

        if ($rezultat) {
            $wszystko_OK = false;
            $_SESSION['e_email'] = "Istnieje już konto przypisane do tego adresu e-mail";
        }

        $userQuery = $db->prepare('SELECT id FROM uzytkownicy WHERE user = :nick');
        $userQuery->bindValue(':nick', $nick, PDO::PARAM_STR);
        $userQuery->execute();
        $rezultat = $userQuery->fetch();

        if ($rezultat) {
            $wszystko_OK = false;
            $_SESSION['e_nick'] = "Istnieje już gracz o takim nicku!";
        }

        if ($wszystko_OK) {
            $userQuery = $db->prepare('INSERT INTO uzytkownicy VALUES (NULL, :nick, :haslo_hash, :email, 100, 100, 100, NOW() + INTERVAL 14 DAY)');
            $userQuery->bindValue(':nick', $nick, PDO::PARAM_STR);
            $userQuery->bindValue(':haslo_hash', $haslo_hash, PDO::PARAM_STR);
            $userQuery->bindValue(':email', $email, PDO::PARAM_STR);
            $userQuery->execute();
            $_SESSION['udanarejestracja'] = true;
            header('Location: witamy.php');
            exit(); // Make sure to exit after a header redirect.
        }
    } catch (Exception $e) {
        echo '<span style="color:red;">Błąd serwera! Przepraszamy za niedogodności i prosimy o rejestrację w innym terminie!</span>';
        echo '<br> Informacja developerska: ' . $e;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Osadnicy - załóż darmowe konto</title>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <style>
        .error {
            color: red;
            margin-top: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <form method="post">
        Nickname: <br> <input type="text" name="nick"><br>
        <?php
        if (isset($_SESSION['e_nick'])) {
            echo '<div class="error">' . $_SESSION['e_nick'] . '</div>';
            unset($_SESSION['e_nick']);
        }
        ?>

        E-mail: <br> <input type="text" name="email"><br>
        <?php
        if (isset($_SESSION['e_email'])) {
            echo '<div class="error">' . $_SESSION['e_email'] . '</div>';
            unset($_SESSION['e_email']);
        }
        ?>
        Twoje hasło: <br> <input type="password" name="haslo1"><br>
        <?php
        if (isset($_SESSION['e_haslo'])) {
            echo '<div class="error">' . $_SESSION['e_haslo'] . '</div>';
            unset($_SESSION['e_haslo']);
        }
        ?>
        Powtórz hasło: <br> <input type="password" name="haslo2"><br>

        <label>
            <input type="checkbox" name="regulamin"> Akceptuję regulamin
        </label><br>
        <?php
        if (isset($_SESSION['e_regulamin'])) {
            echo '<div class="error">' . $_SESSION['e_regulamin'] . '</div>';
            unset($_SESSION['e_regulamin']);
        }
        ?>
        <input type="submit" value="Zarejestruj się">
    </form>

</body>

</html>

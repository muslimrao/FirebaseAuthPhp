<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
<style>
    label {

        font-weight: bold;

    }
</style>
<?php
require __DIR__ . '/vendor/autoload.php';
include("email.php");

$_googleJsonFile = "{YOUR SERVICE JSON FILE}";

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Exception\Auth\UserNotFound;


$isFail                             = [];
$isSuccess                          = FALSE;


$factory                = (new Factory)->withServiceAccount(__DIR__ . '/' . $_googleJsonFile);
$auth                   = $factory->createAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {



    if (isset($_POST['password'])) {
        $email                          = $_POST['email'];
        $password                       = $_POST["password"];
        if ($email == "") {
            $isFail[]                   = "Email address cannot be empty";
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $isFail[]                   = "Email address in not valid.";
        } else if ($password == "") {
            $isFail[]                   = "Password cannot be empty";
        } else if (strlen($password) <= 6) {
            $isFail[]                   = "Password must be greater than 6 characters";
        }


        if (count($isFail) <= 0) {

            try {
                $user               = $auth->signInWithEmailAndPassword($email, $password);
                $isSuccess          = "Your credentials are working !!";
                unset($_POST);
            } catch (UserNotFound $e) {
                $isFail[]       = $e->getMessage();
            } catch (FailedToSignIn $e) {
                $isFail[]       = $e->getMessage();
            }
        }
    } else {
        $email                          = $_POST['email'];
        if ($email == "") {
            $isFail[]                   = "Email address cannot be empty";
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $isFail[]                   = "Email address in not valid.";
        }


        if (count($isFail) <= 0) {

            try {
                $user               = $auth->getUserByEmail($email);
                $password           = substr(sha1(time()), 0, 16);
                $updatedUser        = $auth->changeUserPassword($user->uid, $password);
                $send_email         = send_email($email, "Your email: " . $email . " <br>Your Password: " . $password);
                $isSuccess          = "Your Password is Changed - Please check your <strong>email</strong>";
                unset($_POST);
            } catch (UserNotFound $e) {
                $isFail[]       = $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
</head>

<body>
    <div style="text-align: center;" class="container-sm">

        <?php
        if (count($isFail) > 0) {
        ?>
            <div class="alert alert-danger">
                <!--<strong>Error!</strong>-->
                <?php echo implode("<br>", $isFail); ?>
            </div>
        <?php
        } else if ($isSuccess !== FALSE) {
        ?>
            <div class="alert alert-success">
                <!--<strong>Error!</strong>-->
                <?php echo $isSuccess; ?>
            </div>
        <?php
        }
        ?>

        <label>List of Users !</label>
        <?php
        echo '<ul>';
        foreach ($auth->listUsers() as $luser) {
            echo '<li>' . $luser->email . '</li>';
        }
        echo '</ul>';
        ?>

        <br>
        <br>

        <hr>

        <br>
        <br>


        <label>Forgot Password !</label>





        <form action="" method="post">
            <input type="text" name="email" id="">
            <button type="submit">Send me Password !</button>
        </form>



        <br>
        <br>

        <hr>

        <br>
        <br>

        <label>Verify User Credentials !</label>
        <form action="" method="post">
            <input type="text" name="email" id="">
            <input type="password" name="password" id="">

            <button type="submit">Verify Credentials!</button>
        </form>
    </div>
</body>

</html>
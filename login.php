<?php
    session_start(); // Start session

    // Establish DB Connection
    $host = "303.itpwebdev.com";
    $user = "gjpark_db_user";
    $pass = "uscitp303";
    $db = "gjpark_final";
    $login_error = '';

    $mysqli = new mysqli($host, $user, $pass, $db);

    // Check for connection errors
    if($mysqli->connect_errno) {
        echo $mysqli->connect_error;
        exit();
    }

    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password']; 

        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if($password == $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                header("Location: homepage.html");
                exit();
            } else {
                $login_error = "Incorrect username or password.";
            }
        } else {
            $login_error = "Incorrect username or password.";
        }
        // Close statement
        $stmt->close();
        // Close DB Connection
        $mysqli->close();
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" 
    content="First page that the user should see. User has to login through this page in order to
    access the rest of the website. User is able to navigate only to the create account page from this page.">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="shared.css">
    <title>303TripPlanner | Login</title>

    <style>
        @media(max-width: 767px) {
            h1 {
                margin-top: -25px;
            }
        }
        h1 {
            margin-top: 100px;
        }
        #web-title {
            font-weight: bold;
        }
        #login-container {
            background-color: white;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding-top: 30px;
            padding-bottom: 30px;
            padding-right: 40px;
            padding-left: 40px;
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding-right: 60px;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .error {
            margin-top: 5px;
            color: red;
        }

    </style>
</head>
<body>
    <h1>Login</h1>
        <div id="login-container">
            <form id="login-form" action="" method="post">
                <div id="web-title" class="text-center">
                    303TripPlanner
                </div>
                <div class="form-group">
                    Username
                    <input type="text" id="username" name="username" placeholder="Username">
                    <div id="username-error" class="error">
                        <?php 
                            if(!empty($login_error)) {
                                echo $login_error; 
                            } 
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    Password
                    <input type="password" id="password" name="password" placeholder="Password">
                    <div id="password-error" class="error"></div>
                </div>
                <button type="submit" id="login-btn" class="btn btn-primary">
                    Log In
                </button>
                <button type="button" id="signup-btn" class="btn btn-primary" onclick="location.href='signup.php';">
                    Sign Up
                </button>
            </form>
        </div>

    <script>
        document.getElementById("login-form").onsubmit = function(event) {
            let validForm = true
            document.getElementById("username-error").textContent = ""
            document.getElementById("password-error").textContent = ""

            const username = document.getElementById("username").value.trim()
            const password = document.getElementById("password").value.trim()

            if(username.length === 0) {
                validForm = false
                document.getElementById("username-error").innerHTML = "Username cannot be empty."
            }
            if(password.length === 0) {
                validForm = false
                document.getElementById("password-error").innerHTML = "Password cannot be empty."
            }
            if(!validForm) {
                event.preventDefault();
            }
        }
    </script>

</body>
</html>

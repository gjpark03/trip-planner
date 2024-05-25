<?php

    // Establish DB Connection
    $host = "303.itpwebdev.com";
    $user = "gjpark_db_user";
    $pass = "uscitp303";
    $db = "gjpark_final";

    $mysqli = new mysqli($host, $user, $pass, $db);

    // Check for connection errors
    if($mysqli->connect_errno) {
        echo $mysqli->connect_error;
        exit();
    }

    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Check if email already exists
        $stmt = $mysqli->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $results = $stmt->get_result();
        if($results->num_rows > 0) {
            $email_error = "Email already in use.";
        } else {
            $stmt = $mysqli->prepare("INSERT INTO users(email, username, password) VALUES (?, ?, ?);");
            $stmt->bind_param("sss", $email, $username, $password);
            if(!$stmt->execute()) {
                $general_error = "An error occurred. Please try again.";
            } else {
                session_start();
                $_SESSION['user_id'] = $mysqli->insert_id;
                $_SESSION['username'] = $username;
                header("Location: homepage.html");
                exit();
            }
        }
        $stmt->close();
    }

    $mysqli->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" 
    content="User will be navigated to this page from the login page after pressing the
    create an account button. User will have to enter their email as well as a username and password.
    The email will be used to search up user's trips in the database">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="shared.css">
    <title>303TripPlanner | Signup</title>

    <style>
        h1 {
            margin-top: 50px;
        }
        @media(max-width: 767px) {
            h1 {
                margin-top: 50px;
            }
        }
        #web-title {
            font-weight: bold;
        }
        #signup-container {
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
    <h1>Signup</h1>
    <div id="signup-container">
        <form id="signup-form" action="" method="post">
            <div id="web-title" class="text-center">
                303TripPlanner
            </div>
            <div class="form-group">
                Email
                <input type="text" id="email" name="email" placeholder="Email">
                <div id="email-error" class="error">   
                    <?php 
                        if(!empty($email_error)) {
                            echo $email_error; 
                        } 
                    ?>      
                </div>
            </div>
            <div class="form-group">
                Username
                <input type="text" id="username" name="username" placeholder="Username">
                <div id="username-error" class="error"></div>
            </div>
            <div class="form-group">
                Password
                <input type="password" id="password" name="password" placeholder="Password">
                <div id="password-error" class="error"></div>
            </div>
            <div class="form-group">
                Confirm Password
                <input type="password" id="password-2" name="confirm-password" placeholder="Confirm Password">
                <div id="password-error-2" class="error"></div>
            </div>
            <button type="button" id="login-btn" class="btn btn-primary" onclick="location.href='login.php';">
                Back to Log In
            </button>
            <button type="submit" id="signup-btn" class="btn btn-primary">
                Sign Up
            </button>
        </form>
    </div>

    <script>
        document.getElementById("signup-form").onsubmit = function(event) {
            let validForm = true
            document.getElementById("email-error").textContent = ""
            document.getElementById("username-error").textContent = ""
            document.getElementById("password-error").textContent = ""
            document.getElementById("password-error-2").textContent = ""

            const email = document.getElementById("email").value.trim()
            const username = document.getElementById("username").value.trim()
            const password = document.getElementById("password").value.trim()
            const password2 = document.getElementById("password-2").value.trim()

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]+$/

            if(email.length === 0) {
                validForm = false
                document.getElementById("email-error").innerHTML = "Email cannot be empty."
            } else if (!emailRegex.test(email)) {
                validForm = false;
                document.getElementById("email-error").innerHTML = "Please enter a valid email address.";
            }
            if(username.length === 0) {
                validForm = false
                document.getElementById("username-error").innerHTML = "Username cannot be empty."
            }
            if(password.length === 0) {
                validForm = false
                document.getElementById("password-error").innerHTML = "Password cannot be empty."
            } else if (!passwordRegex.test(password)) {
                validForm = false;
                document.getElementById("password-error").innerHTML = "Password must have at least one letter and number.";
            }
            if(password2.length === 0) {
                validForm = false
                document.getElementById("password-error-2").innerHTML = "Password cannot be empty."
            }
            if(password != password2) {
                validForm = false
                document.getElementById("password-error").innerHTML = "Passwords must match."
                document.getElementById("password-error-2").innerHTML = "Passwords must match."
            }
            if(!validForm) {
                event.preventDefault()
            }
        }
    </script>

</body>
</html>

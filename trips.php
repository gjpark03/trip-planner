<?php

// *** SET UP *** //
session_start();

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
// *** END SET UP *** //

// Redirect to login page if not logged in
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php'); 
    exit();
}

// *** USER ID RETRIEVAL *** //
// $username = $_SESSION['username'];
// $user_id = NULL;

// $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
// if(!$stmt) {
//     echo "Prepare failed: " . $mysqli->error;
//     exit();
// }

// $stmt->bind_param("s", $username);
// $stmt->execute();
// $result = $stmt->get_result();
// if($result->num_rows > 0) {
//     $row = $result->fetch_assoc();
//     $user_id = $row['id'];
// }
$user_id = $_SESSION['user_id'];



// Get the user's trips 
if($user_id) {
    $query = "SELECT name, depart_date, return_date, status, people, places FROM trips WHERE user_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $trips[] = $row;
    }
    $stmt->close();
}
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && isset($_POST['name'])) {
    $name = $_POST['name'];
    $stmt = $mysqli->prepare("DELETE FROM trips WHERE name = ? AND user_id = ?");
    if (!$stmt) {
        echo "Prepare failed: " . $mysqli->error;
    } else {
        $stmt->bind_param("si", $name, $user_id);
        if ($stmt->execute()) {
            $success = "Trip '$name' deleted successfully.";
            header('Location: trips.php'); 
        } else {
            $error = "Delete Error: " . $stmt->error; 
        }
        $stmt->close();

    }
} else if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) { // add

    $name = isset($_POST['trip-name']) ? $mysqli->real_escape_string($_POST['trip-name']) : NULL;
    $depart_date = isset($_POST['depart-date']) ? $_POST['depart-date'] : NULL;
    $return_date = isset($_POST['return-date']) ? $_POST['return-date'] : NULL;
    $status = isset($_POST['status']) ? $mysqli->real_escape_string($_POST['status']) : NULL;
    $people = isset($_POST['people']) ? $mysqli->real_escape_string($_POST['people']) : NULL;
    $places = isset($_POST['places']) ? $mysqli->real_escape_string($_POST['places']) : NULL;

    if ($user_id && $name && $depart_date && $return_date && $status && $people && $places) {
        $sql = "INSERT INTO trips (user_id, name, depart_date, return_date, status, people, places) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);

        if(!$stmt) {
            echo "Prepare failed: " . $mysqli->error;
            exit();
        }

        $stmt->bind_param("issssis", $user_id, $name, $depart_date, $return_date, $status, $people, $places);

        if (!$stmt->execute()) {
            $error = "Insert Error: " . $stmt->error;
        } else {
            $success = "Trip '$name' was successfully added.";
            header('Location: trips.php'); 
        }

        $stmt->close();
    } else {
        $error = "All fields are required.";
    }
    $mysqli->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Users will add or delete trips on this page using the input fields.
    Users can add name, dates, status, people, and places to visit">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="shared.css">
    <title>303TripPlanner | Edit Trips</title>

    <style>
        @media(max-width: 767px) {
            h1 {
                padding-top: 150px;
            }
        }
        h1 {
            margin-top: 125px;
            margin-bottom: 25px;
        }
        h2 {
            font-weight: bold;
        }
        #travel-plans tr {
            border-bottom: 2px solid black;
        }
        #travel-plans tr {
            border-top: 2px solid black;
        }
        #travel-plans thead th {
            border-bottom: 2px solid black;
        }
        #add-trip {
            margin-bottom: 20px;
            background-color:#083058;
        }
        .badge {
            font-size: 15px;
        }
        .badge-idea {
            background-color: #6f42c1;
            color: #fff;
        }
        .badge-booked {
            background-color: #fd7e14;
            color: #fff;
        }
        .badge-completed {
            background-color: #28a745;
            color: #fff;
        }
        .badge-cancelled {
            background-color: #e81010;
            color: #fff;
        }
        #search-name {
            background-color:#083058;
        }
        #search-status {
            background-color:#083058;
        }
     </style>
</head>
<body>
    <div id="navbar">
        <div id="nav">
            <ul>
                <div id="web-title"><li><a href="homepage.html">303TripPlanner</a></li></div>
                <li><a href="trips.php">Plan A Trip</a></li>
                <li><a href="bookings.php">Find Flights</a></li>
                <li><a href="yourtrips.php">Your Trips</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div> <!-- #nav -->
    </div> <!-- #navbar -->
    <h1>Travel Plans</h1>

    <div class="container mt-3">
        <h2>Add New Trip:</h2>
        <div class="row">
            <div class="col-12">
                <form id="add-trip-form" method="POST">
                    <div class="form-row">
                    <input type="hidden" name="add" value="true">
                        <div class="col-md-2 mb-3">
                            <label for="trip-name">Name of Trip</label>
                            <input type="text" name="trip-name" class="form-control" id="trip-name" placeholder="Name of Trip">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="depart-date">Depart Date</label>
                            <input type="date" name="depart-date" class="form-control" id="depart-date">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="return-date">Return Date</label>
                            <input type="date" name="return-date" class="form-control" id="return-date">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="status">Status</label>
                            <select class="custom-select" name="status" id="status">
                                <option selected>Status...</option>
                                <option value="Idea">Idea</option>
                                <option value="Booked">Booked</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="people">People</label>
                            <input type="text" class="form-control" name="people" id="people" placeholder="Number of People">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="places">Places</label>
                            <input type="text" class="form-control" name="places" id="places" placeholder="Places to Visit">
                        </div>
                    </div>
                    <button class="btn btn-primary" id="add-trip" type="submit">+ Add New Trip</button>
                </form>
            </div>
        </div>
    
        <div class="container mt-5">
            <div class="row mt-4">
                <div class="col-12">

                    <?php if(isset($error) && $error): ?>
                        <div class="text-danger font-italic"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if(isset($success) && $success): ?>
                        <div class="text-success"><span class="font-italic"><?php echo $success; ?></span></div>
                    <?php endif; ?>

                </div> <!-- .col -->
            </div> <!-- .row -->

            <div class="row mt-4">
                <div class="col-12">
                    <table id="travel-plans" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Dates</th>
                                <th>Status</th>
                                <th>People</th>
                                <th>Places</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($trips)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No trips yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($trips as $trip): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($trip['name']); ?></td>
                                        <td><?php echo htmlspecialchars($trip['depart_date']) . " to " . htmlspecialchars($trip['return_date']); ?></td>
                                        <td><span class="badge badge-pill badge-<?php echo strtolower($trip['status']); ?> trip-badge"><?php echo htmlspecialchars($trip['status']); ?></span></td>
                                        <td><?php echo htmlspecialchars($trip['people']); ?></td>
                                        <td><?php echo htmlspecialchars($trip['places']); ?></td>
                                        <td>
                                            <form method="POST">
                                                <input type="hidden" name="delete" value="true">
                                                <input type="hidden" name="name" value="<?= $trip['name']; ?>">
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</body>
</html>

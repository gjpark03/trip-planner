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

    $results = [];

    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $searchName = isset($_POST['trip-name']) ? $_POST['trip-name'] : '';
        $searchStatus = isset($_POST['status']) ? $_POST['status'] : '';

        $query = "SELECT * FROM trips WHERE user_id = ?";

        if (!empty($searchName)) {
            $query .= " AND name LIKE ?";
            $searchName = '%' . $searchName . '%';
        }
        if (!empty($searchStatus) && $searchStatus != 'Status...') {
            $query .= " AND status = ?";
        }

        $stmt = $mysqli->prepare($query);

        if (!empty($searchName) && !empty($searchStatus) && $searchStatus != 'Status...') {
            $stmt->bind_param("iss", $_SESSION['user_id'], $searchName, $searchStatus);
        } else if (!empty($searchName)) {
            $stmt->bind_param("is", $_SESSION['user_id'], $searchName);
        } else if (!empty($searchStatus) && $searchStatus != 'Status...') {
            $stmt->bind_param("is", $_SESSION['user_id'], $searchStatus);
        } else {
            $stmt->bind_param("i", $_SESSION['user_id']);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $results = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    // Populate the trips table
    $sql = "SELECT name, status, depart_date, return_date, people, places FROM trips WHERE user_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $results = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // SEARCH BY NAME OR STATUS
    if(isset($_GET['search-name']) || isset($_GET['status'])) {
        $query = "SELECT name, status, depart_date, return_date, people, places FROM trips WHERE user_id = ?";
        
        $params = [$_SESSION['user_id']];
        $types = "i"; 
    
        if(!empty($_GET['trip-name'])) {
            $query .= " AND name LIKE ?";
            $params[] = '%' . $mysqli->real_escape_string($_GET['trip-name']) . '%';
            $types .= "s";
        }
        
        if(!empty($_GET['status']) && $_GET['status'] != 'Status...') {
            $query .= " AND status = ?";
            $params[] = $mysqli->real_escape_string($_GET['status']);
            $types .= "s";
        }
    
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $results = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    // Close DB Connection
    $mysqli->close();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" 
    content="Users will search up bookings for flights using an API
    Users will also be able to save the bookings on the page by sending the
    information into the database.">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="shared.css">
    <title>303TripPlanner | Search</title>

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
        .trip-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 0.25rem;
            padding: 20px;
            margin-bottom: 20px;
        }
        .trip-header {
            font-weight: bold;
            margin-bottom: 15px;
        }
        .trip-info {
            margin-bottom: 10px;
        }
        .badge {
            font-size: 15px;
            margin-bottom: 10px;
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
    <h1>Your Trips</h1>

    <div class="container mt-3">
        <form id="add-trip-form" method="GET">
            <div class="form-row">
                <div class="col-md-4 mb-3">
                    <label for="trip-name">Search by Name</label>
                    <input type="text" class="form-control" id="trip-name" name="trip-name" placeholder="Name of Trip">
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end"> 
                    <button class="btn btn-primary" id="search-name" type="submit">Search</button>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="status">Search by status</label>
                    <select class="custom-select" id="status" name="status">
                        <option selected>Status...</option>
                        <option value="Idea">Idea</option>
                        <option value="Booked">Booked</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end"> 
                    <button class="btn btn-primary" id="search-status" type="submit">Search</button>
                </div>
            </div>
            
        </form>
    </div>

    <div class="container mt-5">
        <?php foreach($results as $trip): ?>
            <div class="trip-card">
            <span class="badge badge-pill <?php echo 'badge-' . strtolower($trip['status']); ?> trip-badge"><?php echo htmlspecialchars($trip['status']); ?></span>
            <div class="trip-header"><?php echo htmlspecialchars($trip['name']); ?></div>
            <div class="trip-info">Dates: <?php echo htmlspecialchars($trip['depart_date']); ?> to <?php echo htmlspecialchars($trip['return_date']); ?></div>
            <div class="trip-info">People: <?php echo htmlspecialchars($trip['people']); ?></div>
            <div class="trip-info">Places: <?php echo htmlspecialchars($trip['places']); ?></div>
            </div>
        <?php endforeach; ?>

        <?php if(empty($results)): ?>
            <p class="text-center">No trips found.</p>
        <?php endif; ?>
    </div>

</body>
</html>

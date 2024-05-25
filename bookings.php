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

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selected_flights'])) {
    // Delete existing user_flights for the user
    $delete_stmt = $mysqli->prepare("DELETE FROM user_flights WHERE user_id = ?");
    $delete_stmt->bind_param("i", $user_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    // Save each selected flight
    $save_stmt = $mysqli->prepare("INSERT INTO user_flights (user_id, flight_id) VALUES (?, ?)");
    foreach($_POST['selected_flights'] as $flight_id) {
        $save_stmt->bind_param("ii", $user_id, $flight_id);
        $save_stmt->execute();
    }
    $save_stmt->close();
}

$query = "SELECT * FROM flights WHERE 1=1";
$params = [];
$types = '';

if(!empty($_GET['depart-airport'])) {
    $query .= " AND depart_airport = ?";
    $params[] = $_GET['depart-airport'];
    $types .= 's';
}

if(!empty($_GET['arrive-airport'])) {
    $query .= " AND return_airport = ?";
    $params[] = $_GET['arrive-airport'];
    $types .= 's';
}

if(!empty($_GET['depart-date'])) {
    $query .= " AND depart_date = ?";
    $params[] = $_GET['depart-date'];
    $types .= 's';
}

if(!empty($_GET['return-date'])) {
    $query .= " AND return_date = ?";
    $params[] = $_GET['return-date'];
    $types .= 's';
}

$stmt = $mysqli->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$results = $stmt->get_result();

// Get the user's saved flights
$saved_flights_stmt = $mysqli->prepare("SELECT flight_id FROM user_flights WHERE user_id = ?");
$saved_flights_stmt->bind_param("i", $user_id);
$saved_flights_stmt->execute();
$saved_flights_result = $saved_flights_stmt->get_result();
$saved_flights = [];
while ($row = $saved_flights_result->fetch_assoc()) {
    $saved_flights[] = $row['flight_id'];
}
$saved_flights_stmt->close();

// Close DB Connection
$mysqli->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Users be able to find flights based on a static database that holds the names of
    departure airports, arrival aiports, and their depart and return dates. Users will be able to add these flights to 
    a flights page.">    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="shared.css">
    <title>303TripPlanner | Find Flights</title>

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
        #search-trip {
            margin-bottom: 20px;
            background-color:#083058;
        }
        #save-button {
            margin-bottom: 20px;
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
    <h1>Find Flights</h1>
    <div class="container mt-3">
        <div class="row">
            <div class="col-12">
                <form id="search-trip-form" method="GET">
                    <div class="form-row">
                        <div class="col-md-3 mb-3">
                            <label for="depart-airport">Depart</label>
                            <select class="form-control" id="depart-airport" name="depart-airport">
                                <option value="" disabled selected>Arrival Airport</option> 
                                <option value="LAX">LAX</option>
                                <option value="PKX">PKX</option>
                                <option value="ICN">ICN</option>
                                <option value="CDG">CDG</option>
                                <option value="DXB">DXB</option>
                                <option value="MAD">MAD</option>
                                <option value="SIN">SIN</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="arrive-airport">Arrive</label>
                            <select class="form-control" id="arrive-airport" name="arrive-airport">
                                <option value="" disabled selected>Depart Airport</option> 
                                <option value="LAX">LAX</option>
                                <option value="PKX">PKX</option>
                                <option value="ICN">ICN</option>
                                <option value="CDG">CDG</option>
                                <option value="DXB">DXB</option>
                                <option value="MAD">MAD</option>
                                <option value="SIN">SIN</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="depart-date">Depart Date</label>
                            <input type="date" class="form-control" id="depart-date" name="depart-date">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="return-date">Return Date</label>
                            <input type="date" class="form-control" id="return-date" name="return-date">
                        </div>
                    </div>
                    <button class="btn btn-primary" id="search-trip" type="submit">Search</button>
                </form>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <form method="POST">
                    <table class="table table-hover" id="flights-table">
                        <thead>
                            <tr>
                                <th>Departure Airport</th>
                                <th>Arrival Airport</th>
                                <th>Departure Date</th>
                                <th>Return Date</th>
                                <th>Save</th> 
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $results->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['depart_airport']); ?></td>
                                    <td><?php echo htmlspecialchars($row['return_airport']); ?></td>
                                    <td><?php echo htmlspecialchars($row['depart_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['return_date']); ?></td>
                                    <td>
                                        <input type="checkbox" name="selected_flights[]" value="<?php echo htmlspecialchars($row['id']); ?>"
                                            <?php if(in_array($row['id'], $saved_flights)) echo 'checked'; ?>>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <button type="submit" id="save-button" class="btn btn-primary">Save Selected Flights</button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>

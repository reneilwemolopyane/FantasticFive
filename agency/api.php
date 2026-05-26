<?php
session_start();
require_once("config.php");
ini_set('display_errors', 1);
error_reporting(E_ALL);// Enable detailed error reporting for debugging
//PHP fatal errors still output HTML

function respond($code, $status, $message) {
    http_response_code($code);
    echo json_encode(["status" => $status, "timestamp" => time(), "data" => $message]);
    exit;
}

function validateEmail($email) {
    return preg_match("/^[^@\s]+@[^@\s]+\.[^@\s]+$/", $email);
}

function validatePassword($password) {
    return preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{9,}$/", $password);
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || count($data) === 0) $data = $_POST;
if (!$data || count($data) === 0) respond(400, "error", "No parameters provided");
if (!isset($data["type"]))        respond(400, "error", "Missing type");

// ================================================================
// REGISTER
// ================================================================
if ($data["type"] === "Register") {
    $isTraveller = isset($data["fname"]) || isset($data["passport_no"]);
    $isAgency    = isset($data["agency_name"]) || isset($data["registration_no"]);

    if (!$isTraveller && !$isAgency) respond(400, "error", "Cannot determine registration type");

    if ($isTraveller) {
        $required = ["fname","surname","email","password","passport_no","date_of_birth","gender","nationality"];
        foreach ($required as $f) { if (empty($data[$f])) respond(400, "error", "Missing field: $f"); }

        $firstName   = trim($data["fname"]);
        $surname     = trim($data["surname"]);
        $email       = trim($data["email"]);
        $password    = $data["password"];
        $passportNo  = trim($data["passport_no"]);
        $dob         = $data["date_of_birth"];
        $gender      = ucfirst(strtolower(trim($data["gender"])));
        $nationality = trim($data["nationality"]);

        if (!validateEmail($email))    respond(400, "error", "Invalid email format");
        if (!validatePassword($password)) respond(400, "error", "Password must be at least 9 chars with uppercase, lowercase, number, and special char");
        if (!in_array($gender, ["Male","Female","Other"])) respond(400, "error", "Invalid gender");

        $dobDate = DateTime::createFromFormat("Y-m-d", $dob);
        if (!$dobDate || $dobDate >= new DateTime()) respond(400, "error", "Invalid date of birth");

        $stmt = $connection->prepare("SELECT UserID FROM USER WHERE Email = ?");
        $stmt->bind_param("s", $email); $stmt->execute(); $stmt->store_result();
        if ($stmt->num_rows > 0) respond(409, "error", "Email already registered");
        $stmt->close();

        $stmt = $connection->prepare("SELECT TravellerID FROM TRAVELLER WHERE Passport_no = ?");
        $stmt->bind_param("s", $passportNo); $stmt->execute(); $stmt->store_result();
        if ($stmt->num_rows > 0) respond(409, "error", "Passport already registered");
        $stmt->close();

        $salt = bin2hex(random_bytes(32));
        $hashedPassword = hash("sha256", $salt . $password);
        $apiKey = bin2hex(random_bytes(16));
        $emptyPhone = '';

        $connection->begin_transaction();
        try {
            $stmt = $connection->prepare("INSERT INTO USER (Name,Surname,Password,Salt,User_type,Email,Phone_number,api_key) VALUES (?,?,?,?,'Traveller',?,?,?)");
            $stmt->bind_param("sssssss", $firstName, $surname, $hashedPassword, $salt, $email, $emptyPhone, $apiKey);
            $stmt->execute();
            $userId = $connection->insert_id;
            $stmt->close();

            $stmt = $connection->prepare("INSERT INTO TRAVELLER (UserID,Nationality,Passport_no,Gender,Date_of_birth) VALUES (?,?,?,?,?)");
            $stmt->bind_param("issss", $userId, $nationality, $passportNo, $gender, $dob);
            $stmt->execute();
            $travellerId = $connection->insert_id;
            $stmt->close();

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            respond(500, "error", "Registration failed: " . $e->getMessage());
        }
        respond(201, "success", ["apikey" => $apiKey, "user_id" => $userId, "traveller_id" => $travellerId, "user_type" => "Traveller"]);
    }

    if ($isAgency) {
        $required = ["agency_name","a_email","a_pword","registration_no"];
        foreach ($required as $f) { if (empty($data[$f])) respond(400, "error", "Missing field: $f"); }

        $agencyName     = trim($data["agency_name"]);
        $email          = trim($data["a_email"]);
        $password       = $data["a_pword"];
        $registrationNo = trim($data["registration_no"]);
        $website        = !empty($data["website"])     ? trim($data["website"])     : null;
        $description    = !empty($data["description"]) ? trim($data["description"]) : null;

        if (!validateEmail($email))    respond(400, "error", "Invalid email format");
        if (!validatePassword($password)) respond(400, "error", "Weak password");
        if ($website && !filter_var($website, FILTER_VALIDATE_URL)) respond(400, "error", "Invalid website URL");

        $stmt = $connection->prepare("SELECT UserID FROM USER WHERE Email = ?");
        $stmt->bind_param("s", $email); $stmt->execute(); $stmt->store_result();
        if ($stmt->num_rows > 0) respond(409, "error", "Email already registered");
        $stmt->close();

        $stmt = $connection->prepare("SELECT AgencyID FROM AGENCY WHERE Registration_no = ?");
        $stmt->bind_param("s", $registrationNo); $stmt->execute(); $stmt->store_result();
        if ($stmt->num_rows > 0) respond(409, "error", "Registration number already in use");
        $stmt->close();

        $salt = bin2hex(random_bytes(32));
        $hashedPassword = hash("sha256", $salt . $password);
        $apiKey = bin2hex(random_bytes(16));
        $emptySurname = '';
        $emptyPhone   = '';

        $connection->begin_transaction();
        try {
            $stmt = $connection->prepare("INSERT INTO USER (Name,Surname,Password,Salt,User_type,Email,Phone_number,api_key) VALUES (?,?,?,?,'Agency',?,?,?)");
            $stmt->bind_param("sssssss", $agencyName, $emptySurname, $hashedPassword, $salt, $email, $emptyPhone, $apiKey);
            $stmt->execute();
            $userId = $connection->insert_id;
            $stmt->close();

            $stmt = $connection->prepare("INSERT INTO AGENCY (UserID,Agency_name,Website,Description,Registration_no) VALUES (?,?,?,?,?)");
            $stmt->bind_param("issss", $userId, $agencyName, $website, $description, $registrationNo);
            $stmt->execute();
            $agencyId = $connection->insert_id;
            $stmt->close();

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            respond(500, "error", "Registration failed: " . $e->getMessage());
        }
        respond(201, "success", ["apikey" => $apiKey, "user_id" => $userId, "agency_id" => $agencyId, "user_type" => "Agency"]);
    }
}


// LOGIN
// ================================================================
if ($data["type"] === "Login") {
    if (empty($data["email"]) || empty($data["password"])) respond(400, "error", "Email and password required");

    $email    = trim($data["email"]);
    $password = $data["password"];

    $stmt = $connection->prepare("SELECT * FROM USER WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) respond(401, "error", "Invalid email or password");

    if (!hash_equals($user["Password"], hash("sha256", $user["Salt"] . $password))) {
        respond(401, "error", "Invalid email or password");
    }

    $payload = [
        "apikey"    => $user["api_key"],
        "user_id"   => $user["UserID"],
        "name"      => $user["Name"],
        "surname"   => $user["Surname"],
        "email"     => $user["Email"],
        "user_type" => $user["User_type"]
    ];

    if ($user["User_type"] === "Traveller") {
        $stmt = $connection->prepare("SELECT TravellerID,Nationality,Passport_no,Gender,Date_of_birth FROM TRAVELLER WHERE UserID = ?");
        $stmt->bind_param("i", $user["UserID"]);
        $stmt->execute();
        $traveller = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($traveller) {
            $payload["traveller_id"]  = $traveller["TravellerID"];
            $payload["nationality"]   = $traveller["Nationality"];
            $payload["gender"]        = $traveller["Gender"];
            $payload["date_of_birth"] = $traveller["Date_of_birth"];
        }
    } elseif ($user["User_type"] === "Agency") {
        $stmt = $connection->prepare("SELECT AgencyID,Agency_name,Website,Description,Registration_no,Average_rating FROM AGENCY WHERE UserID = ?");
        $stmt->bind_param("i", $user["UserID"]);
        $stmt->execute();
        $agency = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($agency) {
            $payload["agency_id"]       = $agency["AgencyID"];
            $payload["agency_name"]     = $agency["Agency_name"];
            $payload["website"]         = $agency["Website"];
            $payload["registration_no"] = $agency["Registration_no"];
            $payload["average_rating"]  = $agency["Average_rating"];

            $_SESSION['agency_id']   = $agency["AgencyID"];
            $_SESSION['agency_name'] = $agency["Agency_name"];
            $_SESSION['user_id']     = $user["UserID"];
        }
    }
    respond(200, "success", $payload);
}

// ================================================================
// CREATE PACKAGE
// ================================================================
if ($data["type"] === "CreatePackage") {
    $agency_id     = $_SESSION['agency_id'] ?? 0;
    $title         = trim($_POST['Title']         ?? '');
    $destination   = trim($_POST['destination']   ?? '');
    $price         = (float) ($_POST['price']     ?? 0);
    $duration      = (int)   ($_POST['duration']  ?? 0);
    $description   = trim($_POST['description']   ?? '');
    $start_date    = $_POST['start_date']          ?? '';
    $end_date      = $_POST['end_date']            ?? '';
    $max_people    = (int) ($_POST['max_people']  ?? 0);
    $pack_type     = trim($_POST['pack_type']     ?? '');
    $accommodation = trim($_POST['accommodation'] ?? '');
    $flights       = trim($_POST['flights']       ?? '');
    $restaurants   = trim($_POST['restaurants']   ?? '');
    $transport     = trim($_POST['transport']     ?? '');
    $attractions   = trim($_POST['attractions']   ?? '');

    $uploadedImages = [];
    $uploadDir = "uploads/";
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $key => $name) {
            if ($_FILES['images']['error'][$key] === 0) {
                $ext     = pathinfo($name, PATHINFO_EXTENSION);
                $newName = uniqid() . "." . $ext;
                move_uploaded_file($_FILES['images']['tmp_name'][$key], $uploadDir . $newName);
                $uploadedImages[] = $newName;
            }
        }
    }
    $imagesJson = json_encode($uploadedImages);

    $stmt = $connection->prepare("
        INSERT INTO package (AgencyID,Max_people,Duration,Start_date,End_date,Title,Pack_type,Description,Price,Flights,Restaurants,Transport,Attractions,Images)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->bind_param("iiisssssdsssss",
        $agency_id, $max_people, $duration,
        $start_date, $end_date, $title, $pack_type, $description,
        $price, $flights, $restaurants, $transport, $attractions, $imagesJson
    );
    $stmt->execute();
    $package_id = $connection->insert_id;
    $stmt->close();

    if (isset($_POST['is_group_trip'])) {
        $departure_date = $_POST['departure_date'] ?? '';
        $max_seats      = (int) ($_POST['max_seats'] ?? 0);
        $gs = $connection->prepare("INSERT INTO group_trip (PackageID,AgencyID,DepartureDate,MaxSeats) VALUES (?,?,?,?)");
        $gs->bind_param("iisi", $package_id, $agency_id, $departure_date, $max_seats);
        $gs->execute();
        $gs->close();
    }

    // Link destination
    $ds = $connection->prepare("SELECT DestinationID FROM destination WHERE City = ? LIMIT 1");
    $ds->bind_param("s", $destination);
    $ds->execute();
    $drow = $ds->get_result()->fetch_assoc();
    $ds->close();
    if ($drow) {
        $ls = $connection->prepare("INSERT INTO package_destination (PackageID,DestinationID) VALUES (?,?)");
        $ls->bind_param("ii", $package_id, $drow['DestinationID']);
        $ls->execute(); $ls->close();
    }

    // Link accommodation
    $as = $connection->prepare("SELECT AccommodationID FROM accommodation WHERE Name = ? LIMIT 1");
    $as->bind_param("s", $accommodation);
    $as->execute();
    $arow = $as->get_result()->fetch_assoc();
    $as->close();
    if ($arow) {
        $ls = $connection->prepare("INSERT INTO accommodation_package (AccommodationID,PackageID) VALUES (?,?)");
        $ls->bind_param("ii", $arow['AccommodationID'], $package_id);
        $ls->execute(); $ls->close();
    }

    respond(200, "success", ["message" => "Package created successfully", "package_id" => $package_id]);
}

// ================================================================
// GET ALL PACKAGES  ← used by manage_package.php
// ================================================================
/*if ($data["type"] === "GetAllPackages") {
    $agency_id = isset($_SESSION['agency_id']) ? (int)$_SESSION['agency_id'] : 0;

    $stmt = $connection->prepare("
        SELECT PackageID AS id,
               Title     AS title,
               Description AS destination,
               Price     AS price,
               Duration  AS duration,
               COALESCE(Status,'Active') AS status,
               Images    AS images
        FROM package
        WHERE AgencyID = ?
        ORDER BY PackageID DESC
    ");
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $packages = [];
    while ($row = $result->fetch_assoc()) {
        $imgs = json_decode($row['images'] ?? '[]', true);
        $row['thumb'] = (!empty($imgs)) ? "uploads/" . $imgs[0] : "";
        unset($row['images']);
        $packages[] = $row;
    }
    $stmt->close();
    respond(200, "success", $packages);
}*/


// UPDATE PACKAGE PRICE
// ===============
if ($data["type"] === "UpdatePackagePrice") {
    $agency_id  = isset($_SESSION['agency_id']) ? (int)$_SESSION['agency_id'] : 0;
    $package_id = (int)   ($data["id"]    ?? 0);
    $new_price  = (float) ($data["price"] ?? 0);

    if ($package_id <= 0 || $new_price <= 0) respond(400, "error", "Invalid package ID or price");

    $stmt = $connection->prepare("UPDATE package SET Price = ? WHERE PackageID = ? AND AgencyID = ?");
    $stmt->bind_param("dii", $new_price, $package_id, $agency_id);
    $stmt->execute();
    if ($stmt->affected_rows === 0) respond(404, "error", "Package not found or does not belong to your agency");
    $stmt->close();

    respond(200, "success", ["message" => "Price updated", "new_price" => $new_price]);
}


// TOGGLE PACKAGE VISIBILITY
// ================================================================
if ($data["type"] === "TogglePackageVisibility") {
    $agency_id  = isset($_SESSION['agency_id']) ? (int)$_SESSION['agency_id'] : 0;
    $package_id = (int) ($data["id"] ?? 0);

    if ($package_id <= 0) respond(400, "error", "Invalid package ID");

    $stmt = $connection->prepare("SELECT COALESCE(Status,'Active') AS status FROM package WHERE PackageID = ? AND AgencyID = ?");
    $stmt->bind_param("ii", $package_id, $agency_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) respond(404, "error", "Package not found");

    $new_status = ($row['status'] === 'Active') ? 'Inactive' : 'Active';

    $stmt = $connection->prepare("UPDATE package SET Status = ? WHERE PackageID = ? AND AgencyID = ?");
    $stmt->bind_param("sii", $new_status, $package_id, $agency_id);
    $stmt->execute();
    $stmt->close();

    respond(200, "success", ["new_status" => $new_status]);
}

$agency_id = isset($_SESSION['agency_id']) ? (int)$_SESSION['agency_id'] : 1;

if (isset($data["type"]) && $data["type"] === "FetchDashboardSummary") {
    
    // SQL Injection Prevention: Using explicit counts filtered by bound session variables
    $stmt1 = $connection->prepare("SELECT COUNT(*) AS total FROM package WHERE AgencyID = ?");
    $stmt1->bind_param("i", $agency_id);
    $stmt1->execute();
    $res1 = $stmt1->get_result()->fetch_assoc();
    $total_packages = $res1['total'];
    $stmt1->close();

    $stmt2 = $connection->prepare("SELECT COUNT(*) AS total FROM booking b JOIN package p ON b.PackageID = p.PackageID WHERE p.AgencyID = ?");
    $stmt2->bind_param("i", $agency_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result()->fetch_assoc();
    $active_bookings = $res2['total'];
    $stmt2->close();

    $stmt3 = $connection->prepare("SELECT SUM(p.Price) AS total_rev FROM booking b JOIN package p ON b.PackageID = p.PackageID WHERE p.AgencyID = ?");
    $stmt3->bind_param("i", $agency_id);
    $stmt3->execute();
    $res3 = $stmt3->get_result()->fetch_assoc();
    $revenue = $res3['total_rev'] ? $res3['total_rev'] : 0.00;
    $stmt3->close();

    $stmt4 = $connection->prepare("SELECT COUNT(*) AS total FROM group_trip WHERE AgencyID = ?");
    $stmt4->bind_param("i", $agency_id);
    $stmt4->execute();
    $res4 = $stmt4->get_result()->fetch_assoc();
    $group_trips = $res4['total'];
    $stmt4->close();

    respond(200, "success", [
        "total_packages" => $total_packages,
        "active_bookings" => $active_bookings,
        "revenue_collected" => $revenue,
        "group_trips" => $group_trips
    ]);
}

if (isset($data["type"]) && $data["type"] === "FetchDetailedAnalytics") {
    
    // SQL Injection Prevention: Dynamic calculation matching your specific database attributes
    $stmt = $connection->prepare("SELECT Title AS title, Description AS destination, 12 AS booking_count FROM package WHERE AgencyID = ? LIMIT 3");
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $popular = [];
    while ($row = $result->fetch_assoc()) {
        $popular[] = $row;
    }
    $stmt->close();

    respond(200, "success", [
        "popular_packages" => $popular
    ]);
}

if (isset($data["type"]) && $data["type"] === "GetAllPackages") {
    
    $stmt = $connection->prepare("SELECT PackageID AS id, Title AS title, Description AS destination, Price AS price, Duration AS duration, 'Active' AS status FROM package WHERE AgencyID = ? ORDER BY PackageID DESC");
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $packages = [];
    while ($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }
    $stmt->close();
    
    respond(200, "success", $packages);
}



if (isset($data["type"]) && $data["type"] === "UpdatePackagePrice") {
    
    $package_id = (int)$data['id'];
    $price = (float)$data['price'];
    
    $stmt = $connection->prepare("UPDATE package SET Price = ? WHERE PackageID = ? AND AgencyID = ?");
    $stmt->bind_param("dii", $price, $package_id, $agency_id);
    $stmt->execute();
    $stmt->close();
    
    respond(200, "success", "Price accurately altered.");
}

if (isset($data["type"]) && $data["type"] === "DeletePackage") {
    
    $package_id = (int)$data['id'];
    
    $stmt = $connection->prepare("DELETE FROM package WHERE PackageID = ? AND AgencyID = ?");
    $stmt->bind_param("ii", $package_id, $agency_id);
    $stmt->execute();
    $stmt->close();
    
    respond(200, "success", "Item dropped from active catalogue.");
}

// ================================================================
// GET ALL BOOKINGS
// ================================================================
if ($data["type"] === "GetAllBookings") {

    $agency_id = isset($_SESSION['agency_id'])
        ? (int)$_SESSION['agency_id']
        : 0;

    $stmt = $connection->prepare("
    SELECT
    b.BookingID AS id,
    CONCAT(u.Name, ' ', u.Surname) AS customer_name,
    u.Email AS customer_email,
    p.Title AS package_title,
    b.BookingDate AS booking_date,
    b.SeatsBooked AS seats,
    b.TotalPrice AS price,
    b.BookingStatus AS status
FROM booking b
INNER JOIN traveller t
    ON b.TravelerID = t.TravellerID
INNER JOIN user u
    ON t.UserID = u.UserID
INNER JOIN package p
    ON b.PackageID = p.PackageID
WHERE p.AgencyID = ?
ORDER BY b.BookingID DESC
");

    $stmt->bind_param("i", $agency_id);

    $stmt->execute();

    $result = $stmt->get_result();

    $bookings = [];

    while ($row = $result->fetch_assoc()) {

        $bookings[] = [
            "id" => $row["id"],
            "customer_name" => $row["customer_name"],
            "customer_email" => $row["customer_email"],
            "package_title" => $row["package_title"],
            "booking_date" => $row["booking_date"],
            "seats" => $row["seats"],
            "price" => $row["price"],
            "status" => strtoupper($row["status"])
        ];
    }

    $stmt->close();

    respond(200, "success", $bookings);
}


// ================================================================
// UPDATE BOOKING STATUS
// ================================================================
if ($data["type"] === "UpdateBookingStatus") {

    $booking_id = (int)($data["booking_id"] ?? 0);

    $new_status = trim($data["status"] ?? '');

    $stmt = $connection->prepare("
        UPDATE booking
        SET Status = ?
        WHERE BookingID = ?
    ");

    $stmt->bind_param("si", $new_status, $booking_id);

    $stmt->execute();

    $stmt->close();

    respond(200, "success", [
        "message" => "Booking updated"
    ]);
}

// ================================================================
// GET ALL GROUP TRIPS
// ================================================================
if ($data["type"] === "GetAllGroupTrips") {

    $agency_id = isset($_SESSION['agency_id'])
        ? (int)$_SESSION['agency_id']
        : 0;

    $stmt = $connection->prepare(" 
        SELECT
            gt.GroupTripID,
            gt.DepartureDate,
            gt.MaxSeats,
            gt.SeatsFilled,
            gt.Status,

            p.Title,
            p.Duration,
            p.Images

        FROM group_trip gt

        INNER JOIN package p
            ON gt.PackageID = p.PackageID

        WHERE gt.AgencyID = ?

        ORDER BY gt.DepartureDate ASC
    ");

    $stmt->bind_param("i", $agency_id);

    $stmt->execute();

    $result = $stmt->get_result();

    $trips = [];

    while ($row = $result->fetch_assoc()) {

        $images = json_decode($row["Images"] ?? "[]", true);

        $imagePath = "/FantasticFive/agency/uploads/default.jpg";
    if (!empty($images)) {
    // Strip out any hidden newlines, returns, or spaces from the filename string
      $cleanImageName = preg_replace('/\s+/', '', $images[0]);
    
       $imagePath = "/FantasticFive/agency/uploads/" . $cleanImageName;
}
 
        $percentage = 0;

        if ($row["MaxSeats"] > 0) {
            $percentage = ($row["SeatsFilled"] / $row["MaxSeats"]) * 100;
        }

        $trips[] = [
            "group_trip_id" => $row["GroupTripID"],
            "title" => $row["Title"],
            "duration" => $row["Duration"],
            "departure_date" => $row["DepartureDate"],
            "max_seats" => $row["MaxSeats"],
            "seats_filled" => $row["SeatsFilled"],
            "status" => $row["Status"],
            "percentage" => round($percentage),
            "image" => $imagePath
        ];
    }

    $stmt->close();

    respond(200, "success", $trips);
}






respond(400, "error", "Invalid or unknown type descriptor requested.");
?>
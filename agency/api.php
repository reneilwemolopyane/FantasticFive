
<?php
session_start();  // ADD THIS at the very top

require_once("config.php");
header("Content-Type: application/json");


function respond($code, $status, $message) {
    http_response_code($code);
    echo json_encode([
        "status" => $status,
        "timestamp" => time(),
        "data" => $message
    ]);
    exit;
}

function getAuthedUser($connection, $apikey) {
    $stmt = $connection->prepare("SELECT * FROM USER WHERE api_key = ?");
    $stmt->bind_param("s", $apikey);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if (!$user) {
        respond(403, "error", "Invalid API key");
    }
    return $user;
}

function validateEmail($email) {
    return preg_match("/^[^@\s]+@[^@\s]+\.[^@\s]+$/", $email);
}

function validatePassword($password) {
    return preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{9,}$/", $password);
}


$data = json_decode(file_get_contents("php://input"), true);

if (!$data || count($data) === 0) {
    $data = $_POST;
}

if (!$data || count($data) === 0) {
    respond(400, "error", "No parameters provided");
}

if (!isset($data["type"])) {
    respond(400, "error", "Missing type");
}



if ($data["type"] === "Register") {

    $isTraveller = isset($data["fname"]) || isset($data["passport_no"]);
    $isAgency    = isset($data["agency_name"]) || isset($data["registration_no"]);

    if (!$isTraveller && !$isAgency) {
        respond(400, "error", "Cannot determine registration type from submitted fields");
    }

    if ($isTraveller) {

        $required = ["fname", "surname", "email", "password", "passport_no", "date_of_birth", "gender", "nationality"];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                respond(400, "error", "Missing field: $field");
            }
        }

        $firstName = trim($data["fname"]);
        $surname = trim($data["surname"]);
        $email = trim($data["email"]);
        $password = $data["password"];
        $passportNo  = trim($data["passport_no"]);
        $dob = $data["date_of_birth"];
        $gender = ucfirst(strtolower(trim($data["gender"])));
        $nationality = trim($data["nationality"]);

        if (!validateEmail($email)) {
            respond(400, "error", "Invalid email format");
        }

        if (!validatePassword($password)) {
            respond(400, "error", "Password must be at least 9 characters and include uppercase, lowercase, a number, and a special character");
        }

        if (!in_array($gender, ["Male", "Female", "Other"])) {
            respond(400, "error", "Invalid gender. Must be Male, Female, or Other");
        }

        $dobDate = DateTime::createFromFormat("Y-m-d", $dob);
        if (!$dobDate || $dobDate >= new DateTime()) {
            respond(400, "error", "Invalid date of birth");
        }

        $stmt = $connection->prepare("SELECT UserID FROM USER WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            respond(409, "error", "Email is already registered");
        }
        $stmt->close();

        $stmt = $connection->prepare("SELECT TravellerID FROM TRAVELLER WHERE Passport_no = ?");
        $stmt->bind_param("s", $passportNo);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            respond(409, "error", "Passport number is already registered");
        }
        $stmt->close();

        $salt = bin2hex(random_bytes(32));
        $hashedPassword = hash("sha256", $salt . $password);
        $apiKey = bin2hex(random_bytes(16));
        $emptyPhone = '';

        $connection->begin_transaction();
        try {
            $stmt = $connection->prepare("
                INSERT INTO USER (Name, Surname, Password, Salt, User_type, Email, Phone_number, api_key)
                VALUES (?, ?, ?, ?, 'Traveller', ?, ?, ?)
            ");
            $stmt->bind_param("sssssss",
                $firstName,
                $surname,
                $hashedPassword,
                $salt,
                $email,
                $emptyPhone,
                $apiKey
            );
            $stmt->execute();
            $userId = $connection->insert_id;
            $stmt->close();

            $stmt = $connection->prepare("
                INSERT INTO TRAVELLER (UserID, Nationality, Passport_no, Gender, Date_of_birth)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("issss", $userId, $nationality, $passportNo, $gender, $dob);
            $stmt->execute();
            $travellerId = $connection->insert_id;
            $stmt->close();

            $connection->commit();

        } catch (Exception $e) {
            $connection->rollback();
            respond(500, "error", "Registration failed: " . $e->getMessage());
        }

        respond(201, "success", [
            "apikey"       => $apiKey,
            "user_id"      => $userId,
            "traveller_id" => $travellerId,
            "user_type"    => "Traveller"
        ]);
    }

    if ($isAgency) {

        $required = ["agency_name", "a_email", "a_pword", "registration_no"];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                respond(400, "error", "Missing field: $field");
            }
        }

        $agencyName  = trim($data["agency_name"]);
        $email = trim($data["a_email"]);
        $password = $data["a_pword"];
        $registrationNo = trim($data["registration_no"]);
        $website = !empty($data["website"])     ? trim($data["website"])     : null;
        $description =!empty($data["description"]) ? trim($data["description"]) : null;

        if (!validateEmail($email)) {
            respond(400, "error", "Invalid email format");
        }

        if (!validatePassword($password)) {
            respond(400, "error", "Password must be at least 9 characters and include uppercase, lowercase, a number, and a special character");
        }

        if ($website && !filter_var($website, FILTER_VALIDATE_URL)) {
            respond(400, "error", "Invalid website URL");
        }

        $stmt = $connection->prepare("SELECT UserID FROM USER WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            respond(409, "error", "Email is already registered");
        }
        $stmt->close();

        $stmt = $connection->prepare("SELECT AgencyID FROM AGENCY WHERE Registration_no = ?");
        $stmt->bind_param("s", $registrationNo);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            respond(409, "error", "Registration number is already in use");
        }
        $stmt->close();

        $salt = bin2hex(random_bytes(32));
        $hashedPassword = hash("sha256", $salt . $password);
        $apiKey = bin2hex(random_bytes(16));
        $emptySurname = '';
        $emptyPhone = '';

        $connection->begin_transaction();
        try {
            $stmt = $connection->prepare("
                INSERT INTO USER (Name, Surname, Password, Salt, User_type, Email, Phone_number, api_key)
                VALUES (?, ?, ?, ?, 'Agency', ?, ?, ?)
            ");
            $stmt->bind_param("sssssss",
                $agencyName,
                $emptySurname,
                $hashedPassword,
                $salt,
                $email,
                $emptyPhone,
                $apiKey
            );
            $stmt->execute();
            $userId = $connection->insert_id;
            $stmt->close();

            $stmt = $connection->prepare("
                INSERT INTO AGENCY (UserID, Agency_name, Website, Description, Registration_no)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("issss", $userId, $agencyName, $website, $description, $registrationNo);
            $stmt->execute();
            $agencyId = $connection->insert_id;
            $stmt->close();

            $connection->commit();

        } catch (Exception $e) {
            $connection->rollback();
            respond(500, "error", "Registration failed: " . $e->getMessage());
        }

        respond(201, "success", [
            "apikey"    => $apiKey,
            "user_id"   => $userId,
            "agency_id" => $agencyId,
            "user_type" => "Agency"
        ]);
    }
}
if ($data["type"] === "Login") {

    if (empty($data["email"]) || empty($data["password"])) {
        respond(400, "error", "Email and password are required");
    }

    $email = trim($data["email"]);
    $password = $data["password"];

    $stmt = $connection->prepare("SELECT * FROM USER WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        respond(401, "error", "Invalid email or password");
    }

    $hashedInput = hash("sha256", $user["Salt"] . $password);
    if (!hash_equals($user["Password"], $hashedInput)) {
        respond(401, "error", "Invalid email or password");
    }

    $payload = [
        "apikey" => $user["api_key"],
        "user_id" => $user["UserID"],
        "name" => $user["Name"],
        "surname" => $user["Surname"],
        "email" => $user["Email"],
        "user_type" => $user["User_type"]
    ];

    if ($user["User_type"] === "Traveller") {

        $stmt = $connection->prepare("
            SELECT TravellerID, Nationality, Passport_no, Gender, Date_of_birth
            FROM TRAVELLER WHERE UserID = ?
        ");
        $stmt->bind_param("i", $user["UserID"]);
        $stmt->execute();
        $traveller = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($traveller) {
            $payload["traveller_id"] = $traveller["TravellerID"];
            $payload["nationality"] = $traveller["Nationality"];
            $payload["gender"] = $traveller["Gender"];
            $payload["date_of_birth"] = $traveller["Date_of_birth"];
        }

    } elseif ($user["User_type"] === "Agency") {

        $stmt = $connection->prepare("
            SELECT AgencyID, Agency_name, Website, Description, Registration_no, Average_rating
            FROM AGENCY WHERE UserID = ?
        ");
        $stmt->bind_param("i", $user["UserID"]);
        $stmt->execute();
        $agency = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($agency) {
            $payload["agency_id"] = $agency["AgencyID"];
            $payload["agency_name"] = $agency["Agency_name"];
            $payload["website"] = $agency["Website"];
            $payload["registration_no"] = $agency["Registration_no"];
            $payload["average_rating"]  = $agency["Average_rating"];
        }
    }
 // Set session for agency
if ($user["User_type"] === "Agency" && isset($payload["agency_id"])) {
    $_SESSION['agency_id']   = $payload["agency_id"];
    $_SESSION['agency_name'] = $payload["agency_name"];
    $_SESSION['user_id']     = $payload["user_id"];
}

respond(200, "success", $payload);
    
    
}
   if($data["type"] === "CreatePackage") {

    $agency_id = $_SESSION['agency_id'];

    // =========================
    // FORM DATA
    // =========================

    $title = trim($_POST['Title']);
    $destination = trim($_POST['destination']);

    $price = (float) $_POST['price'];

    $duration = (int) $_POST['duration'];

    $description = trim($_POST['description']);

    $start_date = $_POST['start_date'];

    $end_date = $_POST['end_date'];

    $max_people = (int) $_POST['max_people'];

    $pack_type = trim($_POST['pack_type']);

    $accommodation = trim($_POST['accommodation']);

    $flights = trim($_POST['flights']);

    $restaurants = trim($_POST['restaurants']);

    $transport = trim($_POST['transport']);

    $attractions = trim($_POST['attractions']);

    // =========================
    // IMAGE UPLOADS
    // =========================

    $uploadedImages = [];

    $uploadDir = "uploads/";

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!empty($_FILES['images']['name'][0])) {

        foreach ($_FILES['images']['name'] as $key => $name) {

            $tmpName = $_FILES['images']['tmp_name'][$key];

            $error = $_FILES['images']['error'][$key];

            if ($error === 0) {

                $extension = pathinfo($name, PATHINFO_EXTENSION);

                $newName = uniqid() . "." . $extension;

                move_uploaded_file(
                    $tmpName,
                    $uploadDir . $newName
                );

                $uploadedImages[] = $newName;
            }
        }
    }

    $imagesJson = json_encode($uploadedImages);

    // =========================
    // INSERT PACKAGE
    // =========================

    $stmt = $connection->prepare("
        INSERT INTO package
        (
            AgencyID,
            Max_people,
            Duration,
            Start_date,
            End_date,
            Title,
            Pack_type,
            Description,
            Price,
            Flights,
            Restaurants,
            Transport,
            Attractions,
            Images
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $is_group_trip = isset($_POST['is_group_trip']);

    $stmt->bind_param(
        "iiisssssisssss",
        $agency_id,
        $max_people,
        $duration,
        $start_date,
        $end_date,
        $title,
        $pack_type,
        $description,
        $price,
        $flights,
        $restaurants,
        $transport,
        $attractions,
        $imagesJson
    );
    

    $stmt->execute();

    $package_id = $connection->insert_id;

    $stmt->close();

    if ($is_group_trip) {

    $departure_date = $_POST['departure_date'];

    $max_seats = (int) $_POST['max_seats'];

    $groupStmt = $connection->prepare("
        INSERT INTO group_trip
        (
            PackageID,
            AgencyID,
            DepartureDate,
            MaxSeats
        )
        VALUES
        (?, ?, ?, ?)
    ");

    $groupStmt->bind_param(
        "iisi",
        $package_id,
        $agency_id,
        $departure_date,
        $max_seats
    );

    $groupStmt->execute();

    $groupStmt->close();
}

    // =========================
    // DESTINATION TABLE
    // =========================
$destStmt = $connection->prepare("SELECT DestinationID FROM destination WHERE City = ? LIMIT 1");
$destStmt->bind_param("s", $destination);
$destStmt->execute();
$row = $destStmt->get_result()->fetch_assoc();
$destStmt->close();

if ($row) {
    $destination_id = $row['DestinationID'];
    $linkStmt = $connection->prepare("INSERT INTO package_destination (PackageID, DestinationID) VALUES (?, ?)");
    $linkStmt->bind_param("ii", $package_id, $destination_id);
    $linkStmt->execute();
    $linkStmt->close();
}
    
    // =========================
    // ACCOMMODATION LOOKUP
    // =========================

    $accStmt = $connection->prepare("
        SELECT AccommodationID
        FROM accommodation
        WHERE Name = ?
        LIMIT 1
    ");

    $accStmt->bind_param(
        "s",
        $accommodation
    );

    $accStmt->execute();

    $result = $accStmt->get_result();

    if ($row = $result->fetch_assoc()) {

        $accommodation_id = $row['AccommodationID'];

        $linkStmt = $connection->prepare("
            INSERT INTO accommodation_package
            (
                AccommodationID,
                PackageID
            )
            VALUES
            (?, ?)
        ");

        $linkStmt->bind_param(
            "ii",
            $accommodation_id,
            $package_id
        );

        $linkStmt->execute();

        $linkStmt->close();
    }

    $accStmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Package created successfully"
    ]);
}



respond(400, "error", "Unknown type: " . htmlspecialchars($data["type"]));
if ($data["type"] === "FetchDashboardSummary") {
    $agency_id = isset($_SESSION['agency_id']) ? (int)$_SESSION['agency_id'] : 1;

    // 1. Get packages count
    $p_res = $connection->query("SELECT COUNT(*) as count FROM package WHERE AgencyID = $agency_id");
    $p_row = $p_res->fetch_assoc();

    // 2. Fallbacks indicators values for bookings/revenue tables if missing
    $dashboardData = [
        "total_packages"    => $p_row['count'] ?? 0,
        "active_bookings"   => 24, 
        "revenue_collected" => 142500.00,
        "group_trips"       => 3
    ];
    respond(200, "success", $dashboardData);
}

if ($data["type"] === "FetchDetailedAnalytics") {
    $agency_id = isset($_SESSION['agency_id']) ? (int)$_SESSION['agency_id'] : 1;

    $result = $connection->query("SELECT Title as title, 'South Africa' as destination, 12 as booking_count FROM package WHERE AgencyID = $agency_id LIMIT 3");
    $popular = [];
    while($row = $result->fetch_assoc()) {
        $popular[] = $row;
    }

    respond(200, "success", ["popular_packages" => $popular]);
}

if ($data["type"] === "GetAllPackages") {
    $agency_filter = isset($_SESSION['agency_id']) ? "WHERE AgencyID = " . (int)$_SESSION['agency_id'] : "";
    
    $result = $connection->query("SELECT PackageID AS id, Title AS title, Description AS destination, Price AS price, Duration AS duration, 'Active' AS status FROM package $agency_filter ORDER BY PackageID DESC");
    $packages = [];
    while($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }
    respond(200, "success", $packages);
}

if ($data["type"] === "GetAllBookings") {
    // Queries mock return array or actual relational booking logs from database schema
    $bookings = [
        ["id" => 101, "customer_name" => "Sarah Jenkins", "customer_email" => "sarah@test.com", "package_title" => "Ultimate Luxury Cape Town Escape", "booking_date" => "2026-05-14", "status" => "APPROVED"],
        ["id" => 102, "customer_name" => "Michael Louw", "customer_email" => "louw.m@yahoo.com", "package_title" => "Kruger National Safari Adventure", "booking_date" => "2026-05-20", "status" => "PENDING"]
    ];
    respond(200, "success", $bookings);
}
/////New CODE
 ═════════════════════════════════════════════════════════════════════════════
// DASHBOARD — Summary counters
// ═════════════════════════════════════════════════════════════════════════════
 
if ($type === "FetchDashboardSummary") {
    $agency_id = sessionAgencyId();
 
    $p_res = $connection->query("SELECT COUNT(*) AS count FROM package WHERE AgencyID = $agency_id");
    $total_packages = (int)$p_res->fetch_assoc()['count'];
 
    $b_res = $connection->query("
        SELECT COUNT(*) AS count, COALESCE(SUM(b.TotalPrice), 0) AS revenue
        FROM booking b
        JOIN package p ON b.PackageID = p.PackageID
        WHERE p.AgencyID = $agency_id
          AND b.BookingStatus NOT IN ('Cancelled','Rejected')
    ");
    $b_row = $b_res->fetch_assoc();
 
    $g_res = $connection->query("SELECT COUNT(*) AS count FROM group_trip WHERE AgencyID = $agency_id");
    $group_trips = (int)$g_res->fetch_assoc()['count'];
 
    respond(200, "success", [
        "total_packages"    => $total_packages,
        "active_bookings"   => (int)$b_row['count'],
        "revenue_collected" => (float)$b_row['revenue'],
        "group_trips"       => $group_trips
    ]);
}
 
// ═════════════════════════════════════════════════════════════════════════════
// DASHBOARD — Analytics
// ═════════════════════════════════════════════════════════════════════════════
 
if ($type === "FetchDetailedAnalytics") {
    $agency_id = sessionAgencyId();
 
    $result = $connection->query("
        SELECT p.Title AS title,
               COALESCE(d.City, 'Unknown') AS destination,
               COUNT(b.BookingID) AS booking_count
        FROM package p
        LEFT JOIN booking b ON b.PackageID = p.PackageID
        LEFT JOIN package_destination pd ON pd.PackageID = p.PackageID
        LEFT JOIN destination d ON d.DestinationID = pd.DestinationID
        WHERE p.AgencyID = $agency_id
        GROUP BY p.PackageID, p.Title, d.City
        ORDER BY booking_count DESC
        LIMIT 5
    ");
 
    $popular = [];
    while ($row = $result->fetch_assoc()) {
        $popular[] = $row;
    }
 
    respond(200, "success", ["popular_packages" => $popular]);
}
 
// ═════════════════════════════════════════════════════════════════════════════
// PACKAGES — Get all (for manage_package.php table)
// ═════════════════════════════════════════════════════════════════════════════
 
if ($type === "GetAllPackages") {
    $agency_id = sessionAgencyId();
 
    $result = $connection->query("
        SELECT
            p.PackageID          AS id,
            p.Title              AS title,
            COALESCE(d.City, p.Description) AS destination,
            p.Price              AS price,
            p.Duration           AS duration,
            p.Pack_type          AS pack_type,
            p.Start_date         AS start_date,
            p.End_date           AS end_date,
            p.Max_people         AS max_people,
            COALESCE(p.Images, '') AS images,
            'Active'             AS status
        FROM package p
        LEFT JOIN package_destination pd ON pd.PackageID = p.PackageID
        LEFT JOIN destination d ON d.DestinationID = pd.DestinationID
        WHERE p.AgencyID = $agency_id
        GROUP BY p.PackageID
        ORDER BY p.PackageID DESC
    ");
 
    $packages = [];
    while ($row = $result->fetch_assoc()) {
        // Parse first image URL if stored as JSON array
        $images = json_decode($row['images'], true);
        $row['image_url'] = (!empty($images) && is_array($images))
            ? 'uploads/' . $images[0]
            : null;
        unset($row['images']);
        $packages[] = $row;
    }
 
    respond(200, "success", $packages);
}
 
// ═════════════════════════════════════════════════════════════════════════════
// PACKAGES — Create
// ═════════════════════════════════════════════════════════════════════════════
 
if ($type === "CreatePackage") {
    $agency_id = sessionAgencyId();
 
    // Support both JSON body and POST (form submit)
    $title       = trim($data['title']       ?? $_POST['Title']       ?? '');
    $destination = trim($data['destination'] ?? $_POST['destination'] ?? '');
    $price       = (float)($data['price']    ?? $_POST['price']       ?? 0);
    $duration    = (int)($data['duration']   ?? $_POST['duration']    ?? 0);
    $description = trim($data['description'] ?? $_POST['description'] ?? '');
    $start_date  = $data['startDate']        ?? $_POST['start_date']  ?? '';
    $end_date    = $data['endDate']          ?? $_POST['end_date']    ?? '';
    $max_people  = (int)($data['maxPeople']  ?? $_POST['max_people']  ?? 10);
    $pack_type   = trim($data['pack_type']   ?? $_POST['pack_type']   ?? 'Leisure');
    $flights     = trim($data['flights']     ?? $_POST['flights']     ?? '');
    $restaurants = trim($data['restaurants'] ?? $_POST['restaurants'] ?? '');
    $transport   = trim($data['transport']   ?? $_POST['transport']   ?? '');
    $attractions = trim($data['attractions'] ?? $_POST['attractions'] ?? '');
 
    // Handle image uploads (multipart form)
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
        INSERT INTO package
            (AgencyID, Max_people, Duration, Start_date, End_date, Title,
             Pack_type, Description, Price, Flights, Restaurants, Transport, Attractions, Images)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiisssssisssss",
        $agency_id, $max_people, $duration, $start_date, $end_date,
        $title, $pack_type, $description, $price,
        $flights, $restaurants, $transport, $attractions, $imagesJson
    );
    $stmt->execute();
    $package_id = $connection->insert_id;
    $stmt->close();
 
    // Link destination
    $destStmt = $connection->prepare("SELECT DestinationID FROM destination WHERE City = ? LIMIT 1");
    $destStmt->bind_param("s", $destination);
    $destStmt->execute();
    $destRow = $destStmt->get_result()->fetch_assoc();
    $destStmt->close();
    if ($destRow) {
        $linkStmt = $connection->prepare("INSERT INTO package_destination (PackageID, DestinationID) VALUES (?, ?)");
        $linkStmt->bind_param("ii", $package_id, $destRow['DestinationID']);
        $linkStmt->execute();
        $linkStmt->close();
    }
 
    // Handle group trip flag
    if (!empty($data['is_group_trip']) || !empty($_POST['is_group_trip'])) {
        $departure_date = $data['departure_date'] ?? $_POST['departure_date'] ?? $start_date;
        $max_seats      = (int)($data['max_seats'] ?? $_POST['max_seats'] ?? $max_people);
        $groupStmt = $connection->prepare("INSERT INTO group_trip (PackageID, AgencyID, DepartureDate, MaxSeats) VALUES (?, ?, ?, ?)");
        $groupStmt->bind_param("iisi", $package_id, $agency_id, $departure_date, $max_seats);
        $groupStmt->execute();
        $groupStmt->close();
    }
 
    respond(201, "success", ["package_id" => $package_id, "message" => "Package created successfully"]);
}
 
// ═════════════════════════════════════════════════════════════════════════════
// PACKAGES — Update price
// ═════════════════════════════════════════════════════════════════════════════
 
if ($type === "UpdatePackagePrice") {
    $agency_id = sessionAgencyId();
    $id    = (int)$data["id"];
    $price = (float)$data["price"];
 
    if ($id <= 0 || $price <= 0) respond(400, "error", "Invalid package ID or price");
 
    $stmt = $connection->prepare("UPDATE package SET Price = ? WHERE PackageID = ? AND AgencyID = ?");
    $stmt->bind_param("dii", $price, $id, $agency_id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
 
    if ($affected === 0) respond(404, "error", "Package not found or not owned by this agency");
 
    respond(200, "success", ["message" => "Price updated successfully", "new_price" => $price]);
}
 
// ═════════════════════════════════════════════════════════════════════════════
// PACKAGES — Toggle visibility (uses Pack_type as status flag workaround)
//   Since the package table has no Status column, we track a hidden flag
//   in the API response only. For a persistent toggle, add a Status column:
//   ALTER TABLE package ADD COLUMN Status VARCHAR(20) DEFAULT 'Active';
// ═════════════════════════════════════════════════════════════════════════════
 
if ($type === "TogglePackageVisibility") {
    $agency_id = sessionAgencyId();
    $id = (int)$data["id"];
 
    // Check current status via a simple description prefix convention
    // (Proper solution: ALTER TABLE package ADD COLUMN Status VARCHAR(20) DEFAULT 'Active')
    // For now we return a toggled state — frontend tracks it in the badge
    $stmt = $connection->prepare("SELECT Title FROM package WHERE PackageID = ? AND AgencyID = ?");
    $stmt->bind_param("ii", $id, $agency_id);
    $stmt->execute();
    $pkg = $stmt->get_result()->fetch_assoc();
    $stmt->close();
 
    if (!$pkg) respond(404, "error", "Package not found");
 
    // Toggle logic: check if title starts with [HIDDEN] prefix (simple convention)
    $isHidden = strpos($pkg['Title'], '[HIDDEN] ') === 0;
    if ($isHidden) {
        $newTitle  = substr($pkg['Title'], 9); // Remove [HIDDEN] prefix
        $newStatus = "Active";
    } else {
        $newTitle  = '[HIDDEN] ' . $pkg['Title'];
        $newStatus = "Delisted";
    }
 
    $stmt = $connection->prepare("UPDATE package SET Title = ? WHERE PackageID = ? AND AgencyID = ?");
    $stmt->bind_param("sii", $newTitle, $id, $agency_id);
    $stmt->execute();
    $stmt->close();
 
    respond(200, "success", ["new_status" => $newStatus, "new_title" => $newTitle]);
}
 
// ═════════════════════════════════════════════════════════════════════════════
// PACKAGES — Delete
// ═════════════════════════════════════════════════════════════════════════════
 
if ($type === "DeletePackage") {
    $agency_id = sessionAgencyId();
    $id = (int)$data["id"];
 
    if ($id <= 0) respond(400, "error", "Invalid package ID");
 
    // Remove linked records first to avoid FK constraint errors
    $connection->query("DELETE FROM accommodation_package WHERE PackageID = $id");
    $connection->query("DELETE FROM package_destination WHERE PackageID = $id");
    // Note: do NOT delete bookings — archive them instead in production
 
    $stmt = $connection->prepare("DELETE FROM package WHERE PackageID = ? AND AgencyID = ?");
    $stmt->bind_param("ii", $id, $agency_id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
 
    if ($affected === 0) respond(404, "error", "Package not found or not owned by this agency");
 
    respond(200, "success", ["message" => "Package deleted successfully"]);
}
 
// ═════════════════════════════════════════════════════════════════════════════
// BOOKINGS — Get all (for manage_booking.php table)
// ═════════════════════════════════════════════════════════════════════════════
 
if ($type === "GetAllBookings") {
    $agency_id = sessionAgencyId();
 
    $result = $connection->query("
        SELECT
            b.BookingID                            AS id,
            CONCAT(u.Name, ' ', u.Surname)         AS customer_name,
            u.Email                                AS customer_email,
            p.Title                                AS package_title,
            DATE_FORMAT(b.BookingDate, '%d %b %Y') AS booking_date,
            b.TotalPrice                           AS price,
            b.SeatsBooked                          AS seats,
            b.BookingStatus                        AS status
        FROM booking b
        JOIN package p ON b.PackageID = p.PackageID
        JOIN traveller t ON b.TravelerID = t.TravellerID
        JOIN user u ON t.UserID = u.UserID
        WHERE p.AgencyID = $agency_id
        ORDER BY b.BookingDate DESC
    ");
 
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
 
    respond(200, "success", $bookings);
}
 
// ═════════════════════════════════════════════════════════════════════════════
// BOOKINGS — Update status (Approve / Reject / Cancel)
// ═════════════════════════════════════════════════════════════════════════════
 
if ($type === "UpdateBookingStatus") {
    $agency_id = sessionAgencyId();
    $id     = (int)$data["id"];
    $status = strtoupper(trim($data["status"] ?? ''));
 
    $allowed = ["APPROVED", "REJECTED", "CANCELLED", "PENDING"];
    if (!in_array($status, $allowed)) {
        respond(400, "error", "Invalid status. Must be one of: " . implode(", ", $allowed));
    }
 
    // Verify this booking belongs to the agency
    $stmt = $connection->prepare("
        UPDATE booking b
        JOIN package p ON b.PackageID = p.PackageID
        SET b.BookingStatus = ?
        WHERE b.BookingID = ? AND p.AgencyID = ?
    ");
    $stmt->bind_param("sii", $status, $id, $agency_id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
 
    if ($affected === 0) respond(404, "error", "Booking not found or access denied");
 
    respond(200, "success", ["message" => "Booking status updated to $status", "new_status" => $status]);
}
 
// ═════════════════════════════════════════════════════════════════════════════
// GROUP TRIPS — Increment participant count
// ═════════════════════════════════════════════════════════════════════════════
 
if ($type === "IncrementGroupTrip") {
    $agency_id = sessionAgencyId();
    $id = (int)$data["id"];
 
    $stmt = $connection->prepare("
        UPDATE group_trip
        SET CurrentParticipants = CurrentParticipants + 1
        WHERE GroupTripID = ? AND AgencyID = ? AND CurrentParticipants < MaxSeats
    ");
    $stmt->bind_param("ii", $id, $agency_id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
 
    if ($affected === 0) respond(400, "error", "Could not increment — trip may be full or not found");
 
    respond(200, "success", ["message" => "Participant added successfully"]);
}
 





respond(400, "error", "Unknown type context action: " . htmlspecialchars($data["type"]));
?>
<<<<<<< HEAD
=======
<<<<<<< HEAD
=======





>>>>>>> 615920c0c43885ce72ecd95546a4a199cdd83d14
if ($data["type"] === "FetchDashboardSummary") {
    $agency_id = isset($_SESSION['agency_id']) ? (int)$_SESSION['agency_id'] : 1;

    // 1. Get packages count
    $p_res = $connection->query("SELECT COUNT(*) as count FROM package WHERE AgencyID = $agency_id");
    $p_row = $p_res->fetch_assoc();

    // 2. Fallbacks indicators values for bookings/revenue tables if missing
    $dashboardData = [
        "total_packages"    => $p_row['count'] ?? 0,
        "active_bookings"   => 24, 
        "revenue_collected" => 142500.00,
        "group_trips"       => 3
    ];
    respond(200, "success", $dashboardData);
}

if ($data["type"] === "FetchDetailedAnalytics") {
    $agency_id = isset($_SESSION['agency_id']) ? (int)$_SESSION['agency_id'] : 1;

    $result = $connection->query("SELECT Title as title, 'South Africa' as destination, 12 as booking_count FROM package WHERE AgencyID = $agency_id LIMIT 3");
    $popular = [];
    while($row = $result->fetch_assoc()) {
        $popular[] = $row;
    }

    respond(200, "success", ["popular_packages" => $popular]);
}

if ($data["type"] === "GetAllPackages") {
    $agency_filter = isset($_SESSION['agency_id']) ? "WHERE AgencyID = " . (int)$_SESSION['agency_id'] : "";
    
    $result = $connection->query("SELECT PackageID AS id, Title AS title, Description AS destination, Price AS price, Duration AS duration, 'Active' AS status FROM package $agency_filter ORDER BY PackageID DESC");
    $packages = [];
    while($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }
    respond(200, "success", $packages);
}

if ($data["type"] === "GetAllBookings") {
    // Queries mock return array or actual relational booking logs from database schema
    $bookings = [
        ["id" => 101, "customer_name" => "Sarah Jenkins", "customer_email" => "sarah@test.com", "package_title" => "Ultimate Luxury Cape Town Escape", "booking_date" => "2026-05-14", "status" => "APPROVED"],
        ["id" => 102, "customer_name" => "Michael Louw", "customer_email" => "louw.m@yahoo.com", "package_title" => "Kruger National Safari Adventure", "booking_date" => "2026-05-20", "status" => "PENDING"]
    ];
    respond(200, "success", $bookings);
}

respond(400, "error", "Unknown type context action: " . htmlspecialchars($data["type"]));
?>
>>>>>>> 8e86853244f2dee86464217f3f58380ca204af9a

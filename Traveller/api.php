<?php
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

    $destinationStmt = $connection->prepare("
        INSERT INTO package_destination
        (
            PackageID,
            Destination_name
        )
        VALUES
        (?, ?)
    ");

    $destinationStmt->bind_param(
        "is",
        $package_id,
        $destination
    );

    $destinationStmt->execute();

    $destinationStmt->close();

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
?>






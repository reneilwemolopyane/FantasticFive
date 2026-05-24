<?php

// =====================================================
// DATABASE CONNECTION
// =====================================================

try {

    $pdo = new PDO(
        "mysql:host=localhost;dbname=travel_booking_db",
        "root",
        "Zamokuhle31$"
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {

    die("DB Connection failed: " . $e->getMessage());
}

// =====================================================
// RAPIDAPI REQUEST
// BOOKING.COM CAR RENTAL API
// =====================================================

$curl = curl_init();

curl_setopt_array($curl, [

    CURLOPT_URL =>
    "https://booking-com15.p.rapidapi.com/api/v1/cars/searchCarRentals?" .
    http_build_query([

        "pick_up_place" => "Cape Town",
        "drop_off_place" => "Cape Town",
        "pick_up_date" => "2026-06-01",
        "drop_off_date" => "2026-06-07",
        "currency_code" => "ZAR"
    ]),

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_HTTPHEADER => [

        "x-rapidapi-host: booking-com15.p.rapidapi.com",

        "x-rapidapi-key: 8c57187365mshe776214d0c9807ep12f5f1jsn150d5cf5dc28"
    ],
]);

$response = curl_exec($curl);

curl_close($curl);

// =====================================================
// SAVE RESPONSE (OPTIONAL)
// =====================================================

file_put_contents(
    "car_rentals.json",
    $response
);

// =====================================================
// DECODE JSON
// =====================================================

$data = json_decode($response, true);

// =====================================================
// ERROR HANDLING
// =====================================================

if (isset($data["message"])) {

    die(
        "<h2>API ERROR:</h2> " .
        $data["message"]
    );
}

// =====================================================
// DEBUG
// =====================================================

echo "<pre>";
print_r($data);
echo "</pre>";

// =====================================================
// GET CAR RESULTS
// =====================================================

$cars = $data["data"] ?? [];

if (empty($cars)) {

    die("No cars found.");
}

$inserted = 0;

// =====================================================
// LOOP THROUGH CARS
// =====================================================

foreach ($cars as $car) {

    // =================================================
    // BASIC INFO
    // =================================================

    $category =
    $car["vehicle_info"]["v_name"] ??
    "Unknown";

    $manufacturer =
    $car["vehicle_info"]["brand"] ??
    "Unknown";

    $model =
    $car["vehicle_info"]["model"] ??
    "Unknown";

    $seats =
    $car["vehicle_info"]["seats"] ??
    4;

    $transmission =
    $car["vehicle_info"]["transmission"] ??
    "Automatic";

    // =================================================
    // PRICE
    // =================================================

    $price_per_day =
    $car["pricing_info"]["price"] ??
    0;

    $total_cost =
    $car["pricing_info"]["total"] ??
    0;

    // =================================================
    // IMAGE
    // =================================================

    $image =
    $car["vehicle_info"]["image_url"] ??
    null;

    // =================================================
    // PACKAGE ID
    // =================================================

    $packageID = rand(1, 5);

    // =================================================
    // CHECK DUPLICATES
    // =================================================

    $check = $pdo->prepare(

        "SELECT CarID
         FROM car
         WHERE Manufacturer = ?
         AND Model = ?"
    );

    $check->execute([

        $manufacturer,
        $model
    ]);

    if ($check->fetch()) {

        echo "
        <h3>
        Skipped duplicate:
        $manufacturer $model
        </h3>
        ";

        continue;
    }

    // =================================================
    // INSERT INTO DATABASE
    // =================================================

    $stmt = $pdo->prepare(

        "INSERT INTO car
        (
            PackageID,
            Category,
            Seats,
            Transmission,
            Manufacturer,
            Model,
            Price_per_day,
            Total_rental_cost,
            ImageURL
        )

        VALUES

        (
            :packageID,
            :category,
            :seats,
            :transmission,
            :manufacturer,
            :model,
            :price_per_day,
            :total_cost,
            :image
        )"
    );

    $stmt->execute([

        ":packageID" => $packageID,

        ":category" => $category,

        ":seats" => $seats,

        ":transmission" => $transmission,

        ":manufacturer" => $manufacturer,

        ":model" => $model,

        ":price_per_day" => $price_per_day,

        ":total_cost" => $total_cost,

        ":image" => $image
    ]);

    // =================================================
    // DISPLAY RESULTS
    // =================================================

    echo "
    <div style='
        border:1px solid #ccc;
        margin:20px;
        padding:20px;
        width:700px;
    '>
    ";

    echo "<h2>$manufacturer $model</h2>";

    echo "<b>Category:</b> $category <br>";

    echo "<b>Seats:</b> $seats <br>";

    echo "<b>Transmission:</b> $transmission <br>";

    echo "<b>Price Per Day:</b> R$price_per_day <br>";

    echo "<b>Total Cost:</b> R$total_cost <br><br>";

    if ($image) {

        echo "
        <img
            src='$image'
            width='350'
            style='border-radius:10px;'
        >
        ";
    }

    echo "</div>";

    echo "
    <h3>
    Inserted:
    $manufacturer $model
    </h3>
    ";

    $inserted++;
}

// =====================================================
// FINAL MESSAGE
// =====================================================

echo "
<h1>
DONE.
TOTAL INSERTED: $inserted
</h1>
";

?>
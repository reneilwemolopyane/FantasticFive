<?php

try {

    $pdo = new PDO(
        "mysql:host=localhost;dbname=travel_booking_db",
        "root",
        "Zamokuhle31$"
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    die("DB Connection failed: " . $e->getMessage());
}

// =============================================
// LOAD JSON RESPONSE
// =============================================

$json = file_get_contents("response.json");

$data = json_decode($json, true);

// =============================================
// MAIN CONTAINER
// =============================================

$main =
$data["data"]["AppPresentation_queryPoiQuestionDetails"];

$hotelName =
$main["container"]["navTitle"] ?? "Unknown Hotel";

echo "<h1>$hotelName</h1>";

// =============================================
// SECTIONS
// =============================================

$sections = $main["sections"] ?? [];

foreach ($sections as $section) {

    // -----------------------------------------
    // ONLY PROCESS QUESTIONS / ANSWERS
    // -----------------------------------------

    if (
        !isset($section["text"])
    ) {
        continue;
    }

    // -----------------------------------------
    // REVIEW / COMMENT TEXT
    // -----------------------------------------

    $text =
    $section["text"] ?? "No text";

    // -----------------------------------------
    // USER INFO
    // -----------------------------------------

    $user =
    $section["memberProfile"]["displayName"]
    ?? "Anonymous";

    // -----------------------------------------
    // DATE
    // -----------------------------------------

    $date =
    $section["writtenDate"]["string"]
    ?? "Unknown Date";

    // -----------------------------------------
    // AVATAR IMAGE
    // -----------------------------------------

    $avatar = null;

    if (
        isset(
            $section["memberProfile"]["avatar"]["data"]["sizes"][7]["url"]
        )
    ) {

        $avatar =
        $section["memberProfile"]["avatar"]["data"]["sizes"][7]["url"];
    }

    // =========================================
    // DISPLAY DATA
    // =========================================

    echo "<div style='
        border:1px solid #ccc;
        padding:20px;
        margin-bottom:20px;
        width:700px;
    '>";

    echo "<h3>$user</h3>";

    echo "<b>Date:</b> $date <br><br>";

    echo "<p>$text</p>";

    if ($avatar) {

        echo "
        <img
            src='$avatar'
            width='120'
            style='border-radius:50%;'
        >
        ";
    }

    echo "</div>";

    // =========================================
    // INSERT INTO DATABASE
    // =========================================

    $stmt = $pdo->prepare(

        "INSERT INTO reviews
        (
            DestinationName,
            UserName,
            ReviewText,
            ReviewDate,
            AvatarURL
        )
        VALUES
        (
            :destination,
            :user,
            :text,
            :date,
            :avatar
        )"
    );

    $stmt->execute([

        ":destination" => $hotelName,
        ":user" => $user,
        ":text" => $text,
        ":date" => $date,
        ":avatar" => $avatar
    ]);

    echo "Inserted review by $user <br><br>";
}

?>
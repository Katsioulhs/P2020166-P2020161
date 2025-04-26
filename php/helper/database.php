<?php

require_once 'logger.php';

function db_connect(): mysqli|null
{
    $conn = new mysqli(
        "di_inter_tech_2025_mysql",
        "webuser",
        "webpass",
        "di_internet_technologies_project"
    );

    if ($conn->connect_error) {
        logError("Database connection failed: " . $conn->connect_error);
        return null;
    }

    return $conn;
}

function db_disconnect(mysqli $db): void
{
    $db->close();
}

// // Check connection
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }

// // Query to fetch quotes from the database
// $sql = "SELECT value FROM test";

// $result = $conn->query($sql);

// // Check if there are any rows returned
// if ($result->num_rows > 0) {
//     // Output data of each row
//     while($row = $result->fetch_assoc()) {
//         echo $row["value"] . "<br>";
//     }
// } else {
//     echo "0 results";
// }
// // Close connection
// $conn->close();

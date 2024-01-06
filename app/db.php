<?php

// Attempt to establish a connection to the database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check if the connection was successful
if (!$conn) {
    // If the connection fails, terminate the script and display an error message
    die("Connection failed: " . mysqli_connect_error());
}

// The connection was successful if the script reaches this point

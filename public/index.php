<?php

// Include necessary files
require_once('../app/configs.php');
require_once('../app/functions.php');
require_once('../app/db.php');

// Define routes
$routes = require_once('../app/routes.php');

// Get the route from the URL, default to 'index'
$route = isset($_GET['route']) ? $_GET['route'] : 'index';

// Check if the route exists and the corresponding function is defined
if (array_key_exists($route, $routes) && function_exists($routes[$route])) {
    // Call the route function and exit
    $routes[$route]($configs, $conn);
    die;
}

// Default route function to display the dashboard
function index($configs, $conn)
{
    return require_once('../view/dashboard.php');
}

// Route function to download products from a CSV file
function downloadProducts($configs, $conn)
{
    try {
        $params = [
            'file_name' => isset($_POST['file_name']) ? $_POST['file_name'] : NULL,
            'file_base64' => isset($_POST['file_base64']) ? $_POST['file_base64'] : NULL,
        ];

        if (!$params['file_base64']) {
            throw new Exception('No Excel file provided', 204);
        }

        // Extract file extension and decode the base64 content
        $ext = explode("data:", substr($params['file_base64'], 0, strpos($params['file_base64'], ';base64')))[1];
        $fileBase64 = explode("base64,", $params['file_base64'])[1];
        $file = base64_decode($fileBase64);

        // Create a temporary file and write the decoded content
        $tempFilePath = tempnam(sys_get_temp_dir(), "products_temp_." . md5(uniqid()));
        file_put_contents($tempFilePath, $file);

        mysqli_begin_transaction($conn);

        // Delete existing products data
        $productsDeleteSql = "DELETE FROM `products`";
        if (!mysqli_query($conn, $productsDeleteSql)) {
            throw new Exception("Error deleting existing data: " . mysqli_error($conn), 500);
        }

        // Open the temporary file for reading
        $handle = fopen($tempFilePath, 'r');

        // Check if the file is successfully opened
        if ($handle) {
            $headers = fgetcsv($handle); // Get the CSV headers

            $products = [];
            $batchSize = 500;

            // Read each row from the CSV file
            while (($row = fgetcsv($handle)) !== false) {
                $product['import_id'] = NULL;

                $product = array_merge($product, array_combine($headers, $row));

                foreach ($product as $key => $value) {
                    $product[$key] = mysqli_real_escape_string($conn, $value);
                }
                $product['import_id'] = NULL;

                $products[] = $product;

                if (count($products) >= $batchSize) {
                    insertBatchIntoDatabase($conn, $params['file_name'], $ext, $products);
                    $products = [];
                }
            }

            fclose($handle);
        }

        return jsonRes(restRes(200, 'Success'));
    } catch (Exception $e) {

        return jsonRes(restRes($e->getCode(), $e->getMessage()));
    } finally {

        if (isset($tempFilePath) && file_exists($tempFilePath)) {
            unlink($tempFilePath);
        }
    }
}

// Function to insert a batch of products into the database
function insertBatchIntoDatabase($conn, $fileName, $fileExt, $products)
{
    try {
        $importHistorySql = "INSERT INTO `import_history` (
                                `file_name`,
                                `file_ext`,
                                `file_type`,
                                `data_count`
                              ) VALUES (
                                '" . $fileName . "',
                                '" . $fileExt . "',
                                'product',
                                " . count($products) . "
                              )";

        if (!mysqli_query($conn, $importHistorySql)) {
            throw new Exception('Error inserting data into import_history: ' . mysqli_error($conn), 500);
        }

        $importHistoryID = mysqli_insert_id($conn);

        // Update each product with the import ID
        foreach ($products as $key => $item) {
            $products[$key]['import_id'] = $importHistoryID;
        }

        // Create an array of SQL values for the products
        $productValues = [];
        foreach ($products as $product) {
            $productValues[] = "('" . implode("', '", $product) . "')";
        }

        // Insert the batch of products into the database
        $productsInsertBatchSql = "INSERT INTO `products` (
                                  `import_id`,
                                  `category`,
                                  `firstname`,
                                  `lastname`,
                                  `email`,
                                  `gender`,
                                  `birthdate`
                                ) VALUES " . implode(", ", $productValues);

        if (!mysqli_query($conn, $productsInsertBatchSql)) {
            throw new Exception("Error inserting data: " . mysqli_error($conn), 500);
        }

        mysqli_commit($conn);
    } catch (Exception $e) {

        mysqli_rollback($conn);
        throw $e;
    }
}

// Function to fetch products with optional filters
function products($configs, $conn)
{
    try {
        $params = [
            'page' => isset($_GET['page']) ? (int)$_GET['page'] : 1,
            'pageSize' => isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10,
            'category' => isset($_GET['category']) ? $_GET['category'] : '',
            'gender' => isset($_GET['gender']) ? $_GET['gender'] : '',
            'dob' => isset($_GET['dob']) ? $_GET['dob'] : '',
            'age' => isset($_GET['age']) ? (int)$_GET['age'] : 0,
            'ageRangeStart' => isset($_GET['ageRangeStart']) ? (int)$_GET['ageRangeStart'] : 0,
            'ageRangeEnd' => isset($_GET['ageRangeEnd']) ? (int)$_GET['ageRangeEnd'] : 0,
            'download_csv' => isset($_GET['download_csv']) ? (int)$_GET['download_csv'] : 0,
        ];

        $offset = ($params['page'] - 1) * $params['pageSize'];

        $whereClause = [];

        if (!empty($params['category'])) {
            $whereClause[] = "`category` = '" . mysqli_real_escape_string($conn, $params['category']) . "'";
        }

        if (!empty($params['gender'])) {
            $whereClause[] = "`gender` = '" . mysqli_real_escape_string($conn, $params['gender']) . "'";
        }

        if (!empty($params['dob'])) {
            $whereClause[] = "`birthdate` = '" . mysqli_real_escape_string($conn, $params['dob']) . "'";
        }

        if ($params['age'] > 0) {
            $whereClause[] = "YEAR(CURDATE()) - YEAR(`birthdate`) = " . (int)$params['age'];
        }

        if ($params['ageRangeStart'] > 0 && $params['ageRangeEnd'] > 0) {
            $whereClause[] = "YEAR(CURDATE()) - YEAR(`birthdate`) BETWEEN " . (int)$params['ageRangeStart'] . " AND " . (int)$params['ageRangeEnd'];
        }

        // Build the WHERE condition
        $whereCondition = '';
        if (!empty($whereClause)) {
            $whereCondition = 'WHERE ' . implode(' AND ', $whereClause);
        }

        $countQuery = "SELECT COUNT(*) AS total FROM `products` $whereCondition";
        $countResult = $conn->query($countQuery);
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $params['pageSize']);

        $selectQuery = "SELECT * FROM `products` $whereCondition LIMIT $offset, " . $params['pageSize'];
        $result = $conn->query($selectQuery);

        // Fetch the data from the result set
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $response = [
            'data' => $data,
            'pagination' => [
                'page' => $params['page'],
                'pageSize' => $params['pageSize'],
                'totalPages' => $totalPages,
                'totalRecords' => $totalRecords,
            ],
        ];

        // Check if CSV download is requested and data is available
        if ($params['download_csv'] === 1 && $data) {
            $csvFileName = 'downloads/products_' . date('Y-m-d_H-i-s') . '.csv';
            downloadCSV($data, $csvFileName);
        }

        return jsonRes($response);
    } catch (Exception $e) {

        return jsonRes(restRes($e->getCode(), $e->getMessage()));
    }
}

// Function to download CSV file from data array
function downloadCSV($data, $filename = 'export.csv')
{
    $filePath = __DIR__ . '/../' . $filename;

    $output = fopen($filePath, 'w');

    if ($output === false) {
        die('Failed to open file for writing');
    }

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Write CSV headers
    fputcsv($output, array_keys($data[0]));

    // Write each row in the CSV file
    foreach ($data as $row) {
        array_walk($row, function (&$value) {
            $value = str_replace('"', '""', $value);
        });

        fputcsv($output, $row);
    }

    fclose($output);

    readfile($filePath);
}

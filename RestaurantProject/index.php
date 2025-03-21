<?php
// Configure logging
$logDir = 'logs';
$logFile = "$logDir/setup-" . date('Y-m-d') . '.log';

// Create log directory if not exists
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
    error_log("Created log directory: $logDir");
}

// Start logging
ini_set('error_log', $logFile);
error_log("=== Starting restaurant application setup ===");

// Check if setup has already been completed
if (file_exists('setup_completed.flag')) {
    $msg = "Setup has already been completed. The SQL setup won't run again.";
    error_log($msg);
    echo $msg;
    exit();
}

try {
    // Get environment variables
    $db_host = getenv('DB_HOST') ?: 'db';
    $db_user = getenv('DB_USER') ?: 'root';
    $db_pass = getenv('DB_PASS') ?: 'rootpass';

    error_log("Attempting database connection to $db_host");
    
    // Create Connection with error logging
    $link = new mysqli($db_host, $db_user, $db_pass);
    
    if ($link->connect_error) {
        throw new Exception("Connection failed: " . $link->connect_error);
    }
    error_log("Successfully connected to database server");

    // Create database
    $sqlCreateDB = "CREATE DATABASE IF NOT EXISTS restaurantdb";
    if ($link->query($sqlCreateDB)) {
        error_log("Database restaurantdb created/verified");
    } else {
        throw new Exception("Error creating database: " . $link->error);
    }

    // Select database
    $link->select_db('restaurantdb');
    error_log("Selected database restaurantdb");

    // Execute SQL from file
    function executeSQLFromFile($filename, $link) {
        error_log("Executing SQL from $filename");
        
        if (!file_exists($filename)) {
            throw new Exception("SQL file $filename not found");
        }
        
        $sql = file_get_contents($filename);
        
        if ($link->multi_query($sql)) {
            while ($link->more_results()) {
                $link->next_result();
            }
            error_log("SQL executed successfully");
            file_put_contents('setup_completed.flag', date('Y-m-d H:i:s'));
        } else {
            throw new Exception("SQL error: " . $link->error);
        }
    }

    executeSQLFromFile('/docker-entrypoint-initdb.d/init.sql', $link);
    $link->close();

} catch (Exception $e) {
    $errorMsg = "SETUP ERROR: " . $e->getMessage();
    error_log($errorMsg);
    die("<div class='alert alert-danger'>$errorMsg</div>");
}

error_log("=== Setup completed successfully ===");
?>
<a href="customerSide/home/home.php" class="btn btn-success">Go to Home Page</a>
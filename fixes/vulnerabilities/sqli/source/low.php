<?php

if( isset( $_REQUEST[ 'Submit' ] ) ) {
    // Get input
    $id = $_REQUEST[ 'id' ];

    switch ($_DVWA['SQLI_DB']) {

        case MYSQL:
            // --- Secure version using prepared statements ---
            $mysqli_conn = $GLOBALS["___mysqli_ston"];

            // Validate input (must be digits only)
            if (!preg_match('/^\d+$/', $id)) {
                $html .= '<pre>Invalid ID parameter.</pre>';
                break;
            }

            // Prepare SQL statement
            if ($stmt = mysqli_prepare($mysqli_conn, "SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1")) {

                // Bind parameter as integer
                mysqli_stmt_bind_param($stmt, "i", $id);

                // Execute query
                mysqli_stmt_execute($stmt);

                // Bind result variables
                mysqli_stmt_bind_result($stmt, $first, $last);

                // Fetch result
                if (mysqli_stmt_fetch($stmt)) {
                    // Output safely (prevent XSS)
                    $first_safe = htmlspecialchars($first, ENT_QUOTES, 'UTF-8');
                    $last_safe  = htmlspecialchars($last, ENT_QUOTES, 'UTF-8');
                    $html .= "<pre>ID: {$id}<br />First name: {$first_safe}<br />Surname: {$last_safe}</pre>";
                } else {
                    $html .= '<pre>No user found.</pre>';
                }

                // Close statement
                mysqli_stmt_close($stmt);
            } else {
                $html .= '<pre>Database error: failed to prepare statement.</pre>';
            }

            mysqli_close($mysqli_conn);
            break;

        case SQLITE:
            global $sqlite_db_connection;

            $query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";
            try {
                $results = $sqlite_db_connection->query($query);
            } catch (Exception $e) {
                echo 'Caught exception: ' . $e->getMessage();
                exit();
            }

            if ($results) {
                while ($row = $results->fetchArray()) {
                    // Get values
                    $first = $row["first_name"];
                    $last  = $row["last_name"];

                    // Feedback for end user
                    $html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
                }
            } else {
                echo "Error in fetch ".$sqlite_db->lastErrorMsg();
            }
            break;
    }
}

?>


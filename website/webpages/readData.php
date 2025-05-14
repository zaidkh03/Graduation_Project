<?php

// function to call the data from tables based on the id
function profile_dash_data($table, $value, $id)
{
    // Include the database connection file
    include 'db_connection.php';

    // Prepare the SQL query to select all data from the specified table
    $sql = "SELECT * FROM $table WHERE id = $id";
    $result = $conn->query($sql);

    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    // check if the result has any rows
    if (mysqli_num_rows($result) > 0) {

        // print the data in the section you call it once 
        while ($row = $result->fetch_assoc()) {
            echo "{$row[$value]}";
        }
    } else {
        echo "No data found.";
    }
}
// function to count the data that has the same things


// function to call the data from other tables based on the foreign key
function calling_data($table, $value, $id, $forgien)
{
    // Include the database connection file
    include 'db_connection.php';

    if ($value == "all") {
        $sql = "
            SELECT $table[0].*, $table[1].*
            FROM $table[0]
            JOIN $table[1] ON $table[0].$forgien = $table[1].id
            WHERE $table[0].id = $id
        ";
    } else {
        $sql = "
            SELECT 
                $table[0].$value AS value1, 
                $table[1].$value AS value2
            FROM $table[0]
            JOIN $table[1] ON $table[0].$forgien = $table[1].id
            WHERE $table[0].id = $id
        ";
    }

    $result = $conn->query($sql);

    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($value == "all") {
                foreach ($row as $key => $val) {
                    echo "{$val}<br>";
                }
            } else {
                echo "{$row['value2']}<br>";
            }
        }
    } else {
        echo " No data found.";
    }
}

function notifications($id ,$role){
    // Include the database connection file
    include 'db_connection.php';

    // Prepare the SQL query to select all data from the specified table
    $sql = "SELECT * FROM notifications WHERE user_id = $id AND user_role = '$role'";
    $result = $conn->query($sql);

    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    // check if the result has any rows 
    if (mysqli_num_rows($result) > 0) {
        // print the data in the section you call it once 
        while ($row = $result->fetch_assoc()) {
            echo "<div class='dropdown-divider'></div>
        <a  class='dropdown-item'>
          <i class='fas fa-envelope mr-2'></i> {$row['message']}
          <span class='float-right text-muted text-sm'>{$row['created_at']}</span>
        </a>";
        }
    } else {
        echo "<div class='dropdown-divider'></div>
        <a  class='dropdown-item'>
          <i class='fas fa-bell-slash mr-2'></i> No notifications available
        </a>";
    }
}

?>
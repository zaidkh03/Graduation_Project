<?php

// function to call the data to create the table in the pages
function table_Data($table, $values, $href)
{
    // Include the database connection file
    include 'db_connection.php';

    // Prepare the SQL query to select all data from the specified table
    $sql = "SELECT * FROM $table";
    $result = $conn->query($sql);

    if (!$result) {
        die("Query failed: " . $conn->error);
    }



        // if the result has rows, loop through each row and print the values
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";

            switch ($href) {

                // case for the teacher notification table to add the checkbox in the first column
                case ($href[0] == "teachernotification"):
                    echo "<td class='text-center'>
                                <input type='checkbox' class='row-checkbox' />
                              </td>";
                    break;
            }
            // Loop through the values array and print each value
            foreach ($values as $column) {
                echo "<td>{$row[$column]}</td>";
                echo "<script> consol.log (<td>{$row[$column]}</td>) </script>";
            }
            switch ($href) {
                // case for the admin to check and add the action buttons
                case ($href[0] == "admin"):
                    echo "
            <td style='text-align: center;'>
            <a href='$href[1]?id={$row['id']}'>
            <button type='button' class='btn btn-sm btn-primary mr-1' title='Edit'>
                <ion-icon name='create-outline'></ion-icon>
            </button>
            </a>
             <a href='$href[2]?id={$row['id']}'>
            <button type='button' class='btn btn-sm btn-danger' title='Delete'>
                <ion-icon name='trash-outline'></ion-icon>
            </button>
            </a>
            </td>
            ";
                    break;
                case ($href[0] == "teacherattendance"):
                    echo "<td class='text-center'>
                            <input type='checkbox' class='absent-checkbox' />
                          </td>";
                    break;
                // case for the parent to check it and add and print the last three rows
                case ($href[0] == "parent"):
                    echo "<td>
                        <input type='radio' name='agreement1' value='agree'> Agree
                        <input type='radio' name='agreement1' value='disagree'> Disagree
                      </td>
                      <td>
                        <select name='excuse1' class='form-control form-control-sm'>
                        <option value='sick'>Sick</option>
                        <option value='personal'>Personal/Family Related</option>
                        <option value='none'>None</option>
                        </select>
                      </td>
                      <td>
                        <button type='submit' class='btn btn-primary btn-sm'>Submit</button>
                      </td>";
                    break;
                // case for the class page to check it and add the action buttons
                case ($href[0] == "class"):
                    echo "
                    <td style='text-align: center'>
                              <a href='../edit/edit_class.php?id={$row['id']}' class='btn btn-sm btn-primary mr-1' title='Edit'>
                                <ion-icon name='create-outline'></ion-icon>
                              </a>
                              <a href='assign_subjects.php?class_id={$row['id']}' class='btn btn-sm btn-warning mr-1' title='Assign Subjects'>
                                <ion-icon name='book-outline'></ion-icon>
                              </a>
                              <a href='manage_students.php?class_id={$row['id']}' class='btn btn-sm btn-info mr-1' title='Manage Students'>
                                <ion-icon name='people-outline'></ion-icon>
                              </a>
                              <a href='../delete/delete_class.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this class?\")' title='Delete'>
                                <ion-icon name='trash-outline'></ion-icon>
                              </a>
                            </td>";
                    break;
            }

            echo "</tr>";
        }
    
}

function select_Data($table, $value, $id_value,$key)
{

    // Include the database connection file
    include 'db_connection.php';

    // Prepare and execute SQL query
$sql = "SELECT * FROM $table WHERE $key = $id_value";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Output a single <select> element
echo "<select id='filterClass' class='form-control form-control-sm' onchange='view_class()'>";

// Loop through the results and create <option>s
while ($row = $result->fetch_assoc()) {
    // Change 'id' and 'name' to match your actual column names
    echo "<option value='{$row['id']}'>{$row[$value]}</option>";
}

echo "</select>";

}

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

function notifications($id){
    // Include the database connection file
    include 'db_connection.php';

    // Prepare the SQL query to select all data from the specified table
    $sql = "SELECT * FROM notifications WHERE user_id = $id";
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

function student_role(){
    
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Include session + role protection + get $adminId
require_once '../../login/auth/init.php';
if ($user['role'] !== 'student') {
  header("Location: ../../login/login.php");
  exit();
}

$studentId =  $user['related_id'];
$table = 'students';
include_once '../../db_connection.php';

// Fetch admin data using the related ID
$stmt = $conn->prepare("SELECT name, national_id, birth_date, gender, address, current_grade,parent_id FROM students WHERE id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
$studentData = $result->fetch_assoc();

}
?>
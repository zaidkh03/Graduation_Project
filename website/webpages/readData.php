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


    //check if the result has any rows
    if (mysqli_num_rows($result) > 0) {

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
    // If the result has no rows, print a message indicating that no data was found
    else {
        echo "<tr><td colspan='6' class='text-center'>No data found.</td></tr>";
    }
}

function select_Data($table, $value, $id_value)
{

    // Include the database connection file
    include 'db_connection.php';

    // Prepare the SQL query to select all data from the specified table
    $sql = "SELECT * FROM $table WHERE id = $id_value";
    $result = $conn->query($sql);

    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    while ($row = $result->fetch_assoc()) {
        echo " <select id='filterClass' class='form-control form-control-sm'>";
        // Loop through the values array and print each value
        foreach ($id_value as $column) {
            echo "<option value='{$row[$value]}' onclick='view_class()'>{$row[$value]}</option>";
        }
        echo "</select>";
    }
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
        echo "<tr><td colspan='6' class='text-center'>No data found.</td></tr>";
    }
}

// function to call the data from other tables based on the foreign key
function calling_data($table, $id)
{

    // Include the database connection file
    include 'db_connection.php';

    // Prepare the SQL query to select all data from the specified table
    $sql = "
    SELECT $table[0].name AS name, $table[1].name AS name
    FROM $table[0]
    JOIN $table[1] ON $table[0].parent_id = $table[1].id
    WHERE $table[0].id = $id 
";
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
        echo "<tr><td colspan='6' class='text-center'>No data found.</td></tr>";
    }
}

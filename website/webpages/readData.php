<?php

function table_Data($table,$values,$href){
    // Include the database connection file
    include 'db_connection.php';

    // Prepare the SQL query to select all data from the specified table
    $sql ="SELECT * FROM $table";
    $result = $conn->query($sql);

    if (!$result) {
        die("Query failed: " . $conn->error);
    }


    while ($row = $result->fetch_assoc()) {
        echo "<tr>";

        switch ($href){

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
        switch ($href){
        case ($href[0] == "admin"):
            echo "
            <td style='text-align: center;'>
            <a href='$href[1]'>
            <button type='button' class='btn btn-sm btn-primary mr-1' title='Edit'>
                <ion-icon name='create-outline'></ion-icon>
            </button>
            </a>
             <a href='$href[2]'>
            <button type='button' class='btn btn-sm btn-danger' title='Delete'>
                <ion-icon name='trash-outline'></ion-icon>
            </button>
            </td>
            </a>";
            break;
        case ($href[0] == "teacherattendance"):
            echo "<td class='text-center'>
                            <input type='checkbox' class='absent-checkbox' />
                          </td>";
            break;
            case ($href[0] == "parent"):
                echo"<td>
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



        }
             
        echo "</tr>";
    }
}

function select_Data($table,$value,$id_value){
    
    // Include the database connection file
    include 'db_connection.php';

    // Prepare the SQL query to select all data from the specified table
    $sql ="SELECT * FROM $table WHERE id = $id_value";
    $result = $conn->query($sql);

    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    while ($row = $result->fetch_assoc()) {
        echo " <select id='filterClass' class='form-control form-control-sm'>";
       // Loop through the values array and print each value
       foreach ($id_value as $column) {
            echo "<option value='{$row[$value]}' onclick='view_class()'>{$row[$value]}</option>";
            echo "<script> consol.log (<option value='{$row[$value]}' onclick='view_class()'>{$row[$value]}</option>) </script>";

       }
    echo "</select>";

}
}

?>
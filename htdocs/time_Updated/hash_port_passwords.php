<?php

include('./dbc.php');

$result = mysqli_query($connect, "SELECT id, password FROM port_logins");
while ($row = mysqli_fetch_assoc($result)) {
    // If not hashed (doesn't start with $2y$)
    if (strpos($row['password'], '$2y$') !== 0) {
        $hashed = password_hash($row['password'], PASSWORD_DEFAULT);
        $id = $row['id'];
        mysqli_query($connect, "UPDATE port_logins SET password='$hashed' WHERE id=$id");
    }
}
echo "Passwords updated!";
?>
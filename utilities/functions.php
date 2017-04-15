<?php
include_once("config.php");

$db = @mysqli_connect(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB);
if (mysqli_connect_error()) {
    // In production, log this to a file rather than the screen.
    echo "DB Connection Error: " . mysqli_connect_error() . "\n";
    exit();
}


function dberr($exit = 0) {
    global $db;
    echo "DB error: " . mysqli_error($db) . "\n";
    if ($exit > 0) {
        exit($exit);
    }
}

function dblock() {
    global $db;
    if (mysqli_query($db, "START TRANSACTION;")) {
        return 1;
    } else {
        dberr(1);
    }
}

function dbcommit() {
    global $db;
    if (mysqli_query($db, "COMMIT;")) {
        return 1;
    } else {
        dberr(1);
    }
}

function dbrollback() {
    global $db;
    if (mysqli_query($db, "ROLLBACK;")) {
        return 1;
    } else {
        dberr(1);
    }
}

// This isn't really necessary, since SQL has LAST_INSERT_ID(), but hey.
function newthing($type, $barcode) {
    global $db;
    // Type can be: 'Person', 'Object', 'Location', 'Department'
    // Barcode must be globally unique
    // Returns newly-created thing's ID or 0 if error
    
    $sql = "INSERT INTO `Things` SET `Type` = '". $type . "', `Barcode` = '" . mysqli_real_escape_string($db, $barcode) . "';";
    
    if (mysqli_query($db, $sql)) {
        return mysqli_insert_id($db);
    } else {
        return 0;
    }
}


// Set up new departments with no owner; update them later.
function newdepartment($name) {
    global $db;
    $name = trim($name);

    dblock();
    if ($id = newthing('Department', 'DEPT:' . $name)) {
        $sql = "INSERT INTO `Department` SET `ID`='" . $id . "', `Name`='" . mysqli_real_escape_string($db, $name) . "';";
        if (mysqli_query($db, $sql)) {
            dbcommit();
            return 1;
        } else {
            dberr();
            dbrollback();
            exit();
        }
    } else {
        dberr();
        dbrollback();
        exit();
    }
}

// Departments should have been set up before users, so allow department assignment at creation.
function newperson($netid, $firstname, $lastname, $email, $department, $status, $pin) {
    global $db;
    foreach (array($netid, $firstname, $lastname, $email, $department, $status, $pin) as &$value) {
        $value = trim($value);
    }
    unset($value);
    if ($department == '') {
        $department = 'No Department';
    }
    
    dblock();
    if ($id = newthing('Person', $netid)) {
        $sql = "INSERT INTO `Person` SET `ID`='" . $id .
            "', `FirstName`='" . mysqli_real_escape_string($db, $firstname) .
            "', `LastName`='" . mysqli_real_escape_string($db, $lastname) .
            "', `Email`='" . mysqli_real_escape_string($db, $email) .
            "', `Status`='" . mysqli_real_escape_string($db, $status) .
            "', `PIN`='" . mysqli_real_escape_string($db, $pin) .
            "', `Department_ID`=(SELECT `ID` FROM Departments WHERE `Name`='" . mysqli_real_escape_string($db, $department) .
            "');";
        if (mysqli_query($db, $sql)) {
            dbcommit();
            return 1;
        } else {
            dberr();
            dbrollback();
            exit();
        }
    } else {
        dberr();
        dbrollback();
        exit();
    }
}    

// newdepartment("Burke IT");
// newperson("jdlarios", "Josh", "Larios", "jdlarios@uw.edu", "Burke IT", "Active", "123456");

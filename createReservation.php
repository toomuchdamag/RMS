<?php
session_start();
include '../inc/dashHeader.php';
require_once '../config.php';
require_once 'validations/ReservationValidator.php';

// Handle form submission with validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = ReservationValidator::validateAll($_POST);
    
    if (empty($errors)) {
        // Proceed with reservation (this will be handled by insertReservation.php)
        header("Location: insertReservation.php");
        exit();
    } else {
        $_SESSION['validation_errors'] = $errors;
        // Preserve form inputs in session
        $_SESSION['form_data'] = $_POST;
        header("Location: createReservation.php");
        exit();
    }
}

// Retrieve any saved form data or errors
$formData = $_SESSION['form_data'] ?? [];
$validationErrors = $_SESSION['validation_errors'] ?? [];
unset($_SESSION['form_data'], $_SESSION['validation_errors']);

$reservationStatus = $_GET['reservation'] ?? null;
$message = '';
if ($reservationStatus === 'success') {
    $message = "Reservation successful";
}
$head_count = $_GET['head_count'] ?? 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Reservation</title>    
    <style>
        .wrapper{ width: 1300px; padding-left: 200px; padding-top: 80px; }
        .error-message { color: red; font-size: 0.9em; margin-top: 5px; }
        .is-invalid { border-color: red; }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <h3>Search for Available Time</h3>
        
        <div id="Search Table">
            <form id="search-form" method="GET" action="availability.php" class="ht-600 w-50">
                <div class="form-group">
                    <label for="reservation_date">Select Date</label><br>
                    <input class="form-control" type="date" id="reservation_date" name="reservation_date" required><br>
                </div>
                
                <div class="form-group"> 
                    <label for="reservation_time">Available Reservation Times</label>
                    <div id="availability-table">
                        <?php
                        $availableTimes = array();
                        for ($hour = 10; $hour <= 20; $hour++) {
                            for ($minute = 0; $minute < 60; $minute += 60) {
                                $time = sprintf('%02d:%02d:00', $hour, $minute);
                                $availableTimes[] = $time;
                            }
                        }
                        echo '<select name="reservation_time" id="reservation_time" class="form-control" required>';
                        echo '<option value="" selected disabled>Select a Time</option>';
                        foreach ($availableTimes as $time) {
                            echo "<option value='$time'>$time</option>";
                        }
                        echo '</select>';
                        if (isset($_GET['message'])) {
                            echo "<p class='error-message'>" . htmlspecialchars($_GET['message']) . "</p>";
                        }
                        ?>
                    </div>
                </div>
                <input type="number" id="head_count" name="head_count" value=1 hidden required>
                
                <div class="form-group mt-2">
                    <input type="submit" name="submit" class="btn btn-dark" value="Search Available">
                </div> 
            </form>
        </div>

        <!-- Reservation Form -->
        <div id="insert-reservation-into-table"><br>
            <h3>Make the Reservation</h3>
            
            <?php if (!empty($validationErrors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($validationErrors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form id="reservation-form" method="POST" action="createReservation.php" class="ht-600 w-50">
                <div class="form-group">
                    <label for="customer_name">Customer Name</label><br>
                    <input type="text" id="customer_name" name="customer_name" 
                           class="form-control <?= isset($validationErrors['customer_name']) ? 'is-invalid' : '' ?>" 
                           value="<?= htmlspecialchars($formData['customer_name'] ?? '') ?>" 
                           required placeholder="Johnny Hatsoff">
                    <?php if (isset($validationErrors['customer_name'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validationErrors['customer_name']) ?></div>
                    <?php endif; ?>
                    <br>
                </div>
                
                <?php
                $defaultReservationDate = $_GET['reservation_date'] ?? date("Y-m-d");
                $defaultReservationTime = $_GET['reservation_time'] ?? "13:00:00";
                ?>
                
                <div class="form-group">
                    <label for="reservation_date">Reservation Date</label><br>
                    <input type="date" id="reservation_date" name="reservation_date"
                           class="form-control <?= isset($validationErrors['reservation_date']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($formData['reservation_date'] ?? $defaultReservationDate) ?>" 
                           readonly required>
                    <?php if (isset($validationErrors['reservation_date'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validationErrors['reservation_date']) ?></div>
                    <?php endif; ?>
                    
                    <input type="time" id="reservation_time" name="reservation_time"
                           class="form-control <?= isset($validationErrors['reservation_time']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($formData['reservation_time'] ?? $defaultReservationTime) ?>" 
                           readonly required>
                    <?php if (isset($validationErrors['reservation_time'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validationErrors['reservation_time']) ?></div>
                    <?php endif; ?>
                </div>
                <br>
                
                <div class="form-group">
                    <label for="table_id_reserve">Pick a Table</label>
                    <select class="form-control <?= isset($validationErrors['table_id']) ? 'is-invalid' : '' ?>" 
                            name="table_id" id="table_id_reserve" required>
                        <option value="" selected disabled>Select a Table</option>
                        <?php
                        $table_id_list = $_GET['reserved_table_id'] ?? '';
                        $head_count = $_GET['head_count'] ?? 1;
                        $reserved_table_ids = explode(',', $table_id_list);
                        $select_query_tables = "SELECT * FROM restaurant_tables WHERE capacity >= '$head_count'";
                        
                        if (!empty($reserved_table_ids)) {
                            $reserved_table_ids_string = implode(',', $reserved_table_ids);
                            $select_query_tables .= " AND table_id NOT IN ($reserved_table_ids_string)";
                        }
                        
                        $result_tables = mysqli_query($link, $select_query_tables);
                        $resultCheckTables = mysqli_num_rows($result_tables);
                        
                        if ($resultCheckTables > 0) {
                            while ($row = mysqli_fetch_assoc($result_tables)) {
                                $selected = ($formData['table_id'] ?? '') == $row['table_id'] ? 'selected' : '';
                                echo '<option value="' . $row['table_id'] . '" ' . $selected . '>For ' . 
                                     $row['capacity'] . ' people. (Table Id: ' . $row['table_id'] . ')</option>';
                            }
                        } else {
                            echo '<option disabled>No tables available, please choose another time.</option>';
                        }
                        ?>
                    </select>
                    <?php if (isset($validationErrors['table_id'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validationErrors['table_id']) ?></div>
                    <?php endif; ?>
                    <input type="number" id="head_count" name="head_count" 
                           value="<?= htmlspecialchars($formData['head_count'] ?? $head_count) ?>" required hidden>
                </div>
                <br>
                
                <div class="form-group">
                    <label for="special_request">Special request:</label><br>
                    <input type="text" id="special_request" name="special_request" 
                           class="ht-600 w-50 form-control <?= isset($validationErrors['special_request']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($formData['special_request'] ?? '') ?>" 
                           placeholder="One baby chair">
                    <?php if (isset($validationErrors['special_request'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validationErrors['special_request']) ?></div>
                    <?php endif; ?>
                    <br>
                </div>
                
                <div class="form-group mt-2">
                    <input type="submit" name="submit" class="btn btn-dark" value="Make Reservation">
                </div>                        
            </form>
        </div>
    </div>

    <script>
        const viewDateInput = document.getElementById("reservation_date");
        const makeDateInput = document.getElementById("reservation_date");

        viewDateInput.addEventListener("change", function () {
            makeDateInput.value = this.value;
        });
    </script>
</body>
</html>
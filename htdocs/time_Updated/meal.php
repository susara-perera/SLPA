<?php
session_start();
ob_start(); 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); 
    exit();
}

include('./dbc.php');
include('includes/header2.php');
include('includes/navbar.php');
include('includes/check_access.php');

// Set timezone for Sri Lanka
date_default_timezone_set('Asia/Colombo');

// Define the page name
$page = 'meal.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    ob_end_flush(); // Flush the output buffer
    exit();
}

// Initialize variables
$divisions = [];
$selectedDivision = '';
$selectedDate = date('Y-m-d');
$message = '';
$messageType = '';

// Fetch divisions from database
$divisionSql = "SELECT division_id, division_name FROM divisions ORDER BY division_name";
$divisionResult = mysqli_query($connect, $divisionSql);
if ($divisionResult) {
    while ($row = mysqli_fetch_assoc($divisionResult)) {
        $divisions[] = $row;
    }
} else {
    // If divisions table doesn't exist, show a helpful message
    $message = "Database setup in progress. Divisions table is being created.";
    $messageType = "info";
}

// Handle form submission for meal booking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'book_meal') {
            $division_id = mysqli_real_escape_string($connect, $_POST['division_id']);
            $meal_date = mysqli_real_escape_string($connect, $_POST['meal_date']);
            $meal_type = mysqli_real_escape_string($connect, $_POST['meal_type']);
            $quantity = intval($_POST['quantity']);
            $special_requirements = mysqli_real_escape_string($connect, $_POST['special_requirements']);
            
            // Check if meal booking already exists
            $checkSql = "SELECT * FROM meal_bookings WHERE division_id = ? AND meal_date = ? AND meal_type = ?";
            $checkStmt = mysqli_prepare($connect, $checkSql);
            mysqli_stmt_bind_param($checkStmt, "iss", $division_id, $meal_date, $meal_type);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            
            if (mysqli_num_rows($checkResult) > 0) {
                // Update existing booking
                $updateSql = "UPDATE meal_bookings SET quantity = ?, special_requirements = ?, updated_at = NOW() WHERE division_id = ? AND meal_date = ? AND meal_type = ?";
                $updateStmt = mysqli_prepare($connect, $updateSql);
                mysqli_stmt_bind_param($updateStmt, "isiss", $quantity, $special_requirements, $division_id, $meal_date, $meal_type);
                
                if (mysqli_stmt_execute($updateStmt)) {
                    $message = "Meal booking updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error updating meal booking: " . mysqli_error($connect);
                    $messageType = "danger";
                }
            } else {
                // Insert new booking
                $insertSql = "INSERT INTO meal_bookings (division_id, meal_date, meal_type, quantity, special_requirements, booked_by, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                $insertStmt = mysqli_prepare($connect, $insertSql);
                mysqli_stmt_bind_param($insertStmt, "issisi", $division_id, $meal_date, $meal_type, $quantity, $special_requirements, $_SESSION['user_id']);
                
                if (mysqli_stmt_execute($insertStmt)) {
                    $message = "Meal booking created successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error creating meal booking: " . mysqli_error($connect);
                    $messageType = "danger";
                }
            }
        }
    }
}

// Handle report generation
$reportData = [];
if (isset($_POST['generate_report'])) {
    $selectedDivision = $_POST['division_id'];
    $selectedDate = $_POST['report_date'];
    
    if (!empty($selectedDivision) && !empty($selectedDate)) {
        // First check if users table exists and what columns it has
        $usersTableCheck = mysqli_query($connect, "SHOW TABLES LIKE 'users'");
        $userJoinClause = "";
        $userSelectClause = "NULL as booked_by_name";
        
        if (mysqli_num_rows($usersTableCheck) > 0) {
            // Check what columns exist in users table
            $usersColumnsCheck = mysqli_query($connect, "SHOW COLUMNS FROM users");
            $usernameColumn = null;
            
            while ($col = mysqli_fetch_assoc($usersColumnsCheck)) {
                if (in_array($col['Field'], ['username', 'user_name', 'name', 'full_name', 'email'])) {
                    $usernameColumn = $col['Field'];
                    break;
                }
            }
            
            if ($usernameColumn) {
                $userJoinClause = "LEFT JOIN users u ON mb.booked_by = u.user_id";
                $userSelectClause = "u.$usernameColumn as booked_by_name";
            }
        }
        
        $reportSql = "SELECT mb.*, d.division_name, $userSelectClause 
                     FROM meal_bookings mb 
                     JOIN divisions d ON mb.division_id = d.division_id 
                     $userJoinClause 
                     WHERE mb.division_id = ? AND mb.meal_date = ? 
                     ORDER BY mb.meal_type";
        $reportStmt = mysqli_prepare($connect, $reportSql);
        mysqli_stmt_bind_param($reportStmt, "is", $selectedDivision, $selectedDate);
        mysqli_stmt_execute($reportStmt);
        $reportResult = mysqli_stmt_get_result($reportStmt);
        
        while ($row = mysqli_fetch_assoc($reportResult)) {
            $reportData[] = $row;
        }
    }
}

// Create meal_bookings table if it doesn't exist
// First, check if divisions table exists and get its structure
$divisionCheckSql = "SHOW TABLES LIKE 'divisions'";
$divisionCheckResult = mysqli_query($connect, $divisionCheckSql);

if (mysqli_num_rows($divisionCheckResult) > 0) {
    // Check the structure of divisions table
    $divisionStructureSql = "DESCRIBE divisions";
    $divisionStructureResult = mysqli_query($connect, $divisionStructureSql);
    $divisionIdExists = false;
    
    while ($row = mysqli_fetch_assoc($divisionStructureResult)) {
        if ($row['Field'] == 'division_id') {
            $divisionIdExists = true;
            break;
        }
    }
    
    if ($divisionIdExists) {
        // Create table with foreign key constraint
        $createTableSql = "CREATE TABLE IF NOT EXISTS meal_bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            division_id INT NOT NULL,
            meal_date DATE NOT NULL,
            meal_type ENUM('breakfast', 'lunch', 'dinner', 'snack') NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            special_requirements TEXT,
            booked_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
            INDEX idx_division_id (division_id),
            INDEX idx_meal_date (meal_date),
            FOREIGN KEY (division_id) REFERENCES divisions(division_id) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB";
    } else {
        // Create table without foreign key constraint if division_id doesn't exist
        $createTableSql = "CREATE TABLE IF NOT EXISTS meal_bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            division_id INT NOT NULL,
            meal_date DATE NOT NULL,
            meal_type ENUM('breakfast', 'lunch', 'dinner', 'snack') NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            special_requirements TEXT,
            booked_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
            INDEX idx_division_id (division_id),
            INDEX idx_meal_date (meal_date)
        ) ENGINE=InnoDB";
    }
} else {
    // Create divisions table first, then meal_bookings
    $createDivisionsSql = "CREATE TABLE IF NOT EXISTS divisions (
        division_id INT AUTO_INCREMENT PRIMARY KEY,
        division_name VARCHAR(255) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        status ENUM('active', 'inactive') DEFAULT 'active'
    ) ENGINE=InnoDB";
    
    mysqli_query($connect, $createDivisionsSql);
    
    // Insert some default divisions
    $insertDefaultDivisions = "INSERT IGNORE INTO divisions (division_name, description) VALUES 
        ('Information System', 'IT and Information Systems Division'),
        ('Administration', 'Administrative Division'),
        ('Operations', 'Operational Division'),
        ('Finance', 'Finance and Accounting Division'),
        ('Human Resources', 'HR Division')";
    mysqli_query($connect, $insertDefaultDivisions);
    
    // Now create meal_bookings table
    $createTableSql = "CREATE TABLE IF NOT EXISTS meal_bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        division_id INT NOT NULL,
        meal_date DATE NOT NULL,
        meal_type ENUM('breakfast', 'lunch', 'dinner', 'snack') NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        special_requirements TEXT,
        booked_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
        INDEX idx_division_id (division_id),
        INDEX idx_meal_date (meal_date),
        FOREIGN KEY (division_id) REFERENCES divisions(division_id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB";
}

// Execute the table creation
$tableCreationResult = mysqli_query($connect, $createTableSql);

if (!$tableCreationResult) {
    // If foreign key constraint fails, create table without it
    $fallbackTableSql = "CREATE TABLE IF NOT EXISTS meal_bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        division_id INT NOT NULL,
        meal_date DATE NOT NULL,
        meal_type ENUM('breakfast', 'lunch', 'dinner', 'snack') NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        special_requirements TEXT,
        booked_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
        INDEX idx_division_id (division_id),
        INDEX idx_meal_date (meal_date)
    ) ENGINE=InnoDB";
    
    mysqli_query($connect, $fallbackTableSql);
}
?>

<style>
/* Professional Meal Management Styling */
.meal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px 0;
    margin-bottom: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.meal-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    border: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 40px;
}

.meal-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.meal-card-header {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    color: white;
    border-radius: 15px 15px 0 0;
    padding: 20px;
    border: none;
}

.meal-form-group {
    margin-bottom: 25px;
}

.meal-form-label {
    font-weight: 600;
    color: #2d3436;
    margin-bottom: 10px;
    display: block;
}

.meal-form-control {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 15px 18px;
    transition: all 0.3s ease;
    font-size: 14px;
    margin-bottom: 5px;
}

.meal-form-control:focus {
    border-color: #74b9ff;
    box-shadow: 0 0 0 0.2rem rgba(116, 185, 255, 0.25);
    outline: none;
}

.meal-btn {
    padding: 15px 35px;
    border-radius: 25px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    border: none;
    margin-top: 10px;
}

.meal-btn-primary {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    color: white;
}

.meal-btn-primary:hover {
    background: linear-gradient(135deg, #0984e3 0%, #2d3436 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}

.meal-btn-success {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
}

.meal-btn-success:hover {
    background: linear-gradient(135deg, #00a085 0%, #2d3436 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}

.meal-stats-card {
    background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
    color: white;
    border-radius: 15px;
    padding: 30px;
    text-align: center;
    margin-bottom: 25px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
}

.meal-stats-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.meal-stats-label {
    font-size: 1rem;
    opacity: 0.9;
}

.meal-table {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    border: none;
}

.meal-table thead {
    background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
    color: white;
}

.meal-table th {
    border: none;
    padding: 18px 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 12px;
}

.meal-table td {
    border: none;
    padding: 18px 15px;
    border-bottom: 1px solid #f1f3f4;
    vertical-align: middle;
}

.meal-badge {
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.meal-badge-breakfast {
    background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
    color: white;
}

.meal-badge-lunch {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    color: white;
}

.meal-badge-dinner {
    background: linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%);
    color: white;
}

.meal-badge-snack {
    background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
    color: white;
}

.meal-badge-pending {
    background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
    color: white;
}

.meal-badge-confirmed {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
}

.meal-badge-cancelled {
    background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
    color: white;
}

.alert-professional {
    border: none;
    border-radius: 10px;
    padding: 20px 25px;
    margin-bottom: 25px;
    font-weight: 500;
}

@media print {
    .meal-form-section, .meal-btn, .alert-professional {
        display: none !important;
    }
    
    .meal-card, .meal-table {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    
    .meal-header {
        background: #333 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}

/* Additional spacing improvements */
.container-fluid {
    padding-left: 30px;
    padding-right: 30px;
    padding-top: 20px;
    padding-bottom: 40px;
}

.row {
    margin-left: -20px;
    margin-right: -20px;
}

.row > [class*='col-'] {
    padding-left: 20px;
    padding-right: 20px;
}

.card-body {
    padding: 30px !important;
}

.meal-card-header {
    padding: 25px 30px !important;
}
</style>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="meal-header text-center">
        <h1 class="display-4 mb-3">🍽️ Meal Management System</h1>
        <p class="lead mb-2">Professional Meal Booking & Reporting for SLPA</p>
        <small class="text-light">Manage meal bookings, track consumption, and generate comprehensive reports</small>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-professional alert-dismissible fade show">
            <strong><?= $messageType == 'success' ? 'Success!' : ($messageType == 'info' ? 'Info!' : 'Error!') ?></strong> <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Database Status Indicator -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="alert alert-info alert-professional">
                <h6><i class="fas fa-database"></i> Database Status</h6>
                <div class="row">
                    <div class="col-md-3">
                        <small>
                            <strong>Divisions:</strong> 
                            <?php 
                            $divCount = count($divisions);
                            echo $divCount > 0 ? 
                                "<span class='badge bg-success'>$divCount found</span>" : 
                                "<span class='badge bg-warning'>0 found</span>";
                            ?>
                        </small>
                    </div>
                    <div class="col-md-3">
                        <small>
                            <strong>Meal Bookings:</strong>
                            <?php
                            $mealTableCheck = mysqli_query($connect, "SHOW TABLES LIKE 'meal_bookings'");
                            if (mysqli_num_rows($mealTableCheck) > 0) {
                                $mealCount = mysqli_query($connect, "SELECT COUNT(*) as count FROM meal_bookings");
                                $count = mysqli_fetch_assoc($mealCount)['count'];
                                echo "<span class='badge bg-success'>Table ready ($count records)</span>";
                            } else {
                                echo "<span class='badge bg-warning'>Table not found</span>";
                            }
                            ?>
                        </small>
                    </div>
                    <div class="col-md-3">
                        <small>
                            <strong>Connection:</strong>
                            <span class='badge bg-success'>Connected</span>
                        </small>
                    </div>
                    <div class="col-md-3">
                        <small>
                            <strong>Database:</strong>
                            <span class='badge bg-success'>slpa_db</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Meal Booking Form -->
        <div class="col-lg-4 meal-form-section">
            <div class="meal-card">
                <div class="meal-card-header">
                    <h4 class="mb-0">📋 Book New Meal</h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="book_meal">
                        
                        <div class="meal-form-group">
                            <label class="meal-form-label" for="division_id">Division</label>
                            <select name="division_id" id="division_id" class="form-select meal-form-control" required>
                                <option value="">Select Division</option>
                                <?php foreach ($divisions as $division): ?>
                                    <option value="<?= $division['division_id'] ?>"><?= htmlspecialchars($division['division_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="meal-form-group">
                            <label class="meal-form-label" for="meal_date">Meal Date</label>
                            <input type="date" class="form-control meal-form-control" id="meal_date" name="meal_date" 
                                   value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="meal-form-group">
                            <label class="meal-form-label" for="meal_type">Meal Type</label>
                            <select name="meal_type" id="meal_type" class="form-select meal-form-control" required>
                                <option value="">Select Meal Type</option>
                                <option value="breakfast">🌅 Breakfast</option>
                                <option value="lunch">☀️ Lunch</option>
                                <option value="dinner">🌙 Dinner</option>
                                <option value="snack">🍪 Snack</option>
                            </select>
                        </div>

                        <div class="meal-form-group">
                            <label class="meal-form-label" for="quantity">Expected Quantity</label>
                            <input type="number" class="form-control meal-form-control" id="quantity" name="quantity" 
                                   min="1" max="500" value="1" required>
                        </div>

                        <div class="meal-form-group">
                            <label class="meal-form-label" for="special_requirements">Special Requirements</label>
                            <textarea class="form-control meal-form-control" id="special_requirements" name="special_requirements" 
                                      rows="3" placeholder="Any dietary restrictions, allergies, or special instructions..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn meal-btn meal-btn-primary w-100">
                            <i class="fas fa-utensils"></i> Book Meal
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="meal-stats-card">
                <div class="meal-stats-number">
                    <?php 
                    try {
                        // Check if meal_bookings table exists first
                        $tableCheck = mysqli_query($connect, "SHOW TABLES LIKE 'meal_bookings'");
                        if (mysqli_num_rows($tableCheck) > 0) {
                            $todayMeals = mysqli_query($connect, "SELECT COUNT(*) as count FROM meal_bookings WHERE meal_date = '" . date('Y-m-d') . "'");
                            if ($todayMeals) {
                                $todayCount = mysqli_fetch_assoc($todayMeals)['count'];
                                echo $todayCount;
                            } else {
                                echo "0";
                            }
                        } else {
                            echo "0";
                        }
                    } catch (Exception $e) {
                        echo "0";
                    }
                    ?>
                </div>
                <div class="meal-stats-label">Today's Bookings</div>
            </div>
        </div>

        <!-- Report Section -->
        <div class="col-lg-8">
            <div class="meal-card">
                <div class="meal-card-header">
                    <h4 class="mb-0">📊 Meal Reports & Analytics</h4>
                </div>
                <div class="card-body p-4">
                    <!-- Report Generation Form -->
                    <form method="POST" action="" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="meal-form-label" for="report_division_id">Division</label>
                                <select name="division_id" id="report_division_id" class="form-select meal-form-control">
                                    <option value="">All Divisions</option>
                                    <?php foreach ($divisions as $division): ?>
                                        <option value="<?= $division['division_id'] ?>" 
                                                <?= $selectedDivision == $division['division_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($division['division_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="meal-form-label" for="report_date">Report Date</label>
                                <input type="date" class="form-control meal-form-control" id="report_date" 
                                       name="report_date" value="<?= $selectedDate ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="meal-form-label" style="opacity: 0;">Generate</label>
                                <button type="submit" name="generate_report" class="btn meal-btn meal-btn-success w-100">
                                    <i class="fas fa-chart-bar"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Report Results -->
                    <?php if (!empty($reportData)): ?>
                        <div class="table-responsive">
                            <table class="table meal-table">
                                <thead>
                                    <tr>
                                        <th>Division</th>
                                        <th>Meal Type</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                        <th>Special Requirements</th>
                                        <th>Booked By</th>
                                        <th>Booking Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reportData as $row): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($row['division_name']) ?></strong></td>
                                            <td>
                                                <span class="meal-badge meal-badge-<?= $row['meal_type'] ?>">
                                                    <?= ucfirst($row['meal_type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary rounded-pill"><?= $row['quantity'] ?></span>
                                            </td>
                                            <td>
                                                <span class="meal-badge meal-badge-<?= $row['status'] ?>">
                                                    <?= ucfirst($row['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= !empty($row['special_requirements']) ? 
                                                    '<small class="text-muted">' . htmlspecialchars(substr($row['special_requirements'], 0, 50)) . 
                                                    (strlen($row['special_requirements']) > 50 ? '...' : '') . '</small>' : 
                                                    '<span class="text-muted">None</span>' ?>
                                            </td>
                                            <td><small class="text-muted"><?= htmlspecialchars($row['booked_by_name'] ?? 'System') ?></small></td>
                                            <td><small class="text-muted"><?= date('M j, g:i A', strtotime($row['created_at'])) ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Statistics -->
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="meal-stats-card" style="background: linear-gradient(135deg, #00b894 0%, #00a085 100%);">
                                    <div class="meal-stats-number"><?= count($reportData) ?></div>
                                    <div class="meal-stats-label">Total Bookings</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="meal-stats-card" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                                    <div class="meal-stats-number"><?= array_sum(array_column($reportData, 'quantity')) ?></div>
                                    <div class="meal-stats-label">Total Quantity</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="meal-stats-card" style="background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);">
                                    <div class="meal-stats-number"><?= count(array_unique(array_column($reportData, 'division_name'))) ?></div>
                                    <div class="meal-stats-label">Divisions</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="meal-stats-card" style="background: linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%);">
                                    <div class="meal-stats-number"><?= count(array_unique(array_column($reportData, 'meal_type'))) ?></div>
                                    <div class="meal-stats-label">Meal Types</div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button onclick="window.print()" class="btn meal-btn meal-btn-primary">
                                <i class="fas fa-print"></i> Print Report
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No meal bookings found</h5>
                            <p class="text-muted">Select a division and date to generate a report</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation and enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Meal type icons and descriptions
    const mealTypeSelect = document.getElementById('meal_type');
    if (mealTypeSelect) {
        mealTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                selectedOption.style.fontWeight = 'bold';
            }
        });
    }

    // Auto-update quantity based on division selection
    const divisionSelect = document.getElementById('division_id');
    const quantityInput = document.getElementById('quantity');
    
    if (divisionSelect && quantityInput) {
        divisionSelect.addEventListener('change', function() {
            // You can add logic here to suggest quantity based on division size
            if (this.value) {
                quantityInput.focus();
            }
        });
    }

    // Date validation
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today && this.id === 'meal_date') {
                alert('Cannot book meals for past dates!');
                this.value = new Date().toISOString().split('T')[0];
            }
        });
    });
});
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');
ob_end_flush(); 
?>

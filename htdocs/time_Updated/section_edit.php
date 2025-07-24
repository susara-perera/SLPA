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

// Define the page name
$page = 'section_Manage.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    ob_end_flush(); 
    exit();
}

$section_id = isset($_GET['section_id']) ? $_GET['section_id'] : null;
$success_message = '';
$error_message = '';

if (!$section_id) {
    $error_message = "Invalid section ID.";
} else {
    // Fetch section details
    $sectionSql = "SELECT s.section_id, s.section_name, s.division_id, d.division_name 
                   FROM sections s 
                   JOIN divisions d ON s.division_id = d.division_id 
                   WHERE s.section_id = ?";
    $stmt = mysqli_prepare($connect, $sectionSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $section_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $section = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$section) {
            $error_message = "Section not found.";
        }
    } else {
        $error_message = "SQL error: " . mysqli_error($connect);
    }

    // Fetch all divisions for the dropdown
    $divisions = [];
    $divisionSql = "SELECT division_id, division_name FROM divisions ORDER BY division_name";
    $result = mysqli_query($connect, $divisionSql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $divisions[] = $row;
        }
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_section_name = $_POST['section_name'];
        $new_division_id = $_POST['division_id'];

        if (!empty($new_section_name) && !empty($new_division_id)) {
            // Check if section name already exists in the selected division (excluding current section)
            $checkSql = "SELECT COUNT(*) AS count FROM sections WHERE section_name = ? AND division_id = ? AND section_id != ?";
            $stmt = mysqli_prepare($connect, $checkSql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sii", $new_section_name, $new_division_id, $section_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($result);

                if ($row['count'] > 0) {
                    $error_message = "Section name already exists in this division.";
                } else {
                    // Update the section
                    $updateSql = "UPDATE sections SET section_name = ?, division_id = ? WHERE section_id = ?";
                    $stmt = mysqli_prepare($connect, $updateSql);
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, "sii", $new_section_name, $new_division_id, $section_id);
                        if (mysqli_stmt_execute($stmt)) {
                            $success_message = "Section updated successfully.";
                            // Update the local section data
                            $section['section_name'] = $new_section_name;
                            $section['division_id'] = $new_division_id;
                            // Update division name
                            foreach ($divisions as $div) {
                                if ($div['division_id'] == $new_division_id) {
                                    $section['division_name'] = $div['division_name'];
                                    break;
                                }
                            }
                        } else {
                            $error_message = "Failed to update section: " . mysqli_stmt_error($stmt);
                        }
                        mysqli_stmt_close($stmt);
                    } else {
                        $error_message = "SQL error: " . mysqli_error($connect);
                    }
                }
            } else {
                $error_message = "SQL error: " . mysqli_error($connect);
            }
        } else {
            $error_message = "All fields are required.";
        }
    }
}
?>

<style>
.card-custom {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: none;
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.card-body-custom {
    background: white;
    border-radius: 0 0 15px 15px;
    color: #333;
}

.form-control-custom {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    padding: 12px 15px;
    transition: all 0.3s ease;
}

.form-control-custom:focus {
    border-color: #f39c12;
    box-shadow: 0 0 0 0.2rem rgba(243, 156, 18, 0.25);
}

.btn-custom {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    border: none;
    border-radius: 10px;
    padding: 12px 30px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    color: white;
}

.btn-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(243, 156, 18, 0.3);
    color: white;
}

.btn-outline-custom {
    border: 2px solid #f39c12;
    color: #f39c12;
    background: transparent;
    border-radius: 10px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-custom:hover {
    background: #f39c12;
    color: white;
    transform: translateY(-2px);
}

.btn-secondary-custom {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    border: none;
    border-radius: 10px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    color: white;
}

.btn-secondary-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
    color: white;
}

.alert-custom {
    border-radius: 10px;
    border: none;
    padding: 15px 20px;
    margin-bottom: 20px;
    font-weight: 500;
}

.alert-success-custom {
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}

.alert-danger-custom {
    background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);
}

.icon-wrapper {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 24px;
}

.success-icon {
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
}

.error-icon {
    background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
}

/* Background decorative elements */
.content-wrapper {
    position: relative;
    overflow: hidden;
}

.content-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 400px;
    height: 100%;
    background: linear-gradient(135deg, rgba(243, 156, 18, 0.05) 0%, rgba(230, 126, 34, 0.05) 100%);
    z-index: 0;
}

.decorative-elements {
    position: absolute;
    top: 0;
    right: 0;
    width: 400px;
    height: 100vh;
    pointer-events: none;
    z-index: 1;
}

.geometric-shape {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(243, 156, 18, 0.1) 0%, rgba(230, 126, 34, 0.1) 100%);
}

.shape-1 {
    width: 120px;
    height: 120px;
    top: 10%;
    right: 10%;
    animation: float 6s ease-in-out infinite;
}

.shape-2 {
    width: 80px;
    height: 80px;
    top: 30%;
    right: 25%;
    animation: float 8s ease-in-out infinite reverse;
}

.shape-3 {
    width: 60px;
    height: 60px;
    top: 60%;
    right: 15%;
    animation: float 7s ease-in-out infinite;
}

.shape-4 {
    width: 100px;
    height: 100px;
    top: 80%;
    right: 30%;
    animation: float 9s ease-in-out infinite reverse;
}

.edit-svg {
    position: absolute;
    top: 20%;
    right: 5%;
    width: 200px;
    height: 200px;
    opacity: 0.1;
    animation: pulse 4s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

@keyframes pulse {
    0%, 100% { opacity: 0.1; transform: scale(1); }
    50% { opacity: 0.2; transform: scale(1.05); }
}

.content {
    position: relative;
    z-index: 2;
}

/* Section info card */
.info-card {
    background: linear-gradient(135deg, rgba(243, 156, 18, 0.1) 0%, rgba(230, 126, 34, 0.1) 100%);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    border-left: 4px solid #f39c12;
}

/* Additional decorative lines */
.decorative-lines {
    position: absolute;
    top: 0;
    right: 0;
    width: 300px;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}

.line {
    position: absolute;
    background: linear-gradient(90deg, transparent 0%, rgba(243, 156, 18, 0.1) 50%, transparent 100%);
    height: 1px;
}

.line-1 { top: 25%; width: 150px; right: 20%; }
.line-2 { top: 45%; width: 100px; right: 15%; }
.line-3 { top: 65%; width: 120px; right: 25%; }
.line-4 { top: 85%; width: 80px; right: 30%; }
</style>

<div class="content-wrapper">
    <!-- Decorative Elements -->
    <div class="decorative-elements">
        <div class="geometric-shape shape-1"></div>
        <div class="geometric-shape shape-2"></div>
        <div class="geometric-shape shape-3"></div>
        <div class="geometric-shape shape-4"></div>
        
        <!-- Edit/Section SVG -->
        <svg class="edit-svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <!-- Edit pencil -->
            <rect x="150" y="40" width="8" height="50" rx="4" fill="currentColor" opacity="0.3" transform="rotate(45 154 65)"/>
            <polygon points="145,35 155,45 160,40 150,30" fill="currentColor" opacity="0.4"/>
            
            <!-- Section boxes being edited -->
            <rect x="60" y="80" width="80" height="15" rx="3" fill="currentColor" opacity="0.3"/>
            <rect x="40" y="110" width="60" height="12" rx="2" fill="currentColor" opacity="0.4"/>
            <rect x="110" y="110" width="60" height="12" rx="2" fill="currentColor" opacity="0.4"/>
            <rect x="30" y="135" width="50" height="10" rx="2" fill="currentColor" opacity="0.5"/>
            <rect x="90" y="135" width="50" height="10" rx="2" fill="currentColor" opacity="0.5"/>
            <rect x="150" y="135" width="50" height="10" rx="2" fill="currentColor" opacity="0.5"/>
            
            <!-- Connection lines -->
            <line x1="100" y1="95" x2="100" y2="110" stroke="currentColor" stroke-width="1" opacity="0.3"/>
            <line x1="70" y1="95" x2="70" y2="110" stroke="currentColor" stroke-width="1" opacity="0.3"/>
            <line x1="140" y1="95" x2="140" y2="110" stroke="currentColor" stroke-width="1" opacity="0.3"/>
            
            <line x1="55" y1="122" x2="55" y2="135" stroke="currentColor" stroke-width="1" opacity="0.3"/>
            <line x1="115" y1="122" x2="115" y2="135" stroke="currentColor" stroke-width="1" opacity="0.3"/>
            <line x1="175" y1="122" x2="175" y2="135" stroke="currentColor" stroke-width="1" opacity="0.3"/>
            
            <!-- Edit indicators (dashed lines) -->
            <line x1="130" y1="60" x2="120" y2="85" stroke="currentColor" stroke-width="2" stroke-dasharray="3,3" opacity="0.4"/>
        </svg>
    </div>
    
    <!-- Decorative Lines -->
    <div class="decorative-lines">
        <div class="line line-1"></div>
        <div class="line line-2"></div>
        <div class="line line-3"></div>
        <div class="line line-4"></div>
    </div>

    <section class="content" style="padding-top: 20px;">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card card-custom">
                        <div class="card-header text-center py-4">
                            <?php if (!empty($success_message)): ?>
                                <div class="icon-wrapper success-icon">
                                    <i class="fas fa-check"></i>
                                </div>
                            <?php elseif (!empty($error_message)): ?>
                                <div class="icon-wrapper error-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                            <?php else: ?>
                                <div class="icon-wrapper">
                                    <i class="fas fa-edit"></i>
                                </div>
                            <?php endif; ?>
                            <h3 class="card-title mb-0" style="color: white; font-weight: 600;">Edit Section</h3>
                            <p class="mb-0" style="color: rgba(255,255,255,0.8);">Modify section details</p>
                        </div>
                        <div class="card-body card-body-custom p-4">
                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger-custom">
                                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success-custom">
                                    <i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($section) && $section): ?>
                                <!-- Current Section Info -->
                                <div class="info-card">
                                    <h5 class="mb-2" style="color: #f39c12;">
                                        <i class="fas fa-info-circle mr-2"></i>Current Section Details
                                    </h5>
                                    <p class="mb-1"><strong>Section ID:</strong> <?php echo htmlspecialchars($section['section_id']); ?></p>
                                    <p class="mb-0"><strong>Current Division:</strong> <?php echo htmlspecialchars($section['division_name']); ?></p>
                                </div>

                                <form method="POST" action="" id="editSectionForm">
                                    <div class="form-group mb-4">
                                        <label for="division_id" class="form-label font-weight-bold">
                                            <i class="fas fa-building text-warning mr-2"></i>Division
                                        </label>
                                        <select name="division_id" id="division_id" class="form-control form-control-custom" required>
                                            <option value="">Choose a division...</option>
                                            <?php foreach ($divisions as $division): ?>
                                                <option value="<?php echo htmlspecialchars($division['division_id']); ?>" 
                                                        <?php echo ($section['division_id'] == $division['division_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($division['division_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text text-muted">Select the division for this section</small>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="section_name" class="form-label font-weight-bold">
                                            <i class="fas fa-sitemap text-warning mr-2"></i>Section Name
                                        </label>
                                        <input class="form-control form-control-custom" 
                                               name="section_name" 
                                               type="text" 
                                               id="section_name" 
                                               value="<?php echo htmlspecialchars($section['section_name']); ?>"
                                               placeholder="Enter section name"
                                               required>
                                        <small class="form-text text-muted">Enter the name for this section</small>
                                    </div>
                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-custom btn-lg px-4 mr-2">
                                            <i class="fas fa-save mr-2"></i>Update Section
                                        </button>
                                        <a href="section_Manage.php" class="btn btn-secondary-custom px-4">
                                            <i class="fas fa-times mr-2"></i>Cancel
                                        </a>
                                    </div>
                                </form>
                                
                                <?php if (!empty($success_message)): ?>
                                    <div class="text-center mt-3">
                                        <hr style="margin: 20px 0;">
                                        <a href="section_Manage.php" class="btn btn-outline-custom">
                                            <i class="fas fa-list mr-2"></i>View All Sections
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
mysqli_close($connect);
include('includes/scripts.php');
include('includes/footer.php');
ob_end_flush(); 
?>
<?php
ob_start(); // Start output buffering
include('includes/header.php');
include('includes/navbar.php');
include('dbc.php');

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $division_id = $_POST['div1'];
    $section_name = $_POST['sec'];

    if (!empty($division_id) && !empty($section_name)) {

        // Check if section name already exists in this division
        $checkSql = "SELECT COUNT(*) AS count FROM sections WHERE section_name = ? AND division_id = ?";
        $stmt = mysqli_prepare($connect, $checkSql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $section_name, $division_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);

            if ($row['count'] > 0) {
                $error_message = "Section name already exists in this division.";
            } else {
                // Insert the new section into the database
                $insertSql = "INSERT INTO sections (section_name, division_id) VALUES (?, ?)";
                $stmt = mysqli_prepare($connect, $insertSql);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ss", $section_name, $division_id);
                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        mysqli_close($connect);

                        // Redirect to the section page with a success message
                        header("Location: section.php?success=" . urlencode("New section created successfully."));
                        ob_end_flush(); 
                        exit();
                    } else {
                        $error_message = "Failed to create new section: " . mysqli_stmt_error($stmt);
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

// Fetch divisions from the database for the form
$divisions = [];
$divisionSql = "SELECT division_id, division_name FROM divisions";
$result = mysqli_query($connect, $divisionSql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $divisions[] = $row;
    }
} else {
    if (empty($error_message)) {
        $error_message = "Failed to fetch divisions: " . mysqli_error($connect);
    }
}
mysqli_close($connect);
?>

<style>
.card-custom {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: none;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.btn-custom {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
    color: white;
}

.btn-outline-custom {
    border: 2px solid #28a745;
    color: #28a745;
    background: transparent;
    border-radius: 10px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-custom:hover {
    background: #28a745;
    color: white;
    transform: translateY(-2px);
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
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.05) 0%, rgba(32, 201, 151, 0.05) 100%);
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
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(32, 201, 151, 0.1) 100%);
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

.organizational-svg {
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
    background: linear-gradient(90deg, transparent 0%, rgba(40, 167, 69, 0.1) 50%, transparent 100%);
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
        
        <!-- Section/Hierarchy SVG -->
        <svg class="organizational-svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <!-- Hierarchical sections -->
            <rect x="80" y="40" width="40" height="20" rx="3" fill="currentColor" opacity="0.3"/>
            <rect x="40" y="80" width="35" height="18" rx="3" fill="currentColor" opacity="0.4"/>
            <rect x="85" y="80" width="35" height="18" rx="3" fill="currentColor" opacity="0.4"/>
            <rect x="125" y="80" width="35" height="18" rx="3" fill="currentColor" opacity="0.4"/>
            <rect x="30" y="120" width="30" height="15" rx="2" fill="currentColor" opacity="0.5"/>
            <rect x="70" y="120" width="30" height="15" rx="2" fill="currentColor" opacity="0.5"/>
            <rect x="110" y="120" width="30" height="15" rx="2" fill="currentColor" opacity="0.5"/>
            <rect x="150" y="120" width="30" height="15" rx="2" fill="currentColor" opacity="0.5"/>
            
            <!-- Connection Lines -->
            <line x1="100" y1="60" x2="100" y2="80" stroke="currentColor" stroke-width="2" opacity="0.3"/>
            <line x1="57.5" y1="60" x2="57.5" y2="80" stroke="currentColor" stroke-width="1" opacity="0.3"/>
            <line x1="102.5" y1="60" x2="102.5" y2="80" stroke="currentColor" stroke-width="1" opacity="0.3"/>
            <line x1="142.5" y1="60" x2="142.5" y2="80" stroke="currentColor" stroke-width="1" opacity="0.3"/>
            
            <line x1="45" y1="98" x2="45" y2="120" stroke="currentColor" stroke-width="1" opacity="0.3"/>
            <line x1="85" y1="98" x2="85" y2="120" stroke="currentColor" stroke-width="1" opacity="0.3"/>
            <line x1="125" y1="98" x2="125" y2="120" stroke="currentColor" stroke-width="1" opacity="0.3"/>
            <line x1="165" y1="98" x2="165" y2="120" stroke="currentColor" stroke-width="1" opacity="0.3"/>
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
                                    <i class="fas fa-sitemap"></i>
                                </div>
                            <?php endif; ?>
                            <h3 class="card-title mb-0" style="color: white; font-weight: 600;">Add New Section</h3>
                            <p class="mb-0" style="color: rgba(255,255,255,0.8);">Create a new section under a division</p>
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
                                <div class="text-center mb-3">
                                    <a href="section_Manage.php" class="btn btn-outline-custom">
                                        <i class="fas fa-list mr-2"></i>View All Sections
                                    </a>
                                    <a href="section.php" class="btn btn-custom ml-2">
                                        <i class="fas fa-plus mr-2"></i>Add Another
                                    </a>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="" id="sectionForm">
                                <div class="form-group mb-4">
                                    <label for="div1" class="form-label font-weight-bold">
                                        <i class="fas fa-building text-success mr-2"></i>Select Division
                                    </label>
                                    <select name="div1" id="div1" class="form-control form-control-custom" required>
                                        <option value="">Choose a division...</option>
                                        <?php foreach ($divisions as $division): ?>
                                            <option value="<?php echo htmlspecialchars($division['division_id']); ?>" 
                                                    <?php echo (isset($_POST['div1']) && $_POST['div1'] == $division['division_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($division['division_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">Select the division for this section</small>
                                </div>
                                <div class="form-group mb-4">
                                    <label for="sec" class="form-label font-weight-bold">
                                        <i class="fas fa-sitemap text-success mr-2"></i>Section Name
                                    </label>
                                    <input class="form-control form-control-custom" 
                                           name="sec" 
                                           type="text" 
                                           id="sec" 
                                           value="<?php echo isset($_POST['sec']) ? htmlspecialchars($_POST['sec']) : ''; ?>"
                                           placeholder="Enter section name"
                                           required>
                                    <small class="form-text text-muted">Enter the name for the new section</small>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-custom btn-lg px-5">
                                        <i class="fas fa-save mr-2"></i>Create Section
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
include('includes/scripts.php');
include('includes/footer.php');
ob_end_flush(); 
?>
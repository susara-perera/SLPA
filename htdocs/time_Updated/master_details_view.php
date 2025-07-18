<?php
include('includes/header.php');
include('includes/navbar.php');
include('./dbc.php');

$original_employee_ID = $_GET['id'];

// Fetch employee details along with section name and division name
$employeeSql = "
    SELECT e.*, s.section_name, d.division_name 
    FROM employees e
    LEFT JOIN sections s ON e.section = s.section_id
    LEFT JOIN divisions d ON e.division = d.division_id
    WHERE e.employee_ID = ?
";
$stmt = mysqli_prepare($connect, $employeeSql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $original_employee_ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $employee = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$employee) {
        $error_message = "Employee not found with ID: " . htmlspecialchars($original_employee_ID);
    }
} else {
    $error_message = "Failed to prepare SQL statement: " . mysqli_error($connect);
}

// Calculate years of service if employee exists
$yearsOfService = 0;
$cardStatus = '';
if ($employee && $employee['appointment_date']) {
    $appointmentDate = new DateTime($employee['appointment_date']);
    $currentDate = new DateTime();
    $interval = $appointmentDate->diff($currentDate);
    $yearsOfService = $interval->y;
}

// Check card validity
if ($employee && $employee['card_valid_date']) {
    $cardValidDate = new DateTime($employee['card_valid_date']);
    $currentDate = new DateTime();
    $daysDiff = $currentDate->diff($cardValidDate)->days;
    
    if ($cardValidDate < $currentDate) {
        $cardStatus = 'expired';
    } elseif ($daysDiff <= 30) {
        $cardStatus = 'expiring';
    } else {
        $cardStatus = 'valid';
    }
}
?>

<style>
/* Professional Employee Details View Styling - Matching Master Pages */
.details-header {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
    padding: 25px 0;
    margin-bottom: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.employee-profile-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    border: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 20px;
    overflow: hidden;
}

.employee-profile-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.profile-header {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
    padding: 25px;
    text-align: center;
    position: relative;
}

.profile-photo {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 5px solid white;
    object-fit: cover;
    margin: 0 auto 15px;
    display: block;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}

.profile-photo-placeholder {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 5px solid white;
    background: linear-gradient(135deg, #636e72 0%, #2d3436 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    color: white;
    font-size: 3rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}

.employee-name {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.employee-id {
    font-size: 1.2rem;
    opacity: 0.9;
    margin-bottom: 10px;
}

.status-badge {
    padding: 8px 20px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: inline-block;
}

.status-active {
    background: rgba(255,255,255,0.2);
    color: white;
    border: 2px solid rgba(255,255,255,0.5);
}

.status-inactive {
    background: rgba(231,112,85,0.9);
    color: white;
    border: 2px solid rgba(231,112,85,0.7);
}

.info-section {
    padding: 25px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.info-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    border-left: 4px solid #00b894;
    transition: all 0.3s ease;
}

.info-card:hover {
    background: rgba(0, 184, 148, 0.1);
    transform: translateX(5px);
}

.info-card-header {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 14px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #e9ecef;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #2d3436;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-value {
    color: #636e72;
    font-weight: 500;
    text-align: right;
    max-width: 60%;
    word-wrap: break-word;
}

.form-icon {
    color: #00b894;
    width: 16px;
}

.alert-professional {
    border: none;
    border-radius: 10px;
    padding: 15px 20px;
    margin-bottom: 20px;
    font-weight: 500;
}

.container-fluid {
    padding: 20px;
}

.back-button {
    background: linear-gradient(135deg, #636e72 0%, #2d3436 100%);
    color: white;
    padding: 12px 25px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    display: inline-block;
    margin-bottom: 20px;
}

.back-button:hover {
    background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    color: white;
    text-decoration: none;
}

.quick-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.quick-stat-card {
    background: white;
    border-radius: 10px;
    padding: 15px;
    text-align: center;
    box-shadow: 0 3px 15px rgba(0,0,0,0.1);
    border-left: 4px solid #00b894;
}

.quick-stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: #00b894;
    margin-bottom: 5px;
}

.quick-stat-label {
    font-size: 12px;
    color: #636e72;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-status-indicator {
    position: absolute;
    top: 15px;
    right: 15px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.card-valid {
    background: rgba(0,184,148,0.2);
    color: white;
    border: 1px solid rgba(255,255,255,0.3);
}

.card-expiring {
    background: rgba(253,203,110,0.9);
    color: #2d3436;
    border: 1px solid rgba(253,203,110,0.7);
}

.card-expired {
    background: rgba(231,112,85,0.9);
    color: white;
    border: 1px solid rgba(231,112,85,0.7);
}

.error-state {
    text-align: center;
    padding: 60px 20px;
    color: #636e72;
}

.error-icon {
    font-size: 4rem;
    color: #e17055;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .employee-name {
        font-size: 1.5rem;
    }
    
    .employee-id {
        font-size: 1rem;
    }
    
    .profile-photo, .profile-photo-placeholder {
        width: 120px;
        height: 120px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .info-value {
        max-width: 100%;
        text-align: left;
    }
}
</style>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="details-header text-center">
        <h1 class="display-4 mb-3">👤 Employee Profile Details</h1>
        <p class="lead mb-2">Comprehensive Employee Information System</p>
        <small class="text-light">Detailed view of employee records and documentation</small>
    </div>

    <!-- Back Button -->
    <a href="master_records_view.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Employee Records
    </a>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-professional">
            <div class="error-state">
                <i class="fas fa-exclamation-triangle error-icon"></i>
                <h4>Error Loading Employee Details</h4>
                <p><?= htmlspecialchars($error_message) ?></p>
                <a href="master_records_view.php" class="back-button mt-3">
                    <i class="fas fa-arrow-left"></i> Return to Employee List
                </a>
            </div>
        </div>
    <?php elseif ($employee): ?>
        <!-- Employee Profile Card -->
        <div class="employee-profile-card">
            <!-- Profile Header with Photo and Basic Info -->
            <div class="profile-header">
                <?php if ($cardStatus): ?>
                    <div class="card-status-indicator card-<?= $cardStatus ?>">
                        <?php if ($cardStatus == 'valid'): ?>
                            <i class="fas fa-check-circle"></i> Card Valid
                        <?php elseif ($cardStatus == 'expiring'): ?>
                            <i class="fas fa-exclamation-triangle"></i> Card Expiring
                        <?php else: ?>
                            <i class="fas fa-times-circle"></i> Card Expired
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($employee['picture']) && file_exists($employee['picture'])): ?>
                    <img src="<?= htmlspecialchars($employee['picture']) ?>" alt="Employee Picture" class="profile-photo">
                <?php else: ?>
                    <div class="profile-photo-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>

                <div class="employee-name"><?= htmlspecialchars($employee['employee_name']) ?></div>
                <div class="employee-id">ID: <?= htmlspecialchars($employee['employee_ID']) ?></div>
                
                <span class="status-badge status-<?= strtolower($employee['status']) ?>">
                    <?= $employee['status'] == 'Active' ? '✅' : '❌' ?> <?= htmlspecialchars($employee['status']) ?>
                </span>
            </div>

            <!-- Quick Stats -->
            <div class="info-section">
                <div class="quick-stats">
                    <div class="quick-stat-card">
                        <div class="quick-stat-number"><?= $yearsOfService ?></div>
                        <div class="quick-stat-label">Years of Service</div>
                    </div>
                    <div class="quick-stat-card">
                        <div class="quick-stat-number"><?= htmlspecialchars($employee['division_name'] ?? 'N/A') ?></div>
                        <div class="quick-stat-label">Division</div>
                    </div>
                    <div class="quick-stat-card">
                        <div class="quick-stat-number"><?= htmlspecialchars($employee['section_name'] ?? 'N/A') ?></div>
                        <div class="quick-stat-label">Section</div>
                    </div>
                    <div class="quick-stat-card">
                        <div class="quick-stat-number"><?= htmlspecialchars($employee['designation']) ?></div>
                        <div class="quick-stat-label">Designation</div>
                    </div>
                </div>

                <!-- Detailed Information Grid -->
                <div class="info-grid">
                    <!-- Personal Information -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <i class="fas fa-user"></i> Personal Information
                        </div>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-id-card form-icon"></i> Full Name
                            </span>
                            <span class="info-value"><?= htmlspecialchars($employee['employee_name']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-id-card-alt form-icon"></i> NIC Number
                            </span>
                            <span class="info-value"><?= htmlspecialchars($employee['nic_number']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-venus-mars form-icon"></i> Gender
                            </span>
                            <span class="info-value"><?= $employee['gender'] == 'Male' ? '👨' : '👩' ?> <?= htmlspecialchars($employee['gender']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-phone form-icon"></i> Telephone
                            </span>
                            <span class="info-value"><?= htmlspecialchars($employee['telephone_number']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-map-marker-alt form-icon"></i> Address
                            </span>
                            <span class="info-value"><?= htmlspecialchars($employee['address']) ?></span>
                        </div>
                    </div>

                    <!-- Employment Information -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <i class="fas fa-briefcase"></i> Employment Details
                        </div>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-id-badge form-icon"></i> Employee ID
                            </span>
                            <span class="info-value"><?= htmlspecialchars($employee['employee_ID']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-sitemap form-icon"></i> Division
                            </span>
                            <span class="info-value"><?= htmlspecialchars($employee['division_name'] ?? 'N/A') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-layer-group form-icon"></i> Section
                            </span>
                            <span class="info-value"><?= htmlspecialchars($employee['section_name'] ?? 'N/A') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-briefcase form-icon"></i> Designation
                            </span>
                            <span class="info-value"><?= htmlspecialchars($employee['designation']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-calendar-plus form-icon"></i> Appointment Date
                            </span>
                            <span class="info-value"><?= date('F j, Y', strtotime($employee['appointment_date'])) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-clock form-icon"></i> Years of Service
                            </span>
                            <span class="info-value"><?= $yearsOfService ?> Years</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-toggle-on form-icon"></i> Status
                            </span>
                            <span class="info-value">
                                <span class="status-badge status-<?= strtolower($employee['status']) ?>">
                                    <?= htmlspecialchars($employee['status']) ?>
                                </span>
                            </span>
                        </div>
                    </div>

                    <!-- ID Card Information -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <i class="fas fa-credit-card"></i> ID Card Details
                        </div>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-calendar-check form-icon"></i> Card Issued Date
                            </span>
                            <span class="info-value"><?= date('F j, Y', strtotime($employee['card_issued_date'])) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-calendar-times form-icon"></i> Card Valid Until
                            </span>
                            <span class="info-value"><?= date('F j, Y', strtotime($employee['card_valid_date'])) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-info-circle form-icon"></i> Card Status
                            </span>
                            <span class="info-value">
                                <span class="status-badge card-<?= $cardStatus ?>">
                                    <?php if ($cardStatus == 'valid'): ?>
                                        <i class="fas fa-check-circle"></i> Valid
                                    <?php elseif ($cardStatus == 'expiring'): ?>
                                        <i class="fas fa-exclamation-triangle"></i> Expiring Soon
                                    <?php else: ?>
                                        <i class="fas fa-times-circle"></i> Expired
                                    <?php endif; ?>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning alert-professional">
            <div class="error-state">
                <i class="fas fa-user-slash error-icon"></i>
                <h4>Employee Not Found</h4>
                <p>No employee record found with the specified ID.</p>
                <a href="master_records_view.php" class="back-button mt-3">
                    <i class="fas fa-arrow-left"></i> Return to Employee List
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Enhanced Employee Details functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling for back button
    const backButton = document.querySelector('.back-button');
    if (backButton) {
        backButton.addEventListener('click', function(e) {
            // Add loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Returning...';
        });
    }

    // Add click-to-copy functionality for important data
    const copyableElements = document.querySelectorAll('.info-value');
    copyableElements.forEach(function(element) {
        element.addEventListener('click', function() {
            const text = this.textContent.trim();
            if (text && text !== 'N/A') {
                navigator.clipboard.writeText(text).then(function() {
                    // Show brief visual feedback
                    const original = element.style.background;
                    element.style.background = 'rgba(0, 184, 148, 0.2)';
                    element.style.transition = 'background 0.3s ease';
                    
                    setTimeout(function() {
                        element.style.background = original;
                    }, 1000);
                }).catch(function() {
                    console.log('Could not copy text');
                });
            }
        });
        
        // Add hover effect to indicate clickable
        element.addEventListener('mouseenter', function() {
            this.style.cursor = 'pointer';
            this.title = 'Click to copy';
        });
    });

    // Card status warnings
    const cardStatus = '<?= $cardStatus ?>';
    if (cardStatus === 'expiring') {
        console.log('⚠️ Employee ID card is expiring soon!');
    } else if (cardStatus === 'expired') {
        console.log('🚨 Employee ID card has expired!');
    }

    // Add keyboard navigation
    document.addEventListener('keydown', function(e) {
        // ESC key to go back
        if (e.key === 'Escape') {
            const backButton = document.querySelector('.back-button');
            if (backButton) {
                window.location.href = backButton.href;
            }
        }
    });
});
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>

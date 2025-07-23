<?php
include('./dbc.php');
include('includes/header.php');
include('includes/navbar.php');
include('includes/check_access.php'); 


$page = 'division.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    exit();
}
?>

<style>
.card-custom {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: none;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 10px;
    padding: 12px 30px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.btn-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.breadcrumb-custom {
    background: transparent;
    padding: 0;
    margin-bottom: 20px;
}

.breadcrumb-custom .breadcrumb-item {
    color: #6c757d;
}

.breadcrumb-custom .breadcrumb-item.active {
    color: #667eea;
    font-weight: 600;
}

.page-title {
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 30px;
    position: relative;
}

.page-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 0;
    width: 60px;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 2px;
}

.icon-wrapper {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 24px;
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
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
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
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
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
    background: linear-gradient(90deg, transparent 0%, rgba(102, 126, 234, 0.1) 50%, transparent 100%);
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
        
        <!-- Organizational Chart SVG -->
        <svg class="organizational-svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <!-- Building/Organization Icon -->
            <rect x="60" y="80" width="80" height="100" rx="5" fill="currentColor" opacity="0.3"/>
            <rect x="70" y="90" width="12" height="12" rx="2" fill="currentColor" opacity="0.5"/>
            <rect x="90" y="90" width="12" height="12" rx="2" fill="currentColor" opacity="0.5"/>
            <rect x="110" y="90" width="12" height="12" rx="2" fill="currentColor" opacity="0.5"/>
            <rect x="70" y="110" width="12" height="12" rx="2" fill="currentColor" opacity="0.5"/>
            <rect x="90" y="110" width="12" height="12" rx="2" fill="currentColor" opacity="0.5"/>
            <rect x="110" y="110" width="12" height="12" rx="2" fill="currentColor" opacity="0.5"/>
            <rect x="70" y="130" width="12" height="12" rx="2" fill="currentColor" opacity="0.5"/>
            <rect x="90" y="130" width="12" height="12" rx="2" fill="currentColor" opacity="0.5"/>
            <rect x="110" y="130" width="12" height="12" rx="2" fill="currentColor" opacity="0.5"/>
            
            <!-- Division Connections -->
            <circle cx="50" cy="50" r="8" fill="currentColor" opacity="0.4"/>
            <circle cx="100" cy="30" r="8" fill="currentColor" opacity="0.4"/>
            <circle cx="150" cy="50" r="8" fill="currentColor" opacity="0.4"/>
            
            <!-- Connection Lines -->
            <line x1="100" y1="40" x2="100" y2="80" stroke="currentColor" stroke-width="2" opacity="0.3"/>
            <line x1="50" y1="58" x2="90" y2="78" stroke="currentColor" stroke-width="2" opacity="0.3"/>
            <line x1="150" y1="58" x2="110" y2="78" stroke="currentColor" stroke-width="2" opacity="0.3"/>
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
                            <div class="icon-wrapper">
                                <i class="fas fa-plus"></i>
                            </div>
                            <h3 class="card-title mb-0" style="color: white; font-weight: 600;">Add New Division</h3>
                            <p class="mb-0" style="color: rgba(255,255,255,0.8);">Create a new division for your organization</p>
                        </div>
                        <div class="card-body card-body-custom p-4">
                            <form method="POST" action="./division_action.php" id="divisionForm">
                                <div class="form-group mb-4">
                                    <label for="divisionID" class="form-label font-weight-bold">
                                        <i class="fas fa-id-card text-primary mr-2"></i>Division ID
                                    </label>
                                    <input class="form-control form-control-custom" 
                                           name="division_id" 
                                           type="text" 
                                           id="divisionID" 
                                           placeholder="Enter unique division ID"
                                           required>
                                    <small class="form-text text-muted">Enter a unique identifier for this division</small>
                                </div>
                                <div class="form-group mb-4">
                                    <label for="divisionName" class="form-label font-weight-bold">
                                        <i class="fas fa-building text-primary mr-2"></i>Division Name
                                    </label>
                                    <input class="form-control form-control-custom" 
                                           name="division_name" 
                                           type="text" 
                                           id="divisionName" 
                                           placeholder="Enter division name"
                                           required>
                                    <small class="form-text text-muted">Enter the full name of the division</small>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-custom btn-lg px-5">
                                        <i class="fas fa-save mr-2"></i>Create Division
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
?>
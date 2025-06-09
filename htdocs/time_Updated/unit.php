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
$page = 'unit.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    ob_end_flush(); // Flush the output buffer
    exit();
}

// Fetch divisions from database
$divisions = [];
$sql = "SELECT division_id, division_name FROM divisions";
$result = mysqli_query($connect, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $divisions[] = $row;
    }
}
?>

</section>

<div class="row mx-md-n8">
    <div class="col px-md-5">
        <h1>Unit Attendance Report</h1>
        <div class="p-3 border bg-light">
            <div class="container">
                <form action="generate_report.php" method="POST">
                    <div class="form-group">
                        <label for="report_type">Select Report Type:</label>
                        <select name="report_type" id="report_type" class="form-control" required>
                            <option value="" disabled selected>Select Report Type</option>
                            <option value="individual">Individual</option>
                            <option value="group">Group</option>
                        </select>
                    </div>
                    <div class="form-group" id="employee_div">
                        <label for="employee_ID">Employee ID:</label>
                        <input type="text" id="employee_ID" name="employee_ID" class="form-control" placeholder="Enter Employee ID">
                    </div>
                    <div class="form-group" id="division_div" style="display: none;">
                        <label for="division">Select Division:</label>
                        <select name="division" id="division" class="form-control">
                        <option value="" disabled selected>Select Division</option>
                            <?php foreach ($divisions as $division): ?>
                                <option value="<?php echo $division['division_id']; ?>">
                                    <?php echo $division['division_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- New Section Selector -->
                    <div class="form-group" id="section_div" style="display: none;">
                        <label for="section">Select Section:</label>
                        <select name="section" id="section" class="form-control">
                            <option value="all">All Sections</option>
                            <!-- Sections will be dynamically populated based on the division -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="from_date">Select From Date:</label>
                        <input type="date" class="form-control" id="from_date" name="from_date" required>
                    </div>
                    <div class="form-group">
                        <label for="to_date">Select To Date:</label>
                        <input type="date" class="form-control" id="to_date" name="to_date" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Generate Report</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Handle the display of the employee and division fields
document.getElementById('report_type').addEventListener('change', function() {
    var reportType = this.value;
    if (reportType === 'individual') {
        document.getElementById('employee_div').style.display = 'block';
        document.getElementById('division_div').style.display = 'none';
        document.getElementById('section_div').style.display = 'none';
        document.getElementById('employee_ID').required = true;
        document.getElementById('division').required = false;
        document.getElementById('section').required = false;
    } else if (reportType === 'group') {
        document.getElementById('employee_div').style.display = 'none';
        document.getElementById('division_div').style.display = 'block';
        document.getElementById('employee_ID').required = false;
        document.getElementById('division').required = true;
        document.getElementById('section').required = false;
    } else {
        document.getElementById('employee_div').style.display = 'none';
        document.getElementById('division_div').style.display = 'none';
        document.getElementById('section_div').style.display = 'none';
    }
});

// Fetch sections dynamically when a division is selected
document.getElementById('division').addEventListener('change', function() {
    var divisionId = this.value;

    if (divisionId) {
        fetch('fetch_sections.php?division_id=' + divisionId)
            .then(response => response.json())
            .then(data => {
                var sectionSelect = document.getElementById('section');
                sectionSelect.innerHTML = '<option value="all">All Sections</option>'; // Default option to select all section

                // Populate sections based on the division
                data.forEach(section => {
                    var option = document.createElement('option');
                    option.value = section.section_id;
                    option.textContent = section.section_name;
                    sectionSelect.appendChild(option);
                });

                // Display the section dropdown
                document.getElementById('section_div').style.display = 'block';
            });
    } else {
        document.getElementById('section_div').style.display = 'none';
    }
});
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>

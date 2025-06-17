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
                    <div class="form-group mb-3">
                        <label>Select Report Type:</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="report_type" id="individual" value="individual" required>
                            <label class="form-check-label" for="individual">Individual</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="report_type" id="group" value="group" required>
                            <label class="form-check-label" for="group">Group</label>
                        </div>
                    </div>

                    <div class="form-group mb-3" id="employee_div" style="display:none;">
                        <label for="employee_ID">Employee ID</label>
                        <input type="text" class="form-control" id="employee_ID" name="employee_ID">
                    </div>

                    <div class="form-group mb-3" id="division_div" style="display:none;">
                        <label for="division">Division</label>
                        <select class="form-control" id="division" name="division">
                            <option value="">Select Division</option>
                            <?php foreach ($divisions as $division): ?>
                                <option value="<?= htmlspecialchars($division['division_id']) ?>">
                                    <?= htmlspecialchars($division['division_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3" id="section_div" style="display:none;">
                        <label for="section">Section</label>
                        <select class="form-control" id="section" name="section">
                            <option value="">Select Section</option>
                            <!-- Sections will be loaded dynamically -->
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="from_date">From Date</label>
                        <input type="date" class="form-control" id="from_date" name="from_date" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="to_date">To Date</label>
                        <input type="date" class="form-control" id="to_date" name="to_date" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Generate</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide fields based on radio selection
document.querySelectorAll('input[name="report_type"]').forEach(function(elem) {
    elem.addEventListener('change', function() {
        var reportType = document.querySelector('input[name="report_type"]:checked').value;
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
            document.getElementById('section_div').style.display = 'block';
            document.getElementById('employee_ID').required = false;
            document.getElementById('division').required = true;
            document.getElementById('section').required = false;
        }
    });
});

// Fetch sections dynamically when a division is selected
document.getElementById('division').addEventListener('change', function() {
    var divisionId = this.value;
    var sectionSelect = document.getElementById('section');
    sectionSelect.innerHTML = '<option value="">Loading...</option>';
    fetch('get_sections.php?division_id=' + divisionId)
        .then(response => response.json())
        .then(data => {
            sectionSelect.innerHTML = '<option value="">Select Section</option>';
            data.forEach(function(section) {
                var opt = document.createElement('option');
                opt.value = section.section_id;
                opt.textContent = section.section_name;
                sectionSelect.appendChild(opt);
            });
        });
});
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>

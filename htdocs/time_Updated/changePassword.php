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

// Note: All authenticated users should be able to change their password
// No additional access control needed beyond login verification

// Retrieve error and success messages from the session, if they exist
$errorMessages = isset($_SESSION['error_messages']) ? $_SESSION['error_messages'] : [];
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

// Clear the messages after displaying them
unset($_SESSION['error_messages']);
unset($_SESSION['success_message']);
?>

<style>
.password-wrapper {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    min-height: 100vh;
    padding: 20px 0;
    position: relative;
    overflow: hidden;
}

.password-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 400px;
    height: 100%;
    background: linear-gradient(135deg, rgba(44, 62, 80, 0.1) 0%, rgba(52, 73, 94, 0.1) 100%);
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
    background: linear-gradient(135deg, rgba(44, 62, 80, 0.2) 0%, rgba(52, 73, 94, 0.2) 100%);
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

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

.main-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 25px 70px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    position: relative;
    z-index: 2;
    margin: 0 auto;
    max-width: 650px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.card-header-custom {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    padding: 40px 30px;
    text-align: center;
    position: relative;
    border-bottom: 3px solid #3498db;
}

.card-header-custom::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.icon-wrapper-large {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 36px;
    box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3);
    border: 3px solid rgba(255, 255, 255, 0.2);
    position: relative;
    z-index: 1;
}

.card-body-custom {
    padding: 45px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.form-group-modern {
    margin-bottom: 30px;
    position: relative;
}

.form-label-modern {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 10px;
    display: block;
    font-size: 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.form-control-modern {
    border-radius: 15px;
    border: 2px solid #e9ecef;
    padding: 18px 25px 18px 55px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    width: 100%;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.form-control-modern:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25), 0 8px 30px rgba(52, 152, 219, 0.1);
    background: white;
    outline: none;
    transform: translateY(-2px);
}

.input-icon {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #7f8c8d;
    font-size: 18px;
    z-index: 2;
    transition: all 0.3s ease;
}

.form-group-modern:focus-within .input-icon {
    color: #3498db;
    transform: translateY(-50%) scale(1.1);
}

.password-toggle {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #7f8c8d;
    cursor: pointer;
    font-size: 18px;
    z-index: 2;
    transition: all 0.3s ease;
    padding: 5px;
    border-radius: 50%;
}

.password-toggle:hover {
    color: #3498db;
    background: rgba(52, 152, 219, 0.1);
}

.btn-change-password {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    border: none;
    border-radius: 15px;
    padding: 18px 40px;
    font-weight: 700;
    font-size: 16px;
    color: white;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3);
    width: 100%;
    min-height: 60px;
}

.btn-change-password::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
    transition: left 0.6s;
}

.btn-change-password:hover::before {
    left: 100%;
}

.btn-change-password:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(52, 152, 219, 0.4);
    color: white;
}

.btn-change-password:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.alert-modern {
    border-radius: 15px;
    border: none;
    padding: 18px 25px;
    margin-bottom: 25px;
    font-weight: 600;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 15px;
}

.alert-success-modern {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    color: white;
    border-left: 5px solid #1e8449;
}

.alert-danger-modern {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
    border-left: 5px solid #922b21;
}

.password-requirements {
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.08) 0%, rgba(41, 128, 185, 0.08) 100%);
    border-radius: 15px;
    padding: 25px;
    margin-top: 25px;
    border: 2px solid rgba(52, 152, 219, 0.1);
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.05);
}

.password-requirements h5 {
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 20px;
    font-size: 16px;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.requirement-item {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 12px;
    font-size: 14px;
    color: #5d6d7e;
    font-weight: 500;
    padding: 8px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.requirement-item.valid {
    background: rgba(39, 174, 96, 0.1);
    color: #27ae60;
}

.requirement-icon {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.requirement-icon.valid {
    background: #27ae60;
    color: white;
    transform: scale(1.1);
}

.page-title {
    font-size: 32px;
    font-weight: 800;
    margin: 0;
    position: relative;
    z-index: 1;
    letter-spacing: -0.5px;
}

.page-subtitle {
    font-size: 16px;
    opacity: 0.9;
    margin: 15px 0 0 0;
    position: relative;
    z-index: 1;
    font-weight: 400;
}

.strength-meter {
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    margin-top: 10px;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

.strength-bar {
    height: 100%;
    border-radius: 3px;
    transition: all 0.4s ease;
    width: 0%;
}

.strength-weak { background: linear-gradient(90deg, #e74c3c, #c0392b); width: 25%; }
.strength-fair { background: linear-gradient(90deg, #f39c12, #e67e22); width: 50%; }
.strength-good { background: linear-gradient(90deg, #3498db, #2980b9); width: 75%; }
.strength-strong { background: linear-gradient(90deg, #27ae60, #2ecc71); width: 100%; }

.strength-text {
    font-size: 13px;
    margin-top: 8px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.security-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
    border: 1px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
}
</style>

<div class="password-wrapper">
    <!-- Decorative Elements -->
    <div class="decorative-elements">
        <div class="geometric-shape shape-1"></div>
        <div class="geometric-shape shape-2"></div>
        <div class="geometric-shape shape-3"></div>
        <div class="geometric-shape shape-4"></div>
    </div>

    <div class="container-fluid px-4">
        <div class="main-card">
            <div class="card-header-custom">
                <div class="icon-wrapper-large">
                    <i class="fas fa-key"></i>
                </div>
                <h1 class="page-title">Change Password</h1>
                <p class="page-subtitle">Update your account password securely</p>
            </div>
            
            <div class="card-body-custom">
                <?php foreach ($errorMessages as $message): ?>
                    <div class="alert alert-danger-modern">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($message); ?>
                    </div>
                <?php endforeach; ?>
                
                <?php if ($successMessage): ?>
                    <div class="alert alert-success-modern">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($successMessage); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="changePasswordAction.php" id="passwordForm">
                    <div class="form-group-modern">
                        <label for="current_password" class="form-label-modern">
                            <i class="fas fa-lock text-primary mr-2"></i>Current Password
                        </label>
                        <div style="position: relative;">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   id="current_password" 
                                   name="current_password" 
                                   class="form-control-modern" 
                                   placeholder="Enter your current password"
                                   required>
                            <i class="fas fa-eye password-toggle" data-target="current_password"></i>
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label for="new_password" class="form-label-modern">
                            <i class="fas fa-key text-primary mr-2"></i>New Password
                        </label>
                        <div style="position: relative;">
                            <i class="fas fa-key input-icon"></i>
                            <input type="password" 
                                   id="new_password" 
                                   name="new_password" 
                                   class="form-control-modern" 
                                   placeholder="Enter your new password"
                                   required>
                            <i class="fas fa-eye password-toggle" data-target="new_password"></i>
                        </div>
                        <div class="strength-meter">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>

                    <div class="form-group-modern">
                        <label for="confirm_password" class="form-label-modern">
                            <i class="fas fa-shield-alt text-primary mr-2"></i>Confirm New Password
                        </label>
                        <div style="position: relative;">
                            <i class="fas fa-shield-alt input-icon"></i>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   class="form-control-modern" 
                                   placeholder="Confirm your new password"
                                   required>
                            <i class="fas fa-eye password-toggle" data-target="confirm_password"></i>
                        </div>
                        <div id="passwordMatchMessage" style="font-size: 12px; margin-top: 5px;"></div>
                    </div>

                    <div class="password-requirements">
                        <h5><i class="fas fa-info-circle mr-2"></i>Password Requirements</h5>
                        <div class="requirement-item">
                            <div class="requirement-icon" id="req-length">
                                <i class="fas fa-times"></i>
                            </div>
                            <span>At least 8 characters long</span>
                        </div>
                        <div class="requirement-item">
                            <div class="requirement-icon" id="req-uppercase">
                                <i class="fas fa-times"></i>
                            </div>
                            <span>At least one uppercase letter (A-Z)</span>
                        </div>
                        <div class="requirement-item">
                            <div class="requirement-icon" id="req-lowercase">
                                <i class="fas fa-times"></i>
                            </div>
                            <span>At least one lowercase letter (a-z)</span>
                        </div>
                        <div class="requirement-item">
                            <div class="requirement-icon" id="req-number">
                                <i class="fas fa-times"></i>
                            </div>
                            <span>At least one number (0-9)</span>
                        </div>
                        <div class="requirement-item">
                            <div class="requirement-icon" id="req-special">
                                <i class="fas fa-times"></i>
                            </div>
                            <span>At least one special character (!@#$%^&*)</span>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-change-password" id="submitBtn">
                            <i class="fas fa-save mr-2"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });
    });

    // Password strength checker
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const submitBtn = document.getElementById('submitBtn');

    newPasswordInput.addEventListener('input', function() {
        const password = this.value;
        checkPasswordStrength(password);
        checkPasswordRequirements(password);
        checkPasswordMatch();
    });

    confirmPasswordInput.addEventListener('input', function() {
        checkPasswordMatch();
    });

    function checkPasswordStrength(password) {
        let strength = 0;
        let strengthClass = '';
        let strengthLabel = '';

        // Length check
        if (password.length >= 8) strength++;
        
        // Uppercase check
        if (/[A-Z]/.test(password)) strength++;
        
        // Lowercase check
        if (/[a-z]/.test(password)) strength++;
        
        // Number check
        if (/\d/.test(password)) strength++;
        
        // Special character check
        if (/[\W]/.test(password)) strength++;

        // Set strength level
        switch(strength) {
            case 0:
            case 1:
                strengthClass = 'strength-weak';
                strengthLabel = 'Weak';
                break;
            case 2:
                strengthClass = 'strength-fair';
                strengthLabel = 'Fair';
                break;
            case 3:
            case 4:
                strengthClass = 'strength-good';
                strengthLabel = 'Good';
                break;
            case 5:
                strengthClass = 'strength-strong';
                strengthLabel = 'Strong';
                break;
        }

        strengthBar.className = 'strength-bar ' + strengthClass;
        strengthText.textContent = 'Password Strength: ' + strengthLabel;
        strengthText.style.color = getStrengthColor(strength);
    }

    function checkPasswordRequirements(password) {
        const requirements = [
            { id: 'req-length', test: password.length >= 8 },
            { id: 'req-uppercase', test: /[A-Z]/.test(password) },
            { id: 'req-lowercase', test: /[a-z]/.test(password) },
            { id: 'req-number', test: /\d/.test(password) },
            { id: 'req-special', test: /[\W]/.test(password) }
        ];

        requirements.forEach(req => {
            const element = document.getElementById(req.id);
            const icon = element.querySelector('i');
            
            if (req.test) {
                element.classList.add('valid');
                icon.className = 'fas fa-check';
            } else {
                element.classList.remove('valid');
                icon.className = 'fas fa-times';
            }
        });

        // Enable/disable submit button based on all requirements
        const allValid = requirements.every(req => req.test);
        updateSubmitButton(allValid);
    }

    function checkPasswordMatch() {
        const password = newPasswordInput.value;
        const confirm = confirmPasswordInput.value;
        const messageElement = document.getElementById('passwordMatchMessage');

        if (confirm === '') {
            messageElement.textContent = '';
            return;
        }

        if (password === confirm) {
            messageElement.textContent = '✓ Passwords match';
            messageElement.style.color = '#4CAF50';
        } else {
            messageElement.textContent = '✗ Passwords do not match';
            messageElement.style.color = '#f44336';
        }
    }

    function updateSubmitButton(passwordValid) {
        const password = newPasswordInput.value;
        const confirm = confirmPasswordInput.value;
        const passwordsMatch = password === confirm && confirm !== '';
        
        if (passwordValid && passwordsMatch) {
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
        } else {
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.6';
        }
    }

    function getStrengthColor(strength) {
        switch(strength) {
            case 0:
            case 1:
                return '#f44336';
            case 2:
                return '#ff9800';
            case 3:
            case 4:
                return '#2196f3';
            case 5:
                return '#4caf50';
            default:
                return '#666';
        }
    }

    // Form submission validation
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        const password = newPasswordInput.value;
        const confirm = confirmPasswordInput.value;

        if (password !== confirm) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }

        if (!password.match(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W]).{8,}$/)) {
            e.preventDefault();
            alert('Password does not meet security requirements!');
            return false;
        }

        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Changing Password...';
        submitBtn.disabled = true;
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-modern');
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});
</script>

<?php
mysqli_close($connect);
include('includes/scripts.php');
include('includes/footer.php');
ob_end_flush(); 
?>
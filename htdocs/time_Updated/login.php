<!DOCTYPE html>
<html lang="en">

<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Favicon -->
    <link rel="icon" href="./dist/img/slpa.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;700;900&display=swap" rel="stylesheet" />
    <title>Login_form</title>

    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            text-decoration: none;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
            min-height: 100vh;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        body.loaded {
            opacity: 1;
        }

        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -2;
            object-fit: cover;
            opacity: 0.7;
        }

        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.4), rgba(118, 75, 162, 0.4));
            z-index: -1;
            backdrop-filter: blur(2px);
        }

        .wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
            z-index: 1;
            padding: 20px;
        }

        .form-box {
            background: rgba(255, 255, 255, 0.15);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 
                0 25px 45px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.4),
                inset 0 -1px 0 rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 900px;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(20px);
            animation: slideInUp 0.8s ease-out 0.1s forwards, float 6s ease-in-out 1s infinite;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }

        .form-box:hover::before {
            left: 100%;
        }

        .form-box:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 35px 60px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.5),
                inset 0 -1px 0 rgba(0, 0, 0, 0.1);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }

        .logo img {
            max-width: 150px;
            height: auto;
            border-radius: 50%;
            box-shadow: 
                0 15px 40px rgba(0, 0, 0, 0.4),
                0 0 30px rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .logo img:hover {
            transform: scale(1.15);
            box-shadow: 
                0 20px 50px rgba(0, 0, 0, 0.5),
                0 0 40px rgba(255, 255, 255, 0.4);
            border-color: rgba(255, 255, 255, 0.5);
        }

        h2 {
            text-align: center;
            font-size: 48px;
            font-weight: 900;
            margin-bottom: 40px;
            text-shadow: 
                0 0 30px rgba(255, 255, 255, 0.8),
                0 4px 15px rgba(0, 0, 0, 0.6),
                0 0 10px rgba(255, 255, 255, 0.5);
            letter-spacing: 8px;
            text-transform: uppercase;
            position: relative;
            animation: colorChange 4s ease-in-out infinite;
            font-family: 'Roboto Slab', serif;
            font-style: normal;
        }

        @keyframes colorChange {
            0% { 
                color: #ffffff;
                text-shadow: 
                    0 0 30px rgba(255, 255, 255, 0.8),
                    0 4px 15px rgba(0, 0, 0, 0.6),
                    0 0 10px rgba(255, 255, 255, 0.5);
            }
            25% { 
                color: #cccccc;
                text-shadow: 
                    0 0 25px rgba(204, 204, 204, 0.7),
                    0 4px 12px rgba(0, 0, 0, 0.5),
                    0 0 8px rgba(255, 255, 255, 0.3);
            }
            50% { 
                color: #000000;
                text-shadow: 
                    0 0 20px rgba(255, 255, 255, 0.9),
                    0 4px 10px rgba(255, 255, 255, 0.7),
                    0 0 15px rgba(255, 255, 255, 0.6);
            }
            75% { 
                color: #333333;
                text-shadow: 
                    0 0 25px rgba(255, 255, 255, 0.8),
                    0 4px 12px rgba(255, 255, 255, 0.6),
                    0 0 12px rgba(255, 255, 255, 0.4);
            }
            100% { 
                color: #ffffff;
                text-shadow: 
                    0 0 30px rgba(255, 255, 255, 0.8),
                    0 4px 15px rgba(0, 0, 0, 0.6),
                    0 0 10px rgba(255, 255, 255, 0.5);
            }
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: -18px;
            left: 50%;
            transform: translateX(-50%);
            width: 140px;
            height: 6px;
            background: linear-gradient(90deg, transparent, #ffffff 15%, #cccccc 30%, #000000 50%, #cccccc 70%, #ffffff 85%, transparent);
            border-radius: 3px;
            box-shadow: 
                0 0 25px rgba(255, 255, 255, 0.7),
                0 3px 10px rgba(0, 0, 0, 0.3);
            animation: underlineColorChange 4s ease-in-out infinite;
        }

        @keyframes underlineColorChange {
            0% { 
                background: linear-gradient(90deg, transparent, #ffffff 15%, #f0f0f0 30%, #ffffff 50%, #f0f0f0 70%, #ffffff 85%, transparent);
                opacity: 0.8; 
                transform: translateX(-50%) scaleX(0.9); 
            }
            25% { 
                background: linear-gradient(90deg, transparent, #cccccc 15%, #999999 30%, #666666 50%, #999999 70%, #cccccc 85%, transparent);
                opacity: 0.9; 
                transform: translateX(-50%) scaleX(1.0); 
            }
            50% { 
                background: linear-gradient(90deg, transparent, #666666 15%, #333333 30%, #000000 50%, #333333 70%, #666666 85%, transparent);
                opacity: 1.0; 
                transform: translateX(-50%) scaleX(1.1); 
            }
            75% { 
                background: linear-gradient(90deg, transparent, #999999 15%, #666666 30%, #333333 50%, #666666 70%, #999999 85%, transparent);
                opacity: 0.9; 
                transform: translateX(-50%) scaleX(1.0); 
            }
            100% { 
                background: linear-gradient(90deg, transparent, #ffffff 15%, #f0f0f0 30%, #ffffff 50%, #f0f0f0 70%, #ffffff 85%, transparent);
                opacity: 0.8; 
                transform: translateX(-50%) scaleX(0.9); 
            }
        }

        .form-content {
            display: flex;
            align-items: center;
            gap: 40px;
        }

        .form-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .form-right {
            flex: 1;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .input-box {
            position: relative;
            margin-bottom: 10px;
        }

        .input-box label {
            display: block;
            color: #e8f4fd;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 15px;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-box input,
        .input-box select {
            width: 100%;
            padding: 16px 22px;
            border: 2px solid rgba(232, 244, 253, 0.3);
            border-radius: 12px;
            outline: none;
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .input-box input::placeholder {
            color: rgba(232, 244, 253, 0.6);
            font-style: italic;
        }

        .input-box input:focus,
        .input-box select:focus {
            border-color: rgba(232, 244, 253, 0.8);
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
            box-shadow: 
                0 10px 25px rgba(0, 0, 0, 0.2),
                0 0 20px rgba(232, 244, 253, 0.3);
        }

        .input-box select {
            color: #ffffff;
            cursor: pointer;
            font-weight: 500;
        }

        .input-box select option {
            background: rgba(20, 30, 48, 0.95);
            color: #e8f4fd;
            padding: 12px;
            font-weight: 500;
        }

        .resetpassword {
            text-align: right;
            margin: 10px 0;
        }

        .resetpassword a {
            color: #c3e9ff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
            text-transform: capitalize;
        }

        .resetpassword a:hover {
            color: #ffffff;
            text-shadow: 0 0 15px rgba(195, 233, 255, 0.8);
            transform: translateY(-1px);
        }



        .button-container {
            margin-top: 25px;
        }

        .button-container button {
            width: 100%;
            padding: 18px;
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 50%, #2c5aa0 100%);
            cursor: pointer;
            transition: all 0.4s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 
                0 10px 30px rgba(74, 144, 226, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .button-container button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .button-container button:hover::before {
            left: 100%;
        }

        .button-container button:hover {
            background: linear-gradient(135deg, #357abd 0%, #2c5aa0 50%, #1e3f73 100%);
            transform: translateY(-4px);
            box-shadow: 
                0 20px 40px rgba(74, 144, 226, 0.6),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        .button-container button:active {
            transform: translateY(-1px);
        }

        /* Error message styling */
        .error-message {
            background: rgba(255, 99, 132, 0.15);
            color: #ff6384;
            padding: 14px;
            border-radius: 10px;
            margin-top: 15px;
            text-align: center;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid rgba(255, 99, 132, 0.3);
            backdrop-filter: blur(10px);
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }


        @media screen and (max-width: 768px) {
            .form-box {
                width: 95%;
                margin: 10px;
                padding: 30px 25px;
                max-width: 450px;
            }

            .form-content {
                flex-direction: column;
                gap: 20px;
            }

            .logo img {
                max-width: 120px;
            }

            h2 {
                font-size: 36px;
                letter-spacing: 5px;
            }

            .input-box input,
            .input-box select {
                padding: 12px 15px;
                font-size: 15px;
            }
        }

        @media screen and (max-width: 480px) {
            .form-box {
                padding: 25px 20px;
                max-width: 400px;
            }

            .form-content {
                flex-direction: column;
                gap: 15px;
            }

            h2 {
                font-size: 32px;
                letter-spacing: 3px;
            }

            .logo img {
                max-width: 60px;
            }
        }

        /* Icon styling */
        .icon {
            margin-right: 10px;
            opacity: 0.9;
            color: #c3e9ff;
            filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.3));
        }

        /* Glassmorphism effects */
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Floating animation for the form - delayed start */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>

<body>
    <!-- Background Video -->
    <video autoplay muted loop playsinline class="video-background" id="bgVideo">
        <source src="./images/web_video_slpa.mp4" type="video/mp4">
        <source src="images/web_video_slpa.mp4" type="video/mp4">
        <source src="./images/web_video_slpa.webm" type="video/webm">
        <!-- Fallback for browsers that don't support video -->
    </video>
    
    <!-- Video Overlay -->
    <div class="video-overlay"></div>
    
    <script>
        // User role mapping based on User ID patterns
        const userRoleMap = {
            // Super Admin IDs (SA prefix)
            'SA001': { role: 'Super_Ad', display: 'Super Admin' },
            'SA002': { role: 'Super_Ad', display: 'Super Admin' },
            'SA003': { role: 'Super_Ad', display: 'Super Admin' },
            'SA1001': { role: 'Super_Ad', display: 'Super Admin' },
            
            // Administration IDs (AD prefix)
            'AD001': { role: 'Administration', display: 'Administration' },
            'AD002': { role: 'Administration', display: 'Administration' },
            'AD003': { role: 'Administration', display: 'Administration' },
            'AD004': { role: 'Administration', display: 'Administration' },
            'AD005': { role: 'Administration', display: 'Administration' },
            
            // Administrative Clerk IDs (AC prefix)
            'AC001': { role: 'Administration_clerk', display: 'Administrative Clerk' },
            'AC002': { role: 'Administration_clerk', display: 'Administrative Clerk' },
            'AC003': { role: 'Administration_clerk', display: 'Administrative Clerk' },
            'AC004': { role: 'Administration_clerk', display: 'Administrative Clerk' },
            'AC005': { role: 'Administration_clerk', display: 'Administrative Clerk' },
            'AC006': { role: 'Administration_clerk', display: 'Administrative Clerk' },
            'AC007': { role: 'Administration_clerk', display: 'Administrative Clerk' },
            'AC008': { role: 'Administration_clerk', display: 'Administrative Clerk' },
            'AC009': { role: 'Administration_clerk', display: 'Administrative Clerk' },
            'AC010': { role: 'Administration_clerk', display: 'Administrative Clerk' },
            
            // Clerk IDs (CL prefix)
            'CL001': { role: 'clerk', display: 'Clerk' },
            'CL002': { role: 'clerk', display: 'Clerk' },
            'CL003': { role: 'clerk', display: 'Clerk' },
            'CL004': { role: 'clerk', display: 'Clerk' },
            'CL005': { role: 'clerk', display: 'Clerk' },
            'CL006': { role: 'clerk', display: 'Clerk' },
            'CL007': { role: 'clerk', display: 'Clerk' },
            'CL008': { role: 'clerk', display: 'Clerk' },
            'CL009': { role: 'clerk', display: 'Clerk' },
            'CL010': { role: 'clerk', display: 'Clerk' },
            'CL011': { role: 'clerk', display: 'Clerk' },
            'CL012': { role: 'clerk', display: 'Clerk' },
            'CL013': { role: 'clerk', display: 'Clerk' },
            'CL014': { role: 'clerk', display: 'Clerk' },
            'CL015': { role: 'clerk', display: 'Clerk' }
        };

        // Function to identify role based on User ID
        function identifyRole(userId) {
            if (!userId) return null;
            
            // Convert to uppercase for consistency
            userId = userId.toUpperCase().trim();
            
            // Direct mapping check
            if (userRoleMap[userId]) {
                return userRoleMap[userId];
            }
            
            // Pattern-based identification if direct mapping not found
            if (userId.startsWith('SA')) {
                return { role: 'Super_Ad', display: 'Super Admin' };
            } else if (userId.startsWith('AD')) {
                return { role: 'Administration', display: 'Administration' };
            } else if (userId.startsWith('AC')) {
                return { role: 'Administration_clerk', display: 'Administrative Clerk' };
            } else if (userId.startsWith('CL')) {
                return { role: 'clerk', display: 'Clerk' };
            }
            
            return null;
        }

        // Ensure immediate form visibility and video plays on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Show form immediately
            const formBox = document.querySelector('.form-box');
            if (formBox) {
                formBox.style.opacity = '1';
                formBox.style.transform = 'translateY(0)';
            }
            
            // Handle video background
            const video = document.getElementById('bgVideo');
            if (video) {
                video.play().catch(function(error) {
                    console.log('Video autoplay failed:', error);
                    // Video failed to play, remove video element to show gradient background
                    video.style.display = 'none';
                });
            }

            // Add event listener for User ID input
            const userIdInput = document.getElementById('user_id');
            const roleHiddenInput = document.getElementById('role');

            if (userIdInput && roleHiddenInput) {
                userIdInput.addEventListener('input', function() {
                    const userId = this.value;
                    const roleInfo = identifyRole(userId);
                    
                    if (roleInfo) {
                        roleHiddenInput.value = roleInfo.role;
                    } else {
                        roleHiddenInput.value = '';
                    }
                });

                // Form validation before submission
                const form = document.querySelector('form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const roleValue = roleHiddenInput.value;
                        if (!roleValue) {
                            e.preventDefault();
                            alert('Please enter a valid User ID. Role could not be identified.');
                            userIdInput.focus();
                        }
                    });
                }
            }
        });

        // Prevent any flash of unstyled content
        window.addEventListener('load', function() {
            document.body.style.opacity = '1';
            document.body.classList.add('loaded');
        });
    </script>
    
    <div class="wrapper">
        <div class="form-box login">
            <div class="form-content">
                <div class="form-left">
                    <div class="logo">
                        <img src="./dist/img/logo.jpg" alt="logo">
                    </div>
                    <h2>Login</h2>
                </div>
                <div class="form-right">
                    <form method="POST" action="./login_action.php">

                        <div class="input-box">
                            <label for="user_id"><span class="icon"><ion-icon name="person-circle"></ion-icon>&nbsp; </span>User ID</label>
                            <input type="text" id="user_id" name="user_id" placeholder="Enter your User ID" required />
                        </div>

                        <input type="hidden" id="role" name="role" />

                        <div class="input-box">
                            <label for="password"><span class="icon"><ion-icon name="lock-closed"></ion-icon>&nbsp; </span>Password</label>
                            <input type="password" id="password" name="password" placeholder="Enter password" required />
                        </div>


                        <div class="resetpassword">
                            <p>
                                <a href="#">forgot password?</a>
                            </p>
                        </div><br>

                        <div class="button-container">
                            <button type="submit" class="btn">Login</button><br>
                        </div>
                        <?php
                        if (isset($_GET['error'])) {
                            $error = $_GET['error'];
                            if ($error === 'password_incorrect') {
                                echo '<div class="error-message">Invalid password. Please try again!</div>';
                            } elseif ($error === 'user_not_found') {
                                echo '<div class="error-message">Invalid User ID or credentials. Please check your User ID.</div>';
                            }
                        }
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>
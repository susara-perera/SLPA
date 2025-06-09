<!DOCTYPE html>
<html lang="en">

<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Favicon -->
    <link rel="icon" href="./dist/img/slpa.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200&display=swap" rel="stylesheet" />
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
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;

        }

        .wrapper {
            display: flex;
            justify-content: center;
            overflow: hidden;
            align-items: center;
            height: 100vh;
            box-shadow: rgba(0, 0, 0, 0.56) 0px 22px 70px 4px;
        }

        .form-box {
            background: #fff;
            padding: 30px;
            height: 60%;
            border-radius: 10px;
            box-shadow: rgba(0, 0, 0, 0.19) 0px 10px 20px, rgba(0, 0, 0, 0.23) 0px 6px 6px;
            width: 60%;
            
        }

        h2 {
            text-align: center;
            color: #1A6893;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        .input-box {
            position: relative;
            margin-bottom: 20px;
        }



        .select-arrow::after {
            content: "\f102";
            font-family: 'Ionicons';
            font-size: 16px;
            position: absolute;
            top: 50%;
            right: 0;
            transform: translateY(-50%);
        }


        .input-box input,
        .input-box select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            outline: none;
        }

        .input-box input:focus,
        .input-box select:focus {
            border-color: #1A6893;

        }



        .button-container button {
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            display: block;
            font-size: 20px;
            color: white;
            border: none;
            border-radius: 5px;
            background-image: linear-gradient(to right, rgb(14, 0, 91), #e7e7e7);
            cursor: pointer;
            transition: 0.3s;
        }

        .button-container button:hover {
            background-image: linear-gradient(to right, #e7e7e7, rgb(14, 0, 91));
        }

        .logo img {
            max-width: 5%;
            height: auto;
        }


        @media screen and (max-width: 768px) {


            .form-box {
                width: 90%;
            }

            .logo img {
                max-width: 8%;
                height: auto;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="form-box login">
            <div class="logo">
                <img src="./dist/img/logo.jpg" alt="logo">
            </div>
            <h2>Login</h2>
            <form method="POST" action="./login_action.php">

                <div class="input-box">
                    <label for="role"><span class="icon"><ion-icon name="person-circle"></ion-icon>&nbsp; </span>User Role</label>
                    <select name="role" id="role" class="form-control" placeholder="User role" required>
                        <option value="" disabled selected>Select Role</option>
                        <option value="Super_Ad">Super Admin </option>
                        <option value="Administration">Administration</option>
                        <option value="Administration_clerk">Administrative Clerk</option>
                        <option value="clerk">Clerk</option>
                    </select>
                </div>

                <div class="input-box">
                    <label for="password"><span class="icon"><ion-icon name="lock-closed"></ion-icon>&nbsp; </span>Password</label>
                    <input type="password" placeholder="Enter password" name="password" required />
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
                if (isset($_GET['error']) && $_GET['error'] === 'password_incorrect') {
                    echo '<p style="color: red;">Invalid password. Please try again!</p>';
                }
                ?>
            </form>
        </div>
    </div>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>
<?php
session_start();
include 'config.php';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare and execute the query
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $row['username'];
                header("Location: admin/index.php");
                exit();
            } else {
                echo '<div class="error-message">Invalid password!</div>';
            }
        } else {
            echo '<div class="error-message">No user found!</div>';
        }
        $stmt->close();
    } else {
        echo '<div class="error-message">Prepare failed: ' . htmlspecialchars($conn->error) . '</div>';
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin Login</title>
    <link href="styles.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2vw;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 5vw;
            border-radius: 2vw;
            box-shadow: 0 2vw 6vw rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 90vw;
            backdrop-filter: blur(1vw);
        }

        .login-header {
            text-align: center;
            margin-bottom: 3vw;
        }

        .login-header h2 {
            color: #333;
            font-size: 6vw;
            margin-bottom: 1.5vw;
        }

        .login-header p {
            color: #666;
            font-size: 3.5vw;
        }

        .form-group {
            margin-bottom: 3.5vw;
        }

        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 1.2vw;
            font-size: 3.5vw;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper input {
            width: 100%;
            padding: 2.5vw 3vw;
            border: 0.4vw solid #e1e8ed;
            border-radius: 2vw;
            font-size: 3.5vw;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 0.6vw rgba(102, 126, 234, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 2.5vw;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 2vw;
            font-size: 4vw;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-top: 1.5vw;
        }

        .btn-login:hover {
            transform: translateY(-0.4vw);
            box-shadow: 0 2vw 5vw rgba(102, 126, 234, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .back-link {
            text-align: center;
            margin-top: 3vw;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 3.5vw;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 2.5vw;
            border-radius: 1.5vw;
            margin-bottom: 3vw;
            font-size: 3.5vw;
            border-left: 0.8vw solid #c33;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 6vw 4vw;
                max-width: 95vw;
            }

            .login-header h2 {
                font-size: 7vw;
            }

            .login-header p {
                font-size: 4vw;
            }

            .form-group label {
                font-size: 4vw;
            }

            .input-wrapper input {
                padding: 3vw 3.5vw;
                font-size: 4vw;
                border-radius: 1.5vw;
            }

            .btn-login {
                padding: 3vw;
                font-size: 4.5vw;
                border-radius: 1.5vw;
            }

            .back-link a {
                font-size: 4vw;
            }

            .error-message {
                font-size: 4vw;
                padding: 3vw;
                border-radius: 1.2vw;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Admin Login</h2>
        </div>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
            </div>

            <button type="submit" name="login" class="btn-login">Sign In</button>
        </form>

        <div class="back-link">
            <a href="index.php">← Back to Shop</a>
        </div>
    </div>
    <script src="js/stylesjs.js"></script>
</body>
</html>

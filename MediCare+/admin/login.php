
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once '../config/admin_db.php';

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare(
            'SELECT id, username, password FROM admins WHERE username = ? LIMIT 1'
        );

        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();

            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();

            if ($admin && password_verify($password, $admin['password'])) {
                session_regenerate_id(true);

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];

                $stmt->close();
                $conn->close();

                header('Location: dashboard.php');
                exit;
            }

            $stmt->close();
        }

        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MediCare+ | Administrator Login</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: #f6fafb;
            color: #123b5d;
            display: flex;
            flex-direction: column;
        }

        .login-header {
            background: #ffffff;
            padding: 18px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            box-shadow: 0 3px 15px rgba(18, 59, 93, 0.08);
            border-bottom: 3px solid #1596a6;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #123b5d;
            text-decoration: none;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e7f7f8;
            color: #1596a6;
            font-size: 25px;
        }

        .brand-name {
            font-size: 23px;
            font-weight: bold;
        }

        .brand-name span {
            color: #1596a6;
        }

        .brand small {
            display: block;
            color: #71808a;
            font-size: 12px;
            margin-top: 3px;
        }

        .home-link {
            color: #123b5d;
            text-decoration: none;
            font-weight: bold;
        }

        .home-link i {
            color: #1596a6;
            margin-right: 6px;
        }

        .login-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 45px 20px;
        }

        .login-card {
            width: 100%;
            max-width: 480px;
            padding: 40px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 35px rgba(18, 59, 93, 0.10);
            border: 1px solid #e5eff1;
        }

        .login-heading {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo {
            font-size: 38px;
            color: #1596a6;
            margin-bottom: 10px;
        }

        .login-heading h1 {
            font-size: 27px;
            margin: 0 0 10px;
            color: #123b5d;
        }

        .login-heading p {
            margin: 0;
            color: #71808a;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 9px;
            color: #123b5d;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap > i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #71808a;
        }

        .form-group input {
            width: 100%;
            padding: 14px 44px;
            border: 1px solid #cfdadd;
            border-radius: 8px;
            background: #ffffff;
            font-size: 16px;
            color: #123b5d;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input:focus {
            border-color: #1596a6;
            box-shadow: 0 0 0 3px rgba(21, 150, 166, 0.12);
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #71808a;
            cursor: pointer;
            font-size: 16px;
            padding: 5px;
        }

        .error-message {
            background: #fff0f0;
            color: #b42318;
            border: 1px solid #f5c2c0;
            padding: 12px;
            border-radius: 7px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .login-submit {
            width: 100%;
            padding: 15px;
            background: #1596a6;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }

        .login-submit:hover {
            background: #123b5d;
        }

        .login-submit i {
            margin-right: 8px;
        }

        .login-footer {
            text-align: center;
            padding: 18px;
            color: #71808a;
            font-size: 13px;
        }

        @media (max-width: 520px) {
            .login-header {
                padding: 15px 5%;
            }

            .brand-name {
                font-size: 20px;
            }

            .login-main {
                padding: 25px 15px;
            }

            .login-card {
                padding: 28px 22px;
            }

            .login-heading h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

<header class="login-header">
    <a href="../index.html" class="brand">
        <div class="brand-icon">
            <i class="fa-solid fa-heart-pulse"></i>
        </div>

        <div>
            <div class="brand-name">MediCare<span>+</span></div>
            <small>Hospital &amp; Pharmacy</small>
        </div>
    </a>

    <a href="../index.html" class="home-link">
        <i class="fa-solid fa-house"></i> Back to Home
    </a>
</header>

<main class="login-main">
    <section class="login-card">

        <div class="login-heading">
            <div class="login-logo">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>

            <h1>Administrator Login</h1>
            <p>Sign in to manage your hospital and pharmacy system.</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="error-message" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">

            <div class="form-group">
                <label for="username">Username</label>

                <div class="input-wrap">
                    <i class="fa-solid fa-user"></i>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="username"
                        required
                        autofocus
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>

                <div class="input-wrap">
                    <i class="fa-solid fa-lock"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        type="button"
                        class="toggle-password"
                        id="togglePassword"
                        aria-label="Show password"
                    >
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="login-submit">
                <i class="fa-solid fa-right-to-bracket"></i>
                Login to Dashboard
            </button>

        </form>
    </section>
</main>

<footer class="login-footer">
    &copy; <?= date('Y') ?> MediCare+. All Rights Reserved.
</footer>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePassword.addEventListener('click', function () {
        const isPassword = passwordInput.type === 'password';

        passwordInput.type = isPassword ? 'text' : 'password';

        this.innerHTML = isPassword
            ? '<i class="fa-solid fa-eye-slash"></i>'
            : '<i class="fa-solid fa-eye"></i>';

        this.setAttribute(
            'aria-label',
            isPassword ? 'Hide password' : 'Show password'
        );
    });
</script>

</body>
</html>

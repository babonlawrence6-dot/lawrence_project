<?php
require_once 'includes/config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$error = '';
$debugLogin = false; // debugging disabled
$debugMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($debugLogin) {
            if ($user) {
                $debugMessage = 'DEBUG: user found. stored hash=' . $user['password'];
            } else {
                $debugMessage = 'DEBUG: user not found for username=' . $username;
            }
        }

        if ($user && password_verify($password, $user['password']) && $user['status'] === 'active') {
            // Login successful
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];

            header('Location: ' . BASE_URL . '/dashboard.php');
            exit;
        } elseif ($user && $user['status'] !== 'active') {
            $error = 'This account is inactive. Please contact an administrator.';
        } else {
            $error = 'Invalid username or password. Please try again.';
            if ($debugLogin) {
                $error .= ' ' . htmlspecialchars($debugMessage);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css?v=20261029-market-login">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <section class="login-brand" aria-label="Seven Evelyn supermarket">
                <img src="<?php echo BASE_URL; ?>/assets/seven-evelyn-logo.svg" alt="Seven Evelyn" class="login-logo">
                <div class="login-brand-copy">
                    <span class="login-brand-kicker">FRESH PICKS, EVERY DAY</span>
                    <h2>Your neighborhood<br>supermarket.</h2>
                    <p>Fresh finds and everyday essentials, all in one place.</p>
                </div>
                <span class="login-brand-footer"><i class="fas fa-basket-shopping"></i> Fresh · Local · Seven Evelyn</span>
            </section>

            <div class="login-panel">
                <div class="login-header">
                    <span class="login-eyebrow">WELCOME BACK</span>
                    <h1>Sign in</h1>
                    <p>Enter your account details to continue.</p>
                </div>

                <?php if ($error): ?>
                <div class="alert alert-error" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="" class="login-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="login-input-wrap">
                            <i class="fas fa-user" aria-hidden="true"></i>
                            <input type="text" id="username" name="username" placeholder="Enter your username" autocomplete="username" required autofocus>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="login-input-wrap">
                            <i class="fas fa-lock" aria-hidden="true"></i>
                            <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block login-submit">
                        Sign in <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>
                <p class="login-panel-footer">Secure access for Seven Evelyn team members</p>
            </div>
        </div>
    </div>
</body>
</html>

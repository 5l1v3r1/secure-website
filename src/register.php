<?php
include_once './php/user.class.php';
include_once './php/session.class.php';
include_once './php/database.class.php';
$user = Session::getUser();
if ($user) {
    header('Location: ./');
    exit();
}
$inputValues = array(
    'token' => '',
    'username' => '',
    'email' => ''
);
$errorMessages = array(
    'global' => '',
    'token' => '',
    'username' => '',
    'email' => '',
    'password' => '',
    'confirmPassword' => ''
);
if (isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
    if (isset($_POST['token']) && isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirmPassword'])) {
        $inputValues['username'] = htmlentities($_POST['username'], ENT_QUOTES, 'UTF-8');
        $inputValues['email'] = htmlentities($_POST['email'], ENT_QUOTES, 'UTF-8');
        $parameters = array(
            'token' => trim($_POST['token']),
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'password' => $_POST['password'],
            'confirmPassword' => $_POST['confirmPassword']
        );
        mb_internal_encoding('UTF-8');
        $error = false;
        if (mb_strlen($parameters['token']) < 1) {
            $errorMessages['token'] = 'Form token was not supplied';
            $error = true;
        } else if (!Session::verifyToken($parameters['token'], 'register')) {
            $errorMessages['token'] = 'Form token is invalid or has expired';
            $error = true;
        }
        if (mb_strlen($parameters['username']) < 1) {
            $errorMessages['username'] = 'Please enter username';
            $error = true;
        } else if (mb_strlen($parameters['username']) > 30) {
            $errorMessages['username'] = 'Username is exceeding 30 characters';
            $error = true;
        } else {
            $exp = '/^[a-zA-Z0-9!#%?*_]+$/';
            if (!preg_match($exp, $parameters['username'])) {
                $errorMessages['username'] = 'Username contains forbidden characters';
                $error = true;
            } else {
                $db = new Database();
                if ($db->isConnected()) {
                    $db->query('SELECT `username` FROM `users` WHERE LOWER(`username`) = :username');
                    $db->bind(':username', strtolower($parameters['username']));
                    if ($db->execute()) {
                        $count = $db->rowCount();
                        if ($count > 0) {
                            $errorMessages['username'] = 'Username already exists';
                            $error = true;
                        } else if ($count < 0) {
                            $errorMessages['global'] = 'Database error';
                            $errorMessages['username'] = 'Cannot verify username';
                            $error = true;
                        }
                    } else {
                        $errorMessages['global'] = 'Database error';
                        $errorMessages['username'] = 'Cannot verify username';
                        $error = true;
                    }
                } else {
                    $errorMessages['global'] = 'Database error';
                    $errorMessages['username'] = 'Cannot verify username';
                    $error = true;
                }
                $db->disconnect();
            }
        }
        if (mb_strlen($parameters['email']) < 1) {
            $errorMessages['email'] = 'Please enter email';
            $error = true;
        } else if (mb_strlen($parameters['email']) > 254) {
            $errorMessages['email'] = 'Email is exceeding 254 characters';
            $error = true;
        } else {
            $exp = '/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';
            if (!preg_match($exp, $parameters['email'])) {
                $errorMessages['email'] = 'Email format is not supported';
                $error = true;
            } else {
                $db = new Database();
                if ($db->isConnected()) {
                    $db->query('SELECT `email` FROM `users` WHERE LOWER(`email`) = :email');
                    $db->bind(':email', strtolower($parameters['email']));
                    if ($db->execute()) {
                        $count = $db->rowCount();
                        if ($count > 0) {
                            $errorMessages['email'] = 'Email already exists';
                            $error = true;
                        } else if ($count < 0) {
                            $errorMessages['global'] = 'Database error';
                            $errorMessages['email'] = 'Cannot verify email';
                            $error = true;
                        }
                    } else {
                        $errorMessages['global'] = 'Database error';
                        $errorMessages['email'] = 'Cannot verify email';
                        $error = true;
                    }
                } else {
                    $errorMessages['global'] = 'Database error';
                    $errorMessages['email'] = 'Cannot verify email';
                    $error = true;
                }
                $db->disconnect();
            }
        }
        if (mb_strlen($parameters['password']) < 1) {
            $errorMessages['password'] = 'Please enter password';
            $error = true;
        } else if (mb_strlen($parameters['password']) < 10) {
            $errorMessages['password'] = 'Password must be at least 10 characters long';
            $error = true;
        } else if (mb_strlen($parameters['password']) > 72) {
            $errorMessages['password'] = 'Password is exceeding 72 characters';
            $error = true;
        }
        if (mb_strlen($parameters['confirmPassword']) < 1) {
            $errorMessages['confirmPassword'] = 'Please confirm password';
            $error = true;
        } else if (mb_strlen($parameters['confirmPassword']) > 72) {
            $errorMessages['confirmPassword'] = 'Confirmed password is exceeding 72 characters';
            $error = true;
        } else if ($parameters['confirmPassword'] !== $parameters['password']) {
            $errorMessages['confirmPassword'] = 'Password and confirmed password do not match';
            $error = true;
        }
        // implement CAPTCHA
        if (!$error) {
            $hash = password_hash($parameters['password'], PASSWORD_BCRYPT, array('cost' => 12));
            if ($hash) {
                $db = new Database();
                if ($db->isConnected()) {
                    $db->query('INSERT INTO `users` (`username`, `email`, `password`, `date_created`) VALUES (:username, :email, :password, :date_created)');
                    $db->bind(':username', $parameters['username']);
                    $db->bind(':email', strtolower($parameters['email']));
                    $db->bind(':password', $hash);
                    $db->bind(':date_created', date('Y-m-d', time()));
                    if ($db->execute()) {
                        $db->disconnect();
                        // implement email verification
                        header('Location: ./sign_in.php');
                        exit();
                    } else {
                        $errorMessages['global'] = 'Database error';
                    }
                } else {
                    $errorMessages['global'] = 'Database error';
                }
                $db->disconnect();
            } else {
                $errorMessages['global'] = 'Server error';
            }
        }
    } else {
        $errorMessages['global'] = 'Required data is missing';
    }
}
$inputValues['token'] = Session::generateToken('register');
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Register | Secure Website</title>
		<meta name="description" content="Become a member.">
		<meta name="keywords" content="HTML, CSS, PHP, JavaScript, jQuery, secure, website">
		<meta name="author" content="Ivan Šincek">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" href="./img/favicon.ico">
		<link rel="stylesheet" href="./css/main.css" hreflang="en" type="text/css" media="all">
		<script src="./js/jquery-3.3.1.js"></script>
		<script src="./js/main.js" defer></script>
	</head>
	<body class="background-img">
		<?php include_once './components/navigation.php'; ?>
		<div class="front-form">
			<div class="layout">
				<header>
					<h1 class="title">Register</h1>
				</header>
				<p class="error-global"><?php echo $errorMessages['global']; ?></p>
				<p class="error-global"><?php echo $errorMessages['token']; ?></p>
				<form method="post" action="./register.php">
					<input name="token" id="token" type="hidden" value="<?php echo $inputValues['token']; ?>">
					<div class="label-info">
						<label for="username">Username</label>
						<div class="info">
							<input id="usernameDropdown" type="checkbox" class="info-checkbox">
							<label for="usernameDropdown" class="info-toogle">
								<img src="./img/info.png" alt="Info">
							</label>
							<ul>
								<li><p>Required</p></li>
								<li><p>Max. 30 characters</p></li>
								<li><p>Allowed ! # % ? * _</p></li>
							</ul>
						</div>
					</div>
					<input name="username" id="username" type="text" spellcheck="false" maxlength="30" pattern="^[a-zA-Z0-9!#%?*_]+$" required="required" autofocus="autofocus" value="<?php echo $inputValues['username']; ?>">
					<p class="error"><?php echo $errorMessages['username']; ?></p>
					<div class="label-info">
						<label for="email">Email</label>
						<div class="info">
							<input id="emailDropdown" type="checkbox" class="info-checkbox">
							<label for="emailDropdown" class="info-toogle">
								<img src="./img/info.png" alt="Info">
							</label>
							<ul>
								<li><p>Required</p></li>
								<li><p>Max. 254 characters</p></li>
								<li><p>Valid format</p></li>
							</ul>
						</div>
					</div>
					<input name="email" id="email" type="text" spellcheck="false" maxlength="254" pattern="^(([^<>()\[\]\\.,;:\s@\u0022]+(\.[^<>()\[\]\\.,;:\s@\u0022]+)*)|(\u0022.+\u0022))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$" required="required" value="<?php echo $inputValues['email']; ?>">
					<p class="error"><?php echo $errorMessages['email']; ?></p>
					<div class="label-info">
						<label for="password">Password</label>
						<div class="info">
							<input id="passwordDropdown" type="checkbox" class="info-checkbox">
							<label for="passwordDropdown" class="info-toogle">
								<img src="./img/info.png" alt="Info">
							</label>
							<ul>
								<li><p>Required</p></li>
								<li><p>Min. 10 characters</p></li>
								<li><p>Max. 72 characters</p></li>
							</ul>
						</div>
					</div>
					<input name="password" id="password" type="password" autocomplete="off" maxlength="72" required="required">
					<p class="error"><?php echo $errorMessages['password']; ?></p>
					<div class="label-info">
						<label for="confirmPassword">Confirm password</label>
						<div class="info">
							<input id="confirmPasswordDropdown" type="checkbox" class="info-checkbox">
							<label for="confirmPasswordDropdown" class="info-toogle">
								<img src="./img/info.png" alt="Info">
							</label>
							<ul>
								<li><p>Required</p></li>
								<li><p>Max. 72 characters</p></li>
								<li><p>Match password</p></li>
							</ul>
						</div>
					</div>
					<input name="confirmPassword" id="confirmPassword" type="password" autocomplete="off" maxlength="72" required="required">
					<p class="error"><?php echo $errorMessages['confirmPassword']; ?></p>
					<input type="submit" value="Register">
				</form>
				<ul class="links">
					<li><a href="./sign_in.php">Already have an account?</a></li>
				</ul>
			</div>
		</div>
		<?php include_once './components/footer.php'; ?>
	</body>
</html>

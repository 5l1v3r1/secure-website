<?php
$depth = '../';
include_once '../php/user.class.php';
include_once '../php/session.class.php';
include_once '../php/database.class.php';
$user = Session::getUser();
if (!$user || $user->getRole() != 1) {
    header('Location: ../');
    exit();
}
$inputValues = array(
    'token' => '',
    'id' => '',
    'username' => '',
    'email' => '',
    'activated' => '',
    'banned' => '',
    'role' => ''
);
$errorMessages = array(
    'global' => '',
    'uri' => '',
    'token' => '',
    'id' => '',
    'username' => '',
    'email' => '',
    'password' => '',
    'confirmPassword' => '',
    'role' => ''
);
$uniqueValues = array(
    'username' => '',
    'email' => ''
);
$uriError = false;
if (isset($_GET['id'])) {
    if (is_numeric($_GET['id'])) {
        $db = new Database();
        if ($db->isConnected()) {
            $db->query('SELECT `id`, `username`, `email`, `activated`, `sign_in_count`, `locked_until`, `banned`, `role_id` FROM `users` WHERE `id` = :id');
            $db->bind(':id', $_GET['id']);
            if ($db->execute()) {
                $count = $db->rowCount();
                if ($count > 0) {
                    $data = $db->fetch();
                    if ($data) {
                        $inputValues['id'] = htmlentities($data['id'], ENT_QUOTES, 'UTF-8');
                        $inputValues['username'] = htmlentities($data['username'], ENT_QUOTES, 'UTF-8');
                        $uniqueValues['username'] = $inputValues['username'];
                        $inputValues['email'] = htmlentities($data['email'], ENT_QUOTES, 'UTF-8');
                        $uniqueValues['email'] = $inputValues['email'];
                        $inputValues['activated'] = $data['activated'];
                        $inputValues['sign_in_count'] = htmlentities($data['sign_in_count'], ENT_QUOTES, 'UTF-8');
                        $inputValues['locked_until'] = htmlentities($data['locked_until'], ENT_QUOTES, 'UTF-8');
                        $inputValues['banned'] = $data['banned'];
                        $inputValues['role'] = htmlentities($data['role_id'], ENT_QUOTES, 'UTF-8');
                    } else {
                        $errorMessages['global'] = 'Database error';
                        $errorMessages['uri'] = 'Cannot verify URI ID';
                        $uriError = true;
                    }
                } else if ($count === 0) {
                    $errorMessages['uri'] = 'URI ID does not exists';
                    $uriError = true;
                } else {
                    $errorMessages['global'] = 'Database error';
                    $errorMessages['uri'] = 'Cannot verify URI ID';
                    $uriError = true;
                }
            } else {
                $errorMessages['global'] = 'Database error';
                $errorMessages['uri'] = 'Cannot verify URI ID';
                $uriError = true;
            }
        } else {
            $errorMessages['global'] = 'Database error';
            $errorMessages['uri'] = 'Cannot verify URI ID';
            $uriError = true;
        }
        $db->disconnect();
    } else {
        $errorMessages['uri'] = 'URI ID does not match a numeric value';
        $uriError = true;
    }
} else {
    $errorMessages['uri'] = 'URI ID is missing';
    $uriError = true;
}
if (!$uriError) {
    if (isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
        if (isset($_POST['token']) && isset($_POST['id']) && isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirmPassword']) && isset($_POST['role'])) {
            $inputValues['id'] = htmlentities($_POST['id'], ENT_QUOTES, 'UTF-8');
            $inputValues['username'] = htmlentities($_POST['username'], ENT_QUOTES, 'UTF-8');
            $inputValues['email'] = htmlentities($_POST['email'], ENT_QUOTES, 'UTF-8');
            $inputValues['role'] = htmlentities($_POST['role'], ENT_QUOTES, 'UTF-8');
            $inputValues['activated'] = isset($_POST['activated']);
            $inputValues['banned'] = isset($_POST['banned']);
            $inputValues['role'] = $_POST['role'];
            $parameters = array(
                'token' => trim($_POST['token']),
                'id' => trim($_POST['id']),
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                'password' => $_POST['password'],
                'confirmPassword' => $_POST['confirmPassword'],
                'activated' => isset($_POST['activated']),
                'banned' => isset($_POST['banned']),
                'role' => trim($_POST['role'])
            );
            mb_internal_encoding('UTF-8');
            $error = false;
            if (mb_strlen($parameters['token']) < 1) {
                $errorMessages['token'] = 'Form token was not supplied';
                $error = true;
            } else if (!Session::verifyToken($parameters['token'], 'edit_user')) {
                $errorMessages['token'] = 'Form token is invalid or has expired';
                $error = true;
            }
            if (mb_strlen($parameters['id']) < 1) {
                $errorMessages['id'] = 'ID was not supplied';
                $error = true;
            } else if (!is_numeric($parameters['id'])) {
                $errorMessages['id'] = 'ID does not match a numeric value';
                $error = true;
            } else if ($parameters['id'] !== $_GET['id']) {
                $errorMessages['id'] = 'ID does not match URI ID';
                $error = true;
            }
            if (mb_strlen($parameters['username']) < 1) {
                $errorMessages['username'] = 'Please enter username';
                $error = true;
            } else if (mb_strlen($parameters['username']) > 30) {
                $errorMessages['username'] = 'Username is exceeding 30 characters';
                $error = true;
            } else if ($uniqueValues['username'] !== $parameters['username']) {
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
                } else if ($uniqueValues['email'] !== $parameters['email']) {
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
            if (mb_strlen($parameters['password']) > 0) {
                if (mb_strlen($parameters['password']) < 10) {
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
                } else {
                    $parameters['password'] = password_hash($parameters['password'], PASSWORD_BCRYPT, array('cost' => 12));
                }
            } else if (mb_strlen($parameters['confirmPassword']) > 0) {
                $errorMessages['confirmPassword'] = 'Please enter password';
                $error = true;
            }
            if (mb_strlen($parameters['role']) < 1) {
                $errorMessages['role'] = 'Please select role';
                $error = true;
            } else if (!is_numeric($parameters['role'])) {
                $errorMessages['role'] = 'Role does not match a numeric value';
                $error = true;
            } else {
                $db = new Database();
                if ($db->isConnected()) {
                    $db->query('SELECT `name` FROM `roles` WHERE LOWER(`id`) = :id');
                    $db->bind(':id', strtolower($parameters['role']));
                    if ($db->execute()) {
                        $count = $db->rowCount();
                        if ($count === 0) {
                            $errorMessages['role'] = 'Role does not exists';
                            $error = true;
                        } else if ($count < 0) {
                            $errorMessages['global'] = 'Database error';
                            $errorMessages['role'] = 'Cannot verify role';
                            $error = true;
                        }
                    } else {
                        $errorMessages['global'] = 'Database error';
                        $errorMessages['role'] = 'Cannot verify role';
                        $error = true;
                    }
                } else {
                    $errorMessages['global'] = 'Database error';
                    $errorMessages['role'] = 'Cannot verify role';
                    $error = true;
                }
                $db->disconnect();
            }
            if (!$error) {
                $hash = password_hash($parameters['password'], PASSWORD_BCRYPT, array('cost' => 12));
                if ($hash) {
                    $db = new Database();
                    if ($db->isConnected()) {
                        if ($parameters['password']) {
                            $db->query('UPDATE `users` SET `username` = :username, `email` = :email, `activated` = :activated, `banned` = :banned , `role_id` = :role_id, `password` = :password WHERE `id` = :id');
                            $db->bind(':password', $parameters['password']);
                        } else {
                            $db->query('UPDATE `users` SET `username` = :username, `email` = :email, `activated` = :activated, `banned` = :banned , `role_id` = :role_id WHERE `id` = :id');
                        }
                        $db->bind(':id', $parameters['id']);
                        $db->bind(':username', $parameters['username']);
                        $db->bind(':email', strtolower($parameters['email']));
                        $db->bind(':activated', $parameters['activated']);
                        $db->bind(':banned', $parameters['banned']);
                        $db->bind(':role_id', $parameters['role']);
                        if ($db->execute()) {
                            $db->disconnect();
                            header('Location: ./');
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
            $errorMessages['global'] = 'Not all required data are submitted';
        }
    }
}
$db = new Database();
if ($db->isConnected()) {
    $db->query('SELECT `id`, `name` FROM `roles`');
    if ($db->execute()) {
        $count = $db->rowCount();
        if ($count > 0) {
            $roles = $db->fetchAll();
            if ($roles) {
                $sanitised = array();
                foreach ($roles as $role) {
                    $role['id'] = htmlentities($role['id'], ENT_QUOTES, 'UTF-8');
                    $role['name'] = htmlentities($role['name'], ENT_QUOTES, 'UTF-8');
                    array_push($sanitised, $role);
                }
                $roles = $sanitised;
            }
        } else if ($count < 0) {
            $errorMessages['global'] = 'Database error';
        }
    }
}
$inputValues['token'] = Session::generateToken('edit_user');
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Edit User | Secure Website</title>
		<meta name="description" content="Edit existing user.">
		<meta name="keywords" content="HTML, CSS, PHP, JavaScript, jQuery, secure, website">
		<meta name="author" content="Ivan Šincek">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" href="../img/favicon.ico">
		<link rel="stylesheet" href="../css/main.css" hreflang="en" type="text/css" media="all">
		<script src="../js/jquery-3.3.1.js"></script>
		<script src="../js/main.js" defer></script>
	</head>
	<body>
		<?php include_once '../components/navigation.php'; ?>
		<div class="crud-add-edit">
			<p class="error-global"><?php echo $errorMessages['global']; ?></p>
			<p class="error-global"><?php echo $errorMessages['uri']; ?></p>
			<p class="error-global"><?php echo $errorMessages['token']; ?></p>
			<form id="edit-form" method="post" action="./edit.php?id=<?php echo $uriError ? '' : htmlentities($_GET['id'], ENT_QUOTES, 'UTF-8'); ?>">
				<input name="token" id="token" type="hidden" value="<?php echo $inputValues['token']; ?>">
				<div class="data-row">
					<label for="id">ID</label>
					<input name="id" id="id" type="text" readonly="readonly" value="<?php echo $inputValues['id']; ?>">
					<p class="error"><?php echo $errorMessages['id']; ?></p>
				</div>
				<div class="data-row">
					<label for="username">Username</label>
					<input name="username" id="username" type="text" spellcheck="false" maxlength="30" pattern="^[a-zA-Z0-9!#%?*_]+$" required="required" value="<?php echo $inputValues['username']; ?>">
					<p class="error"><?php echo $errorMessages['username']; ?></p>
				</div>
				<div class="data-row">
					<label for="username">Email</label>
					<input name="email" id="email" type="text" spellcheck="false" maxlength="254" pattern="^(([^<>()\[\]\\.,;:\s@\u0022]+(\.[^<>()\[\]\\.,;:\s@\u0022]+)*)|(\u0022.+\u0022))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$" required="required" value="<?php echo $inputValues['email']; ?>">
					<p class="error"><?php echo $errorMessages['email']; ?></p>
				</div>
				<div class="data-row">
					<label for="username">Password</label>
					<input name="password" id="password" type="password" maxlength="72" autocomplete="off" placeholder="Enter to change">
					<p class="error"><?php echo $errorMessages['password']; ?></p>
				</div>
				<div class="data-row">
					<label for="confirmPassword">Confirm password</label>
					<input name="confirmPassword" id="confirmPassword" type="password" maxlength="72" autocomplete="off" placeholder="Confirm to change">
					<p class="error"><?php echo $errorMessages['confirmPassword']; ?></p>
				</div>
				<div class="data-checkbox">
					<label for="activated">Activated</label>
					<input name="activated" id="activated" type="checkbox"<?php echo $inputValues['activated'] ? ' checked="checked"' : ''; ?>>
				</div>
				<div class="data-checkbox">
					<label for="banned">Banned</label>
					<input name="banned" id="banned" type="checkbox"<?php echo $inputValues['banned'] ? ' checked="checked"' : ''; ?>>
				</div>
				<div class="data-row">
					<label for="role">Role</label>
					<select name="role" id="role" required="required">
						<option value="">none</option>
						<?php foreach ($roles as $role): ?>
							<option value="<?php echo $role['id']; ?>"<?php echo $role['id'] === $inputValues['role'] ? ' selected="selected"' : ''; ?>><?php echo $role['name']; ?></option>
						<?php endforeach ?>
					</select>
					<p class="error"><?php echo $errorMessages['role']; ?></p>
				</div>
				<div class="btn">
					<input type="submit" value="Edit"<?php echo $uriError ? ' disabled="disabled"' : ''; ?>>
				</div>
			</form>
		</div>
		<?php include_once '../components/footer.php'; ?>
	</body>
</html>

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
    'name' => '',
    'description' => ''
);
$errorMessages = array(
    'global' => '',
    'token' => '',
    'name' => '',
    'description' => ''
);
if (isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
    if (isset($_POST['token']) && isset($_POST['name']) && isset($_POST['description'])) {
        $inputValues['name'] = htmlentities($_POST['name'], ENT_QUOTES, 'UTF-8');
        $inputValues['description'] = htmlentities($_POST['description'], ENT_QUOTES, 'UTF-8');
        $parameters = array(
            'token' => trim($_POST['token']),
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description'])
        );
        mb_internal_encoding('UTF-8');
        $error = false;
        if (mb_strlen($parameters['token']) < 1) {
            $errorMessages['token'] = 'Form token was not supplied';
            $error = true;
        } else if (!Session::verifyToken($parameters['token'], 'add_role')) {
            $errorMessages['token'] = 'Form token is invalid or has expired';
            $error = true;
        }
        if (mb_strlen($parameters['name']) < 1) {
            $errorMessages['name'] = 'Please enter name';
            $error = true;
        } else if (mb_strlen($parameters['name']) > 20) {
            $errorMessages['name'] = 'Name is exceeding 20 characters';
            $error = true;
        } else {
            $db = new Database();
            if ($db->isConnected()) {
                $db->query('SELECT `name` FROM `roles` WHERE LOWER(`name`) = :name');
                $db->bind(':name', strtolower($parameters['name']));
                if ($db->execute()) {
                    $count = $db->rowCount();
                    if ($count > 0) {
                        $errorMessages['name'] = 'Name already exists';
                        $error = true;
                    } else if ($count < 0) {
                        $errorMessages['global'] = 'Database error';
                        $errorMessages['name'] = 'Cannot verify name';
                        $error = true;
                    }
                } else {
                    $errorMessages['global'] = 'Database error';
                    $errorMessages['name'] = 'Cannot verify name';
                    $error = true;
                }
            } else {
                $errorMessages['global'] = 'Database error';
                $errorMessages['name'] = 'Cannot verify name';
                $error = true;
            }
            $db->disconnect();
        }
        if (mb_strlen($parameters['description']) < 1) {
            $errorMessages['description'] = 'Please enter description';
            $error = true;
        } else if (mb_strlen($parameters['description']) > 300) {
            $errorMessages['description'] = 'Description is exceeding 300 characters';
            $error = true;
        }
        if (!$error) {
            $db = new Database();
            if ($db->isConnected()) {
                $db->query('INSERT INTO `roles` (`name`, `description`) VALUES (:name, :description)');
                $db->bind(':name', $parameters['name']);
                $db->bind(':description', $parameters['description']);
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
        }
    } else {
        $errorMessages['global'] = 'Required data is missing';
    }
}
$inputValues['token'] = Session::generateToken('add_role');
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Add Role | Secure Website</title>
		<meta name="description" content="Add new role.">
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
			<p class="error-global"><?php echo $errorMessages['token']; ?></p>
			<form id="add-form" method="post" action="./add.php">
				<input name="token" id="token" type="hidden" value="<?php echo $inputValues['token']; ?>">
				<div class="data-row">
					<label for="name">Name</label>
					<input name="name" id="name" type="text" spellcheck="false" maxlength="20" required="required" autofocus="autofocus" value="<?php echo $inputValues['name']; ?>">
					<p class="error"><?php echo $errorMessages['name']; ?></p>
				</div>
				<div class="data-row">
					<label for="description">Description</label>
					<textarea name="description" id="description" form="add-form" rows="6" required="required"><?php echo $inputValues['description']; ?></textarea>
					<p class="error"><?php echo $errorMessages['description']; ?></p>
				</div>
				<div class="btn">
					<input type="submit" value="Add">
				</div>
			</form>
		</div>
		<?php include_once '../components/footer.php'; ?>
	</body>
</html>

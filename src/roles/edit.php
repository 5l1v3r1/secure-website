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
    'name' => '',
    'description' => ''
);
$errorMessages = array(
    'global' => '',
    'uri' => '',
    'token' => '',
    'name' => '',
    'description' => ''
);
$uniqueValues = array(
    'name' => ''
);
$uriError = false;
if (isset($_GET['id'])) {
    if (is_numeric($_GET['id'])) {
        $db = new Database();
        if ($db->isConnected()) {
            $db->query('SELECT `id`, `name`, `description` FROM `roles` WHERE `id` = :id');
            $db->bind(':id', $_GET['id']);
            if ($db->execute()) {
                $count = $db->rowCount();
                if ($count > 0) {
                    $data = $db->fetch();
                    if ($data) {
                        $inputValues['id'] = htmlentities($data['id'], ENT_QUOTES, 'UTF-8');
                        $inputValues['name'] = htmlentities($data['name'], ENT_QUOTES, 'UTF-8');
                        $uniqueValues['name'] = $inputValues['name'];
                        $inputValues['description'] = htmlentities($data['description'], ENT_QUOTES, 'UTF-8');
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
        if (isset($_POST['token']) && isset($_POST['id']) && isset($_POST['name']) && isset($_POST['description'])) {
            $inputValues['id'] = htmlentities($_POST['id'], ENT_QUOTES, 'UTF-8');
            $inputValues['name'] = htmlentities($_POST['name'], ENT_QUOTES, 'UTF-8');
            $inputValues['description'] = htmlentities($_POST['description'], ENT_QUOTES, 'UTF-8');
            $parameters = array(
                'token' => trim($_POST['token']),
                'id' => trim($_POST['id']),
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description'])
            );
            mb_internal_encoding('UTF-8');
            $error = false;
            if (mb_strlen($parameters['token']) < 1) {
                $errorMessages['token'] = 'Form token was not supplied';
                $error = true;
            } else if (!Session::verifyToken($parameters['token'], 'edit_role')) {
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
            if (mb_strlen($parameters['name']) < 1) {
                $errorMessages['name'] = 'Please enter name';
                $error = true;
            } else if (mb_strlen($parameters['name']) > 20) {
                $errorMessages['name'] = 'Name is exceeding 20 characters';
                $error = true;
            } else if ($uniqueValues['name'] !== $parameters['name']) {
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
                    $db->query('UPDATE `roles` SET `name` = :name, `description` = :description WHERE `id` = :id');
                    $db->bind(':id', $parameters['id']);
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
}
$inputValues['token'] = Session::generateToken('edit_role');
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Edit Role | Secure Website</title>
		<meta name="description" content="Edit existing role.">
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
					<label for="name">Name</label>
					<input name="name" id="name" type="text" spellcheck="false" maxlength="20" required="required" value="<?php echo $inputValues['name']; ?>">
					<p class="error"><?php echo $errorMessages['name']; ?></p>
				</div>
				<div class="data-row">
					<label for="description">Description</label>
					<textarea name="description" id="description" form="edit-form" rows="6" required="required"><?php echo $inputValues['description']; ?></textarea>
					<p class="error"><?php echo $errorMessages['description']; ?></p>
				</div>
				<div class="btn">
					<input type="submit" value="Edit"<?php echo $uriError ? ' disabled="disabled"' : ''; ?>>
				</div>
			</form>
		</div>
		<?php include_once '../components/footer.php'; ?>
	</body>
</html>

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
$errorMessages = array(
    'global' => ''
);
if (isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) === 'get') {
    if (isset($_GET['id'])) {
        if (is_numeric($_GET['id'])) {
            $db = new Database();
            if ($db->isConnected()) {
                $db->query('SELECT `users`.`id`, `users`.`username`, `users`.`password`, `users`.`email`, `users`.`date_created`, `users`.`activated`, `users`.`sign_in_count`, `users`.`locked_until`, `users`.`banned`, `roles`.`name` AS `role` FROM `users` LEFT JOIN `roles` ON `users`.`role_id` = `roles`.`id` WHERE `users`.`id` = :id');
                $db->bind(':id', $_GET['id']);
                if ($db->execute()) {
                    $count = $db->rowCount();
                    if ($count > 0) {
                        $data = $db->fetch();
                        if ($data) {
                            $data['id'] = htmlentities($data['id'], ENT_QUOTES, 'UTF-8');
                            $data['username'] = htmlentities($data['username'], ENT_QUOTES, 'UTF-8');
                            $data['password'] = htmlentities($data['password'], ENT_QUOTES, 'UTF-8');
                            $data['email'] = htmlentities($data['email'], ENT_QUOTES, 'UTF-8');
                            $data['date_created'] = htmlentities($data['date_created'], ENT_QUOTES, 'UTF-8');
                            $data['activated'] = htmlentities($data['activated'], ENT_QUOTES, 'UTF-8');
                            $data['sign_in_count'] = htmlentities($data['sign_in_count'], ENT_QUOTES, 'UTF-8');
                            $data['locked_until'] = htmlentities($data['locked_until'], ENT_QUOTES, 'UTF-8');
                            $data['banned'] = htmlentities($data['banned'], ENT_QUOTES, 'UTF-8');
                            $data['role'] = htmlentities($data['role'], ENT_QUOTES, 'UTF-8');
                        } else {
                            $errorMessages['global'] = 'Database error';
                        }
                    } else if ($count === 0) {
                        $errorMessages['global'] = 'ID does not exists';
                    } else {
                        $errorMessages['global'] = 'Database error';
                    }
                } else {
                    $errorMessages['global'] = 'Database error';
                }
            } else {
                $errorMessages['global'] = 'Database error';
            }
            $db->disconnect();
        } else {
            $errorMessages['global'] = 'ID does not match a numeric value';
        }
    } else {
        $errorMessages['global'] = 'ID is missing';
    }
} else {
    $errorMessages['global'] = 'Bad request';
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>View User | Secure Website</title>
		<meta name="description" content="View user data.">
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
		<div class="crud-view">
			<p class="error-global"><?php echo $errorMessages['global']; ?></p>
			<div class="data-row">
				<p class="label">ID</p>
				<p class="value"><?php echo $data['id']; ?></p>
			</div>
			<div class="data-row">
				<p class="label">Username</p>
				<p class="value"><?php echo $data['username']; ?></p>
			</div>
			<div class="data-row">
				<p class="label">Password</p>
				<p class="value long"><?php echo $data['password']; ?></p>
			</div>
			<div class="data-row">
				<p class="label">Email</p>
				<p class="value long"><?php echo $data['email']; ?></p>
			</div>
			<div class="data-row">
				<p class="label">Date created</p>
				<p class="value"><?php echo $data['date_created']; ?></p>
			</div>
			<div class="data-row">
				<p class="label">Activated</p>
				<p class="value"><?php echo $data['activated']; ?></p>
			</div>
			<div class="data-row">
				<p class="label">Sign in count</p>
				<p class="value"><?php echo $data['sign_in_count']; ?></p>
			</div>
			<div class="data-row">
				<p class="label">Locked until</p>
				<p class="value"><?php echo $data['locked_until']; ?></p>
			</div>
			<div class="data-row">
				<p class="label">Banned</p>
				<p class="value"><?php echo $data['banned']; ?></p>
			</div>
			<div class="data-row">
				<p class="label">Role</p>
				<p class="value"><?php echo $data['role']; ?></p>
			</div>
		</div>
		<?php include_once '../components/footer.php'; ?>
	</body>
</html>

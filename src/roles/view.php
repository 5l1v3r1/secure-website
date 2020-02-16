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
                $db->query('SELECT `id`, `name`, `description` FROM `roles` WHERE `id` = :id');
                $db->bind(':id', $_GET['id']);
                if ($db->execute()) {
                    $count = $db->rowCount();
                    if ($count > 0) {
                        $data = $db->fetch();
                        if ($data) {
                            $data['id'] = htmlentities($data['id'], ENT_QUOTES, 'UTF-8');
                            $data['name'] = htmlentities($data['name'], ENT_QUOTES, 'UTF-8');
                            $data['description'] = htmlentities($data['description'], ENT_QUOTES, 'UTF-8');
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
		<title>View Role | Secure Website</title>
		<meta name="description" content="View role data.">
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
				<p class="label">Name</p>
				<p class="value"><?php echo $data['name']; ?></p>
			</div>
			<div class="data-row">
				<p class="label">Description</p>
				<p class="value"><?php echo $data['description']; ?></p>
			</div>
		</div>
		<?php include_once '../components/footer.php'; ?>
	</body>
</html>

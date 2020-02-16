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
$db = new Database();
if ($db->isConnected()) {
    $db->query('SELECT `users`.`id`, `users`.`username`, `users`.`password`, `users`.`email`, `users`.`date_created`, `users`.`activated`, `users`.`sign_in_count`, `users`.`banned`, `roles`.`name` AS `role` FROM `users` LEFT JOIN `roles` ON `users`.`role_id` = `roles`.`id`');
    if ($db->execute()) {
        $count = $db->rowCount();
        if ($count > 0) {
            $data = $db->fetchAll();
            if ($data) {
                $sanitised = array();
                foreach ($data as $d) {
                    $d['id'] = htmlentities($d['id'], ENT_QUOTES, 'UTF-8');
                    $d['username'] = htmlentities($d['username'], ENT_QUOTES, 'UTF-8');
                    $d['password'] = htmlentities($d['password'], ENT_QUOTES, 'UTF-8');
                    $d['email'] = htmlentities($d['email'], ENT_QUOTES, 'UTF-8');
                    $d['date_created'] = htmlentities($d['date_created'], ENT_QUOTES, 'UTF-8');
                    $d['activated'] = htmlentities($d['activated'], ENT_QUOTES, 'UTF-8');
                    $d['sign_in_count'] = htmlentities($d['sign_in_count'], ENT_QUOTES, 'UTF-8');
                    $d['banned'] = htmlentities($d['banned'], ENT_QUOTES, 'UTF-8');
                    $d['role'] = htmlentities($d['role'], ENT_QUOTES, 'UTF-8');
                    array_push($sanitised, $d);
                }
                $data = $sanitised;
            } else {
                $errorMessages['global'] = 'Database error';
            }
        } else if ($count === 0) {
            $errorMessages['global'] = 'Table is empty';
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
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Users | Secure Website</title>
		<meta name="description" content="View users table.">
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
		<div class="crud-list">
			<header>
				<h1 class="title">Users</h1>
			</header>
			<a href="./add.php" class="add-new">Add new</a>
			<p class="error-global"><?php echo $errorMessages['global']; ?></p>
			<div class="crud-table">
				<table>
					<thead>
						<tr>
							<th>ID</th>
							<th>Username</th>
							<th>Password</th>
							<th>Email</th>
							<th>Date created</th>
							<th>Activated</th>
							<th>Sign in count</th>
							<th>Banned</th>
							<th>Role</th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php if ($data): ?>
							<?php foreach ($data as $d): ?>
								<tr>
									<td class="right"><?php echo $d['id']; ?></td>
									<td><?php echo $d['username']; ?></td>
									<td><?php echo $d['password']; ?></td>
									<td><?php echo $d['email']; ?></td>
									<td class="center"><?php echo $d['date_created']; ?></td>
									<td class="center"><?php echo $d['activated']; ?></td>
									<td class="center"><?php echo $d['sign_in_count']; ?></td>
									<td class="center"><?php echo $d['banned']; ?></td>
									<td><?php echo $d['role']; ?></td>
									<td class="center"><a href="./view.php?id=<?php echo $d['id']; ?>">View</a></td>
									<td class="center"><a href="./edit.php?id=<?php echo $d['id']; ?>">Edit</a></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php include_once '../components/footer.php'; ?>
	</body>
</html>

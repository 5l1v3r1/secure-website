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
    $db->query('SELECT `id`, `name`, `description` FROM `roles`');
    if ($db->execute()) {
        $count = $db->rowCount();
        if ($count > 0) {
            $data = $db->fetchAll();
            if ($data) {
                $sanitised = array();
                foreach ($data as $d) {
                    $d['id'] = htmlentities($d['id'], ENT_QUOTES, 'UTF-8');
                    $d['name'] = htmlentities($d['name'], ENT_QUOTES, 'UTF-8');
                    $d['description'] = htmlentities($d['description'], ENT_QUOTES, 'UTF-8');
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
		<title>Roles | Secure Website</title>
		<meta name="description" content="View roles table.">
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
				<h1 class="title">Roles</h1>
			</header>
			<a href="./add.php" class="add-new">Add new</a>
			<p class="error-global"><?php echo $errorMessages['global']; ?></p>
			<div class="crud-table">
				<table>
					<thead>
						<tr>
							<th>ID</th>
							<th>Name</th>
							<th>Description</th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php if ($data): ?>
							<?php foreach ($data as $d): ?>
								<tr>
									<td class="right"><?php echo $d['id']; ?></td>
									<td><?php echo $d['name']; ?></td>
									<td><?php echo $d['description']; ?></td>
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

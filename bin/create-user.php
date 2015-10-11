<?php

if (!isset($_SERVER['argv'][4])) {
    echo '
Add new user to database.

Usage: create-user.php <name> <password> <email> <role>
';
    exit(1);
}

list(, $name, $password, $email, $role) = $_SERVER['argv'];

$container = require __DIR__ . '/../app/bootstrap.php';

/** @var \App\Model\UserManager $manager */
$manager = $container->getByType('App\Model\UserManager');

try {
    $manager->add($name, $password, $email, $role);
    echo "User $name was added.\n";

} catch (App\Model\DuplicateNameException $e) {
    echo "Error: duplicate name.\n";
    exit(1);
}

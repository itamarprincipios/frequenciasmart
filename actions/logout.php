<?php
// actions/logout.php
verificar_csrf();

$_SESSION = [];
session_destroy();

redirect('/login');

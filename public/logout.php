<?php
require __DIR__ . '/../src/bootstrap.php';
session_destroy();
redirect('/');

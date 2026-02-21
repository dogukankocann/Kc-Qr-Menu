<?php
session_start();
session_destroy();
header('Location: /pashaqr-live/login.php');
exit;

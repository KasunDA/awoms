<?php
include('config/config.php');
unset($_SESSION['RF']);
Session::saveSessionToDB();
header('Location: /files/readall');
exit();
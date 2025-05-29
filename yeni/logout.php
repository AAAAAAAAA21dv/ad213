<?php
session_start();
session_destroy();
header("Location: sayfa.html");
exit;
?>

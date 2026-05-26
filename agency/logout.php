<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Logging out...</title>
</head>
<body>
<script>
    localStorage.removeItem('user');
    window.location.href = 'login.php';
</script>
</body>
</html>
<?php
$this->layout('templates/AdminLayoutConveyor', [
        'page' => 'Admin Home Conveyor',
        'ws_context'=> $ws_context,
])
?>
<html>
<head>Admin page Conveyor</head>
<body>
<ul>
    <li>
        User name is: <?=$userName?>
    </li>
</ul>
<form action="/logout" method="POST" enctype="multipart/form-data">
    <input type="submit" value="logout"/>
</form>
</body>
</html>
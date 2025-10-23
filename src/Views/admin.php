<html>
<head>Admin page</head>
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
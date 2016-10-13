<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<{array:meta:<meta name="@name" content="@content">}>
	
	<title><{var:title}></title>
	
	<{array:stylesheets:<link rel="stylesheet" href="@src" />}>
	<{array:scripts:<script src="@src" @attrs></script>}>
</head>
<body>
	<{include:header}>
	<{var:content}>
</body>
<html>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<{array::meta::<meta name="@name" content="@content">}>
	
	<title><{var::title}></title>
	
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
	<{array::stylesheets::<link rel="stylesheet" href="@src" />}>
	<{array::scripts::<script src="@src" @attrs></script>}>
</head>
<body>
	<{include::header}>
	<main>
		<{content::main::editor}>
	</main>
	<{include::footer}>
</body>
<html>
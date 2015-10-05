<!DOCTYPE html>
<html>
<head>
<title>{{ $title | "Title" }} - CS Stat Client</title>
<meta charset="UTF-8">
<meta name="viewport" content="initial-scale=1.0" />
<link type="text/css" rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link type="text/css" rel="stylesheet" href="/public/css/style.css">
<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="/public/js/jquery.tablesorter.min.js"></script>
<script src="/public/js/script.js"></script>
<script>
var basepath = '@approot';
var token = '@token';
</script>

</head>
<body>

    @content

</body>
</html>

<!DOCTYPE html>
<html>
<head>
<title><?php echo isset($title) ? $title : "Title"; ?></title>
<meta charset="UTF-8">
<meta name="viewport" content="initial-scale=1.0" />
<link type="text/css" rel="stylesheet" href="<?php echo App::root(); ?>/style/reset.css">
<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script>
$(document).ready(function() {
    $.ajax({
        url: '<?php echo App::root(); ?>/test/1',
        method: 'post',
        data: {
            string: 'This line is loaded via AJAX.',
            token: '<?php echo token(); ?>'
        },
        success: function(response) {
            $('#dyn').html(response.data);
        }
    });
});
</script>
<style>

body {
    background-color: #FFF;
}

#content {
    color: #666;
    padding: 2em 3em;
    font-family: sans-serif;
}

</style>
</head>
<body>

<div id="content">
 
    <?php echo $viewContent; ?>
    <div id="dyn"></div>
<?php echo new View("partials/footer"); ?>
</div>

</body>
</html>
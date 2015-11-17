{{-- This is a comment that won't appear in the HTML --}}
<!DOCTYPE html>
<html>
<head>
{{-- Here is a variable with a fallback. If $title isn't passed to the view, display "Title" instead --}}
<title>{{ $title | "Title" }}</title>
<meta charset="UTF-8">
<meta name="viewport" content="initial-scale=1.0" />
{{-- All local file urls should be rooted. They will adjust automatically if the site is in a subdirectory --}}
<link type="text/css" rel="stylesheet" href="/style/reset.css">
<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script>
$(document).ready(function() {
    $.ajax({
        url: '@approot/test/1',
        method: 'post',
        data: {
            string: 'This line is loaded via AJAX.',
            token: '@token'
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
 
    {{--
        This is a layout file, so we need to tell it where to inject the view.
        This is done with @content or, if you're more used to .NET Razor, @RenderBody.
    --}}
    
    @content

    <div id="dyn"></div>
    
    @include partials/footer
    
</div>

</body>
</html>
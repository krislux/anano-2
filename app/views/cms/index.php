<html>
    <head>
        <title>Anano CMS</title>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <link rel="stylesheet" type="text/css" media="all" href="/public/packages/tigracalendar/tcal.anano.css" />
        <link rel="stylesheet" type="text/css" media="all" href="/public/packages/cms/fonts/font-awesome/css/font-awesome.min.css" />
        <link rel="stylesheet" type="text/css" media="all" href="/public/packages/cms/css/style.css" />
        
        <script src="/public/packages/cms/js/jquery-2.1.1.min.js"></script>
        <script src="/public/packages/cms/js/jquery.selection.js"></script>
        <script src="/public/packages/cms/js/markdown.js"></script>
        <script src="/public/packages/tigracalendar/tcal.js"></script>
        <script src="/public/packages/cms/js/script.js"></script>
        <script>
            var BASEDIR = "@approot";
            var token = "@token";
        </script>
    </head>
    
    <body>
        <div id="loading" style="display:none;"><img src="/public/packages/cms/img/loading-anim.gif" /></div>
        
        <div id="menu">
            <div id="menu-header"></div>
            <ul>
                <li id="menu-pages"><i class="fa fa-file-o"></i><p>Pages</p></li>
                <li id="menu-media"><i class="fa fa-file-image-o"></i><p>Media</p></li>
                <li id="menu-settings"><i class="fa fa-wrench"></i><p>Settings</p></li>
                <li id="menu-developer"><i class="fa fa-cog"></i><p>Developer</p></li>
                <li id="menu-users"><i class="fa fa-user"></i><p>Users</p></li>
                <li id="menu-members"><i class="fa fa-users"></i><p>Members</p></li>
            </ul>
        </div>
        <div id="submenu">
            <div id="submenu-header">
                <div class="input-search">
                    <i class="fa fa-search"></i>
                    <input type="textfield"></input>
                </div>
            </div>
            <ul>
            </ul>
        </div>
        <div id="context-menu" style="display: none;">
            <div id="context-menu-header">
                <h3></h3>
            </div>
            <ul>
            </ul>
        </div>
        
        <div id="content">
            <div id="content-header">
                <div class="content-container">
                    <h3></h3>
                </div>
            </div>
            <div class="content-container">
                <form id="content-body">
                </form>
            </div>
        </div>
    </body>
</html>
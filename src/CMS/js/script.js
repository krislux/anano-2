function length (obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) {
            size++;
        }
    }
    return size;
}

function toggleSubitems(item) {
    item = $(item);
    var sibling = item.parent().next();

    if (sibling.prop('tagName') == 'UL') {
        if (sibling.is(':visible')) {
            sibling.hide();
            item.addClass('fa-caret-right');
            item.removeClass('fa-caret-down');
        } else {
            sibling.show();
            item.removeClass('fa-caret-right');
            item.addClass('fa-caret-down');
        }
    }
}

function openSubmenu (name) {
    $.ajax({
        url: 'menus.php?getSubmenu=' + name,
        cache: false,
        type: 'POST',
        contentType: false,
        processData: false,
        success: function (json) {
            var container = $('#submenu ul');
            var html = '';
            
            try {
                var submenu = JSON.parse(json);
                var liCategory = $('<li class="submenu-category">' + name + '</li>');

                container.empty();
                for(var i = 0; i < length(submenu); i++) {
                    var item = submenu[i];
                    
                    if (length(item.subitems) > 0) {
                        var ul = $('<ul style="display:none"></ul>');
                        var liItem = $('<li><i onclick="toggleSubitems(this);" class="fa fa-caret-right"></i><i class="fa fa-' + item.icon + '"></i><p onclick="openContent($(this.parentNode).data(\'properties\'))">' + item.name + '</p><i class="submenu-context-button fa fa-ellipsis-h" onclick="openContextMenu(\'' + item.type + '\')"></i></li>');
                        
                        liItem.data ( "properties", item.properties );

                        for(var s in item.subitems) {
                            var subitem = item.subitems[s];
                            var liSubitem = $('<li><i class="fa fa-' + subitem.icon +'"></i><p onclick="openContent($(this.parentNode).data(\'properties\'))">' + subitem.name + '</p><i class="submenu-context-button fa fa-ellipsis-h" onclick="openContextMenu(\'' + item.type + '\')"></i></li>');
                            
                            liItem.data ( "properties", subitem.properties );
                            
                            ul.append(liSubitem);
                        }

                        container.append(liItem);
                        container.append(ul);

                    } else {
                        var liItem = $('<li><i class="fa fa-' + item.icon + '"></i><p onclick="openContent($(this.parentNode).data(\'properties\'))">' + item.name + '</p><i class="submenu-context-button fa fa-ellipsis-h" onclick="openContextMenu(\'' + item.type + '\')"></i></li>');
                        liItem.data ( "properties", item.properties );

                        container.append(liItem);
                    }
                }
                


            } catch (error) {
                alert ( "ERROR\n" + error );
            
            }
        }
    });
}

function closeContextMenu () {
    var menuContainer = $('#context-menu');
    menuContainer.animate({ left: (442 - menuContainer.width() - 20) + 'px' }, 250, 'swing', function () {
        menuContainer.hide();
    });
}

function openContextMenu (nodeType) {
    var menuContainer = $('#context-menu');
    var container = $('#context-menu ul');

    $.ajax({
        url: 'menus.php?getContextMenu=' + nodeType,
        cache: false,
        type: 'POST',
        contentType: false,
        processData: false,
        success: function (json) {
            var html = '';
          
            $('#context-menu-header h3').html(nodeType);

            try {
                var contextMenu = JSON.parse(json);
                var width = 250;

                for(var i = 0; i < contextMenu.length; i++) {
                    html += '<li class="context-menu-category">' + contextMenu[i].title + '</li>\n';

                    for(var s in contextMenu[i].items) {
                        var item = contextMenu[i].items[s];
                        html += '<li><i class="fa fa-' + item.icon + '"></i>' + item.name + '</li>\n';
                    }
                }
                
                if (menuContainer.is(':visible')) {
                    menuContainer.animate({ left: (442 - menuContainer.width() - 20) + 'px' }, 150, 'swing', function () {
                        container.html(html);
                        menuContainer.css('left', (442 - menuContainer.width() - 20) + 'px');
                        menuContainer.animate({ left: '442px' }, 250, 'swing');
                    });
                } else {
                    menuContainer.show();
                    container.html(html);
                    menuContainer.css('left', (442 - menuContainer.width() - 20) + 'px');
                    menuContainer.animate({ left: '442px' }, 250, 'swing');
                }

            } catch (error) {
                alert ( 'ERROR\n' + error + '\n' + json );
            
            }
        }
    });
}

function openContent (properties) {
    var divContent = $('#content-body .content-container');
    
    closeContextMenu();
    divContent.empty();
   
    for(var i in properties) {
        var prop = properties[i];

        divContent.append(prop.name + ', ' + prop.type + ', ' + prop.value + '\n');
    }
}

$(document).ready(function() {
    $('#menu-content').click(function() {
        openSubmenu('content');
    });

    $('#content').click(function() {
        closeContextMenu();
    });
});

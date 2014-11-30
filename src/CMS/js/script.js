/*********************
 * Helper functions
 *********************/
function length (obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) {
            size++;
        }
    }
    return size;
}

/*********************
 * Overlays
 *********************/
function setLoading(isLoading) {
    if (isLoading) {
        $('#loading').show();
    } else {
        $('#loading').hide();
    }
}

/*********************
 * Navigation
 *********************/
/* Nodes */
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

/* Submenu */
function openSubmenu (name) {
    setLoading(true);
    
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
                
                renderSubmenu(submenu, $('#submenu ul'), name);

            } catch (error) {
                alert ( "ERROR\n" + error );
            
            }
            
            setLoading(false);
        }
    });
}

/* Context menu */
function closeContextMenu () {
    var menuContainer = $('#context-menu');
    menuContainer.animate({ left: (442 - menuContainer.width() - 20) + 'px' }, 250, 'swing', function () {
        menuContainer.hide();
    });
}

function openContextMenu (nodeType) {
    setLoading(true);
    
    $.ajax({
        url: 'menus.php?getContextMenu=' + nodeType,
        cache: false,
        type: 'POST',
        contentType: false,
        processData: false,
        success: function (json) {
            try {
                var contextMenu = JSON.parse(json);
            
                renderContextMenu(contextMenu, nodeType);
            
            } catch (error) {
                alert ( 'ERROR\n' + error + '\n' + json );
            
            }
            
            setLoading(false);
        }
    });
}

/* Content */
function openContent(properties, title) {
    closeContextMenu();
 
    $('#content-header h3').html(title);

    renderContent(properties); 
}

/*********************
 * Rendering
 *********************/
/* Context menu */
function renderContextMenu(contextMenu, nodeType) {
    var menuContainer = $('#context-menu');
    var container = $('#context-menu ul');
    var width = 250;
    var html = '';
  
    $('#context-menu-header h3').html(nodeType);

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
}

/* Submenu */
function renderSubmenu(submenu, ulParent, name) {
    if (name) {
        ulParent.empty();
        ulParent.append( $('<li class="submenu-category">' + name + '</li>') );
    }

    for(var i = 0; i < length(submenu); i++) {
        var item = submenu[i];
        var caret = '';
        var name = item.name;

        if (name.length > 20) {
            name = name.substring(0, 17) + '...';
        }

        if (length(item.subitems) > 0) {
            caret = '<i onclick="toggleSubitems(this);" class="fa fa-caret-right"></i>';
        }

        var li = $('<li>' + caret + '<i class="fa fa-' + item.icon + '"></i><p onclick="openContent($(this.parentNode).data(\'properties\'), \'' + item.name + '\')">' + name + '</p><i class="submenu-context-button fa fa-ellipsis-h" onclick="openContextMenu(\'' + item.type + '\')"></i></li>');
        li.data ( "properties", item.properties );
        
        ulParent.append(li);

        if (length(item.subitems) > 0) {
            var ul = $('<ul style="display: none"></ul>');
            ulParent.append(ul);
            renderSubmenu(item.subitems, ul);
        }
    }
}

/* Content */
function renderContent (properties) {
    var divContent = $('#content-body');
    var editors = [];
    divContent.empty();

    for(var i in properties) {
        var prop = properties[i];
        var divContainer = $('<div class="content-property"></div>');
        var h3Name = $('<h3 class="content-property-name">' + prop.name + '</h3>');
        var divValue = $('<div class="content-property-value content-property-' + prop.type + '"></div>');
        var inputValue = null;
            
        switch (prop.type) {
            case 'varchar':
                inputValue = $('<input type="text" value="' + prop.value + '" /></div>');
                break;

            case 'text':
                divContainer.toggleClass('large', true);
                inputValue = $('<textarea oninput="document.getElementById(\'preview\').innerHTML = markdown.toHTML(this.value);" name="content-property-' + prop.name + '" id="content-property-' + prop.name + '" rows="10" cols="80">' + prop.value + '</textarea>');
                break;

            case 'int':
                inputValue = $('<input type="number" value="' + prop.value + '" />');
                break;
            
            case 'double':
                inputValue = $('<input type="number" step="any" value="' + prop.value + '" />');
                break;

            case 'bool':
                inputValue = $('<input type="checkbox" class="content-property-checkbox" value="' + prop.value + '" />');
                break;

            case 'datetime':
                inputValue = $('<input class="content-date-picker" value="' + prop.value + '" type="text"/>');
                inputValue.glDatePicker({
                    showAlways: true,
                    selectableDateRange: [
                        { from: new Date(2013, 0, 1),
                            to: new Date(2015, 11, 31) },
                    ],
                    selectableYears: [2013, 2014, 2015],
                    onClick: function(el, cell, date, data) {
                        el.val
                        (
                            date.getDate()+"-"+(date.getMonth() + 1)+"-"+date.getFullYear()
                        );
                    }
                });

                break;

            case 'enum':
                inputValue = $('<select></select>');
                // TODO: enum logic
                break;
        }

        divContainer.append(h3Name);
        divContainer.append(divValue);
        divValue.append(inputValue);

        divContent.append(divContainer);

        divContent.append('<div class="clear"></div>');
    }

    var btnSave = $('<button class="content-button-submit" type="button">Save</button>');
    divContent.append(btnSave);
}

/*********************
 * Init
 *********************/
$(document).ready(function() {
    $('#menu-content').click(function() {
        openSubmenu('content');
    });

    $('#content').click(function() {
        closeContextMenu();
    });
});

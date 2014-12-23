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
        url: BASEDIR + '/cms/menus/' + name,
        data: {
            token: token
        },
        cache: false,
        type: 'POST',
        success: function (submenu) {
            var container = $('#submenu ul');
            var html = '';
            
            //try {
                renderSubmenu(submenu, $('#submenu ul'), name);
            //} catch (error) {
            //    alert ( "ERROR\n" + error );
            //}
            
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
function openContent(id) {
    closeContextMenu();

    $.ajax({
        url: BASEDIR + '/cms/get_content/' + id,
        cache: false,
        type: 'POST',
        data: {
            token: token
        },
        success: function(node) {
            try {
                $('#content-header h3').html(node.title);
                renderContent(node.properties);
            
            } catch (error) {
                alert ( 'ERROR\n' + error + '\n' + json );
            
            }
            
            setLoading(false);
        }
    });
}

function toggleCheckbox(box) {
    var bool = box.data('checked');
    
    bool = !bool;

    box.toggleClass('fa-square-o', !bool);
    box.toggleClass('fa-check-square-o', bool);
    box.data('checked', bool);
}

function toggleMarkdown(editor, mode) {
    var input = editor.find('.content-markdown-input');
    var inputButton = editor.find('.fa-edit');
    var output = editor.find('.content-markdown-output');
    var outputButton = editor.find('.fa-font');

    if (mode == 'input') {
        if (input.is(':visible')) {
            if (output.is(':visible')) {
                input.toggleClass('active', false);
                output.toggleClass('fill', true);
                inputButton.toggleClass('active', false);
            }
        } else {
            input.toggleClass('active', true);
            output.toggleClass('fill', false);
            inputButton.toggleClass('active', true);
        }
    } else if (mode == 'output') {
        if (output.is(':visible')) {
            if (input.is(':visible')) {
                output.toggleClass('active', false);
                input.toggleClass('fill', true);
                outputButton.toggleClass('active', false);
            }
        } else {
            output.toggleClass('active', true);
            input.toggleClass('fill', false);
            outputButton.toggleClass('active', true);
        }
    }
}

function toggleMarkdownSplit(editor) {
    var input = editor.find('.content-markdown-input');
    var output = editor.find('.content-markdown-output');
    var splitButton = editor.find('.fa-columns');
    var show = output.is(':visible');
    
    output.toggleClass('active', !show);
    splitButton.toggleClass('active', !show);
    input.toggleClass('fill', show);
}

function insertAtIndex(a, b, position) {
    return [a.slice(0, position), b, a.slice(position)].join('');
}

function wrapSelection(editor, before, after) {
    var input = editor.find('.content-markdown-input');
    var output = editor.find('.content-markdown-output');
   
    if (input.selection('get').length > 0) {
        if (before && after) {
            input.selection('insert', {text: before, mode: 'before'}).selection('insert', {text: after, mode: 'after'}); 
        } else if (before) {
            input.selection('insert', {text: before, mode: 'before'}); 
        } else if (after) {
            input.selection('insert', {text: after, mode: 'after'}); 
        }
        
        output.html(markdown.toHTML(input.val()));
    }
}

function setBold(editor) {
    wrapSelection(editor,'**','**');
}

function setItalic(editor) {
    wrapSelection(editor,'_','_');
}

function setLink(editor) {
    wrapSelection(editor,'[','](http://)');
}

function getSelectionStart(input) {
    var index = input.selection('getPos').start;
    var lines = input.val().split('\n');
    var count = 0;

    for(var i = 0; i < lines.length; i++) {
        if (index >= count && index <= count + lines[i].length) {
            index = count == 0 ? count : count+1;
            break;
        } else {
            count += lines[i].length;
        }
    }

    return index;
}

function setSmallerHeader(editor) {
    var input = editor.find('.content-markdown-input');
    var output = editor.find('.content-markdown-output');
    var pos = input.selection('getPos');
    var index = getSelectionStart(input);
        
    if (input.val().slice(index,index+6) != '######') {
        input.val(insertAtIndex(input.val(), '#', index)); 
        output.html(markdown.toHTML(input.val()));
        input.selection('setPos',pos);
    }
}

function setLargerHeader(editor) {
    var input = editor.find('.content-markdown-input');
    var output = editor.find('.content-markdown-output');
    var index = getSelectionStart(input);
    var firstCharacter = input.val().slice(index,index+1);

    if (firstCharacter == '#') {
        var pos = input.selection('getPos');
        var before = input.val().slice(0,index);
        var after = input.val().slice(index+1,input.val().length);
        
        input.val(before + after);
        output.html(markdown.toHTML(input.val()));
        input.selection('setPos',pos);
    }
}

/*********************
 * Rendering
 *********************/
/* Controls */
function renderDatePicker(prop) {
    return $('<input type="text" class="tcal content-date-picker" value="' + prop.value +'"/><i class="fa fa-calendar content-date-icon"></i>');
}

function renderMarkdownEditor(prop) {
    return $('' +
        '<div class="content-markdown-editor">' +
            '<div class="content-markdown-toolbar">' +
                '<i onclick="toggleMarkdownSplit($(this.parentNode.parentNode))" class="fa fa-columns active"></i>' +
                '<i onclick="setBold($(this.parentNode.parentNode))" class="fa fa-bold"></i>' +  
                '<i onclick="setItalic($(this.parentNode.parentNode))" class="fa fa-italic"></i>' +  
                '<i onclick="setLargerHeader($(this.parentNode.parentNode))" class="fa fa-header"></i>' +  
                '<i onclick="setSmallerHeader($(this.parentNode.parentNode))" class="fa fa-header fa-small"></i>' +  
                '<i onclick="setLink($(this.parentNode.parentNode))" class="fa fa-link"></i>' +  
            '</div>' +
            '<textarea class="content-markdown-input active" oninput="$(this).next().html(markdown.toHTML(this.value));" rows="10" cols="80">' + prop.value + '</textarea>' +
            '<div class="content-markdown-output active">' + markdown.toHTML(prop.value) + '</div>' +
        '</div>' +
    '');
}

function renderCheckbox(prop) {
    var box = $('<div class="content-property-checkbox fa" onclick="toggleCheckbox($(this))"></div>');
    box.data('checked', prop.value == 'true');
    toggleCheckbox(box, prop.value == 'true');
    return box;
}

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

        var li = $('<li>' + caret + '<i class="fa fa-' + item.icon + '"></i><p onclick="openContent($(this.parentNode).data(\'id\'), \'' + item.name + '\')">' + name + '</p><i class="submenu-context-button fa fa-ellipsis-h" onclick="openContextMenu(\'' + item.type + '\')"></i></li>');
        li.data ( "id", item.id );
        
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
        var divValue = $('<div id="content-property-id-' + prop.slug + '" class="content-property-value content-property-' + prop.type + '"></div>');
        var inputValue = null;
            
        switch (prop.type) {
            case 'varchar':
                inputValue = $('<input type="text" value="' + prop.value + '" /></div>');
                break;

            case 'text':
                divContainer.toggleClass('large', true);
                inputValue = renderMarkdownEditor(prop);
                break;

            case 'int':
                inputValue = $('<input type="number" value="' + prop.value + '" />');
                break;
            
            case 'double':
                inputValue = $('<input type="number" step="any" value="' + prop.value + '" />');
                break;

            case 'bool':
                inputValue = renderCheckbox(prop);
                break;

            case 'datetime':
                inputValue = renderDatePicker(prop);
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

    f_tcalInit();
        
    var btnSave = $('<button class="content-button-submit" type="button">Save</button>');
    divContent.append(btnSave);
}

/*********************
 * Init
 *********************/
$(document).ready(function() {
    $('#menu-pages').click(function() {
        openSubmenu('pages');
    });

    $('#content').click(function() {
        closeContextMenu();
    });
});

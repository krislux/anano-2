<?php
    $testNodes = array();

    for ($i = 0; $i < 4; $i++){
        $properties = array(
            array(
                'name' => 'Title',
                'slug' => 'title',
                'type' => 'varchar',
                'value' => 'This is a title',
            ),
            array(
                'name' => 'BodyText',
                'slug' => 'bodytext',
                'type' => 'text',
                'value' => "### This is some text\r\nAnd [this](www.pol.dk) is a link",
            ),
            array(
                'name' => 'Count',
                'slug' => 'count',
                'type' => 'int',
                'value' => 0,
            ),
            array(
                'name' => 'Degrees',
                'slug' => 'degrees',
                'type' => 'double',
                'value' => 0.5,
            ),
            array(
                'name' => 'Flag',
                'slug' => 'flag',
                'type' => 'bool',
                'value' => true,
            ),
            array(
                'name' => 'CreationDateTime',
                'slug' => 'creationdatetime',
                'type' => 'datetime',
                'value' => '2014-10-06',
            ),
            array(
                'name' => 'Option',
                'slug' => 'option',
                'type' => 'enum',
                'value' => 'fisk',
            ),
        );
        
        $item = array(
            'name' => 'SubNode',
            'slug' => 'subnode',
            'type' => 'page',
            'icon' => 'file',
            'properties' => $properties,
        );

        $item2 = $item;
        $item2['subitems'] = array (
            $item,
            $item,
            $item,
        );

        $testNodes[$i] = array(
            'name' => 'Node',
            'slug' => 'node',
            'type' => 'page',
            'icon' => 'file',
            'properties' => $properties,
            'subitems' => array(
                $item,
                $item2,
            ),
        );
    }


    function get ($field, $default = '') {
        if (isset($_REQUEST[$field])) {
            return $_REQUEST[$field];
        } else {
            return $default;
        }
    }

    // Get submenu
    if(get('getSubmenu')){
        $menu_id = get('getSubmenu');
        $submenu = array();

        switch($menu_id){
            case 'pages':
                $submenu = $testNodes;
                break;
        }

        echo json_encode($submenu);

    // Get context menu
    } else if(get('getContextMenu')) {
        $node_type = get('getContextMenu');

        switch($node_type){
            case 'page':
                $item = array (
                    'name' => 'Action',
                    'icon' => 'gear',
                );

                $section = array (
                    'title' => 'Section',
                    'items' => array (
                        $item,
                        $item,
                        $item,
                    )
                );
                
                $context_menu = array (
                    $section,
                    $section,
                );

                echo json_encode($context_menu);
                break;
        }

    // Get content
    } else if (get('getContent')) {
        $id = get('getContextMenu');
        echo json_encode($testNodes[$id]);
    }
?>

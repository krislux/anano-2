<?php

class Cms extends Controller
{
    private function getTestNodes()
    {
        $test_nodes = array();

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
                'id' => '0',
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

            $test_nodes[$i] = array(
                'name' => 'Node',
                'id' => $i,
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

        return $test_nodes;
    }

    function __construct()
    {
        $this->filter('csrf', array('on' => 'POST'));
        //$this->filter('auth', array('except' => array('index', 'login')));
    }
    
    function index()
    {
        new CMS\Data\CmsModel('mtest');
        return new View('cms/index');
    }
    
    function createTable()
    {
        $migrator = new Anano\Database\Migrator;
        $rv = $migrator->fromJson(STORAGE_DIR . '/cms/table.json')->buildQuery();
        //with(new Anano\Database\Database)->query($rv);
        return Response::text($rv);
    }

    function getContent($node_id)
    {
        $model = with(new Mtest)->find($node_id);
        return Response::json($model->toArray());
        
        //return Response::json($this->getTestNodes()[$node_id]);    
    }

    function menus($menu_id)
    {
        $submenu = array();
        
        switch($menu_id){
        case 'pages':
            $submenu = $this->getTestNodes();
            break;
        }
        
        return Response::json($submenu);
    }
}

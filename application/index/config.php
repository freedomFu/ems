<?php
return [
    //视图输出字符串内容替换
    'view_replace_str'      =>  [
        '__PUBLIC__'        =>  SITE_URL.'/public/static/index',
        '__AJUMP__'         =>  SITE_URL.'/public/index',
    ],
    
    'paginate'               => [
        'type'      => 'bootstrap',
        'var_page'  => 'page',
        'list_rows' => 15,
        'page_size' => 5, //页码数量
        'page_button'=>[
            'total_rows'=>true, //是否显示总条数
            'turn_page'=>true, //上下页按钮
            'turn_group'=>true, //上下组按钮
            'first_page'=>true, //首页
            'last_page'=>true  //尾页
        ]
    ]
];
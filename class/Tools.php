<?php
namespace helpers;
/*
 *      $db = base\Application::get_db();
        Tools::tables_to_model($db);
 */
class Tools{

    /**
     * 通用表模型生成工具
     * @param $db
     */
    public static  function tables_to_model($db,$table=''){
        $db_name = 'deploy';
        $tables_path = APP_PATH.'/models/Table/';
        $db->query("USE {$db_name}");
        if(!empty($table)){
            $result = $db->query("show tables like '{$table}'")->fetchAll();
        }else{
            $result = $db->query("show tables")->fetchAll();
        }

        $all_tables = Arr::getColumn($result,'0');

        foreach($all_tables as $table){
            $model_table = str_replace("\t",'',ucwords(str_replace(array('_'),array("\t"),strstr($table,'_'))));
            $template    = file_get_contents($tables_path.'Template.php');
            $model_data  = str_replace(array('Template','TABLE'),array($model_table,$db_name.'.'.$table),$template);
            $file_name   = $tables_path.$model_table.'.php';
            echo $file_name;
            file_put_contents($file_name,$model_data);
        }
    }
}
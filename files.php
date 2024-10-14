<?php
$files = glob('./class/*');
print_r($files);
foreach ($files as $file){
    include $file;
}

Timer::go(1);
$qb = new QueryBuilder();
$qb1 = new QueryBuilder();
//添加
$data = array(
    'user_id'=>1,
    'platform_id'=>1000,
    'username'=>'zhangshan',
    'ip'=>'127.0.0.1',
    'm'=>'m',
    'a'=>'a',
    'addtime'=>time(),
);
$sql = $qb->table('ga_admin_log')->insert($data);
//
//
//
$where = array(
    'id'=>[1,2,3],// in查询
    'id >' => 1,//添加查询
    'username'=>'123',
    'addtime <'=>'123456',//时间范围查询
    'addtime >'=>'789465',
    'username ~'=>'z',//like查询
);
$one = $qb1->table("ga_admin_log")->where($where)->group_by('id')->limit(0,3)->fetchAll();
//echo $one;

echo $qb1->table("ga_admin_log")->update($data,['id'=>1060]);

echo (new QueryBuilder())->insert($data);

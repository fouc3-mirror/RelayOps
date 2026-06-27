<?php
// 全局中间件定义文件
return [
    // 安装检查中间件 - 检测是否需要安装
    \app\middleware\InstallCheck::class,

    // Session初始化（鉴权需要）
    \think\middleware\SessionInit::class,
];

<?php

use xiaodi\Permission\Model\Permission;
use xiaodi\Permission\Model\User;
use xiaodi\Permission\Model\Role;
use xiaodi\Permission\Model\RolePermissionAccess;
use xiaodi\Permission\Model\UserRoleAccess;

return [
    // 超级管理员id
    'super_id' => 1,

    // 用户模型
    'user' => [
        'model'       => User::class,
        'froeign_key' => 'user_id',
    ],

    // 规则模型
    'permission' => [
        'model'       => Permission::class,
        'froeign_key' => 'permission_id',
    ],

    // 角色模型
    'role' => [
        'model'       => Role::class,
        'froeign_key' => 'role_id',
    ],

    // 角色与规则中间表模型
    'role_permission_access' => RolePermissionAccess::class,

    // 用户与角色中间表模型
    'user_role_access' => UserRoleAccess::class,
];

# 雨梦FRPS多节点管理系统

FRPS 节点管理销售系统，基于 ThinkPHP 8 + Vue 3 前后端分离架构。

## 环境要求

- PHP >= 8.2（需要fileinfo,opcache 扩展）
- MySQL >= 5.7
- 宝塔面板

## 安装步骤

### 1. 上传项目

将项目文件上传到宝塔网站根目录（如 `/www/wwwroot/yourdomain.com`）。
授予读写权限：`chmod -R 777 /www/wwwroot/yourdomain.com`
或在宝塔中直接给网站目录`/www/wwwroot/yourdomain.com`这个文件夹授予777权限。

### 2. 宝塔面板配置

1. **创建站点**：宝塔 → 网站 → 添加站点 → 输入域名 → PHP 选择 8.2+
2. **设置运行目录**：站点设置 → 网站目录 → 运行目录选择 `public` → 保存
3. **设置伪静态**：站点设置 → 伪静态 → 选择 `thinkphp`
。

### 3. 访问安装页面

浏览器访问：`http://yourdomain.com/`将会自动进入安装界面`

按向导完成：
1. 环境检测
2. 数据库配置（填写宝塔创建的数据库信息）
3. 管理员配置（设置后台管理员账号密码）
4. 完成安装

安装完成后系统自动创建以下数据表：
- `RO_admin` — 管理员表
- `RO_user` — 用户表
- `RO_node` — FRPS 节点表
- `RO_setting` — 系统配置表
- `RO_user_node` — 用户节点表
- `RO_user_node_log` — 用户节点日志表
- `RO_node_log` — FRPS 节点日志表
- 等等所需的数据表。

### 4. 完成

安装完成后直接访问域名即可使用：
- 用户前台：`http://yourdomain.com/`
- 管理后台：`http://yourdomain.com/admin`

## 目录结构

```
tp_project/
├── app/
│   ├── controller/
│   │   ├── Index.php          # 前端入口控制器
│   │   └── api/
│   │       ├── User.php       # 用户 API
│   │       └── Admin.php      # 管理员 API
│   ├── middleware/
│   │   ├── Cors.php           # CORS 跨域
│   │   ├── UserAuth.php       # 用户鉴权
│   │   ├── AdminAuth.php      # 管理员鉴权
│   │   └── InstallCheck.php   # 安装检查
│   └── install/
│       └── sql/schema.sql     # 建表 SQL（含所有数据表）
├── public/                    # 宝塔运行目录
│   └── index.php              # 入口文件
├── route/
│   └── app.php                # 所有路由（安装 + API）
├── web/                       # Vue 3 前端源码（开发者用）
└── README.md
```

## API 接口

| 方法 | 路径 | 鉴权 | 说明 |
|------|------|------|------|
| POST | `/api/user/login` | 无 | 用户登录 |
| GET | `/api/user/info` | UserAuth | 获取用户信息 |
| POST | `/api/user/logout` | UserAuth | 用户登出 |
| POST | `/api/admin/login` | 无 | 管理员登录 |
| GET | `/api/admin/info` | AdminAuth | 获取管理员信息 |
| POST | `/api/admin/logout` | AdminAuth | 管理员登出 |
| GET | `/api/admin/users` | AdminAuth | 用户列表 |

## 许可证

Apache 2.0

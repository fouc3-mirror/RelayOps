<template>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-logo">{{ siteName }}</div>
            <nav class="sidebar-menu">
                <router-link to="/admin/dashboard">仪表盘</router-link>
                <router-link to="/admin/nodes">节点管理</router-link>
                <router-link to="/admin/products">商品管理</router-link>
                <router-link to="/admin/users">用户管理</router-link>
                <router-link to="/admin/orders">订单管理</router-link>
                <router-link to="/admin/settings">系统设置</router-link>
            </nav>
        </aside>
        <div class="admin-main">
            <header class="admin-header">
                <span>管理后台</span>
                <div>
                    <span style="margin-right:16px;color:#666;">管理员</span>
                    <a href="/api/admin/logout" style="color:#409eff;text-decoration:none;">退出</a>
                </div>
            </header>
            <div class="admin-body">
                <router-view />
            </div>
        </div>
    </div>
</template>

<script setup>
const siteName = document.title || 'Admin'
</script>

<style scoped>
.admin-layout { display: flex; min-height: 100vh; }
.admin-sidebar { width: 220px; background: #1d1e2c; color: #fff; display: flex; flex-direction: column; }
.sidebar-logo { padding: 20px 24px; font-size: 18px; font-weight: 700; color: #409eff; border-bottom: 1px solid rgba(255,255,255,0.08); }
.sidebar-menu { flex: 1; padding: 12px 0; }
.sidebar-menu a { display: flex; align-items: center; padding: 12px 24px; color: #a0a3bd; font-size: 14px; text-decoration: none; transition: all .2s; }
.sidebar-menu a:hover { background: rgba(255,255,255,0.05); color: #fff; }
.sidebar-menu a.router-link-exact-active { background: #409eff; color: #fff; }
.admin-main { flex: 1; display: flex; flex-direction: column; }
.admin-header { display: flex; align-items: center; justify-content: space-between; padding: 0 28px; height: 56px; background: #fff; border-bottom: 1px solid #eee; }
.admin-body { flex: 1; padding: 24px; background: #f0f2f5; }
</style>

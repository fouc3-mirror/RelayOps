<template>
    <div class="user-layout">
        <header class="user-header">
            <div class="header-left">
                <router-link to="/" class="logo">雨梦FRPS业务管理系统</router-link>
            </div>
            <nav class="header-nav">
                <router-link to="/">首页</router-link>
                <template v-if="userStore.userInfo">
                    <router-link to="/console">控制台</router-link>
                    <router-link to="/console/shop">购买端口</router-link>
                    <router-link to="/console/cart">购物车</router-link>
                    <router-link to="/console/orders">我的订单</router-link>
                </template>
            </nav>
            <div class="header-right">
                <template v-if="userStore.userInfo">
                    <span>{{ userStore.userInfo.nickname || userStore.userInfo.username }}</span>
                    <button @click="handleLogout" class="btn-logout">退出</button>
                </template>
                <template v-else>
                    <router-link to="/login" class="btn-login">注册 / 登录</router-link>
                </template>
            </div>
        </header>
        <main class="user-main">
            <router-view />
        </main>
        <footer class="user-footer">
            <p>Copyright &copy; {{ new Date().getFullYear() }} 雨梦FRPS业务管理系统. All rights reserved.</p>
        </footer>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user'
import { userLogout } from '../../api/user'

const router = useRouter()
const userStore = useUserStore()

async function handleLogout() {
    await userLogout()
    userStore.clearInfo()
    router.push('/login')
}
</script>

<style scoped>
.user-layout { min-height: 100vh; display: flex; flex-direction: column; }
.user-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 40px;
    height: 64px;
    background: #fff;
    box-shadow: 0 1px 6px rgba(0,0,0,0.08);
    position: sticky;
    top: 0;
    z-index: 100;
}
.logo { font-size: 20px; font-weight: 700; color: #409eff; text-decoration: none; white-space: nowrap; }
.header-nav { display: flex; align-items: center; gap: 28px; margin-left: 48px; }
.header-nav a { color: #555; font-size: 15px; font-weight: 500; text-decoration: none; white-space: nowrap; transition: color .2s; }
.header-nav a:hover { color: #409eff; }
.header-nav a.router-link-exact-active { color: #409eff; }
.header-right { display: flex; align-items: center; gap: 14px; margin-left: auto; }
.header-right a { text-decoration: none; }
.header-right > span { color: #333; font-size: 14px; }
.btn-login {
    padding: 8px 20px;
    background: #409eff;
    color: #fff;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    transition: all .2s;
}
.btn-login:hover { background: #337ecc; box-shadow: 0 2px 8px rgba(64,158,255,0.35); }
.btn-logout {
    padding: 6px 16px;
    border: 1px solid #dcdfe6;
    border-radius: 6px;
    background: #fff;
    cursor: pointer;
    font-size: 13px;
    color: #666;
    transition: all .2s;
}
.btn-logout:hover { color: #f56c6c; border-color: #f56c6c; }

.user-main {
    flex: 1;
    /* NO padding — each page controls its own */
}

.user-footer {
    text-align: center;
    padding: 16px;
    color: #999;
    font-size: 13px;
    border-top: 1px solid #eee;
    background: #fff;
}
</style>

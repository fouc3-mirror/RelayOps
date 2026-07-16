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
                    <button @click="handleLogout" style="padding:4px 12px;border:1px solid #dcdfe6;border-radius:4px;background:#fff;cursor:pointer;font-size:13px;">退出</button>
                </template>
                <template v-else>
                    <router-link to="/login">登录</router-link>
                </template>
            </div>
        </header>
        <main class="user-main">
            <router-view />
        </main>
        <footer class="user-footer">
            <p>Copyright &copy; 雨梦FRPS业务管理系统</p>
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
.user-header { display: flex; align-items: center; justify-content: space-between; padding: 0 24px; height: 60px; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
.logo { font-size: 20px; font-weight: bold; color: #409eff; text-decoration: none; }
.header-nav a { margin: 0 12px; color: #666; text-decoration: none; }
.header-right { display: flex; align-items: center; gap: 12px; }
.header-right a { color: #409eff; text-decoration: none; }
.user-main { flex: 1; padding: 24px; background: #f5f5f5; }
.user-footer { text-align: center; padding: 16px; color: #999; font-size: 13px; }
</style>

<template>
    <div class="login-page">
        <div class="login-card">
            <h2>雨梦FRPS业务管理系统</h2>
            <p class="sub">用户登录</p>
            <el-form :model="form" @submit.prevent="handleLogin">
                <el-form-item>
                    <el-input v-model="form.username" placeholder="用户名" prefix-icon="User" size="large" />
                </el-form-item>
                <el-form-item>
                    <el-input v-model="form.password" type="password" placeholder="密码" prefix-icon="Lock" size="large" show-password />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" :loading="loading" @click="handleLogin" size="large" style="width:100%">登 录</el-button>
                </el-form-item>
            </el-form>
            <div class="links">
                <router-link to="/">← 返回首页</router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { userLogin } from '../../api/user'
import { useUserStore } from '../../stores/user'

const router = useRouter()
const userStore = useUserStore()
const loading = ref(false)
const form = reactive({ username: '', password: '' })

async function handleLogin() {
    if (!form.username || !form.password) {
        ElMessage.warning('请输入用户名和密码')
        return
    }
    loading.value = true
    try {
        const res = await userLogin(form)
        if (res.code === 1) {
            userStore.setInfo(res.data)
            ElMessage.success('登录成功')
            router.push('/console')
        } else {
            ElMessage.error(res.msg || '登录失败')
        }
    } catch (e) {
        ElMessage.error('登录失败')
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f0f2f5; }
.login-card { width: 420px; padding: 44px 40px; background: #fff; border-radius: 14px; box-shadow: 0 8px 40px rgba(0,0,0,0.10); }
.login-card h2 { text-align: center; margin: 0 0 6px 0; font-size: 22px; color: #333; }
.login-card .sub { text-align: center; margin-bottom: 32px; font-size: 14px; color: #999; }
.links { text-align: center; margin-top: 20px; }
.links a { color: #999; font-size: 13px; }
.links a:hover { color: #409eff; }
</style>

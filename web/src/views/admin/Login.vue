<template>
    <div class="login-container">
        <div class="login-card">
            <h2>雨梦FRPS业务管理系统 管理后台</h2>
            <el-form :model="form" @submit.prevent="handleLogin" label-width="0">
                <el-form-item>
                    <el-input v-model="form.username" placeholder="管理员账号" prefix-icon="User" size="large" />
                </el-form-item>
                <el-form-item>
                    <el-input v-model="form.password" type="password" placeholder="密码" prefix-icon="Lock" size="large" show-password />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" :loading="loading" @click="handleLogin" size="large" style="width:100%">
                        登 录
                    </el-button>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { adminLogin } from '../../api/admin'
import { useUserStore } from '../../stores/user'

const router = useRouter()
const userStore = useUserStore()
const loading = ref(false)
const form = reactive({ username: '', password: '' })

async function handleLogin() {
    if (!form.username || !form.password) {
        ElMessage.warning('请输入账号和密码')
        return
    }
    loading.value = true
    try {
        const res = await adminLogin(form)
        if (res.code === 1) {
            userStore.setInfo(res.data)
            ElMessage.success('登录成功')
            router.push('/admin')
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
.login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.login-card { width: 400px; padding: 40px; background: #fff; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
h2 { text-align: center; margin-bottom: 30px; color: #333; }
</style>

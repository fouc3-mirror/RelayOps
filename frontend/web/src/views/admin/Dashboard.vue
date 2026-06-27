<template>
    <div>
        <el-card>
            <template #header>管理员信息</template>
            <p>用户名：{{ info?.username }}</p>
            <p>昵称：{{ info?.nickname }}</p>
            <p>邮箱：{{ info?.email || '未设置' }}</p>
            <p>上次登录：{{ info?.last_login_time ? new Date(info.last_login_time * 1000).toLocaleString() : '首次登录' }}</p>
        </el-card>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getAdminInfo } from '../../api/admin'

const info = ref(null)

onMounted(async () => {
    try {
        const res = await getAdminInfo()
        if (res.code === 1) info.value = res.data
    } catch (e) { /* intercepted by 401 handler */ }
})
</script>

<template>
  <div class="console">
    <div class="console-header">
      <el-avatar :size="56" :style="{ background: 'linear-gradient(135deg, #409eff, #337ecc)' }">
        {{ (userStore.userInfo?.nickname || userStore.userInfo?.username || '?')[0] }}
      </el-avatar>
      <div>
        <h2>{{ userStore.userInfo?.nickname || userStore.userInfo?.username }}</h2>
        <p style="color:#999;font-size:13px;">{{ userStore.userInfo?.email || '未绑定邮箱' }}</p>
      </div>
    </div>

    <el-row :gutter="16" style="margin-bottom:24px;">
      <el-col :span="6">
        <el-card shadow="never">
          <div style="text-align:center;">
            <div style="font-size:28px;margin-bottom:8px;"></div>
            <div style="color:#999;font-size:13px;">我的端口</div>
            <div style="font-size:24px;font-weight:600;">{{ portCount }}</div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="never">
          <div style="text-align:center;">
            <div style="font-size:28px;margin-bottom:8px;"></div>
            <div style="color:#999;font-size:13px;">购物车</div>
            <div style="font-size:24px;font-weight:600;">{{ cartStore.count }}</div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="never">
          <div style="text-align:center;">
            <div style="font-size:28px;margin-bottom:8px;"></div>
            <div style="color:#999;font-size:13px;">有效订单</div>
            <div style="font-size:24px;font-weight:600;">{{ orderCount }}</div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="never">
          <div style="text-align:center;">
            <div style="font-size:28px;margin-bottom:8px;"></div>
            <div style="color:#999;font-size:13px;">注册时间</div>
            <div style="font-size:16px;">{{ regDate }}</div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-card shadow="never">
      <template #header>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span>快捷操作</span>
        </div>
      </template>
      <el-space :size="16">
        <el-button type="primary" @click="$router.push('/console/shop')">
          <el-icon><ShoppingCart /></el-icon> 购买端口
        </el-button>
        <el-button @click="$router.push('/console/cart')">
          <el-icon><Cart /></el-icon> 购物车
        </el-button>
        <el-button @click="$router.push('/console/orders')">
          <el-icon><List /></el-icon> 我的订单
        </el-button>
      </el-space>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useUserStore } from '../../stores/user'
import { useCartStore } from '../../stores/cart'
import { getOrders } from '../../api/shop'

const userStore = useUserStore()
const cartStore = useCartStore()

const portCount = ref(0)
const orderCount = ref(0)
const regDate = ref('')

onMounted(async () => {
  // 加载用户信息
  if (!userStore.userInfo) {
    try {
      const res = await import('../../api/user').then(m => m.getUserInfo())
      if (res.code === 1) userStore.setInfo(res.data)
    } catch (e) { /* ignore */ }
  }

  cartStore.load()

  // 加载订单统计
  try {
    const res = await getOrders()
    if (res.code === 1) {
      const orders = res.data || []
      orderCount.value = orders.filter(o => o.status === 1).length
    }
  } catch (e) { /* ignore */ }

  if (userStore.userInfo?.create_time) {
    regDate.value = userStore.userInfo.create_time.split(' ')[0]
  }
})
</script>

<style scoped>
.console { max-width: 960px; margin: 0 auto; padding: 32px 40px; }
.console-header { display: flex; align-items: center; gap: 16px; margin-bottom: 24px; }
</style>

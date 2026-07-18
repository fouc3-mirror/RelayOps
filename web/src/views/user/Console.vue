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
            <div style="color:#999;font-size:13px;">我的产品</div>
            <div style="font-size:24px;font-weight:600;">{{ clients.length }}</div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="never">
          <div style="text-align:center;">
            <div style="color:#999;font-size:13px;">购物车</div>
            <div style="font-size:24px;font-weight:600;">{{ cartStore.count }}</div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="never">
          <div style="text-align:center;">
            <div style="color:#999;font-size:13px;">有效订单</div>
            <div style="font-size:24px;font-weight:600;">{{ orderCount }}</div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="never">
          <div style="text-align:center;">
            <div style="color:#999;font-size:13px;">注册时间</div>
            <div style="font-size:16px;">{{ regDate }}</div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-card shadow="never" style="margin-bottom:20px;">
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

    <el-card shadow="never">
      <template #header>
        <span>我的产品</span>
      </template>
      <el-table :data="clients" v-loading="clientLoading">
        <el-table-column label="节点" prop="node_name" />
        <el-table-column label="连接地址" width="240">
          <template #default="{ row }">{{ (row.domain || row.server_addr || '-') + ':' + row.port }}</template>
        </el-table-column>
        <el-table-column label="端口" width="100">
          <template #default="{ row }"><span style="font-family:monospace;">{{ row.port }}</span></template>
        </el-table-column>
        <el-table-column label="类型" width="80">
          <template #default="{ row }"><el-tag size="small">{{ row.proxy_type?.toUpperCase() }}</el-tag></template>
        </el-table-column>
        <el-table-column label="到期时间" width="120"><template #default="{ row }">{{ row.expire_date }}</template></el-table-column>
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="row.status_text === '运行中' ? 'success' : row.status_text === '已过期' ? 'danger' : 'info'" size="small">{{ row.status_text }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="80">
          <template #default="{ row }">
            <el-button link type="primary" @click="$router.push('/console/client/' + row.id)">详情</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div v-if="!clientLoading && clients.length === 0" style="text-align:center;padding:40px;color:#999;">
        <p>暂无产品，<router-link to="/console/shop" style="color:#409eff;">去购买</router-link></p>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useUserStore } from '../../stores/user'
import { useCartStore } from '../../stores/cart'
import { getOrders, getClients } from '../../api/shop'

const userStore = useUserStore()
const cartStore = useCartStore()

const clients = ref([])
const clientLoading = ref(false)
const orderCount = ref(0)
const regDate = ref('')

onMounted(async () => {
  if (!userStore.userInfo) {
    try {
      const res = await import('../../api/user').then(m => m.getUserInfo())
      if (res.code === 1) userStore.setInfo(res.data)
    } catch (e) {}
  }

  cartStore.load()

  clientLoading.value = true
  try {
    const res = await getClients()
    if (res.code === 1) clients.value = res.data || []
  } catch (e) {}
  clientLoading.value = false

  try {
    const res = await getOrders()
    if (res.code === 1) {
      const orders = res.data || []
      orderCount.value = orders.filter(o => o.status === 1).length
    }
  } catch (e) {}

  if (userStore.userInfo?.create_time) {
    regDate.value = userStore.userInfo.create_time.split(' ')[0]
  }
})
</script>

<style scoped>
.console { max-width: 960px; margin: 0 auto; padding: 32px 40px; }
.console-header { display: flex; align-items: center; gap: 16px; margin-bottom: 24px; }
</style>

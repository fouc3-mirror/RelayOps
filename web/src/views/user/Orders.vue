<template>
  <div class="orders-page">
    <el-page-header @back="$router.push('/console')" title="返回控制台" content="我的订单" style="margin-bottom:24px;" />

    <el-card shadow="never">
      <el-table :data="orders" style="width:100%;" v-loading="loading">
        <el-table-column label="订单号" prop="order_no" width="200">
          <template #default="{ row }">
            <span style="font-family:monospace;font-size:13px;">{{ row.order_no }}</span>
          </template>
        </el-table-column>
        <el-table-column label="节点" prop="node_name" width="140" />
        <el-table-column label="端口" width="100">
          <template #default="{ row }">
            <span style="font-family:monospace;">{{ row.port }}</span>
          </template>
        </el-table-column>
        <el-table-column label="类型" width="80">
          <template #default="{ row }">
            <el-tag size="small">{{ row.proxy_type?.toUpperCase() }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="时长" width="80">
          <template #default="{ row }">{{ row.duration }}月</template>
        </el-table-column>
        <el-table-column label="金额" width="100">
          <template #default="{ row }">
            <span style="color:#f56c6c;font-weight:600;">¥{{ row.amount }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="statusType(row.status)">{{ row.status_text }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="创建时间" prop="create_time_text" width="160" />
        <el-table-column label="操作" width="120">
          <template #default="{ row }">
            <el-button v-if="row.status === 0" type="primary" link @click="handlePay(row)">去支付</el-button>
            <span v-else-if="row.status === 1" style="color:#67c23a;font-size:13px;"></span>
          </template>
        </el-table-column>
      </el-table>

      <div v-if="!loading && orders.length === 0" style="text-align:center;padding:60px;color:#999;">
        <div style="font-size:48px;margin-bottom:16px;"></div>
        <p>暂无订单</p>
        <el-button type="primary" @click="$router.push('/console/shop')">去选购</el-button>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getOrders, orderPay } from '../../api/shop'

const orders = ref([])
const loading = ref(false)

function statusType(status) {
  const map = { 0: 'warning', 1: 'success', 2: 'info', 3: 'danger' }
  return map[status] || 'info'
}

async function loadOrders() {
  loading.value = true
  try {
    const res = await getOrders()
    if (res.code === 1) {
      orders.value = res.data || []
    }
  } finally {
    loading.value = false
  }
}

async function handlePay(row) {
  const payType = 'alipay' // 默认支付宝，后续可加选择
  try {
    const res = await orderPay(row.id, payType)
    if (res.code === 1 && res.url) {
      window.location.href = res.url
    } else {
      ElMessage.error(res.msg || '获取支付链接失败')
    }
  } catch (e) {
    ElMessage.error('请求失败')
  }
}

onMounted(() => {
  loadOrders()
})
</script>

<style scoped>
.orders-page { max-width: 960px; margin: 0 auto; padding: 32px 40px; }
</style>

<template>
  <div class="cart-page">
    <el-page-header @back="$router.push('/console')" title="返回控制台" content="购物车" style="margin-bottom:24px;" />

    <el-card shadow="never">
      <template #header>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span>购物车 ({{ cartStore.count }})</span>
          <el-button v-if="cartStore.count > 0" type="danger" link @click="handleClear">清空购物车</el-button>
        </div>
      </template>

      <div v-if="cartStore.count === 0" style="text-align:center;padding:60px;color:#999;">
        <div style="font-size:48px;margin-bottom:16px;">🛒</div>
        <p>购物车是空的</p>
        <el-button type="primary" @click="$router.push('/console/shop')">去选购</el-button>
      </div>

      <el-table v-else :data="cartStore.items" style="width:100%;">
        <el-table-column label="节点" prop="node_name" />
        <el-table-column label="端口" width="120">
          <template #default="{ row }">
            <span style="font-family:monospace;">{{ row.port }}</span>
          </template>
        </el-table-column>
        <el-table-column label="类型" width="100">
          <template #default="{ row }">
            <el-tag size="small">{{ row.proxy_type.toUpperCase() }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="时长" width="100">
          <template #default="{ row }">{{ row.duration }} 个月</template>
        </el-table-column>
        <el-table-column label="单价" width="100">
          <template #default="{ row }">¥{{ row.price }}/月</template>
        </el-table-column>
        <el-table-column label="小计" width="120">
          <template #default="{ row }">
            <span style="color:#f56c6c;font-weight:600;">¥{{ (row.price * row.duration).toFixed(2) }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="80">
          <template #default="{ row }">
            <el-button type="danger" link @click="handleRemove(row.index)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <div v-if="cartStore.count > 0" style="margin-top:24px;display:flex;justify-content:flex-end;align-items:center;gap:24px;padding-top:16px;border-top:1px solid #f0f0f0;">
        <span style="font-size:14px;color:#666;">
          共 <strong>{{ cartStore.count }}</strong> 项
        </span>
        <span style="font-size:16px;">
          合计：<span style="color:#f56c6c;font-size:22px;font-weight:700;">¥{{ cartStore.total.toFixed(2) }}</span>
        </span>
        <el-button type="primary" size="large" @click="handleCheckout" :loading="checking">
          结算
        </el-button>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessageBox, ElMessage } from 'element-plus'
import { useCartStore } from '../../stores/cart'
import { orderCreate, orderPay } from '../../api/shop'

const router = useRouter()
const cartStore = useCartStore()
const checking = ref(false)

onMounted(() => {
  cartStore.load()
})

async function handleRemove(index) {
  await cartStore.removeItem(index)
}

async function handleClear() {
  try {
    await ElMessageBox.confirm('确定清空购物车？', '提示', { type: 'warning' })
    await cartStore.clearAll()
  } catch { /* cancel */ }
}

async function handleCheckout() {
  checking.value = true
  try {
    // 1. 创建订单
    const res = await orderCreate()
    if (res.code !== 1) {
      ElMessage.error(res.msg || '创建订单失败')
      return
    }

    const orders = res.orders || []
    if (orders.length === 0) {
      ElMessage.error('订单创建失败')
      return
    }

    // 2. 如果只有一个订单，直接跳转支付
    if (orders.length === 1) {
      const payRes = await orderPay(orders[0].id)
      if (payRes.code === 1 && payRes.url) {
        window.location.href = payRes.url
      } else {
        ElMessage.error(payRes.msg || '获取支付链接失败')
      }
    } else {
      // 多个订单跳转订单列表
      ElMessage.success(`已创建 ${orders.length} 个订单`)
      router.push('/console/orders')
    }
  } catch (e) {
    ElMessage.error('结算失败: ' + (e.message || '未知错误'))
  } finally {
    checking.value = false
  }
}
</script>

<style scoped>
.cart-page { max-width: 960px; margin: 0 auto; }
</style>

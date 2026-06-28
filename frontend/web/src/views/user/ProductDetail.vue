<template>
  <div class="product-detail" v-loading="loading">
    <el-page-header @back="$router.back()" title="返回商品列表" style="margin-bottom:24px;" />

    <el-card v-if="product" class="product-info">
      <div class="product-header">
        <h1>{{ product.name }}</h1>
        <el-tag size="large" :type="proxyTagType(product.proxy_type)">{{ product.proxy_type.toUpperCase() }}</el-tag>
      </div>

      <div class="product-meta">
        <div class="meta-item">
          <span class="label">节点</span>
          <span class="value">{{ product.node_name }}</span>
        </div>
        <div class="meta-item">
          <span class="label">服务器</span>
          <span class="value">{{ product.server_addr }}:{{ product.server_port }}</span>
        </div>
        <div class="meta-item">
          <span class="label">端口范围</span>
          <span class="value">{{ product.port_start }} - {{ product.port_end }}</span>
        </div>
        <div class="meta-item">
          <span class="label">可用端口</span>
          <span class="value" :class="{ 'text-danger': product.available_count === 0 }">
            {{ product.available_count }} 个
          </span>
        </div>
      </div>

      <div class="product-desc" v-if="product.description">
        <h3>商品描述</h3>
        <p>{{ product.description }}</p>
      </div>

      <el-divider />

      <div class="order-form">
        <h3>订购配置</h3>

        <el-form label-width="100px" label-position="left">
          <el-form-item label="选择端口">
            <el-select v-model="selectedPort" placeholder="请选择端口" style="width: 100%;">
              <el-option
                v-for="port in product.available_ports"
                :key="port"
                :label="`端口 ${port}`"
                :value="port"
              />
            </el-select>
            <div class="form-tip">已选端口将独占使用，直到订单到期</div>
          </el-form-item>

          <el-form-item label="购买时长">
            <el-radio-group v-model="selectedDuration">
              <el-radio-button v-for="d in product.durations" :key="d" :value="d">
                {{ d }} 个月
              </el-radio-button>
            </el-radio-group>
          </el-form-item>

          <el-form-item label="费用合计">
            <div class="price-display">
              <span class="price-label">¥{{ product.price.toFixed(2) }}</span>
              <span class="price-unit">/月 × {{ selectedDuration }} 个月 =</span>
              <span class="price-total">¥{{ totalPrice.toFixed(2) }}</span>
            </div>
          </el-form-item>
        </el-form>

        <div class="order-actions">
          <el-button
            type="primary"
            size="large"
            :loading="submitting"
            :disabled="!canSubmit"
            @click="handleOrder"
          >
            立即订购
          </el-button>
        </div>
      </div>
    </el-card>

    <el-empty v-if="!loading && !product" description="商品不存在或已下架" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { getProductDetail, orderCreateDirect } from '../../api/shop'

const route = useRoute()
const router = useRouter()

const product = ref(null)
const loading = ref(true)
const submitting = ref(false)
const selectedPort = ref(null)
const selectedDuration = ref(1)

const totalPrice = computed(() => {
  if (!product.value) return 0
  return product.value.price * selectedDuration.value
})

const canSubmit = computed(() => {
  return product.value && selectedPort.value && product.value.available_count > 0
})

function proxyTagType(type) {
  const map = { tcp: '', udp: 'warning', http: 'success', https: 'danger' }
  return map[type] || ''
}

async function loadProduct() {
  loading.value = true
  try {
    const res = await getProductDetail(route.params.id)
    if (res.code === 1) {
      product.value = res.data
      // 默认选中第一个可用端口
      if (res.data.available_ports?.length > 0) {
        selectedPort.value = res.data.available_ports[0]
      }
      // 默认选中最短时长
      if (res.data.durations?.length > 0) {
        selectedDuration.value = res.data.durations[0]
      }
    } else {
      ElMessage.error(res.msg || '加载失败')
    }
  } catch (e) {
    ElMessage.error('网络错误')
  } finally {
    loading.value = false
  }
}

async function handleOrder() {
  if (!canSubmit.value) return

  submitting.value = true
  try {
    const res = await orderCreateDirect({
      product_id: product.value.id,
      port: selectedPort.value,
      duration: selectedDuration.value,
    })

    if (res.code === 1) {
      ElMessage.success('订单创建成功')

      // 如果有支付链接，跳转到支付页面
      if (res.data.pay_url) {
        window.location.href = res.data.pay_url
      } else {
        // 跳转到订单详情或订单列表
        router.push('/console/orders')
      }
    } else {
      ElMessage.error(res.msg || '下单失败')
    }
  } catch (e) {
    ElMessage.error('网络错误')
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  loadProduct()
})
</script>

<style scoped>
.product-detail {
  max-width: 800px;
  margin: 0 auto;
}

.product-info {
  padding: 24px;
}

.product-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.product-header h1 {
  margin: 0;
  font-size: 24px;
}

.product-meta {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.meta-item .label {
  font-size: 13px;
  color: #999;
}

.meta-item .value {
  font-size: 15px;
  color: #333;
  font-weight: 500;
}

.text-danger {
  color: #f56c6c !important;
}

.product-desc {
  margin-bottom: 24px;
}

.product-desc h3 {
  font-size: 16px;
  margin-bottom: 8px;
}

.product-desc p {
  color: #666;
  line-height: 1.6;
}

.order-form {
  margin-top: 24px;
}

.order-form h3 {
  font-size: 18px;
  margin-bottom: 20px;
}

.form-tip {
  font-size: 12px;
  color: #999;
  margin-top: 4px;
}

.price-display {
  display: flex;
  align-items: baseline;
  gap: 8px;
}

.price-label {
  font-size: 20px;
  font-weight: 600;
  color: #f56c6c;
}

.price-unit {
  font-size: 14px;
  color: #999;
}

.price-total {
  font-size: 28px;
  font-weight: 700;
  color: #f56c6c;
}

.order-actions {
  margin-top: 32px;
  text-align: center;
}

.order-actions .el-button {
  min-width: 200px;
}
</style>

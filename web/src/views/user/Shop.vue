<template>
  <div class="shop">
    <el-page-header @back="$router.push('/console')" title="返回控制台" content="购买端口" style="margin-bottom:24px;" />

    <div v-if="products.length === 0 && !loading" style="text-align:center;padding:80px;color:#999;">
      <div style="font-size:48px;margin-bottom:16px;"></div>
      <p>暂无在售商品</p>
    </div>

    <!-- 商品卡片列表 -->
    <el-row :gutter="16">
      <el-col :span="8" v-for="product in products" :key="product.id" style="margin-bottom:16px;">
        <el-card shadow="hover" class="product-card">
          <div class="product-header">
            <h3>{{ product.name }}</h3>
            <el-tag size="small" :type="proxyTagType(product.proxy_type)">{{ product.proxy_type.toUpperCase() }}</el-tag>
          </div>
          <div class="product-node">{{ product.node_name }}</div>
          <div class="product-ports">
            端口范围：<span style="font-family:monospace;">{{ product.port_start }}-{{ product.port_end }}</span>
          </div>
          <div class="product-price">
            ¥<strong>{{ product.price.toFixed(2) }}</strong>/月
          </div>
          <div class="product-desc" v-if="product.description">{{ product.description }}</div>

          <div class="product-actions">
            <el-button type="primary" style="width:100%;margin-top:8px;" @click="goDetail(product.id)">
              立即订购
            </el-button>
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getProducts } from '../../api/shop'

const router = useRouter()
const products = ref([])
const loading = ref(true)

function proxyTagType(type) {
  const map = { tcp: '', udp: 'warning', http: 'success', https: 'danger' }
  return map[type] || ''
}

function goDetail(id) {
  router.push(`/product/${id}`)
}

async function loadProducts() {
  loading.value = true
  try {
    const res = await getProducts()
    if (res.code === 1) {
      products.value = res.data || []
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadProducts()
})
</script>

<style scoped>
.shop { max-width: 1100px; margin: 0 auto; padding: 32px 40px; }
.product-card { height: 100%; }
.product-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.product-header h3 { font-size: 16px; margin: 0; }
.product-node { font-size: 13px; color: #666; margin-bottom: 6px; }
.product-ports { font-size: 13px; color: #999; margin-bottom: 8px; }
.product-price { font-size: 14px; color: #f56c6c; margin-bottom: 8px; }
.product-price strong { font-size: 22px; }
.product-desc { font-size: 12px; color: #999; margin-bottom: 8px; }
.product-actions { border-top: 1px solid #f0f0f0; padding-top: 12px; }
</style>

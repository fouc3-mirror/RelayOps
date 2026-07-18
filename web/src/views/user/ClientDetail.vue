<template>
  <div class="client-detail" v-loading="loading">
    <el-page-header @back="$router.push('/console')" title="返回控制台" style="margin-bottom:24px;" />

    <el-card v-if="client">
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
        <h1 style="font-size:20px;margin:0;">{{ client.product_name || client.node_name || '产品详情' }}</h1>
        <el-tag>{{ client.proxy_type?.toUpperCase() }}</el-tag>
        <el-tag :type="statusType">{{ client.status_text }}</el-tag>
      </div>

      <h3 style="font-size:15px;color:#888;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #f0f0f0;">基本信息</h3>
      <el-row :gutter="16" style="margin-bottom:24px;">
        <el-col :span="12"><div style="margin-bottom:8px;"><span style="color:#999;font-size:13px;">节点</span><div>{{ client.node_name || '-' }}</div></div></el-col>
        <el-col :span="12"><div style="margin-bottom:8px;"><span style="color:#999;font-size:13px;">连接地址</span><div style="font-family:monospace;">{{ connectAddr }}</div></div></el-col>
        <el-col :span="12"><div style="margin-bottom:8px;"><span style="color:#999;font-size:13px;">端口</span><div style="font-family:monospace;font-size:18px;font-weight:600;color:#409eff;">{{ client.port }}</div></div></el-col>
        <el-col :span="12"><div style="margin-bottom:8px;"><span style="color:#999;font-size:13px;">到期时间</span><div :style="{color: client.is_expired ? '#f56c6c' : '#333'}">{{ client.expire_date }}</div></div></el-col>
      </el-row>

      <h3 style="font-size:15px;color:#888;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #f0f0f0;">流量信息</h3>
      <el-row :gutter="16" style="margin-bottom:24px;">
        <el-col :span="8">
          <div style="margin-bottom:8px;"><span style="color:#999;font-size:13px;">剩余流量</span>
            <div v-if="client.remaining_traffic === -1" style="color:#999;font-size:18px;font-weight:600;">不限</div>
            <div v-else-if="client.remaining_traffic <= 0" style="color:#f56c6c;font-size:18px;font-weight:600;">已用完</div>
            <div v-else style="color:#67c23a;font-size:18px;font-weight:600;">{{ (client.remaining_traffic / 1024 / 1024 / 1024).toFixed(2) }} GB</div>
          </div>
        </el-col>
        <el-col :span="8"><div style="margin-bottom:8px;"><span style="color:#999;font-size:13px;">已用流量</span><div style="font-family:monospace;">{{ client.traffic_used_gb?.toFixed(2) || '0.00' }} GB</div></div></el-col>
        <el-col :span="8"><div style="margin-bottom:8px;"><span style="color:#999;font-size:13px;">流量上限</span><div>{{ client.traffic_limit_gb > 0 ? client.traffic_limit_gb + ' GB' : '不限' }}</div></div></el-col>
      </el-row>

      <div style="background:#f5f7fa;border-radius:8px;padding:20px;">
        <h3 style="font-size:15px;color:#888;margin-bottom:16px;">连接信息</h3>
        <div style="font-size:14px;line-height:2;">
          <div><span style="color:#999;display:inline-block;width:60px;">地址</span><code>{{ connectAddr }}</code></div>
          <div><span style="color:#999;display:inline-block;width:60px;">端口</span><code>{{ client.port }}</code></div>
          <div><span style="color:#999;display:inline-block;width:60px;">协议</span><code>{{ client.proxy_type }}</code></div>
        </div>
      </div>
    </el-card>

    <el-empty v-if="!loading && !client" description="产品不存在" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { getClientDetail } from '../../api/shop'

const route = useRoute()
const client = ref(null)
const loading = ref(true)

const connectAddr = computed(() => {
  if (!client.value) return '-'
  const host = client.value.domain || client.value.server_addr || '-'
  return host + ':' + client.value.port
})

const statusType = computed(() => {
  if (!client.value) return 'info'
  if (client.value.status_text === '运行中') return 'success'
  if (client.value.status_text === '已过期') return 'danger'
  return 'info'
})

onMounted(async () => {
  const id = route.params.id || route.path.split('/').pop()
  try {
    const res = await getClientDetail(id)
    if (res.code === 1) client.value = res.data
  } catch (e) {}
  loading.value = false
})
</script>

<style scoped>
.client-detail { max-width: 700px; margin: 0 auto; padding: 32px 40px; }
</style>

<template>
  <el-card>
    <template #header>
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <span>订单列表</span>
        <div style="display:flex;gap:10px;">
          <el-select v-model="filterStatus" placeholder="全部" clearable @change="loadList" style="width:120px;">
            <el-option :value="0" label="待支付" />
            <el-option :value="1" label="已支付" />
            <el-option :value="2" label="已过期" />
            <el-option :value="3" label="已取消" />
          </el-select>
          <el-button type="primary" size="small" @click="showAdd">+ 手动下单</el-button>
        </div>
      </div>
    </template>
    <el-table :data="list" v-loading="loading">
      <el-table-column prop="id" label="ID" width="60" />
      <el-table-column prop="order_no" label="订单号" width="180"><template #default="{ row }"><span style="font-family:monospace;font-size:12px;">{{ row.order_no }}</span></template></el-table-column>
      <el-table-column label="用户" width="120"><template #default="{ row }">{{ row.username || '用户'+row.user_id }}</template></el-table-column>
      <el-table-column label="节点" prop="node_name" width="120" />
      <el-table-column label="端口" width="70"><template #default="{ row }"><span style="font-family:monospace;">{{ row.port }}</span></template></el-table-column>
      <el-table-column label="金额" width="90"><template #default="{ row }">¥{{ Number(row.amount).toFixed(2) }}</template></el-table-column>
      <el-table-column label="状态" width="80">
        <template #default="{ row }"><el-tag :type="['warning','success','info','danger'][row.status] || 'info'">{{ ['待支付','已支付','已过期','已取消'][row.status] || '未知' }}</el-tag></template>
      </el-table-column>
      <el-table-column label="下单时间" width="140"><template #default="{ row }">{{ row.create_time || '-' }}</template></el-table-column>
      <el-table-column label="操作" width="120">
        <template #default="{ row }">
          <el-button v-if="row.status === 0" link type="success" @click="payOrder(row.id)">标记支付</el-button>
          <el-button link type="danger" @click="deleteOrder(row.id)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="dialogVisible" title="手动下单" width="500px">
      <el-form :model="form" label-width="100px">
        <el-form-item label="选择用户"><el-select v-model="form.user_id" style="width:100%" filterable><el-option v-for="u in users" :key="u.id" :value="u.id" :label="u.username" /></el-select></el-form-item>
        <el-form-item label="选择节点"><el-select v-model="form.node_id" style="width:100%"><el-option v-for="n in nodes" :key="n.id" :value="n.id" :label="n.name" /></el-select></el-form-item>
        <el-form-item label="代理类型"><el-select v-model="form.proxy_type" style="width:100%"><el-option value="tcp" label="TCP" /><el-option value="udp" label="UDP" /><el-option value="http" label="HTTP" /><el-option value="https" label="HTTPS" /></el-select></el-form-item>
        <el-form-item label="端口号"><el-input-number v-model="form.port" :min="1" /></el-form-item>
        <el-form-item label="购买时长"><el-select v-model="form.duration" style="width:100%"><el-option v-for="m in [1,3,6,12]" :key="m" :value="m" :label="m+'个月'" /></el-select></el-form-item>
        <el-form-item label="金额(元)"><el-input-number v-model="form.amount" :min="0" :precision="2" /></el-form-item>
        <el-form-item label="订单状态"><el-select v-model="form.status" style="width:100%"><el-option :value="0" label="待支付" /><el-option :value="1" label="已支付" /></el-select></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSave" :loading="saving">创建订单</el-button>
      </template>
    </el-dialog>
  </el-card>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import { ElMessage, ElMessageBox } from 'element-plus'

const http = axios.create({ baseURL: '', timeout: 30000, withCredentials: true })
const list = ref([]), users = ref([]), nodes = ref([]), loading = ref(false), saving = ref(false), dialogVisible = ref(false), filterStatus = ref('')
const form = reactive({ user_id: 0, node_id: 0, proxy_type: 'tcp', port: 0, duration: 1, amount: 0, status: 1 })

function resetForm() { Object.assign(form, { user_id: 0, node_id: 0, proxy_type: 'tcp', port: 0, duration: 1, amount: 0, status: 1 }) }
async function loadList() { loading.value = true; try { const params = filterStatus.value !== '' ? { status: filterStatus.value } : {}; const res = await http.get('/api/admin/orders', { params }); if (res.data.code === 1) list.value = res.data.data || [] } finally { loading.value = false } }
async function loadRefs() { try { const [u, n] = await Promise.all([http.get('/api/admin/users'), http.get('/api/admin/nodes')]); if (u.data.code === 1) users.value = u.data.data || []; if (n.data.code === 1) nodes.value = n.data.data || [] } catch (e) {} }
async function showAdd() { resetForm(); await loadRefs(); dialogVisible.value = true }
async function handleSave() { saving.value = true; try { const res = await http.post('/api/admin/order/save', form); if (res.data.code === 1) { ElMessage.success(res.data.msg); dialogVisible.value = false; loadList() } else ElMessage.error(res.data.msg || '创建失败') } catch (e) { ElMessage.error('网络错误') } finally { saving.value = false } }
async function payOrder(id) { try { await ElMessageBox.confirm('确认标记为已支付？', '提示', { type: 'warning' }); const res = await http.post('/api/admin/order/pay', { id }); if (res.data.code === 1) { ElMessage.success(res.data.msg); loadList() } else ElMessage.error(res.data.msg || '操作失败') } catch (e) {} }
async function deleteOrder(id) { try { await ElMessageBox.confirm('确定删除该订单？', '警告', { type: 'warning', confirmButtonText: '删除' }); const res = await http.post('/api/admin/order/delete', { id }); if (res.data.code === 1) { ElMessage.success(res.data.msg); loadList() } else ElMessage.error(res.data.msg || '删除失败') } catch (e) {} }
onMounted(() => loadList())
</script>

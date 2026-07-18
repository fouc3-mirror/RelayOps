<template>
  <div>
    <el-card>
      <template #header>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span>商品列表</span>
          <el-button type="primary" size="small" @click="showAdd">+ 添加商品</el-button>
        </div>
      </template>
      <el-table :data="list" v-loading="loading">
        <el-table-column prop="id" label="ID" width="60" />
        <el-table-column prop="name" label="商品名称" />
        <el-table-column prop="node_name" label="所属节点" />
        <el-table-column label="类型" width="80">
          <template #default="{ row }"><el-tag :type="typeColors[row.proxy_type] || ''" size="small">{{ row.proxy_type?.toUpperCase() }}</el-tag></template>
        </el-table-column>
        <el-table-column label="端口范围" width="140"><template #default="{ row }">{{ row.port_start }} - {{ row.port_end }}</template></el-table-column>
        <el-table-column label="月单价" width="100"><template #default="{ row }">¥{{ Number(row.price).toFixed(2) }}</template></el-table-column>
        <el-table-column prop="duration_options" label="可选时长" width="100" />
        <el-table-column label="状态" width="80">
          <template #default="{ row }"><el-tag :type="row.status === 1 ? 'success' : 'info'">{{ row.status === 1 ? '上架' : '下架' }}</el-tag></template>
        </el-table-column>
        <el-table-column prop="sort" label="排序" width="60" />
        <el-table-column label="操作" width="180">
          <template #default="{ row }">
            <el-button link type="primary" @click="editProduct(row.id)">编辑</el-button>
            <el-button link :type="row.status === 1 ? 'warning' : 'success'" @click="toggleProduct(row.id)">{{ row.status === 1 ? '下架' : '上架' }}</el-button>
            <el-button link type="danger" @click="deleteProduct(row.id)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog v-model="dialogVisible" :title="editingId ? '编辑商品' : '添加商品'" width="600px">
      <el-form :model="form" label-width="130px">
        <el-row :gutter="16">
          <el-col :span="12"><el-form-item label="商品名称"><el-input v-model="form.name" /></el-form-item></el-col>
          <el-col :span="12"><el-form-item label="所属节点"><el-select v-model="form.node_id" style="width:100%"><el-option v-for="n in nodes" :key="n.id" :value="n.id" :label="n.name" /></el-select></el-form-item></el-col>
        </el-row>
        <el-row :gutter="16">
          <el-col :span="12"><el-form-item label="代理类型"><el-select v-model="form.proxy_type" style="width:100%"><el-option value="tcp" label="TCP" /><el-option value="udp" label="UDP" /><el-option value="http" label="HTTP" /><el-option value="https" label="HTTPS" /></el-select></el-form-item></el-col>
          <el-col :span="12"><el-form-item label="状态"><el-select v-model="form.status" style="width:100%"><el-option :value="1" label="上架" /><el-option :value="0" label="下架" /></el-select></el-form-item></el-col>
        </el-row>
        <el-row :gutter="16">
          <el-col :span="12"><el-form-item label="端口起始"><el-input-number v-model="form.port_start" :min="0" /></el-form-item></el-col>
          <el-col :span="12"><el-form-item label="端口结束"><el-input-number v-model="form.port_end" :min="0" /></el-form-item></el-col>
        </el-row>
        <el-row :gutter="16">
          <el-col :span="12"><el-form-item label="月单价(元)"><el-input-number v-model="form.price" :min="0" :precision="2" /></el-form-item></el-col>
          <el-col :span="12"><el-form-item label="排序"><el-input-number v-model="form.sort" :min="0" /></el-form-item></el-col>
        </el-row>
        <el-row :gutter="16">
          <el-col :span="12"><el-form-item label="可选时长"><el-input v-model="form.duration_options" placeholder="1,3,6,12" /></el-form-item></el-col>
          <el-col :span="12"><el-form-item label="流量限制(GB)"><el-input-number v-model="form.traffic_limit" :min="0" placeholder="0=不限" /></el-form-item></el-col>
        </el-row>
        <el-form-item label="商品描述"><el-input v-model="form.description" type="textarea" :rows="2" /></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSave" :loading="saving">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import { ElMessage, ElMessageBox } from 'element-plus'

const http = axios.create({ baseURL: '', timeout: 30000, withCredentials: true })
const typeColors = { tcp: '', udp: 'warning', http: 'success', https: 'danger' }
const list = ref([]), nodes = ref([]), loading = ref(false), saving = ref(false), dialogVisible = ref(false), editingId = ref(0)
const form = reactive({ name: '', node_id: 0, proxy_type: 'tcp', port_start: 0, port_end: 0, price: 0, duration_options: '1,3,6,12', status: 1, sort: 0, description: '', traffic_limit: 0 })

function resetForm() { Object.assign(form, { name: '', node_id: 0, proxy_type: 'tcp', port_start: 0, port_end: 0, price: 0, duration_options: '1,3,6,12', status: 1, sort: 0, description: '', traffic_limit: 0 }) }
async function loadList() { loading.value = true; try { const res = await http.get('/api/admin/products'); if (res.data.code === 1) list.value = res.data.data || [] } finally { loading.value = false } }
async function loadNodes() { try { const res = await http.get('/api/admin/nodes'); if (res.data.code === 1) nodes.value = res.data.data || [] } catch (e) {} }
function showAdd() { editingId.value = 0; resetForm(); loadNodes(); dialogVisible.value = true }
async function editProduct(id) { await loadNodes(); try { const res = await http.get('/api/admin/products'); const p = (res.data.data || []).find(p => p.id === id); if (p) { editingId.value = p.id; Object.assign(form, p); dialogVisible.value = true } } catch (e) {} }
async function handleSave() { saving.value = true; try { const res = await http.post('/api/admin/product/save', { ...form, id: editingId.value }); if (res.data.code === 1) { ElMessage.success(res.data.msg); dialogVisible.value = false; loadList() } else ElMessage.error(res.data.msg || '保存失败') } catch (e) { ElMessage.error('网络错误') } finally { saving.value = false } }
async function toggleProduct(id) { try { await ElMessageBox.confirm('确定切换商品状态？', '提示', { type: 'warning' }); const res = await http.post('/api/admin/product/toggle', { id }); if (res.data.code === 1) { ElMessage.success(res.data.msg); loadList() } else ElMessage.error(res.data.msg || '操作失败') } catch (e) {} }
async function deleteProduct(id) { try { await ElMessageBox.confirm('确定删除该商品？', '警告', { type: 'warning', confirmButtonText: '删除' }); const res = await http.post('/api/admin/product/delete', { id }); if (res.data.code === 1) { ElMessage.success(res.data.msg); loadList() } else ElMessage.error(res.data.msg || '删除失败') } catch (e) {} }
onMounted(() => { loadList(); loadNodes() })
</script>

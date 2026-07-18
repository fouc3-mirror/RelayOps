<template>
  <div>
    <el-card>
      <template #header>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span>节点列表</span>
          <el-button type="primary" size="small" @click="showAdd">+ 添加节点</el-button>
        </div>
      </template>
      <el-table :data="list" v-loading="loading">
        <el-table-column prop="id" label="ID" width="60" />
        <el-table-column prop="name" label="节点名称" />
        <el-table-column prop="server_addr" label="服务器地址" />
        <el-table-column prop="domain" label="域名">
          <template #default="{ row }">{{ row.domain || '-' }}</template>
        </el-table-column>
        <el-table-column prop="server_port" label="服务端口" width="80" />
        <el-table-column prop="dashboard_port" label="Dashboard" width="100" />
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="row.status === 1 ? 'success' : 'danger'">{{ row.status === 1 ? '正常' : '禁用' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="最后心跳" width="160">
          <template #default="{ row }">{{ row.last_heartbeat ? new Date(row.last_heartbeat * 1000).toLocaleString() : '无' }}</template>
        </el-table-column>
        <el-table-column label="操作" width="180">
          <template #default="{ row }">
            <el-button link type="primary" @click="editNode(row.id)">编辑</el-button>
            <el-button link :type="row.status === 1 ? 'warning' : 'success'" @click="toggleNode(row.id)">{{ row.status === 1 ? '禁用' : '启用' }}</el-button>
            <el-button link type="danger" @click="deleteNode(row.id)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog v-model="dialogVisible" :title="editingId ? '编辑节点' : '添加节点'" width="600px">
      <el-form :model="form" label-width="120px">
        <el-row :gutter="16">
          <el-col :span="12"><el-form-item label="节点名称"><el-input v-model="form.name" /></el-form-item></el-col>
          <el-col :span="12"><el-form-item label="状态"><el-select v-model="form.status" style="width:100%"><el-option :value="1" label="启用" /><el-option :value="0" label="禁用" /></el-select></el-form-item></el-col>
        </el-row>
        <el-row :gutter="16">
          <el-col :span="12"><el-form-item label="服务器地址"><el-input v-model="form.server_addr" /></el-form-item></el-col>
          <el-col :span="12"><el-form-item label="服务端口"><el-input-number v-model="form.server_port" :min="1" :max="65535" /></el-form-item></el-col>
        </el-row>
        <el-row :gutter="16">
          <el-col :span="12"><el-form-item label="域名"><el-input v-model="form.domain" placeholder="选填" /></el-form-item></el-col>
          <el-col :span="12"><el-form-item label="认证令牌"><el-input v-model="form.auth_token" /></el-form-item></el-col>
        </el-row>
        <el-row :gutter="16">
          <el-col :span="12"><el-form-item label="Dashboard端口"><el-input-number v-model="form.dashboard_port" :min="1" :max="65535" /></el-form-item></el-col>
          <el-col :span="12"><el-form-item label="HTTP端口"><el-input-number v-model="form.http_port" :min="1" :max="65535" /></el-form-item></el-col>
        </el-row>
        <el-row :gutter="16">
          <el-col :span="12"><el-form-item label="Dashboard用户"><el-input v-model="form.dashboard_user" /></el-form-item></el-col>
          <el-col :span="12"><el-form-item label="Dashboard密码"><el-input v-model="form.dashboard_pass" /></el-form-item></el-col>
        </el-row>
        <el-row :gutter="16">
          <el-col :span="12"><el-form-item label="HTTPS端口"><el-input-number v-model="form.https_port" :min="1" :max="65535" /></el-form-item></el-col>
          <el-col :span="12"><el-form-item label="端口范围起始"><el-input-number v-model="form.port_range_start" :min="0" /></el-form-item></el-col>
        </el-row>
        <el-row :gutter="16">
          <el-col :span="12"><el-form-item label="端口范围结束"><el-input-number v-model="form.port_range_end" :min="0" /></el-form-item></el-col>
          <el-col :span="12" />
        </el-row>
        <el-form-item label="节点描述"><el-input v-model="form.description" type="textarea" :rows="2" /></el-form-item>
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
const list = ref([]), loading = ref(false), saving = ref(false), dialogVisible = ref(false), editingId = ref(0)
const form = reactive({ name: '', server_addr: '', server_port: 7000, auth_token: '', domain: '', http_port: 80, https_port: 443, dashboard_port: 7500, dashboard_user: '', dashboard_pass: '', port_range_start: 0, port_range_end: 0, status: 1, description: '' })

function resetForm() { Object.assign(form, { name: '', server_addr: '', server_port: 7000, auth_token: '', domain: '', http_port: 80, https_port: 443, dashboard_port: 7500, dashboard_user: '', dashboard_pass: '', port_range_start: 0, port_range_end: 0, status: 1, description: '' }) }

async function loadList() { loading.value = true; try { const res = await http.get('/api/admin/nodes'); if (res.data.code === 1) list.value = res.data.data || [] } finally { loading.value = false } }

function showAdd() { editingId.value = 0; resetForm(); dialogVisible.value = true }

async function editNode(id) { try { const res = await http.get('/api/admin/node/detail', { params: { id } }); if (res.data.code === 1) { const n = res.data.data; editingId.value = n.id; Object.assign(form, n); dialogVisible.value = true } } catch (e) {} }

async function handleSave() { saving.value = true; try { const res = await http.post('/api/admin/node/save', { ...form, id: editingId.value }); if (res.data.code === 1) { ElMessage.success(res.data.msg); dialogVisible.value = false; loadList() } else ElMessage.error(res.data.msg || '保存失败') } catch (e) { ElMessage.error('网络错误') } finally { saving.value = false } }

async function toggleNode(id) { try { await ElMessageBox.confirm('确定切换节点状态？', '提示', { type: 'warning' }); const res = await http.post('/api/admin/node/toggle', { id }); if (res.data.code === 1) { ElMessage.success(res.data.msg); loadList() } else ElMessage.error(res.data.msg || '操作失败') } catch (e) {} }

async function deleteNode(id) { try { await ElMessageBox.confirm('确定删除该节点？此操作不可恢复！', '警告', { type: 'warning', confirmButtonText: '删除' }); const res = await http.post('/api/admin/node/delete', { id }); if (res.data.code === 1) { ElMessage.success(res.data.msg); loadList() } else ElMessage.error(res.data.msg || '删除失败') } catch (e) {} }

onMounted(() => loadList())
</script>

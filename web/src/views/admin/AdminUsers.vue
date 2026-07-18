<template>
  <el-card>
    <template #header>
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <span>用户列表</span>
        <el-button type="primary" size="small" @click="showAdd">+ 添加用户</el-button>
      </div>
    </template>
    <el-table :data="list" v-loading="loading">
      <el-table-column prop="id" label="ID" width="60" />
      <el-table-column prop="username" label="用户名" />
      <el-table-column label="昵称"><template #default="{ row }">{{ row.nickname || '-' }}</template></el-table-column>
      <el-table-column label="邮箱"><template #default="{ row }">{{ row.email || '-' }}</template></el-table-column>
      <el-table-column label="手机"><template #default="{ row }">{{ row.phone || '-' }}</template></el-table-column>
      <el-table-column label="状态" width="80">
        <template #default="{ row }"><el-tag :type="row.status === 1 ? 'success' : 'danger'">{{ row.status === 1 ? '正常' : '禁用' }}</el-tag></template>
      </el-table-column>
      <el-table-column label="注册时间" width="140"><template #default="{ row }">{{ row.create_time || '-' }}</template></el-table-column>
      <el-table-column label="操作" width="180">
        <template #default="{ row }">
          <el-button link type="primary" @click="editUser(row.id)">编辑</el-button>
          <el-button link :type="row.status === 1 ? 'warning' : 'success'" @click="toggleUser(row.id)">{{ row.status === 1 ? '禁用' : '启用' }}</el-button>
          <el-button link type="danger" @click="deleteUser(row.id)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="dialogVisible" :title="editingId ? '编辑用户' : '添加用户'" width="500px">
      <el-form :model="form" label-width="80px">
        <el-form-item label="用户名"><el-input v-model="form.username" /></el-form-item>
        <el-form-item label="密码"><el-input v-model="form.password" type="password" :placeholder="editingId ? '留空则不修改' : ''" /></el-form-item>
        <el-form-item label="昵称"><el-input v-model="form.nickname" /></el-form-item>
        <el-form-item label="邮箱"><el-input v-model="form.email" /></el-form-item>
        <el-form-item label="手机"><el-input v-model="form.phone" /></el-form-item>
        <el-form-item label="状态"><el-select v-model="form.status" style="width:100%"><el-option :value="1" label="正常" /><el-option :value="0" label="禁用" /></el-select></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSave" :loading="saving">保存</el-button>
      </template>
    </el-dialog>
  </el-card>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import { ElMessage, ElMessageBox } from 'element-plus'

const http = axios.create({ baseURL: '', timeout: 30000, withCredentials: true })
const list = ref([]), loading = ref(false), saving = ref(false), dialogVisible = ref(false), editingId = ref(0)
const form = reactive({ username: '', password: '', nickname: '', email: '', phone: '', status: 1 })

function resetForm() { Object.assign(form, { username: '', password: '', nickname: '', email: '', phone: '', status: 1 }) }
async function loadList() { loading.value = true; try { const res = await http.get('/api/admin/users'); if (res.data.code === 1) list.value = res.data.data || [] } finally { loading.value = false } }
function showAdd() { editingId.value = 0; resetForm(); dialogVisible.value = true }
async function editUser(id) { try { const res = await http.get('/api/admin/user/detail', { params: { id } }); if (res.data.code === 1) { editingId.value = res.data.data.id; Object.assign(form, { ...res.data.data, password: '' }); dialogVisible.value = true } } catch (e) {} }
async function handleSave() { saving.value = true; try { const res = await http.post('/api/admin/user/save', { ...form, id: editingId.value }); if (res.data.code === 1) { ElMessage.success(res.data.msg); dialogVisible.value = false; loadList() } else ElMessage.error(res.data.msg || '保存失败') } catch (e) { ElMessage.error('网络错误') } finally { saving.value = false } }
async function toggleUser(id) { try { await ElMessageBox.confirm('确定切换用户状态？', '提示', { type: 'warning' }); const res = await http.post('/api/admin/user/toggle', { id }); if (res.data.code === 1) { ElMessage.success(res.data.msg); loadList() } else ElMessage.error(res.data.msg || '操作失败') } catch (e) {} }
async function deleteUser(id) { try { await ElMessageBox.confirm('确定删除该用户？此操作不可恢复！', '警告', { type: 'warning', confirmButtonText: '删除' }); const res = await http.post('/api/admin/user/delete', { id }); if (res.data.code === 1) { ElMessage.success(res.data.msg); loadList() } else ElMessage.error(res.data.msg || '删除失败') } catch (e) {} }
onMounted(() => loadList())
</script>

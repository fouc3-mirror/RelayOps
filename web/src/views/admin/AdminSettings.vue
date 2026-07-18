<template>
  <el-card>
    <el-tabs v-model="activeTab">
      <el-tab-pane label="基本设置" name="basic">
        <el-form :model="basic" label-width="140px">
          <el-row :gutter="16">
            <el-col :span="12"><el-form-item label="系统名称"><el-input v-model="basic.site_name" /></el-form-item></el-col>
            <el-col :span="12"><el-form-item label="网站关键词"><el-input v-model="basic.site_keywords" /></el-form-item></el-col>
          </el-row>
          <el-form-item label="系统描述"><el-input v-model="basic.site_description" type="textarea" :rows="2" /></el-form-item>
          <el-row :gutter="16">
            <el-col :span="12"><el-form-item label="Logo URL"><el-input v-model="basic.site_logo" /></el-form-item></el-col>
            <el-col :span="12"><el-form-item label="Favicon URL"><el-input v-model="basic.site_favicon" /></el-form-item></el-col>
          </el-row>
          <el-form-item label="页脚代码"><el-input v-model="basic.site_footer" type="textarea" :rows="2" /></el-form-item>
          <el-form-item label="轮播图片"><el-input v-model="basic.site_banner_1" placeholder="第1张" style="margin-bottom:6px;" /><el-input v-model="basic.site_banner_2" placeholder="第2张" style="margin-bottom:6px;" /><el-input v-model="basic.site_banner_3" placeholder="第3张" /></el-form-item>
          <el-button type="primary" @click="save('basic')" :loading="saving">保存基本设置</el-button>
        </el-form>
      </el-tab-pane>
      <el-tab-pane label="系统信息" name="system">
        <el-form :model="system" label-width="140px">
          <el-row :gutter="16">
            <el-col :span="12"><el-form-item label="管理员邮箱"><el-input v-model="system.admin_email" /></el-form-item></el-col>
            <el-col :span="12"><el-form-item label="管理员电话"><el-input v-model="system.admin_phone" /></el-form-item></el-col>
          </el-row>
          <el-form-item label="ICP备案号"><el-input v-model="system.icp_number" /></el-form-item>
          <el-button type="primary" @click="save('system')" :loading="saving">保存系统信息</el-button>
        </el-form>
      </el-tab-pane>
      <el-tab-pane label="邮件配置" name="email">
        <el-form :model="email" label-width="140px">
          <el-row :gutter="16">
            <el-col :span="12"><el-form-item label="SMTP服务器"><el-input v-model="email.smtp_host" /></el-form-item></el-col>
            <el-col :span="12"><el-form-item label="SMTP端口"><el-input v-model="email.smtp_port" /></el-form-item></el-col>
          </el-row>
          <el-row :gutter="16">
            <el-col :span="12"><el-form-item label="SMTP用户名"><el-input v-model="email.smtp_user" /></el-form-item></el-col>
            <el-col :span="12"><el-form-item label="SMTP密码"><el-input v-model="email.smtp_pass" type="password" /></el-form-item></el-col>
          </el-row>
          <el-row :gutter="16">
            <el-col :span="12"><el-form-item label="发件人邮箱"><el-input v-model="email.smtp_from" /></el-form-item></el-col>
            <el-col :span="12"><el-form-item label="发件人名称"><el-input v-model="email.smtp_name" /></el-form-item></el-col>
          </el-row>
          <el-row :gutter="16">
            <el-col :span="12"><el-form-item label="启用SSL"><el-switch v-model="email.smtp_ssl" active-value="1" inactive-value="0" /></el-form-item></el-col>
            <el-col :span="12"><el-form-item label="验证码有效期"><el-input v-model="email.verify_expire" placeholder="秒" /></el-form-item></el-col>
          </el-row>
          <el-button type="primary" @click="save('email')" :loading="saving">保存邮件配置</el-button>
        </el-form>
      </el-tab-pane>
      <el-tab-pane label="支付配置" name="pay">
        <el-form :model="pay" label-width="140px">
          <el-form-item label="易支付接口地址"><el-input v-model="pay.epay_url" /></el-form-item>
          <el-form-item label="商户ID"><el-input v-model="pay.epay_pid" /></el-form-item>
          <el-form-item label="商户密钥"><el-input v-model="pay.epay_key" /></el-form-item>
          <el-button type="primary" @click="save('pay')" :loading="saving">保存支付配置</el-button>
        </el-form>
      </el-tab-pane>
    </el-tabs>
  </el-card>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import { ElMessage } from 'element-plus'

const http = axios.create({ baseURL: '', timeout: 30000, withCredentials: true })
const activeTab = ref('basic'), saving = ref(false)
const basic = reactive({ site_name: '', site_keywords: '', site_description: '', site_logo: '', site_favicon: '', site_footer: '', site_banner_1: '', site_banner_2: '', site_banner_3: '' })
const system = reactive({ admin_email: '', admin_phone: '', icp_number: '' })
const email = reactive({ smtp_host: '', smtp_port: '465', smtp_user: '', smtp_pass: '', smtp_from: '', smtp_name: '', smtp_ssl: '1', verify_expire: '300' })
const pay = reactive({ epay_url: '', epay_pid: '', epay_key: '' })

async function loadSettings() { try { const res = await http.get('/api/admin/settings'); if (res.data.code === 1) { const s = res.data.data || {}; if (s.basic) Object.assign(basic, s.basic); if (s.system) Object.assign(system, s.system); if (s.email) Object.assign(email, s.email); if (s.pay) Object.assign(pay, s.pay) } } catch (e) {} }

async function save(group) { saving.value = true; try { const items = group === 'basic' ? basic : group === 'system' ? system : group === 'email' ? email : pay; const res = await http.post('/api/admin/settings', { group, items }); if (res.data.code === 1) ElMessage.success('已保存'); else ElMessage.error(res.data.msg || '保存失败') } catch (e) { ElMessage.error('网络错误') } finally { saving.value = false } }

onMounted(() => loadSettings())
</script>

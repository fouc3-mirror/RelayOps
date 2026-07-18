<template>
    <div class="home">
        <section class="hero">
            <div class="hero-grid-bg"></div>
            <div class="hero-inner">
                <div class="hero-text">
                    <div class="hero-eyebrow">轻量级 FRPS 节点管理平台</div>
                    <h1>{{ siteName }}</h1>
                    <p>高性能反向代理节点集中管控，多节点智能调度、实时流量监控、安全鉴权分离——让您的 FRPS 基础设施安全、稳定、高效运行</p>
                    <div class="hero-actions">
                        <template v-if="userStore.userInfo">
                            <a href="/console" class="btn-hero-primary">进入控制台</a>
                            <a href="/console/shop" class="btn-hero-ghost">选购商品</a>
                        </template>
                        <template v-else>
                            <a href="/login" class="btn-hero-primary">立即开始使用</a>
                            <a href="#features" class="btn-hero-ghost">了解更多</a>
                        </template>
                    </div>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat-card">
                        <div class="num blue">{{ statNodes }}</div>
                        <div class="lbl">全球节点</div>
                    </div>
                    <div class="hero-stat-card">
                        <div class="num green">{{ statProducts }}</div>
                        <div class="lbl">在售商品</div>
                    </div>
                    <div class="hero-stat-card">
                        <div class="num orange">99.9%</div>
                        <div class="lbl">系统可用率</div>
                    </div>
                </div>
            </div>
            <div class="hero-scroll-hint">
                <span>向下滚动</span>
                <div class="arrow"></div>
            </div>
        </section>

        <section class="stats-strip">
            <div class="stats-strip-inner">
                <div class="stat-item"><div class="val c1">{{ statNodes }}</div><div class="desc">运行节点</div></div>
                <div class="stat-item"><div class="val c2">{{ statProducts }}</div><div class="desc">在售商品</div></div>
                <div class="stat-item"><div class="val c3">--</div><div class="desc">注册用户</div></div>
                <div class="stat-item"><div class="val c4">99.9%</div><div class="desc">系统可用率</div></div>
            </div>
        </section>

        <section class="section" id="features">
            <div class="section-container">
                <div class="section-header">
                    <h2>为什么选择我们</h2>
                    <p>从节点部署到流量监控，提供全链路 FRPS 管理解决方案</p>
                </div>
                <div class="feature-grid">
                    <div class="feature-card">
                        <div class="icon-wrap blue">🖥️</div>
                        <h3>多节点集中管控</h3>
                        <p>统一面板管理分布在全球的 FRPS 节点，支持一键启用/禁用，实时查看节点心跳与在线状态</p>
                    </div>
                    <div class="feature-card">
                        <div class="icon-wrap green">📊</div>
                        <h3>实时流量监控</h3>
                        <p>分钟级流量采集与上报，精确追踪每个隧道的人站/出站带宽，异常流量自动触发告警</p>
                    </div>
                    <div class="feature-card">
                        <div class="icon-wrap orange">🔒</div>
                        <h3>安全鉴权分离</h3>
                        <p>管理员与用户权限完全隔离，节点通信采用独立 Bearer Token 认证体系，数据安全有保障</p>
                    </div>
                    <div class="feature-card">
                        <div class="icon-wrap purple">🛒</div>
                        <h3>自动化销售流程</h3>
                        <p>在线选购 → 下单支付 → 自动开通隧道，全流程自动化，支持易支付多通道收款</p>
                    </div>
                    <div class="feature-card">
                        <div class="icon-wrap teal">⚙️</div>
                        <h3>灵活端口管理</h3>
                        <p>自定义端口范围分配，Redis 分布式锁防并发抢占，数据库唯一索引双重保障端口正确性</p>
                    </div>
                    <div class="feature-card">
                        <div class="icon-wrap pink">📧</div>
                        <h3>轻量级通知体系</h3>
                        <p>SMTP 邮件验证码注册/找回密码，订单状态变更实时通知，到期提醒自动发送</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section section-gray" id="products">
            <div class="section-container">
                <div class="section-header">
                    <h2>热门隧道产品</h2>
                    <p>覆盖 TCP / UDP / HTTP / HTTPS 全协议栈，适配各种业务场景</p>
                </div>
                <div class="product-row">
                    <div v-if="products.length === 0" class="product-panel product-placeholder">
                        <div class="loading-placeholder">
                            <div class="l-icon"></div>
                            <p>加载中...</p>
                        </div>
                    </div>
                    <div v-for="p in products" :key="p.id" class="product-panel">
                        <div class="product-panel-top">
                            <span class="ptype" :class="p.proxy_type">{{ p.proxy_type.toUpperCase() }}</span>
                            <h4>{{ p.name }}</h4>
                            <div class="sub">{{ p.domain || p.node_name }}</div>
                            <div class="specs">
                                <div class="spec">端口 <strong>{{ p.port_start }}-{{ p.port_end }}</strong></div>
                                <div class="spec">可选 <strong>{{ (p.durations || [1]).join('/') }} 月</strong></div>
                            </div>
                        </div>
                        <div class="price-bar">
                            <div class="price">¥{{ Number(p.price).toFixed(2) }} <small>/ 月</small></div>
                            <router-link :to="'/product/' + p.id" class="buy-btn">立即订购</router-link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="how">
            <div class="section-container">
                <div class="section-header">
                    <h2>简单四步开始使用</h2>
                    <p>快速接入 FRPS 节点管理，体验轻量级效率提升</p>
                </div>
                <div class="steps-grid">
                    <div class="step-card">
                        <div class="step-num">1</div>
                        <h4>注册账户</h4>
                        <p>邮箱验证注册，安全可靠，即开即用</p>
                    </div>
                    <div class="step-card">
                        <div class="step-num">2</div>
                        <h4>选购商品</h4>
                        <p>浏览隧道产品，选择心仪的端口与时长</p>
                    </div>
                    <div class="step-card">
                        <div class="step-num">3</div>
                        <h4>在线支付</h4>
                        <p>支持支付宝/微信/QQ 扫码支付，即时到账</p>
                    </div>
                    <div class="step-card">
                        <div class="step-num">4</div>
                        <h4>即刻使用</h4>
                        <p>支付成功自动开通隧道，控制台查看连接信息</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-container">
                <div class="cta-block">
                    <h2>准备好管理您的 FRPS 节点了吗？</h2>
                    <p>立即注册，体验轻量级节点管理平台带来的效率提升</p>
                    <template v-if="userStore.userInfo">
                        <a href="/console" class="btn-cta">进入控制台</a>
                    </template>
                    <template v-else>
                        <a href="/login" class="btn-cta">免费注册</a>
                    </template>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useUserStore } from '../../stores/user'
import { getProducts } from '../../api/shop'

const userStore = useUserStore()
const siteName = document.title || '雨梦FRPS'

const statNodes = ref('--')
const statProducts = ref('--')
const products = ref([])

onMounted(async () => {
    try {
        const res = await getProducts()
        if (res.code === 1) {
            statProducts.value = res.data.length
            statNodes.value = res.data.length
            products.value = res.data.slice(0, 6)
        }
    } catch (e) { /* ignore */ }
})
</script>

<style>

.hero {
    position: relative;
    width: 100%;
    min-height: 520px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: linear-gradient(135deg, #0a1628 0%, #132742 40%, #0d3b6e 100%);
}
.hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 80% 60% at 20% 50%, rgba(64,158,255,0.12) 0%, transparent 60%),
        radial-gradient(ellipse 60% 80% at 85% 30%, rgba(99,179,237,0.10) 0%, transparent 55%),
        radial-gradient(ellipse 50% 50% at 70% 80%, rgba(15,52,96,0.25) 0%, transparent 50%);
    pointer-events: none;
}
.hero::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 120px;
    background: linear-gradient(to top, rgba(245,247,250,0.08) 0%, transparent 100%);
    pointer-events: none;
}
.hero-grid-bg {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
    background-size: 60px 60px;
    pointer-events: none;
}
.hero-inner {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 80px 40px;
    display: flex;
    align-items: center;
    gap: 80px;
}
.hero-text { flex: 1; max-width: 580px; }
.hero-eyebrow {
    display: inline-block;
    padding: 4px 14px;
    background: rgba(64,158,255,0.18);
    border: 1px solid rgba(64,158,255,0.30);
    border-radius: 20px;
    color: #7cb8ff;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: .5px;
    margin-bottom: 20px;
}
.hero-text h1 {
    font-size: 48px;
    font-weight: 800;
    color: #fff;
    line-height: 1.15;
    margin: 0 0 20px 0;
    letter-spacing: -0.5px;
}
.hero-text p {
    font-size: 17px;
    color: rgba(255,255,255,0.65);
    line-height: 1.7;
    margin-bottom: 36px;
    max-width: 460px;
}
.hero-actions { display: flex; gap: 14px; flex-wrap: wrap; }
.btn-hero-primary {
    padding: 13px 36px;
    font-size: 15px;
    border-radius: 8px;
    font-weight: 600;
    background: #409eff;
    color: #fff;
    text-decoration: none;
    box-shadow: 0 4px 18px rgba(64,158,255,0.35);
    transition: all .2s;
}
.btn-hero-primary:hover {
    background: #529bff;
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(64,158,255,0.45);
}
.btn-hero-ghost {
    padding: 13px 36px;
    font-size: 15px;
    border-radius: 8px;
    font-weight: 600;
    background: rgba(255,255,255,0.12);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.25);
    text-decoration: none;
    transition: all .2s;
}
.btn-hero-ghost:hover { background: rgba(255,255,255,0.22); }

.hero-stats { flex-shrink: 0; display: flex; flex-direction: column; gap: 16px; }
.hero-stat-card {
    background: rgba(255,255,255,0.06);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 14px;
    padding: 24px 28px;
    min-width: 200px;
    transition: all .3s;
}
.hero-stat-card:hover {
    background: rgba(255,255,255,0.10);
    border-color: rgba(255,255,255,0.15);
    transform: translateX(4px);
}
.hero-stat-card .num {
    font-size: 36px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 4px;
}
.hero-stat-card .num.blue { color: #409eff; }
.hero-stat-card .num.green { color: #67c23a; }
.hero-stat-card .num.orange { color: #e6a23c; }
.hero-stat-card .lbl {
    font-size: 13px;
    color: rgba(255,255,255,0.5);
    font-weight: 500;
}

.hero-scroll-hint {
    position: absolute;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    animation: float 2.5s ease-in-out infinite;
}
.hero-scroll-hint span { font-size: 12px; color: rgba(255,255,255,0.35); letter-spacing: 1px; }
.hero-scroll-hint .arrow {
    width: 20px; height: 20px;
    border-right: 2px solid rgba(255,255,255,0.30);
    border-bottom: 2px solid rgba(255,255,255,0.30);
    transform: rotate(45deg);
}
@keyframes float {
    0%, 100% { transform: translateX(-50%) translateY(0); }
    50% { transform: translateX(-50%) translateY(8px); }
}

.stats-strip {
    background: #fff;
    border-bottom: 1px solid #eee;
}
.stats-strip-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 40px;
    display: flex;
    justify-content: space-around;
}
.stat-item {
    text-align: center;
    padding: 32px 20px;
}
.stat-item .val {
    font-size: 32px;
    font-weight: 700;
    color: #1a1a2e;
}
.stat-item .val.c1 { color: #409eff; }
.stat-item .val.c2 { color: #67c23a; }
.stat-item .val.c3 { color: #e6a23c; }
.stat-item .val.c4 { color: #7b5cff; }
.stat-item .desc { font-size: 13px; color: #999; margin-top: 6px; }

.section { padding: 80px 40px; }
.section-gray { background: #f8f9fc; }
.section-header { text-align: center; max-width: 640px; margin: 0 auto 56px; }
.section-header h2 { font-size: 32px; font-weight: 700; color: #1a1a2e; margin-bottom: 12px; letter-spacing: -0.3px; }
.section-header p { font-size: 16px; color: #888; line-height: 1.6; }
.section-container { max-width: 1200px; margin: 0 auto; }

.feature-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
}
.feature-card {
    background: #fff;
    padding: 40px 32px;
    border-radius: 16px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    transition: all .3s;
    position: relative;
    overflow: hidden;
}
.feature-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #409eff, #67c23a);
    opacity: 0;
    transition: opacity .3s;
}
.feature-card:hover::before { opacity: 1; }
.feature-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.10);
    border-color: transparent;
}
.feature-card .icon-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 56px; height: 56px;
    border-radius: 14px;
    margin-bottom: 20px;
    font-size: 28px;
}
.feature-card .icon-wrap.blue   { background: #ecf5ff; }
.feature-card .icon-wrap.green  { background: #f0f9eb; }
.feature-card .icon-wrap.orange { background: #fdf6ec; }
.feature-card .icon-wrap.purple { background: #f3efff; }
.feature-card .icon-wrap.teal   { background: #eafffa; }
.feature-card .icon-wrap.pink   { background: #fef0f6; }
.feature-card h3 { font-size: 18px; font-weight: 600; color: #1a1a1a; margin: 0 0 10px 0; }
.feature-card p  { font-size: 14px; color: #888; line-height: 1.65; margin: 0; }

.product-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
}
.product-panel {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #f0f0f0;
    overflow: hidden;
    transition: all .3s;
    display: flex;
    flex-direction: column;
}
.product-panel:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 48px rgba(0,0,0,0.10);
}
.product-panel-top { padding: 28px 28px 0; }
.product-panel .ptype {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    margin-bottom: 14px;
}
.product-panel .ptype.tcp     { background: #409eff; }
.product-panel .ptype.udp     { background: #e6a23c; }
.product-panel .ptype.http    { background: #67c23a; }
.product-panel .ptype.https   { background: #f56c6c; }
.product-panel h4  { font-size: 17px; font-weight: 600; margin: 0 0 6px 0; }
.product-panel .sub { font-size: 13px; color: #999; margin-bottom: 14px; }
.product-panel .specs { display: flex; gap: 20px; margin-bottom: 16px; }
.product-panel .spec { font-size: 13px; color: #666; }
.product-panel .spec strong { color: #333; }
.product-panel .price-bar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 28px;
    background: #fafbfc;
    border-top: 1px solid #f0f0f0;
}
.product-panel .price-bar .price { font-size: 24px; font-weight: 700; color: #f56c6c; }
.product-panel .price-bar .price small { font-size: 13px; font-weight: 400; }
.buy-btn {
    padding: 8px 20px;
    background: #409eff;
    color: #fff;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: all .2s;
}
.buy-btn:hover { background: #337ecc; }
.product-placeholder { text-align: center; }
.loading-placeholder { text-align: center; padding: 48px; color: #999; width: 100%; }
.loading-placeholder .l-icon { font-size: 40px; margin-bottom: 12px; }

.steps-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 28px;
}
.step-card {
    background: #fff;
    border-radius: 16px;
    padding: 36px 28px;
    border: 1px solid #f0f0f0;
    text-align: center;
    position: relative;
    transition: all .3s;
}
.step-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.08);
}
.step-card .step-num {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px; height: 44px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: linear-gradient(135deg, #409eff, #337ecc);
    color: #fff;
    font-size: 18px;
    font-weight: 700;
}
.step-card h4 { font-size: 16px; font-weight: 600; margin: 0 0 8px 0; color: #1a1a1a; }
.step-card p  { font-size: 13px; color: #888; line-height: 1.6; margin: 0; }

.cta-block {
    background: linear-gradient(135deg, #0a1628 0%, #132742 40%, #0d3b6e 100%);
    border-radius: 20px;
    padding: 64px 60px;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.cta-block::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 50% 100% at 30% 50%, rgba(64,158,255,0.10) 0%, transparent 60%),
        radial-gradient(ellipse 50% 100% at 70% 50%, rgba(99,179,237,0.08) 0%, transparent 60%);
    pointer-events: none;
}
.cta-block h2 { position: relative; font-size: 32px; font-weight: 700; color: #fff; margin: 0 0 12px 0; }
.cta-block p  { position: relative; font-size: 16px; color: rgba(255,255,255,0.6); margin: 0 0 32px 0; }
.btn-cta {
    position: relative;
    display: inline-block;
    padding: 14px 44px;
    font-size: 16px;
    border-radius: 10px;
    font-weight: 600;
    background: #fff;
    color: #409eff;
    text-decoration: none;
    transition: all .2s;
}
.btn-cta:hover { background: #ecf5ff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

@media (max-width: 1024px) {
    .hero-inner { flex-direction: column; gap: 40px; padding: 60px 24px; }
    .hero-stats { flex-direction: row; width: 100%; justify-content: center; }
    .hero-stat-card { min-width: 140px; padding: 18px; }
    .feature-grid, .product-row { grid-template-columns: repeat(2, 1fr); }
    .steps-grid { grid-template-columns: repeat(2, 1fr); }
    .section { padding: 60px 24px; }
    .stats-strip-inner { flex-wrap: wrap; padding: 0 24px; }
}
@media (max-width: 640px) {
    .hero-text h1 { font-size: 32px; }
    .hero { min-height: 400px; }
    .feature-grid, .product-row, .steps-grid { grid-template-columns: 1fr; }
    .section { padding: 40px 16px; }
    .section-header h2 { font-size: 26px; }
    .hero-stats { flex-wrap: wrap; }
    .cta-block { padding: 40px 24px; border-radius: 14px; }
}
</style>
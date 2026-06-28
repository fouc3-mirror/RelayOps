import { createRouter, createWebHistory } from 'vue-router'

import UserLayout from '../views/user/UserLayout.vue'
import Home from '../views/user/Home.vue'
import UserLogin from '../views/user/Login.vue'
import Console from '../views/user/Console.vue'
import Shop from '../views/user/Shop.vue'
import ProductDetail from '../views/user/ProductDetail.vue'
import Cart from '../views/user/Cart.vue'
import Orders from '../views/user/Orders.vue'
import AdminLayout from '../views/admin/AdminLayout.vue'
import AdminLogin from '../views/admin/Login.vue'
import AdminDashboard from '../views/admin/Dashboard.vue'

const routes = [
    // ====== 管理员路由（必须在通配符前面）======
    {
        path: '/admin/login',
        component: AdminLogin,
        meta: { title: '管理员登录' }
    },
    {
        path: '/admin',
        component: AdminLayout,
        meta: { title: '管理后台' },
        children: [
            { path: '', redirect: '/admin/dashboard' },
            { path: 'dashboard', component: AdminDashboard, meta: { title: '仪表盘' } },
        ]
    },

    // ====== 用户路由 ======
    {
        path: '/login',
        component: UserLogin,
        meta: { title: '用户登录' }
    },
    {
        path: '/',
        component: UserLayout,
        meta: { title: '首页' },
        children: [
            { path: '', component: Home },
        ]
    },
    {
        path: '/product/:id',
        component: ProductDetail,
        meta: { title: '商品详情' }
    },
    {
        path: '/console',
        component: UserLayout,
        meta: { title: '控制台' },
        children: [
            { path: '', component: Console, meta: { title: '控制台' } },
            { path: 'shop', component: Shop, meta: { title: '购买端口' } },
            { path: 'cart', component: Cart, meta: { title: '购物车' } },
            { path: 'orders', component: Orders, meta: { title: '我的订单' } },
        ]
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

export default router

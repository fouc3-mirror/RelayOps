import { createRouter, createWebHistory } from 'vue-router'

import UserLayout from '../views/user/UserLayout.vue'
import Home from '../views/user/Home.vue'
import UserLogin from '../views/user/Login.vue'
import Console from '../views/user/Console.vue'
import Shop from '../views/user/Shop.vue'
import ProductDetail from '../views/user/ProductDetail.vue'
import Cart from '../views/user/Cart.vue'
import Orders from '../views/user/Orders.vue'
import ClientDetail from '../views/user/ClientDetail.vue'
import AdminLayout from '../views/admin/AdminLayout.vue'
import AdminLogin from '../views/admin/Login.vue'
import AdminDashboard from '../views/admin/Dashboard.vue'
import AdminNodes from '../views/admin/AdminNodes.vue'
import AdminProducts from '../views/admin/AdminProducts.vue'
import AdminUsers from '../views/admin/AdminUsers.vue'
import AdminOrders from '../views/admin/AdminOrders.vue'
import AdminSettings from '../views/admin/AdminSettings.vue'

const routes = [
    {
        path: '/admin/login',
        component: AdminLogin,
        meta: { title: 'Admin Login' }
    },
    {
        path: '/admin',
        component: AdminLayout,
        meta: { title: 'Admin' },
        children: [
            { path: '', redirect: '/admin/dashboard' },
            { path: 'dashboard', component: AdminDashboard, meta: { title: 'Dashboard' } },
            { path: 'nodes', component: AdminNodes, meta: { title: 'Nodes' } },
            { path: 'products', component: AdminProducts, meta: { title: 'Products' } },
            { path: 'users', component: AdminUsers, meta: { title: 'Users' } },
            { path: 'orders', component: AdminOrders, meta: { title: 'Orders' } },
            { path: 'settings', component: AdminSettings, meta: { title: 'Settings' } },
        ]
    },

    {
        path: '/login',
        component: UserLogin,
        meta: { title: 'Login' }
    },
    {
        path: '/',
        component: UserLayout,
        meta: { title: 'Home' },
        children: [
            { path: '', component: Home },
        ]
    },
    {
        path: '/product/:id',
        component: ProductDetail,
        meta: { title: 'Product Detail' }
    },
    {
        path: '/console/client/:id',
        component: ClientDetail,
        meta: { title: 'Product Detail' }
    },
    {
        path: '/console',
        component: UserLayout,
        meta: { title: 'Console' },
        children: [
            { path: '', component: Console, meta: { title: 'Console' } },
            { path: 'shop', component: Shop, meta: { title: 'Shop' } },
            { path: 'cart', component: Cart, meta: { title: 'Cart' } },
            { path: 'orders', component: Orders, meta: { title: 'My Orders' } },
        ]
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

export default router

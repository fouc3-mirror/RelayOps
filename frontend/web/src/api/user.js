import axios from 'axios'

const http = axios.create({
    baseURL: '',
    timeout: 10000,
})

// 响应拦截器：401 且是 /api/user 接口 → 跳转用户登录
http.interceptors.response.use(
    res => res.data,
    err => {
        if (err.response?.status === 401) {
            const url = err.config?.url || ''
            if (url.includes('/api/user')) {
                window.location.href = '/login'
            }
        }
        return Promise.reject(err)
    }
)

export function userLogin(data) {
    return http.post('/api/user/login', data)
}

export function getUserInfo() {
    return http.get('/api/user/info')
}

export function userLogout() {
    return http.post('/api/user/logout')
}

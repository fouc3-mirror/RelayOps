import axios from 'axios'

const http = axios.create({
    baseURL: '',
    timeout: 10000,
})

http.interceptors.response.use(
    res => res.data,
    err => {
        if (err.response?.status === 401) {
            const url = err.config?.url || ''
            if (url.includes('/api/admin')) {
                window.location.href = '/admin/login'
            }
        }
        return Promise.reject(err)
    }
)

export function adminLogin(data) {
    return http.post('/api/admin/login', data)
}

export function getAdminInfo() {
    return http.get('/api/admin/info')
}

export function adminLogout() {
    return http.post('/api/admin/logout')
}

export function getUserList() {
    return http.get('/api/admin/users')
}

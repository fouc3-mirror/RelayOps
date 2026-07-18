import axios from 'axios'

const http = axios.create({
    baseURL: '',
    timeout: 30000,
    withCredentials: true,
})

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

export default http

export function userLogin(data) {
    return http.post('/api/user/login', data)
}

export function getUserInfo() {
    return http.get('/api/user/info')
}

export function userLogout() {
    return http.post('/api/user/logout')
}

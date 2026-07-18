import http from './user'

export function getProducts() {
  return http.get('/api/user/products')
}

export function getProductDetail(id) {
  return http.get(`/api/user/product/${id}`)
}

export function getNodes() {
  return http.get('/api/user/nodes')
}

export function getPorts(nodeId) {
  return http.get('/api/user/ports', { params: { node_id: nodeId } })
}

export function cartAdd(data) {
  return http.post('/api/user/cart/add', data)
}

export function cartList() {
  return http.get('/api/user/cart')
}

export function cartRemove(index) {
  return http.post('/api/user/cart/remove', { index })
}

export function cartClear() {
  return http.post('/api/user/cart/clear')
}

export function orderCreate() {
  return http.post('/api/user/order/create')
}

export function orderCreateDirect(data) {
  return http.post('/api/user/order/create-direct', data)
}

export function orderPay(orderId, payType = 'alipay') {
  return http.get(`/api/user/order/${orderId}/pay`, { params: { pay_type: payType } })
}

export function getOrderDetail(orderId) {
  return http.get(`/api/user/order/${orderId}`)
}

export function getOrders() {
  return http.get('/api/user/orders')
}

export function getClients() {
  return http.get('/api/user/clients')
}

export function getClientDetail(id) {
  return http.get('/api/user/client/detail', { params: { id } })
}

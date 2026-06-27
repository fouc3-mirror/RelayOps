import http from './user'

// 商品列表
export function getProducts() {
  return http.get('/api/user/products')
}

// 商品详情
export function getProductDetail(id) {
  return http.get(`/api/user/product/${id}`)
}

// 节点列表
export function getNodes() {
  return http.get('/api/user/nodes')
}

// 节点可用端口
export function getPorts(nodeId) {
  return http.get('/api/user/ports', { params: { node_id: nodeId } })
}

// 购物车 - 加入
export function cartAdd(data) {
  return http.post('/api/user/cart/add', data)
}

// 购物车 - 列表
export function cartList() {
  return http.get('/api/user/cart')
}

// 购物车 - 删除
export function cartRemove(index) {
  return http.post('/api/user/cart/remove', { index })
}

// 购物车 - 清空
export function cartClear() {
  return http.post('/api/user/cart/clear')
}

// 订单 - 创建（从购物车）
export function orderCreate() {
  return http.post('/api/user/order/create')
}

// 订单 - 直接创建
export function orderCreateDirect(data) {
  return http.post('/api/user/order/create-direct', data)
}

// 订单 - 支付
export function orderPay(orderId, payType = 'alipay') {
  return http.get(`/api/user/order/${orderId}/pay`, { params: { pay_type: payType } })
}

// 订单 - 详情
export function getOrderDetail(orderId) {
  return http.get(`/api/user/order/${orderId}`)
}

// 订单 - 列表
export function getOrders() {
  return http.get('/api/user/orders')
}

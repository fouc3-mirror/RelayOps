import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { cartList as fetchCart, cartAdd, cartRemove, cartClear } from '../api/shop'

export const useCartStore = defineStore('cart', () => {
  const items = ref([])
  const count = computed(() => items.value.length)
  const total = computed(() => {
    return items.value.reduce((sum, item) => sum + (item.price * item.duration), 0)
  })

  async function load() {
    try {
      const res = await fetchCart()
      if (res.code === 1) {
        items.value = res.data.items
      }
    } catch (e) {
      console.error('加载购物车失败', e)
    }
  }

  async function addItem(payload) {
    const res = await cartAdd(payload)
    if (res.code === 1) {
      await load()
    }
    return res
  }

  async function removeItem(index) {
    const res = await cartRemove(index)
    if (res.code === 1) {
      await load()
    }
    return res
  }

  async function clearAll() {
    const res = await cartClear()
    if (res.code === 1) {
      items.value = []
    }
    return res
  }

  return { items, count, total, load, addItem, removeItem, clearAll }
})

import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUserStore = defineStore('user', () => {
    const userInfo = ref(null)

    function setInfo(data) {
        userInfo.value = data
    }

    function clearInfo() {
        userInfo.value = null
    }

    return { userInfo, setInfo, clearInfo }
})

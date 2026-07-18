import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  base: '/static/dist/',
  plugins: [vue()],
  server: {
    proxy: {
      '/api': 'http://localhost:8000',
      '/install': 'http://localhost:8000',
    }
  },
  build: {
    outDir: '../public/static/dist',
    emptyOutDir: true,
  }
})

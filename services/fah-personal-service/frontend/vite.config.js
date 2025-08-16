import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            '@': resolve(__dirname, 'src')
        }
    },
    server: {
        host: '0.0.0.0',
        port: 5174,
        proxy: {
            '/api': {
                target: 'http://localhost:8009',
                changeOrigin: true,
                secure: false,
                configure: (proxy, options) => {
                    console.log('👥 Proxy personal:', options.target)
                }
            }
        }
    },
    build: {
        outDir: 'dist',
        assetsDir: 'assets'
    }
})
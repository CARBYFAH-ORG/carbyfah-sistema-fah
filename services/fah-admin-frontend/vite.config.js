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
        port: 5173,
        proxy: {
            // RUTAS ESPECÍFICAS PRIMERO
            '/api/catalogos': {
                target: 'http://localhost:8008',
                changeOrigin: true,
                secure: false,
                configure: (proxy, options) => {
                    console.log('🎯 Proxy catalogos:', options.target)
                }
            },
            '/api/organizacion': {
                target: 'http://localhost:8010',
                changeOrigin: true,
                secure: false,
                configure: (proxy, options) => {
                    console.log('🏛️ Proxy organizacion:', options.target)
                }
            },
            '/api/auth': {
                target: 'http://localhost:8000',
                changeOrigin: true,
                secure: false
            },
            '/api/archivos': {
                target: 'http://localhost:8012',
                changeOrigin: true,
                secure: false
            },
            '/api/manual': {
                target: 'http://localhost:8012',
                changeOrigin: true,
                secure: false
            }
        }
    },
    build: {
        outDir: 'dist',
        assetsDir: 'assets'
    }
})
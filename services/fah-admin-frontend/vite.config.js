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
        // ← SOLO LAS OPCIONES NECESARIAS PARA HMR SIN SOBRECARGAR
        hmr: {
            overlay: true        // Solo mostrar errores, sin clientPort forzado
        },
        // ← WATCH MÁS CONSERVADOR (solo si es necesario)
        watch: {
            usePolling: false,   // Usar eventos nativos primero
            ignored: ['**/node_modules/**', '**/dist/**']  // Ignorar carpetas pesadas
        },
        proxy: {
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
            '/api': {
                target: 'http://localhost:8008',
                changeOrigin: true,
                secure: false
            }
        }
    },
    build: {
        outDir: 'dist',
        assetsDir: 'assets'
    }
    // ← SIN optimizeDeps.force para evitar rebuilds constantes
})
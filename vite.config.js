import { defineConfig } from "vite"

export default defineConfig({
    build: {
        manifest: true,
        rollupOptions: {
            input: {
                critical: 'src/critical.js',
                main: 'src/main.js',
            },
        },
    },
})
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./index.html",
        "./src/**/*.{vue,js,ts,jsx,tsx}",
    ],
    theme: {
        extend: {
            colors: {
                'fah-blue': {
                    50: '#eff6ff',
                    500: '#3b82f6',
                    700: '#1d4ed8',
                    900: '#1e3a8a'
                }
            }
        },
    },
    plugins: [],
}
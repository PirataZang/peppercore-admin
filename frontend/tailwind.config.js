/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#ff4d4d',
          hover: '#ff3333',
        },
        secondary: '#6366f1',
        success: '#10b981',
        warning: '#f59e0b',
      }
    },
  },
  plugins: [],
}

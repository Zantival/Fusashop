/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                "primary": "#006c47",
                "primary-container": "#00b67a",
                "on-primary": "#ffffff",
                "surface": "#fcf9f8",
                "surface-container-lowest": "#ffffff",
                "surface-container-low": "#f6f3f2",
                "surface-container": "#f0eded",
                "surface-container-highest": "#e5e2e1",
                "on-surface": "#1b1c1c",
                "on-surface-variant": "#3c4a41",
                "background": "#fcf9f8",
                "secondary-container": "#feb700",
                "error": "#ba1a1a",
            },
            fontFamily: {
                headline: ["Manrope", "sans-serif"],
                body: ["Inter", "sans-serif"],
            },
        },
    },
    plugins: [],
};

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
                manrope: ['Manrope', 'sans-serif'],
            },
            animation: {
                'float-slow': 'float 6s ease-in-out infinite',
                'spin-slow': 'spin 12s linear infinite',
                'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                'grain-shift': 'grain 8s linear infinite',
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-20px)' },
                },
                fadeInUp: {
                    '0%': { opacity: '0', transform: 'translate3d(0, 40px, 0)' },
                    '100%': { opacity: '1', transform: 'translate3d(0, 0, 0)' },
                },
                grain: {
                    '0%, 100%': { backgroundPosition: '0% 0%' },
                    '50%': { backgroundPosition: '100% 100%' },
                }
            },
        },
    },
    plugins: [],
};

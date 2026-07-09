/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#eef6ff',
                    100: '#d9eaff',
                    200: '#b8d7ff',
                    300: '#87bbff',
                    400: '#5295ff',
                    500: '#2563eb',
                    600: '#1d4ed8',
                    700: '#1e40af',
                    800: '#1e3a8a',
                    900: '#172554',
                },
            },
            boxShadow: {
                soft: '0 18px 40px rgba(15, 23, 42, 0.08)',
            },
        },
    },
    plugins: [],
};


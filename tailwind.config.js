import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },

            // Same palette as before, now available as utilities (text-primary, bg-cream, ...)
            colors: {
                primary: {
                    DEFAULT: '#FF7010',
                    dark: '#E85F00',
                    light: '#FF9248',
                },
                cream: '#fdf6f0',
                peach: '#f9e9d9',
                sand: '#F0D09A',
            },

            keyframes: {
                'fade-up': {
                    '0%': { opacity: '0', transform: 'translateY(24px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'fade-down': {
                    '0%': { opacity: '0', transform: 'translateY(-12px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-14px)' },
                },
                'spin-reverse': {
                    from: { transform: 'rotate(360deg)' },
                    to: { transform: 'rotate(0deg)' },
                },
                twinkle: {
                    '0%, 100%': { opacity: '0.25', transform: 'scale(0.8)' },
                    '50%': { opacity: '1', transform: 'scale(1.25)' },
                },
                'pulse-glow': {
                    '0%, 100%': { opacity: '0.5', transform: 'scale(1)' },
                    '50%': { opacity: '0.9', transform: 'scale(1.08)' },
                },
                'ping-soft': {
                    '0%': { transform: 'scale(1)', opacity: '0.6' },
                    '100%': { transform: 'scale(2)', opacity: '0' },
                },
            },

            animation: {
                'fade-up': 'fade-up 0.8s cubic-bezier(0.22, 1, 0.36, 1) both',
                'fade-down': 'fade-down 0.35s ease-out both',
                float: 'float 6s ease-in-out infinite',
                'float-slow': 'float 9s ease-in-out infinite',
                'spin-slow': 'spin 20s linear infinite',
                'spin-slower': 'spin 40s linear infinite',
                'spin-reverse': 'spin-reverse 30s linear infinite',
                twinkle: 'twinkle 3s ease-in-out infinite',
                'pulse-glow': 'pulse-glow 4s ease-in-out infinite',
                'ping-soft': 'ping-soft 1.8s cubic-bezier(0, 0, 0.2, 1) infinite',
            },
        },
    },

    plugins: [forms],
};
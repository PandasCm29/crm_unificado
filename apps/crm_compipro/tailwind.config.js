/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.php",                  // TODOS los archivos PHP del proyecto
    "./admin/**/*.php",            // TODOS los PHP dentro de /admin
    "./assets/js/**/*.js",         // Archivos JS
    "./assets/css/**/*.css",       // Si usas clases Tailwind en CSS
  ],
  theme: {
    extend: {
      colors: {
        primary: '#ec9306',
        dark: '#000000',
        light: '#ffeac8',
        lightAlt: '#f8f2e8',
      },
      fontFamily: {
        sans: ['Poppins', 'sans-serif'],
      },
      fontWeight: {
        normal: 400,
        medium: 500,
        semibold: 600,
      },
      animation: {
        gradient: 'gradient 10s ease infinite',
      },
      keyframes: {
        gradient: {
          '0%': { backgroundPosition: '0% 50%' },
          '50%': { backgroundPosition: '100% 50%' },
          '100%': { backgroundPosition: '0% 50%' },
        },
      },
      // backgroundImage: {
      //   'gradient-primary': 'linear-gradient(-45deg, #d97706, #ec9306, #ffeac8, #ec9306, #d97706)',
      // },
    }
  },
  plugins: [],
}


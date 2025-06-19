// frontend/tailwind.config.js
/** @type {import('tailwindcss').Config} */
module.exports = {
  // Configure purge paths to scan all your Vue files for Tailwind classes
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}", // Scan all .vue, .js, .ts, .jsx, .tsx files in src/
  ],
  theme: {
    extend: {
      // You can extend Tailwind's default theme here, e.g., custom colors, fonts
      fontFamily: {
        inter: ['Inter', 'sans-serif'], // Define 'inter' font family
      },
    },
  },
  plugins: [],
};

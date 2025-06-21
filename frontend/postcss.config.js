// frontend/postcss.config.js
module.exports = {
  plugins: {
    // Changed 'tailwindcss' to '@tailwindcss/postcss' as per the error message
    "@tailwindcss/postcss": {},
    autoprefixer: {}, // Autoprefixer adds vendor prefixes to CSS rules
  },
};

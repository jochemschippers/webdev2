## Project setup
npm install
npm run build
npm run serve

Admin inlog:
username: admin
password: admin











frontend/
├── public/                 # Static assets
├── src/
│   ├── assets/             # Global assets like CSS (e.g., main.css for Tailwind)
│   ├── cards/              # Smaller, reusable card components (e.g., ProductCard.vue)
│   ├── components/         # General reusable UI components (e.g., Navbar.vue, Message.vue, LoadingSpinner.vue)
│   ├── forms/              # Form-specific components (e.g., GraphicCardForm.vue, ManufacturerForm.vue)
│   ├── pages/              # Top-level components representing distinct application pages (e.g., ProductListPage.vue, CartPage.vue)
│   ├── router/             # Vue Router configuration (index.js)
│   ├── store/              # Vuex store configuration (if used for global state)
│   ├── utils/              # Utility functions and shared helpers (e.g., api.js for API calls, components.js for Vue components)
│   └── App.vue             # Main Vue.js application component
│   └── main.js             # Entry point for the Vue application
├── .eslintrc.cjs           # ESLint configuration for code linting
├── index.html              # Main HTML file
├── package.json            # Project dependencies and scripts
├── postcss.config.js       # PostCSS configuration (for Tailwind CSS)
└── tailwind.config.js      # Tailwind CSS configuration
└── vite.config.js          # Vite configuration


import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import store from "./store";

// Import the global CSS file which contains Tailwind CSS directives
import "./assets/main.css";

createApp(App).use(store).use(router).mount("#app");

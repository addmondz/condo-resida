import { createApp } from "vue";
import { createPinia } from "pinia";
import router from "./router";
import App from "./App.vue";
import "./bootstrap";

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

router.isReady().then(() => {
    app.mount("#app");
    const splash = document.getElementById("splash");
    if (splash) {
        splash.classList.add("hide");
        splash.addEventListener("transitionend", () => splash.remove());
    }
});

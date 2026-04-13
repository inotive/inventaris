import "./bootstrap";
import "../css/app.css";
import "simplebar"; // JS init
import "simplebar/dist/simplebar.min.css";
import "primeicons/primeicons.css";
import "lineicons/dist/lineicons.css";
import "nprogress/nprogress.css";
import "preline/dist/preline";
import "vue-multiselect/dist/vue-multiselect.css";
import "leaflet/dist/leaflet.css";

import NProgress from "nprogress";
import FlowbiteVuePlugin from "@/Composables/flowbite";
import Multiselect from "vue-multiselect";

import { createApp, h } from "vue";
import { createInertiaApp, Link, Head } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "./ziggy-vue";
import { isPageLoading } from "@/Composables/loading";
import PrimeVue from "primevue/config";
import Aura from "@primevue/themes/aura";
import ToastService from "primevue/toastservice";
import { definePreset } from "@primevue/themes";
import VueQrcode from "@chenfengyuan/vue-qrcode";
import VueApexCharts from "vue3-apexcharts";
import FullCalendar from "@fullcalendar/vue3";

const AuraBlue = definePreset(Aura, {
    semantic: {
        primary: {
            50: "{blue.50}",
            100: "{blue.100}",
            200: "{blue.200}",
            300: "{blue.300}",
            400: "{blue.400}",
            500: "{blue.500}",
            600: "{blue.600}",
            700: "{blue.700}",
            800: "{blue.800}",
            900: "{blue.900}",
            950: "{blue.950}",
        },
        colorScheme: {
            light: {
                primary: {
                    color: "{primary.500}",
                    contrastColor: "#ffffff",
                    hoverColor: "{primary.600}",
                    activeColor: "{primary.700}",
                },
                surface: {
                    0: "#ffffff",
                },
            },
        },
    },
});

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

NProgress.configure({ showSpinner: true, trickleSpeed: 120 });
let npStartAt = 0;
const NP_MIN_MS = 500;
window.__suppressProgress = false;
let npActive = false;

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);
        app.use(FlowbiteVuePlugin);
        app.use(ZiggyVue);
        app.use(
            PrimeVue,
            {
                theme: {
                    preset: AuraBlue,
                    options: {
                        darkModeSelector: false,
                        cssLayer: {
                            name: "primevue",
                            order: "tailwind-base, primevue, tailwind-utilities",
                        },
                    },
                },
            }
        );
        app.use(ToastService);
        app.use(VueApexCharts);

        app.component("apexchart", VueApexCharts);
        app.component("Link", Link);
        app.component("Head", Head);
        app.component("VueQrcode", VueQrcode);
        app.component("Multiselect", Multiselect);
        app.component("FullCalendar", FullCalendar);

        document.addEventListener("inertia:start", () => {
            if (window.__suppressProgress) return;
            isPageLoading.value = true;
            npStartAt = Date.now();
            npActive = true;
            NProgress.start();
        });

        document.addEventListener("inertia:finish", () => {
            if (!npActive) return;
            isPageLoading.value = false;
            const elapsed = Date.now() - npStartAt;
            const wait = Math.max(0, NP_MIN_MS - elapsed);
            setTimeout(() => {
                NProgress.done();
                npActive = false;
            }, wait);
        });

        // Handle 419 Page Expired — refresh page to get a fresh CSRF token
        document.addEventListener("inertia:invalid", (event) => {
            if (event.detail.response.status === 419) {
                event.preventDefault();
                window.location.reload();
            }
        });

        app.mount(el);
    },
});

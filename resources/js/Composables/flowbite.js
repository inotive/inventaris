import * as FlowbiteVue from "flowbite-vue";

export default {
    install(app) {
        for (const componentKey in FlowbiteVue) {
            app.component(componentKey, FlowbiteVue[componentKey]);
        }
    },
};

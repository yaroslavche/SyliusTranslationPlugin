import Vue from 'vue';
import YaroslavcheSyliusTranslationPlugin from './YaroslavcheSyliusTranslationPlugin.vue';
import {store} from './store';

Vue.config.productionTip = false;

require('vue-snotify/styles/material.css');
import Snotify, {SnotifyPosition} from 'vue-snotify';
const options = {
    toast: {
        position: SnotifyPosition.leftTop,
        timeout: 7000
    }
};
Vue.use(Snotify, options);

window.onload = () => {
    const vm = new Vue({
        store,
        render: h => h(YaroslavcheSyliusTranslationPlugin),
    }).$mount('#yaroslavche-sylius-translation-plugin');
};


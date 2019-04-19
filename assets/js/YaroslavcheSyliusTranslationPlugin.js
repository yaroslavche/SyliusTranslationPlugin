import Vue from 'vue';
import YaroslavcheSyliusTranslationPlugin from './YaroslavcheSyliusTranslationPlugin.vue';
import {store} from './store';

Vue.config.productionTip = false;
require('vue-snotify/styles/material.css');

window.onload = () => {
    const vm = new Vue({
        store,
        render: h => h(YaroslavcheSyliusTranslationPlugin),
    }).$mount('#yaroslavche-sylius-translation-plugin');
};


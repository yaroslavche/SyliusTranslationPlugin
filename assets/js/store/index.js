import Vue from 'vue';
import Vuex from 'vuex';
import axios from "axios";

Vue.use(Vuex);

export const store = new Vuex.Store({
    state: {
        locales: [],
        fullMessageCatalogue: [],
        totalMessagesCount: 0,
        messageCatalogues: [],
        selectedLocale: '',
        selectedDomain: ''
    },
    getters: {
        locales: state => {
            return state.locales;
        },
        fullMessageCatalogue: state => {
            return state.fullMessageCatalogue;
        },
        totalMessagesCount: state => {
            return state.totalMessagesCount;
        },
        messageCatalogues: state => {
            return state.messageCatalogues;
        },
        selectedLocale: state => {
            return state.selectedLocale;
        },
        selectedDomain: state => {
            return state.selectedDomain;
        },
    },
    mutations: {
        setSelectedLocale: (state, selectedLocale) => {
            state.selectedLocale = selectedLocale;
        },
        setSelectedDomain: (state, selectedDomain) => {
            state.selectedDomain = selectedDomain;
        },
    },
    actions: {
        fetchLocales: async (context) => {
            return new Promise((resolve, reject) => {
                axios.get('/admin/translation/getLocales').then(response => {
                    if (response.data.status === 'success') {
                        const locales = response.data.locales;
                        context.state.locales = locales;
                        Object.keys(locales).forEach(localeCode => {
                            context.dispatch('fetchMessageCatalogue', {localeCode});
                        });
                        resolve(response.data);
                    }
                }, error => {
                    reject(error);
                });
            });
        },
        fetchMessageCatalogue: async (context, payload) => {
            return new Promise((resolve, reject) => {
                axios.post('/admin/translation/getMessageCatalogue', payload).then(response => {
                    if (response.data.status === 'success') {
                        if (typeof payload === 'undefined') {
                            const fullMessageCatalogue = response.data.messageCatalogue;
                            context.state.fullMessageCatalogue = fullMessageCatalogue;
                            let totalMessagesCount = 0;
                            Object.keys(fullMessageCatalogue).forEach(domain => {
                                totalMessagesCount += Object.keys(fullMessageCatalogue[domain]).length;
                            });
                            context.state.totalMessagesCount = totalMessagesCount;
                        } else if(typeof payload.localeCode === 'string') {
                            Vue.set(context.state.messageCatalogues, payload.localeCode, response.data.messageCatalogue);
                        }
                        resolve(response.data);
                    }
                }, error => {
                    reject(error);
                });
            });
        }
    }
});

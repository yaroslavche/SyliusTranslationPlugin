import Vue from 'vue';
import Vuex from 'vuex';
import axios from "axios";

Vue.use(Vuex);

export const store = new Vuex.Store({
    state: {
        locales: {},
        fullMessageCatalogue: {},
        totalMessagesCount: null,
        messageCatalogues: [],
        selectedLocale: '',
        selectedDomain: '',
        filter: {}
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
        filter: state => {
            return state.filter;
        },
    },
    mutations: {
        setSelectedLocale: (state, selectedLocale) => {
            state.selectedLocale = selectedLocale;
        },
        setSelectedDomain: (state, selectedDomain) => {
            state.selectedDomain = selectedDomain;
        },
        setFilter: (state, filter) => {
            state.filter = filter;
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
                    }
                    resolve(response.data);
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
                        } else if (typeof payload.localeCode === 'string') {
                            Vue.set(context.state.messageCatalogues, payload.localeCode, response.data.messageCatalogue);
                        }
                    }
                    resolve(response.data);
                }, error => {
                    reject(error);
                });
            });
        },
        addLocale: async (context, payload) => {
            return new Promise((resolve, reject) => {
                axios.post('/admin/translation/addLocale', {localeCode: payload.localeCode}).then(response => {
                    if (response.data.status === 'success') {
                        Vue.set(context.state.locales, payload.localeCode, response.data.localeLanguageName);
                        context.dispatch('fetchMessageCatalogue', {localeCode: payload.localeCode});
                    }
                    resolve(response.data);
                }, error => {
                    reject(error);
                });
            });
        },
        removeLocale: async (context, payload) => {
            return new Promise((resolve, reject) => {
                if (!Object.keys(context.state.locales).includes(payload.localeCode)) {
                    reject({message: `Locale code ${payload.localeCode} not found`});
                    return;
                }
                axios.post('/admin/translation/removeLocale', {localeCode: payload.localeCode}).then(response => {
                    if (response.data.status === 'success') {
                        Vue.delete(context.state.locales, payload.localeCode);
                        Vue.delete(context.state.messageCatalogues, payload.localeCode);
                    }
                    resolve(response.data);
                }, error => {
                    reject(error);
                });
            });
        },
    }
});

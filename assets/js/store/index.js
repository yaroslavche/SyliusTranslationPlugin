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
        customMessageCatalogues: [],
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
        customMessageCatalogues: state => {
            return state.customMessageCatalogues;
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
            Object.entries(filter).forEach(filterData => {
                const
                    filterKey = filterData[0],
                    filterValue = filterData[1];
                Vue.set(state.filter, filterKey, filterValue);
            });
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
        calculateTotalMessagesCount: (context) => {
            let totalMessagesCount = 0;
            Object.keys(context.state.fullMessageCatalogue).forEach(domain => {
                totalMessagesCount += Object.keys(context.state.fullMessageCatalogue[domain]).length;
            });
            context.state.totalMessagesCount = totalMessagesCount;
        },
        fetchMessageCatalogue: async (context, payload) => {
            return new Promise((resolve, reject) => {
                axios.post('/admin/translation/getMessageCatalogue', payload).then(response => {
                    if (response.data.status === 'success') {
                        if (typeof payload === 'undefined') {
                            context.state.fullMessageCatalogue = response.data.messageCatalogue;
                        } else if (typeof payload.localeCode === 'string') {
                            Vue.set(context.state.messageCatalogues, payload.localeCode, response.data.messageCatalogue);
                            Vue.set(context.state.customMessageCatalogues, payload.localeCode, response.data.customMessageCatalogue);
                            /** todo: collect full message catalogue here from received, not on server side */
                            /** todo: check all possible errors (not set domain, id, etc) */
                            Object.keys(response.data.customMessageCatalogue).forEach(domain => {
                                const translationMessageIds = Object.keys(context.state.fullMessageCatalogue[domain]);
                                Object.keys(response.data.customMessageCatalogue[domain]).forEach(id => {
                                    if (!translationMessageIds.includes(id)) {
                                        Vue.set(context.state.fullMessageCatalogue[domain], id, '');
                                    }
                                });
                            });
                        }
                        context.dispatch('calculateTotalMessagesCount');
                    }
                    resolve(response.data);
                }, error => {
                    reject(error);
                });
            });
        },
        setMessage: async (context, payload) => {
            return new Promise((resolve, reject) => {
                payload['localeCode'] = context.state.selectedLocale;
                axios.post('/admin/translation/setMessage', payload).then(response => {
                    if (response.data.status === 'success') {
                        Vue.set(context.state.messageCatalogues[payload.localeCode][payload.domain], payload.id, payload.message);
                        Vue.set(context.state.customMessageCatalogues[payload.localeCode][payload.domain], payload.id, payload.message);
                        Vue.set(context.state.fullMessageCatalogue[payload.domain], payload.id, '');
                        context.dispatch('calculateTotalMessagesCount');
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

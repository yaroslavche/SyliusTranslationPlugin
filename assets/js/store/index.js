import Vue from 'vue';
import Vuex from 'vuex';
import axios from "axios";

Vue.use(Vuex);

export const store = new Vuex.Store({
    state: {
        availableLocales: {},
        fullMessageCatalogue: {},
        messageCatalogues: {},
        customMessageCatalogues: {},
        supportedLocales: {},
        totalMessagesCount: null,
        defaultLocaleCode: '',
        selectedLocaleCode: '',
        selectedDomain: '',
        filter: {}
    },
    getters: {
        availableLocales: state => {
            return state.availableLocales;
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
        selectedLocaleCode: state => {
            return state.selectedLocaleCode;
        },
        selectedDomain: state => {
            return state.selectedDomain;
        },
        filter: state => {
            return state.filter;
        },
        supportedLocales: state => {
            return state.supportedLocales;
        },
        defaultLocaleCode: state => {
            return state.defaultLocaleCode;
        },
    },
    mutations: {
        setSelectedLocaleCode: (state, selectedLocaleCode) => {
            state.selectedLocaleCode = selectedLocaleCode;
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
        }
    },
    actions: {
        fetchLocalesData: async (context) => {
            return new Promise((resolve, reject) => {
                axios.get('/admin/translation/fetchLocalesData').then(response => {
                    if (response.data.status === 'success') {
                        const availableLocales = response.data.availableLocales;
                        context.state.availableLocales = availableLocales;
                        context.state.supportedLocales = response.data.supportedLocales;
                        context.state.defaultLocaleCode = response.data.defaultLocaleCode;
                        Object.keys(availableLocales).forEach(localeCode => {
                            context.dispatch('fetchLocaleMessageCatalogues', {localeCode});
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
        fetchFullMessageCatalogue: async (context) => {
            return new Promise((resolve, reject) => {
                axios.get('/admin/translation/fetchFullMessageCatalogue').then(response => {
                    if (response.data.status === 'success') {
                        Vue.set(context.state, 'fullMessageCatalogue', response.data.full);
                        context.dispatch('calculateTotalMessagesCount');
                    }
                    resolve(response.data);
                }, error => {
                    reject(error);
                });
            });
        },
        fetchLocaleMessageCatalogues: async (context, payload) => {
            return new Promise((resolve, reject) => {
                axios.post('/admin/translation/fetchLocaleMessageCatalogues', payload).then(response => {
                    if (response.data.status === 'success') {
                        for (const domain in response.data.translated) {
                            const catalogue = response.data.translated[domain];
                            for (const id in catalogue) {
                                const message = catalogue[id];
                                context.dispatch('setMessageUpdateStore', {
                                    type: 'messageCatalogues',
                                    localeCode: payload.localeCode,
                                    domain: domain,
                                    id: id,
                                    message: message
                                });
                            }
                        }
                        for (const domain in response.data.custom) {
                            const catalogue = response.data.custom[domain];
                            for (const id in catalogue) {
                                const message = catalogue[id];
                                context.dispatch('setMessageUpdateStore', {
                                    type: 'customMessageCatalogues',
                                    localeCode: payload.localeCode,
                                    domain: domain,
                                    id: id,
                                    message: message
                                });
                                context.dispatch('setMessageUpdateStore', {
                                    type: 'messageCatalogues',
                                    localeCode: payload.localeCode,
                                    domain: domain,
                                    id: id,
                                    message: message
                                });
                            }
                        }
                        context.dispatch('calculateTotalMessagesCount');
                    }
                    resolve(response.data);
                }, error => {
                    reject(error);
                });
            });
        },
        setMessageUpdateStore: (context, payload) => {
            const {type, localeCode, domain, id, message} = payload;

            if (typeof context.state[type][localeCode] !== 'object') {
                Vue.set(context.state[type], localeCode, {});
            }
            if (typeof context.state[type][localeCode][domain] !== 'object') {
                Vue.set(context.state[type][localeCode], domain, {});
            }
            Vue.set(context.state[type][localeCode][domain], id, message);
        },
        setMessage: async (context, payload) => {
            return new Promise((resolve, reject) => {
                axios.post('/admin/translation/setMessage', payload).then(response => {
                    if (response.data.status === 'success') {
                        context.dispatch('setMessageUpdateStore', payload);
                        if (response.data.reloadFullCatalogue === true) {
                            context.dispatch('fetchFullMessageCatalogue');
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
                axios.post('/admin/translation/addLocale', payload).then(response => {
                    if (response.data.status === 'success') {
                        Vue.set(context.state.availableLocales, payload.localeCode, response.data.localeLanguageName);
                        context.dispatch('fetchLocaleMessageCatalogues', payload);
                    }
                    resolve(response.data);
                }, error => {
                    reject(error);
                });
            });
        },
        removeLocale: async (context, payload) => {
            return new Promise((resolve, reject) => {
                if (!Object.keys(context.state.availableLocales).includes(payload.localeCode)) {
                    reject({message: `Locale code ${payload.localeCode} not found`});
                    return;
                }
                axios.post('/admin/translation/removeLocale', payload).then(response => {
                    if (response.data.status === 'success') {
                        Vue.delete(context.state.availableLocales, payload.localeCode);
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

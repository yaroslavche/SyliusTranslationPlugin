<template>
    <div>
        <div v-show="selectedLocale.length === 0">
            <!-- dashboard total-->
            <div v-show="Object.keys(locales).length > 0">
                <div class="ui segment"
                     v-for="(languageName, localeCode) in locales"
                     :key="localeCode"
                >
                    <YaroslavcheSyliusTranslationPluginDashboardLocale :localeCode="localeCode">
                    </YaroslavcheSyliusTranslationPluginDashboardLocale>
                </div>
            </div>
        </div>
        <div v-show="selectedLocale.length > 0">
            <div class="ui two column stackable grid">
                <div class="four wide column">
                    <div class="ui segment">
                        <div class="ui two column stackable grid">
                            <div class="ten wide column">
                                <button class="ui blue inverted button tiny" @click="setSelectedLocale('')">
                                    <i class="angle left icon"></i>
                                    Dashboard
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- filter -->
                </div>
                <div class="twelve wide column">
                    <!-- list -->
                </div>
            </div>
        </div>
        <vue-snotify></vue-snotify>
    </div>
</template>

<script>
    import Vue from 'vue';
    import {mapGetters} from 'vuex';
    import Snotify, {SnotifyPosition} from 'vue-snotify';
    import YaroslavcheSyliusTranslationPluginDashboardLocale
        from './components/YaroslavcheSyliusTranslationPluginDashboardLocale';

    const options = {
        toast: {
            position: SnotifyPosition.rightTop
        }
    };

    Vue.use(Snotify, options);

    export default {
        name: 'YaroslavcheSyliusTranslationPlugin',
        components: {
            YaroslavcheSyliusTranslationPluginDashboardLocale
        },
        computed: {
            ...mapGetters([
                'locales',
                'fullMessageCatalogue',
                'totalMessagesCount',
                'messageCatalogues',
                'selectedLocale'
            ])
        },
        methods: {
            setSelectedLocale(localeCode) {
                this.$store.commit('setSelectedLocale', localeCode);
            }
        },
        created() {
            this.$store.dispatch('fetchLocales').then(result => {
                if (result.status === 'error') {
                    this.$snotify.error(result.message);
                }
            }, error => {
                this.$snotify.error(error.message);
            });
            this.$store.dispatch('fetchMessageCatalogue').then(result => {
                if (result.status === 'error') {
                    this.$snotify.error(result.message);
                }
            }, error => {
                this.$snotify.error(error.message);
            });
        }
    }
</script>

<style scoped>

</style>

<template>
    <div class="container-fluid">
        <vue-snotify></vue-snotify>
        <div class="ui stackable grid">
            <div class="column">
                <h1 class="ui header">
                    <i class="circular translate icon"></i>
                    <div class="content">
                        Translation
                        <div class="sub header">YaroslavcheSyliusTranslationPlugin v0.3.0</div>
                    </div>
                </h1>
            </div>
        </div>
        <div v-show="selectedLocale.length === 0">
            <YaroslavcheSyliusTranslationPluginDashboardTotal></YaroslavcheSyliusTranslationPluginDashboardTotal>
            <div v-show="Object.keys(locales).length > 0">
                <YaroslavcheSyliusTranslationPluginDashboardLocale
                        v-for="(languageName, localeCode) in locales"
                        :localeCode="localeCode"
                        :key="localeCode"
                >
                </YaroslavcheSyliusTranslationPluginDashboardLocale>
            </div>
            <div class="ui segment">
                <div class="ui floating dropdown labeled search icon button" ref="newLocaleCode">
                    <i class="world icon"></i>
                    <span class="text">Select Language</span>
                    <div class="menu">
                        <div class="item">Some list</div>
                    </div>
                </div>
                <button class="ui primary button" @click="addLocale">Add</button>
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
    </div>
</template>

<script>
    import {mapGetters} from 'vuex';
    import YaroslavcheSyliusTranslationPluginDashboardLocale
        from './components/YaroslavcheSyliusTranslationPluginDashboardLocale';
    import YaroslavcheSyliusTranslationPluginDashboardTotal
        from './components/YaroslavcheSyliusTranslationPluginDashboardTotal';

    export default {
        name: 'YaroslavcheSyliusTranslationPlugin',
        components: {
            YaroslavcheSyliusTranslationPluginDashboardTotal,
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
            },
            addLocale() {
                const payload = {localeCode: this.$refs.newLocaleCode.value};
                this.$store.dispatch('addLocale', payload).then(result => {
                    if (result.status === 'error') {
                        this.$snotify.error(result.message);
                    }
                }, error => {
                    this.$snotify.error(error.message);
                });
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

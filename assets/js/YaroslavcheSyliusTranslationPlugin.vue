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
                <div class="ui floating dropdown labeled search icon button" ref="supportedLocalesDropdown">
                    <i class="world icon"></i>
                    <span class="text">Select Language</span>
                    <div class="menu">
                        <div class="item"
                             v-for="(languageName, localeCode) in supportedLocales" :key="localeCode"
                             @click="selectedNewLocale = localeCode"
                             @keyup.enter="selectedNewLocale = localeCode"
                        >
                            <i :class="[localeCode, 'flag']"></i>
                            {{ languageName }}
                        </div>
                    </div>
                </div>
                <input type="hidden" ref="newLocaleCode">
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
                    <YaroslavcheSyliusTranslationPluginLocaleFilter></YaroslavcheSyliusTranslationPluginLocaleFilter>
                </div>
                <div class="twelve wide column">
                    <YaroslavcheSyliusTranslationPluginLocaleMessages></YaroslavcheSyliusTranslationPluginLocaleMessages>
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
    import YaroslavcheSyliusTranslationPluginLocaleFilter
        from './components/YaroslavcheSyliusTranslationPluginLocaleFilter';
    import YaroslavcheSyliusTranslationPluginLocaleMessages
        from './components/YaroslavcheSyliusTranslationPluginLocaleMessages';

    export default {
        name: 'YaroslavcheSyliusTranslationPlugin',
        data() {
            return {
                selectedNewLocale: '',
            };
        },
        components: {
            YaroslavcheSyliusTranslationPluginDashboardTotal,
            YaroslavcheSyliusTranslationPluginDashboardLocale,
            YaroslavcheSyliusTranslationPluginLocaleFilter,
            YaroslavcheSyliusTranslationPluginLocaleMessages
        },
        computed: {
            ...mapGetters([
                'locales',
                'fullMessageCatalogue',
                'totalMessagesCount',
                'messageCatalogues',
                'selectedLocale',
                'supportedLocales',
                'defaultLocale'
            ])
        },
        methods: {
            setSelectedLocale(localeCode) {
                this.$store.commit('setSelectedLocale', localeCode);
            },
            addLocale() {
                const payload = {localeCode: this.selectedNewLocale};
                this.$store.dispatch('addLocale', payload).then(result => {
                    if (result.status === 'success') {
                        this.$snotify.success(result.message);
                    } else if (result.status === 'error') {
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
        },
        watch: {
            supportedLocales: function () {
                jQuery(this.$refs.supportedLocalesDropdown).dropdown({
                    onChange: () => {
                        // if need
                    }
                });
            },
        },
    }
</script>

<style scoped>

</style>

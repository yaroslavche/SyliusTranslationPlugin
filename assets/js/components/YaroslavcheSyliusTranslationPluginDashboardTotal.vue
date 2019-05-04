<template>
    <div class="ui segment">
        <div class="ui center aligned four column stackable divided grid">
            <div class="column">
                <div class="ui statistic">
                    <div class="value locale">
                        <span v-show="availableLocalesLoader">
                        <div class="ui active inline loader"></div>
                    </span>
                        {{ totalAvailableLocalesCount }}
                    </div>
                    <div class="label">
                        Sylius available locales
                    </div>
                </div>
            </div>
            <div class="column">
                <div class="ui statistic">
                    <div class="value">
                    <span v-show="fullMessageCatalogueLoader">
                        <div class="ui active inline loader"></div>
                    </span>
                        {{ totalDomainsCount }}
                    </div>
                    <div class="label">
                        Total domains count
                    </div>
                </div>
            </div>
            <div class="column">
                <div class="ui statistic">
                    <div class="value">
                    <span v-show="fullMessageCatalogueLoader">
                        <div class="ui active inline loader"></div>
                    </span>
                        {{ totalMessagesCount }}
                    </div>
                    <div class="label">
                        Total messages count
                    </div>
                </div>
            </div>
            <div class="column">
                <div class="ui statistic">
                    <div class="value">
                    <span v-show="totalTranslationProgressPercentageLoader">
                        <div class="ui active inline loader"></div>
                    </span>
                        {{ totalTranslationProgressPercentage }}
                    </div>
                    <div class="label">
                        Total translation progress
                    </div>
                </div>
            </div>
        </div>
        <div class="ui bottom attached progress" ref="progress">
            <div class="bar"></div>
        </div>
        <vue-snotify></vue-snotify>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex';

    export default {
        name: "YaroslavcheSyliusTranslationPluginDashboardTotal",
        data() {
            return {
                fullMessageCatalogueLoader: true,
                availableLocalesLoader: true,
                totalTranslationProgressPercentageLoader: true
            };
        },
        computed: {
            ...mapGetters([
                'availableLocales',
                'fullMessageCatalogue',
                'totalMessagesCount',
                'messageCatalogues',
            ]),
            totalAvailableLocalesCount: function () {
                if (Object.entries(this.availableLocales).length === 0) {
                    return '';
                }
                return Object.keys(this.availableLocales).length;
            },
            totalDomainsCount: function () {
                if (Object.entries(this.fullMessageCatalogue).length === 0) {
                    return '';
                }
                return Object.keys(this.fullMessageCatalogue).length;
            },
            totalTranslationProgressPercentage: function () {
                if (
                    Object.entries(this.fullMessageCatalogue).length === 0 ||
                    Object.entries(this.availableLocales).length === 0
                ) {
                    return '';
                }
                let totalTranslatedMessages = 0;
                let totalAvailableLocales = 0;
                Object.keys(this.availableLocales).forEach(localeCode => {
                    const localeCodeDomains = this.messageCatalogues[localeCode];
                    if (typeof localeCodeDomains !== 'undefined') {
                        totalAvailableLocales++;
                        Object.keys(localeCodeDomains).forEach(domain => {
                            totalTranslatedMessages += Object.keys(localeCodeDomains[domain]).length;
                        });
                    }
                });
                const totalPercentage = ((totalTranslatedMessages / (this.totalMessagesCount * totalAvailableLocales)) * 100).toFixed(2);
                if (isNaN(parseInt(totalPercentage))) {
                    return '';
                }
                jQuery(this.$refs.progress).progress({percent: parseInt(totalPercentage)});
                this.totalTranslationProgressPercentageLoader = false;
                return `${totalPercentage}%`;
            }
        },
        watch: {
            fullMessageCatalogue() {
                this.fullMessageCatalogueLoader = false;
            },
            availableLocales() {
                this.availableLocalesLoader = false;
            }
        }
    }
</script>

<style scoped>
    .ui.loader {
        font-size: 10px;
    }
</style>

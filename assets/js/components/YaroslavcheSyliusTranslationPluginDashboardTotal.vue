<template>
    <div class="ui segment">
        <div class="ui center aligned four column stackable divided grid">
            <div class="column">
                <div class="ui statistic">
                    <div class="value locale">
                        <span v-show="localesLoader">
                        <div class="ui active inline loader"></div>
                    </span>
                        {{ totalLocalesCount }}
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
                localesLoader: true,
                totalTranslationProgressPercentageLoader: true
            };
        },
        computed: {
            ...mapGetters([
                'locales',
                'fullMessageCatalogue',
                'totalMessagesCount',
                'messageCatalogues',
            ]),
            totalLocalesCount: function () {
                if (Object.entries(this.locales).length === 0) {
                    return '';
                }
                return Object.keys(this.locales).length;
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
                    Object.entries(this.locales).length === 0
                ) {
                    return '';
                }
                let totalTranslatedMessages = 0;
                let totalLocales = 0;
                Object.keys(this.locales).forEach(localeCode => {
                    const localeCodeDomains = this.messageCatalogues[localeCode];
                    if (typeof localeCodeDomains !== 'undefined') {
                        totalLocales++;
                        Object.keys(localeCodeDomains).forEach(domain => {
                            totalTranslatedMessages += Object.keys(localeCodeDomains[domain]).length;
                        });
                    }
                });
                const totalPercentage = ((totalTranslatedMessages / (this.totalMessagesCount * totalLocales)) * 100).toFixed(2);
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
            locales() {
                this.localesLoader = false;
            }
        }
    }
</script>

<style scoped>
    .ui.loader {
        font-size: 10px;
    }
</style>

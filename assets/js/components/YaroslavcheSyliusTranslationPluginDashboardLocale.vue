<template>
    <div class="ui center aligned four column stackable divided grid">
        <div class="column">
            <div class="ui statistic">
                <div class="value locale">
                    <a @click="setSelectedLocale(localeCode)">{{ localeCode }}</a>
                </div>
                <div class="label">
                    {{ localeLanguageName }}
                </div>
            </div>
        </div>
        <div class="column">
            <div class="ui statistic">
                <div class="value">
                    <span v-show="translatedMessagesCountLoader">
                        <div class="ui active inline loader"></div>
                    </span>
                    {{ translatedMessagesCount }}
                </div>
                <div class="label">
                    Translated messages
                </div>
            </div>
        </div>
        <div class="column">
            <div class="ui statistic">
                <div class="value">
                    <span v-show="untranslatedMessagesCountLoader">
                        <div class="ui active inline loader"></div>
                    </span>
                    {{ untranslatedMessagesCount }}
                </div>
                <div class="label">
                    Untranslated messages
                </div>
            </div>
        </div>
        <div class="column">
            <div class="ui statistic">
                <div class="value">
                    <span v-show="translatedMessagesPercentageLoader">
                        <div class="ui active inline loader"></div>
                    </span>
                    {{ translatedMessagesPercentage }}
                </div>
                <div class="label">
                    Translation progress
                </div>
            </div>
        </div>
    </div>
    <div class="ui bottom attached progress" data-percent="0">
        <div class="bar"></div>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex';

    export default {
        name: "YaroslavcheSyliusTranslationPluginDashboardLocale",
        props: {
            localeCode: {
                type: String,
                required: true
            }
        },
        data() {
            return {
                translatedMessagesCountLoader: true,
                untranslatedMessagesCountLoader: true,
                translatedMessagesPercentageLoader: true,
            };
        },
        computed: {
            ...mapGetters([
                'locales',
                'fullMessageCatalogue',
                'totalMessagesCount',
                'messageCatalogues',
                'selectedLocale',
                'selectedDomain',
            ]),
            localeLanguageName: {
                get: function () {
                    return this.locales[this.localeCode];
                }
            },
            translatedMessagesCount: {
                get: function () {
                    if (typeof this.messageCatalogues[this.localeCode] === 'undefined') {
                        return '';
                    } else {
                        let translatedMessagesCount = 0;
                        Object.keys(this.messageCatalogues[this.localeCode]).forEach(domain => {
                            translatedMessagesCount += Object.keys(this.messageCatalogues[this.localeCode][domain]).length;
                        });
                        this.translatedMessagesCountLoader = false;
                        return translatedMessagesCount;
                    }
                }
            },
            untranslatedMessagesCount: {
                get: function () {
                    if (typeof this.messageCatalogues[this.localeCode] === 'undefined') {
                        return '';
                    } else {
                        const untranslatedMessagesCount = this.totalMessagesCount - this.translatedMessagesCount;
                        this.untranslatedMessagesCountLoader = false;
                        return untranslatedMessagesCount;
                    }
                }
            },
            translatedMessagesPercentage: {
                get: function () {
                    if (typeof this.messageCatalogues[this.localeCode] === 'undefined') {
                        return '';
                    } else {
                        const percentage = ((this.translatedMessagesCount / this.totalMessagesCount) * 100).toFixed(2);
                        this.translatedMessagesPercentageLoader = false;
                        return `${percentage}%`;
                    }
                }
            }
        },
        methods: {
            setSelectedLocale(localeCode) {
                this.$store.commit('setSelectedLocale', localeCode);
            }
        }
    }
</script>

<style scoped>

</style>

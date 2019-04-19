<template>
    <div class="ui segment">
        <div class="ui center aligned four column stackable divided grid">
            <div class="column">
                <div class="ui statistic">
                    <div class="value locale">
                        <a class="link" @click="setSelectedLocale">{{ localeCode }}</a>
                    </div>
                    <div class="label">
                        {{ localeLanguageName }}
                    </div>
                </div>
                <div class="ui right internal attached rail">
                    <button class="ui icon button removeLocale" @click="removeLocale">
                        <i class="trash icon"></i>
                    </button>
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
        <div class="ui bottom attached progress" ref="progress">
            <div class="bar"></div>
        </div>
        <vue-snotify></vue-snotify>
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
                        jQuery(this.$refs.progress).progress({percent: parseInt(percentage)});
                        return `${percentage}%`;
                    }
                }
            }
        },
        methods: {
            setSelectedLocale() {
                this.$store.commit('setSelectedLocale', this.localeCode);
            },
            removeLocale() {
                this.$store.dispatch('removeLocale', {localeCode: this.localeCode}).then(result => {
                    if (result.status === 'error') {
                        this.$snotify.error(result.message);
                    }
                }, error => {
                    this.$snotify.error(error.message);
                });
            }
        }
    }
</script>

<style scoped>
    .ui.loader {
        font-size: 10px;
    }

    .link {
        cursor: pointer;
    }

    .removeLocale {
        float: right;
        margin: 0 !important;
    }
</style>

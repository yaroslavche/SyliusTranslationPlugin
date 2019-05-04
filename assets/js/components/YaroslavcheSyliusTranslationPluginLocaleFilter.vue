<template>
    <div class="ui segment">
        <div class="ui form">
            <div class="ui segment">
                <div class="field">
                    <label>Domain</label>
                    <div class="ui middle aligned list">
                        <div class="item domain-item"
                             v-for="(domainMessages, domainName) in fullMessageCatalogue" :key="domainName"
                        >
                            <i class="icon folder"
                               v-bind:class="{ open: domain === domainName, teal: domain === domainName }"></i>
                            <div class="content">
                                <div class="header">
                                    <a @click="setFilterDomain(domainName)">{{ domainName }} ({{ Object.keys(domainMessages).length }})</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ui segment">
                <div class="field">
                    <div class="ui toggle checkbox">
                        <input id="filterTranslated" type="checkbox" name="showTranslated"
                               v-model="showTranslated">
                        <label for="filterTranslated">Show Translated</label>
                    </div>
                </div>
                <div class="field" v-show="showTranslated">
                    <div class="ui toggle checkbox checked">
                        <input id="filterCustom" type="checkbox" name="showCustom"
                               v-model="showCustom">
                        <label for="filterCustom">Only Custom</label>
                    </div>
                </div>
                <div class="field">
                    <div class="ui toggle checkbox checked">
                        <input id="filterUntranslated" type="checkbox" name="showUntranslated"
                               v-model="showUntranslated">
                        <label for="filterUntranslated">Show Untranslated</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex';

    export default {
        name: "YaroslavcheSyliusTranslationPluginLocaleFilter",
        data() {
            return {
                showTranslated: false,
                showUntranslated: true,
                showCustom: true,
                domain: ''
            };
        },
        computed: {
            ...mapGetters([
                'fullMessageCatalogue'
            ])
        },
        watch: {
            showTranslated: function () {
                this.setFilter();
            },
            showUntranslated: function () {
                this.setFilter();
            },
            showCustom: function () {
                this.setFilter();
            },
            domain: function () {
                this.setFilter();
            },
        },
        methods: {
            setFilter: function () {
                const filter = {
                    showTranslated: this.showTranslated,
                    showUntranslated: this.showUntranslated,
                    showCustom: this.showCustom,
                    domain: this.domain
                };
                this.$store.commit('setFilter', filter);
            },
            setFilterDomain: function (domain) {
                this.domain = domain;
            }
        }
    }
</script>

<style scoped>

</style>

<template>
    <div class="ui segment">
        <div class="ui segment">
            <div class="ui two column stackable grid">
                <div class="ten wide column">
                    <h2>{{ filter.domain }}</h2>
                </div>
                <div class="six wide column">
                    <div class="ui green inverted button tiny" style="float: right;"
                         @click="showAddTranslationModal"
                    >
                        <i class="add icon"></i>
                        Add
                    </div>
                </div>
            </div>
            <div class="ui grid">
                <div class="row"
                     v-for="(message, id) in messages"
                     v-show="showMessage(message)"
                >
                    <div class="six wide column">
                        <div class="ui big label fluid" v-bind="showTooltip(id)">
                            <i class="icon check circle outline green" v-show="message.translated"></i>
                            <i class="icon cog teal" v-show="message.custom"></i>
                            <i class="icon circle outline red" v-show="!message.translated"></i>
                            <i class="icon ellipsis horizontal yellow" v-show="id.length > idMaxLength"></i>
                            <span class="fluid" style="font-size: 14px;">{{ message.id }} {{ id.length > idMaxLength ? '...' : '' }}</span>
                        </div>
                    </div>
                    <div class="ten wide column">
                        <div class="ui icon input fluid">
                            <input class="translation_input" type="text"
                                   :placeholder="message.translatedMessage"
                                   :value="message.translatedMessage"
                            >
                            <i class="edit link icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ui basic modal" ref="addTranslationModal">
            <div class="ui icon header">
                Add new message in
                <div class="ui inline dropdown" ref="domainDropdown">
                    <div class="text">
                        {{ filter.domain ? filter.domain : 'messages' }}
                    </div>
                    <i class="dropdown"></i>
                    <div class="menu">
                        <div class="item">
                            {{ filter.domain ? filter.domain : 'messages' }}
                        </div>
                        <div class="item" v-for="(domainMessages, domain) in fullMessageCatalogue" :key="domain"
                             @click="newMessage.domain = domain">
                            {{ domain }}
                        </div>
                    </div>
                </div>
                domain for "{{ selectedLocale }}" locale
            </div>
            <div class="content">
                <div class="ui center aligned grid">
                    <div class="sixteen wide column">
                        <div class="ui labeled inverted input">
                            <div class="ui label">
                                Translation Id
                            </div>
                            <input v-model="newMessage.id">
                        </div>
                    </div>
                    <div class="sixteen wide column">
                        <div class="ui labeled inverted input">
                            <div class="ui label">
                                Translation
                            </div>
                            <input v-model="newMessage.translation">
                        </div>
                    </div>
                </div>
            </div>
            <div class="actions">
                <div class="ui red basic cancel inverted button" @click="onCloseModal">
                    <i class="remove icon"></i>
                    Cancel
                </div>
                <div class="ui green ok inverted button" @click="addTranslationMessage">
                    <i class="checkmark icon"></i>
                    Apply
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex';

    export default {
        name: "YaroslavcheSyliusTranslationPluginLocaleMessages",
        data() {
            return {
                idMaxLength: 35,
                deltaY: 0,
                currentIndex: 0,
                newMessage: {}
            };
        },
        computed: {
            ...mapGetters([
                'fullMessageCatalogue',
                'messageCatalogues',
                'filter',
                'selectedLocale'
            ]),
            messages: {
                get: function () {
                    let messages = {};
                    Object.keys(this.fullMessageCatalogue).forEach(domain => {
                        if (domain !== this.filter.domain) return;
                        Object.keys(this.fullMessageCatalogue[domain]).forEach(id => {
                            const message = this.fullMessageCatalogue[domain][id];
                            let translatedMessage = null;
                            if (this.messageCatalogues[this.selectedLocale]) {
                                if (this.messageCatalogues[this.selectedLocale][domain]) {
                                    if (this.messageCatalogues[this.selectedLocale][domain][id]) {
                                        translatedMessage = this.messageCatalogues[this.selectedLocale][domain][id];
                                    }
                                }
                            }
                            messages[id] = {
                                id: id.substring(0, this.idMaxLength),
                                message,
                                translatedMessage,
                                translated: typeof (translatedMessage) === 'string',
                                custom: false,
                            };
                        });
                    });
                    return messages;
                }
            }
        },
        methods: {
            showTooltip: function (id) {
                if (id.length > this.idMaxLength) {
                    return {
                        'data-tooltip': id,
                        'data-inverted': '',
                        'data-position': 'top center',
                    }
                }
                return {}
            },
            showMessage: function (message) {
                if (this.filter.showTranslated && this.filter.showUntranslated) return true;
                if (this.filter.showTranslated && message.translated) return true;
                if (this.filter.showUntranslated && !message.translated) return true;
                if (this.filter.showCustom && message.custom) return true;
            },
            addTranslationMessage: function () {
                console.log(this.newMessage);
            },
            showAddTranslationModal() {
                jQuery(this.$refs.addTranslationModal)
                    .modal('show', {
                        onApprove: function () {
                            console.log(this);
                        },
                        onDeny: function () {
                            console.log(this);
                        }
                    })
                ;
            },
            onCloseModal: function () {
                this.newMessage = {};
                console.log('close');
            }
        },
        mounted() {
            jQuery(this.$refs.domainDropdown).dropdown({showOnFocus: false});
        },
    }
</script>

<style scoped>
    .ui.inline.dropdown {
        color: #9c6f04;
        text-decoration: underline !important;
        text-underline-style: dotted !important;
    }
</style>

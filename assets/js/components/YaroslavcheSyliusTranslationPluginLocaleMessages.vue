<template>
    <div class="ui segment">
        <vue-snotify></vue-snotify>
        <div class="ui segment">
            <div class="ui two column stackable grid">
                <div class="eight wide column">
                    <h2>{{ filter.domain }}</h2>
                </div>
                <div class="eight wide column">
                    <div class="ui two column stackable middle aligned grid">
                        <div class="ten wide column right aligned">
                            <div class="ui right labeled left icon input">
                                <i class="search icon"></i>
                                <input placeholder="Search" type="text" v-model="filterIdValue">
                                <a class="ui label">
                                    {{ Object.keys(messages).length }} / {{ totalMessagesCount }}
                                </a>
                            </div>
                        </div>
                        <div class="six wide column right aligned">
                            <div class="ui green inverted button tiny fluid"
                                 @click="showAddTranslationModal"
                            >
                                <i class="add icon"></i>
                                Add
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ui grid">
                <div class="row" v-for="(message, id) in messages">
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
                            <input type="text"
                                   :placeholder="message.translatedMessage"
                                   v-model="message.translatedMessage"
                                   @keyup.enter="editTranslationMessage(id, message, $event)"
                            >
                            <i class="edit link icon"
                               @click="editTranslationMessage(id, message, $event)"
                            ></i>
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
                        <div class="item"
                             @click="newMessage.domain = filter.domain ? filter.domain : 'messages'"
                        >
                            {{ filter.domain ? filter.domain : 'messages' }}
                        </div>
                        <div class="item" v-for="(domainMessages, domain) in fullMessageCatalogue" :key="domain"
                             @click="newMessage.domain = domain"
                        >
                            {{ domain }}
                        </div>
                    </div>
                </div>
                domain for "{{ selectedLocaleCode }}" locale
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
                            <input v-model="newMessage.message">
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
                idMaxLength: 40,
                newMessage: {},
                filterIdValue: ''
            };
        },
        computed: {
            ...mapGetters([
                'fullMessageCatalogue',
                'totalMessagesCount',
                'messageCatalogues',
                'customMessageCatalogues',
                'filter',
                'selectedLocaleCode'
            ]),
            messages: {
                get: function () {
                    let messages = {};
                    const domain = this.filter.domain || 'messages';
                    if (!this.filter.showTranslated && !this.filter.showUntranslated) return messages;
                    if (typeof this.fullMessageCatalogue[domain] === 'undefined') return;
                    Object.keys(this.fullMessageCatalogue[domain]).forEach(id => {
                        if (this.filterIdValue.length > 0 && !id.toLowerCase().includes(this.filterIdValue.toLowerCase())) return;

                        let translatedMessage = null;
                        let customMessage = null;
                        if (this.messageCatalogues[this.selectedLocaleCode]) {
                            if (this.messageCatalogues[this.selectedLocaleCode][domain]) {
                                if (this.messageCatalogues[this.selectedLocaleCode][domain][id]) {
                                    translatedMessage = this.messageCatalogues[this.selectedLocaleCode][domain][id];
                                }
                            }
                        }
                        if (this.customMessageCatalogues[this.selectedLocaleCode]) {
                            if (this.customMessageCatalogues[this.selectedLocaleCode][domain]) {
                                if (this.customMessageCatalogues[this.selectedLocaleCode][domain][id]) {
                                    translatedMessage = customMessage = this.customMessageCatalogues[this.selectedLocaleCode][domain][id];
                                }
                            }
                        }

                        const isTranslatedMessage = typeof (translatedMessage) === 'string';
                        const isCustomMessage = typeof (customMessage) === 'string';

                        if (!this.filter.showTranslated || !this.filter.showUntranslated) {
                            if (this.filter.showTranslated && !isTranslatedMessage) return;
                            if (this.filter.showUntranslated && isTranslatedMessage) return;
                        }
                        if (this.filter.showCustom && isTranslatedMessage && !isCustomMessage) return;

                        messages[id] = {
                            id: id.substring(0, this.idMaxLength),
                            translatedMessage,
                            translated: typeof (translatedMessage) === 'string',
                            custom: isCustomMessage,
                        };
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
            addTranslationMessage: function () {
                this.setMessage({
                    type: 'customMessageCatalogues',
                    localeCode: this.selectedLocaleCode,
                    domain: this.newMessage.domain,
                    id: this.newMessage.id,
                    message: this.newMessage.message
                });
            },
            editTranslationMessage: function (fullMessageId, message, event) {
                /** can be input and icon */
                // console.log(event.target);
                this.setMessage({
                    type: 'customMessageCatalogues',
                    localeCode: this.selectedLocaleCode,
                    domain: this.filter.domain,
                    id: fullMessageId,
                    message: message.translatedMessage
                });
            },
            setMessage: function (message) {
                this.$store.dispatch('setMessage', message).then(result => {
                    if (result.status === 'success') {
                        this.$snotify.success(result.message);
                    } else if (result.status === 'error') {
                        this.$snotify.error(result.message);
                    }
                }, error => {
                    this.$snotify.error(error.message);
                });
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
                this.newMessage = {
                    domain: this.filter.domain,
                    id: null,
                    message: null
                };
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

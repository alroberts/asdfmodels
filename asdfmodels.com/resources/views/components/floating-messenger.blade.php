<div
    class="site-messenger"
    x-data="siteMessenger({
        openUrlTemplate: @js(route('messages.open', ['recipient' => '__USER__'])),
        threadUrlTemplate: @js(route('messages.thread', ['thread' => '__THREAD__'])),
        unsendUrlTemplate: @js(route('messages.unsend', ['message' => '__MESSAGE__'])),
        sendUrl: @js(route('messages.store')),
        indexUrl: @js(route('messages.index')),
        csrf: @js(csrf_token())
    })"
    x-init="init()"
>
    <div class="site-messenger-window" x-cloak x-show="isOpen" x-transition>
        <header class="site-messenger-header">
            <div class="site-messenger-person">
                <span class="site-messenger-avatar">
                    <template x-if="recipient.avatar">
                        <img :src="recipient.avatar" :alt="recipient.name">
                    </template>
                    <template x-if="!recipient.avatar">
                        <span x-text="recipient.initials || '?'"></span>
                    </template>
                </span>
                <span>
                    <strong x-text="recipient.name || 'Message'"></strong>
                    <small x-text="statusText"></small>
                </span>
            </div>
            <div class="site-messenger-actions">
                <a :href="threadUrl || indexUrl" title="Open full conversation">
                    <i class="fas fa-up-right-from-square"></i>
                </a>
                <button type="button" @click="isOpen = false" aria-label="Close messenger">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </header>

        <div class="site-messenger-body" x-ref="messages">
            <template x-if="isLoading">
                <div class="site-messenger-empty">Opening conversation...</div>
            </template>

            <template x-if="!isLoading && messages.length === 0">
                <div class="site-messenger-empty">Start the conversation with a quick message.</div>
            </template>

            <template x-for="message in messages" :key="message.id">
                <div class="site-messenger-row" :class="{ 'is-mine': message.is_mine }">
                    <div class="site-messenger-bubble" :class="{ 'is-unsent': message.is_unsent }" @contextmenu.prevent="message.can_unsend && unsend(message)">
                        <p x-text="message.is_unsent ? 'Message unsent' : message.body"></p>
                        <time x-text="formatMessageTime(message)"></time>
                        <button
                            type="button"
                            class="site-messenger-unsend"
                            x-show="message.can_unsend"
                            @click="unsend(message)"
                        >
                            Unsend
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <form class="site-messenger-compose" @submit.prevent="send()">
            <textarea
                x-model="draft"
                @keydown.enter.prevent="send()"
                rows="1"
                placeholder="Write a message..."
                :disabled="isSending || isLoading"
            ></textarea>
            <button type="submit" :disabled="isSending || isLoading || !draft.trim()" aria-label="Send message">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
        <p class="site-messenger-error" x-show="error" x-text="error"></p>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }

    .site-messenger {
        position: fixed;
        bottom: 22px;
        right: 22px;
        z-index: 110;
    }

    .site-messenger-window {
        width: min(390px, calc(100vw - 28px));
        overflow: hidden;
        border: 2px solid #050505;
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
    }

    .site-messenger-header {
        align-items: center;
        background: #050505;
        color: #fff;
        display: flex;
        justify-content: space-between;
        gap: 14px;
        padding: 14px;
    }

    .site-messenger-person {
        align-items: center;
        display: flex;
        gap: 10px;
        min-width: 0;
    }

    .site-messenger-person strong,
    .site-messenger-person small {
        display: block;
    }

    .site-messenger-person strong {
        font-size: 15px;
        font-weight: 850;
        line-height: 1.1;
    }

    .site-messenger-person small {
        color: #cbd5e1;
        font-size: 12px;
        margin-top: 3px;
    }

    .site-messenger-avatar {
        align-items: center;
        background: #1f2937;
        border: 1px solid rgba(255, 255, 255, .24);
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        flex: 0 0 auto;
        font-size: 12px;
        font-weight: 850;
        height: 38px;
        justify-content: center;
        overflow: hidden;
        width: 38px;
    }

    .site-messenger-avatar img {
        display: block;
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

    .site-messenger-actions {
        align-items: center;
        display: flex;
        gap: 6px;
    }

    .site-messenger-actions a,
    .site-messenger-actions button {
        align-items: center;
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .14);
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        height: 34px;
        justify-content: center;
        text-decoration: none;
        width: 34px;
    }

    .site-messenger-body {
        background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
        display: flex;
        flex-direction: column;
        gap: 9px;
        height: 390px;
        overflow-y: auto;
        padding: 14px;
    }

    .site-messenger-empty {
        align-items: center;
        color: #64748b;
        display: flex;
        flex: 1;
        font-size: 14px;
        justify-content: center;
        text-align: center;
    }

    .site-messenger-row {
        display: flex;
        justify-content: flex-start;
    }

    .site-messenger-row.is-mine {
        justify-content: flex-end;
    }

    .site-messenger-bubble {
        max-width: 78%;
        border-radius: 18px 18px 18px 5px;
        background: #e5e7eb;
        color: #111827;
        padding: 9px 11px;
    }

    .site-messenger-row.is-mine .site-messenger-bubble {
        border-radius: 18px 18px 5px 18px;
        background: #050505;
        color: #fff;
    }

    .site-messenger-bubble.is-unsent {
        background: #f3f4f6;
        border: 1px dashed #cbd5e1;
        color: #64748b;
        font-style: italic;
    }

    .site-messenger-row.is-mine .site-messenger-bubble.is-unsent {
        background: #1f2937;
        border-color: #475569;
        color: #cbd5e1;
    }

    .site-messenger-bubble p {
        font-size: 14px;
        line-height: 1.42;
        margin: 0;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .site-messenger-bubble time {
        display: block;
        font-size: 10px;
        margin-top: 5px;
        opacity: .58;
    }

    .site-messenger-unsend {
        background: transparent;
        border: 0;
        color: inherit;
        display: block;
        font-size: 10px;
        font-weight: 850;
        margin: 5px 0 0 auto;
        opacity: .72;
        padding: 0;
        text-decoration: underline;
    }

    .site-messenger-compose {
        align-items: flex-end;
        border-top: 1px solid #e5e7eb;
        display: grid;
        gap: 9px;
        grid-template-columns: minmax(0, 1fr) 42px;
        padding: 12px;
    }

    .site-messenger-compose textarea {
        border: 1px solid #cbd5e1;
        border-radius: 16px;
        max-height: 110px;
        min-height: 42px;
        padding: 10px 12px;
        resize: vertical;
    }

    .site-messenger-compose button {
        align-items: center;
        background: #050505;
        border: 0;
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        height: 42px;
        justify-content: center;
        width: 42px;
    }

    .site-messenger-compose button:disabled {
        cursor: not-allowed;
        opacity: .35;
    }

    .site-messenger-error {
        color: #b91c1c;
        font-size: 12px;
        font-weight: 700;
        margin: -4px 12px 12px;
    }

    @media (max-width: 640px) {
        .site-messenger {
            bottom: 10px;
            right: 10px;
            left: 10px;
        }

        .site-messenger-window {
            width: 100%;
        }

        .site-messenger-body {
            height: min(58vh, 390px);
        }
    }
</style>

<script>
    window.siteMessenger = window.siteMessenger || function siteMessenger(config) {
        return {
            isOpen: false,
            isLoading: false,
            isSending: false,
            statusText: 'Messenger',
            error: '',
            indexUrl: config.indexUrl || '#',
            threadId: null,
            threadUrl: '',
            recipient: {},
            messages: [],
            knownMessageIds: new Set(),
            draft: '',
            pollTimer: null,
            init() {
                document.addEventListener('click', (event) => {
                    const link = event.target.closest('a[href*="/messages/create"]');

                    if (!link) {
                        return;
                    }

                    const url = new URL(link.href, window.location.origin);
                    const userId = url.searchParams.get('user_id');

                    if (!userId) {
                        return;
                    }

                    event.preventDefault();
                    this.openForUser(userId);
                });

                window.addEventListener('asdf:new-message-notification', (event) => {
                    const threadId = event.detail?.data?.thread_id;

                    if (threadId) {
                        this.openThread(threadId, true);
                    }
                });

                window.addEventListener('asdf:open-message-thread', (event) => {
                    const threadId = event.detail?.threadId;

                    if (threadId) {
                        this.openThread(threadId);
                    }
                });
            },
            async openForUser(userId) {
                this.isOpen = true;
                this.isLoading = true;
                this.error = '';
                this.statusText = 'Opening...';
                this.messages = [];
                this.recipient = {};

                try {
                    const url = config.openUrlTemplate.replace('__USER__', userId);
                    const response = await fetch(url, this.fetchOptions('GET'));
                    const payload = await this.parseResponse(response);
                    this.applyThread(payload);
                    this.startPolling();
                } catch (error) {
                    this.error = error.message || 'Could not open the conversation.';
                    this.statusText = 'Could not connect';
                } finally {
                    this.isLoading = false;
                }
            },
            async openThread(threadId, fromNotification = false) {
                this.isOpen = true;
                this.isLoading = true;
                this.error = '';
                this.statusText = fromNotification ? 'New message' : 'Opening...';

                try {
                    const url = config.threadUrlTemplate.replace('__THREAD__', threadId);
                    const response = await fetch(url, this.fetchOptions('GET'));
                    const payload = await this.parseResponse(response);
                    this.applyThread(payload);
                    this.startPolling();
                } catch (error) {
                    this.error = error.message || 'Could not open the conversation.';
                    this.statusText = 'Could not connect';
                } finally {
                    this.isLoading = false;
                }
            },
            async refresh() {
                if (!this.threadId || !this.isOpen) {
                    return;
                }

                try {
                    const url = config.threadUrlTemplate.replace('__THREAD__', this.threadId);
                    const response = await fetch(url, this.fetchOptions('GET'));
                    const payload = await this.parseResponse(response);
                    const hasNewIncoming = this.hasNewIncomingMessage(payload);
                    this.applyThread(payload, false);

                    if (hasNewIncoming) {
                        window.asdfSound?.play('message');
                    }
                } catch (error) {
                    console.warn(error);
                }
            },
            async send() {
                const body = this.draft.trim();

                if (!body || !this.recipient.id) {
                    return;
                }

                this.isSending = true;
                this.error = '';

                try {
                    const form = new FormData();
                    form.append('recipient_id', this.recipient.id);
                    form.append('body', body);

                    if (this.threadId) {
                        form.append('thread_id', this.threadId);
                    }

                    const response = await fetch(config.sendUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': config.csrf,
                        },
                        body: form,
                    });
                    const payload = await this.parseResponse(response);
                    this.draft = '';
                    this.applyThread(payload.thread || payload);
                } catch (error) {
                    this.error = error.message || 'Message could not be sent.';
                } finally {
                    this.isSending = false;
                }
            },
            async unsend(message) {
                if (!message?.can_unsend || !window.confirm('Unsend this message?')) {
                    return;
                }

                this.error = '';

                try {
                    const url = config.unsendUrlTemplate.replace('__MESSAGE__', message.id);
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': config.csrf,
                        },
                    });
                    const payload = await this.parseResponse(response);
                    this.applyThread(payload.thread || payload, false);
                } catch (error) {
                    this.error = error.message || 'Message could not be unsent.';
                }
            },
            applyThread(payload, shouldScroll = true) {
                this.threadId = payload.id;
                this.threadUrl = payload.url;
                this.recipient = payload.recipient || {};
                this.messages = payload.messages || [];
                this.knownMessageIds = new Set(this.messages.map((message) => message.id).filter(Boolean));
                this.statusText = 'Active now';

                if (shouldScroll) {
                    this.$nextTick(() => this.scrollToBottom());
                }
            },
            hasNewIncomingMessage(payload) {
                const incomingMessages = (payload.messages || [])
                    .filter((message) => message.id && !message.is_mine && !message.is_unsent);

                return incomingMessages.some((message) => !this.knownMessageIds.has(message.id));
            },
            formatMessageTime(message) {
                if (!message?.created_at_iso) {
                    return message?.created_at || '';
                }

                const date = new Date(message.created_at_iso);

                if (Number.isNaN(date.getTime())) {
                    return message.created_at || '';
                }

                return new Intl.DateTimeFormat([], {
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                }).format(date);
            },
            startPolling() {
                if (this.pollTimer) {
                    window.clearInterval(this.pollTimer);
                }

                this.pollTimer = window.setInterval(() => this.refresh(), 5000);
            },
            scrollToBottom() {
                const box = this.$refs.messages;

                if (box) {
                    box.scrollTop = box.scrollHeight;
                }
            },
            fetchOptions(method) {
                return {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                };
            },
            async parseResponse(response) {
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const message = payload.message || Object.values(payload.errors || {}).flat()[0] || 'Something went wrong.';
                    throw new Error(message);
                }

                return payload;
            },
        };
    };
</script>

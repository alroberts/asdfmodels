<x-app-layout>
    <style>
        .messages-app {
            background: #f8fafc;
            min-height: calc(100vh - 66px);
            padding: 34px 18px;
        }

        .messages-shell {
            background: #fff;
            border: 2px solid #0f172a;
            border-radius: 28px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .12);
            display: grid;
            grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
            height: min(780px, calc(100vh - 134px));
            margin: 0 auto;
            max-width: 1180px;
            overflow: hidden;
        }

        .messages-sidebar {
            background: #fff;
            border-right: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .messages-sidebar-head {
            border-bottom: 1px solid #e5e7eb;
            padding: 22px;
        }

        .messages-sidebar-head p {
            color: #64748b;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .14em;
            margin: 0 0 5px;
            text-transform: uppercase;
        }

        .messages-sidebar-head h1 {
            align-items: center;
            display: flex;
            font-size: 25px;
            font-weight: 950;
            gap: 10px;
            margin: 0;
        }

        .messages-thread-list {
            flex: 1;
            overflow-y: auto;
            padding: 8px;
        }

        .messages-thread-button {
            align-items: center;
            background: transparent;
            border: 0;
            border-radius: 18px;
            color: #111827;
            cursor: pointer;
            display: grid;
            gap: 12px;
            grid-template-columns: 48px minmax(0, 1fr) auto;
            padding: 12px;
            text-align: left;
            width: 100%;
        }

        .messages-thread-button:hover,
        .messages-thread-button.is-active {
            background: #f1f5f9;
        }

        .messages-avatar {
            align-items: center;
            background: #111827;
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            font-size: 14px;
            font-weight: 950;
            height: 48px;
            justify-content: center;
            overflow: hidden;
            width: 48px;
        }

        .messages-avatar img {
            display: block;
            height: 100%;
            object-fit: cover;
            width: 100%;
        }

        .messages-thread-copy {
            min-width: 0;
        }

        .messages-thread-copy strong,
        .messages-thread-copy span,
        .messages-thread-meta em {
            display: block;
        }

        .messages-thread-copy strong {
            font-size: 14px;
            font-weight: 900;
        }

        .messages-thread-copy span {
            color: #64748b;
            font-size: 12px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .messages-thread-meta {
            align-items: flex-end;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .messages-thread-meta em {
            color: #94a3b8;
            font-size: 10px;
            font-style: normal;
            white-space: nowrap;
        }

        .messages-thread-meta b {
            align-items: center;
            background: #050505;
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            font-size: 10px;
            font-weight: 950;
            height: 20px;
            justify-content: center;
            min-width: 20px;
            padding: 0 6px;
        }

        .messages-conversation {
            display: grid;
            grid-template-rows: auto minmax(0, 1fr) auto;
            min-width: 0;
        }

        .messages-conversation-head {
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding: 18px 22px;
        }

        .messages-person {
            align-items: center;
            display: flex;
            gap: 12px;
            min-width: 0;
        }

        .messages-person strong,
        .messages-person small {
            display: block;
        }

        .messages-person strong {
            font-size: 17px;
            font-weight: 950;
        }

        .messages-person small {
            color: #64748b;
            font-size: 12px;
        }

        .messages-popout {
            align-items: center;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            color: #111827;
            display: inline-flex;
            font-size: 13px;
            font-weight: 850;
            gap: 8px;
            padding: 9px 13px;
            text-decoration: none;
        }

        .messages-body {
            background: linear-gradient(180deg, #f8fafc 0%, #fff 80%);
            display: flex;
            flex-direction: column;
            gap: 10px;
            overflow-y: auto;
            padding: 22px;
        }

        .messages-row {
            display: flex;
            justify-content: flex-start;
        }

        .messages-row.is-mine {
            justify-content: flex-end;
        }

        .messages-bubble {
            background: #e5e7eb;
            border-radius: 20px 20px 20px 6px;
            color: #111827;
            max-width: min(640px, 76%);
            padding: 11px 13px;
        }

        .messages-row.is-mine .messages-bubble {
            background: #050505;
            border-radius: 20px 20px 6px 20px;
            color: #fff;
        }

        .messages-bubble.is-unsent {
            background: #f1f5f9;
            border: 1px dashed #cbd5e1;
            color: #64748b;
            font-style: italic;
        }

        .messages-bubble p {
            font-size: 14px;
            line-height: 1.45;
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .messages-bubble time {
            display: block;
            font-size: 10px;
            margin-top: 5px;
            opacity: .58;
        }

        .messages-unsend {
            background: transparent;
            border: 0;
            color: inherit;
            display: block;
            font-size: 10px;
            font-weight: 900;
            margin: 6px 0 0 auto;
            opacity: .72;
            padding: 0;
            text-decoration: underline;
        }

        .messages-compose {
            align-items: flex-end;
            border-top: 1px solid #e5e7eb;
            display: grid;
            gap: 10px;
            grid-template-columns: minmax(0, 1fr) 46px;
            padding: 14px;
        }

        .messages-compose textarea {
            border: 1px solid #cbd5e1;
            border-radius: 18px;
            max-height: 140px;
            min-height: 46px;
            padding: 12px 14px;
            resize: vertical;
        }

        .messages-compose button {
            align-items: center;
            background: #050505;
            border: 0;
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            height: 46px;
            justify-content: center;
            width: 46px;
        }

        .messages-compose button:disabled {
            cursor: not-allowed;
            opacity: .35;
        }

        .messages-empty {
            align-items: center;
            color: #64748b;
            display: flex;
            flex: 1;
            font-size: 15px;
            justify-content: center;
            padding: 34px;
            text-align: center;
        }

        .messages-error {
            color: #b91c1c;
            font-size: 12px;
            font-weight: 800;
            margin: -8px 14px 12px;
        }

        @media (max-width: 840px) {
            .messages-app {
                padding: 16px 10px;
            }

            .messages-shell {
                grid-template-columns: 1fr;
                height: auto;
                min-height: calc(100vh - 98px);
            }

            .messages-sidebar {
                border-bottom: 1px solid #e5e7eb;
                border-right: 0;
                max-height: 320px;
            }

            .messages-conversation {
                min-height: 560px;
            }
        }
    </style>

    <main
        class="messages-app"
        x-data="messagesInbox({
            threads: @js($threads),
            selectedThreadId: @js($selectedThreadId),
            threadUrlTemplate: @js(route('messages.thread', ['thread' => '__THREAD__'])),
            unsendUrlTemplate: @js(route('messages.unsend', ['message' => '__MESSAGE__'])),
            sendUrl: @js(route('messages.store')),
            summaryUrl: @js(route('messages.summary')),
            csrf: @js(csrf_token())
        })"
        x-init="init()"
    >
        <section class="messages-shell">
            <aside class="messages-sidebar">
                <header class="messages-sidebar-head">
                    <p>Inbox</p>
                    <h1><i class="fas fa-comment-dots"></i> Messages</h1>
                </header>

                <div class="messages-thread-list">
                    <template x-if="threads.length === 0">
                        <div class="messages-empty">No conversations yet.</div>
                    </template>

                    <template x-for="thread in threads" :key="thread.id">
                        <button
                            type="button"
                            class="messages-thread-button"
                            :class="{ 'is-active': activeThreadId === thread.id }"
                            @click="selectThread(thread.id)"
                        >
                            <span class="messages-avatar">
                                <template x-if="thread.recipient.avatar">
                                    <img :src="thread.recipient.avatar" :alt="thread.recipient.name">
                                </template>
                                <template x-if="!thread.recipient.avatar">
                                    <span x-text="thread.recipient.initials || '?'"></span>
                                </template>
                            </span>
                            <span class="messages-thread-copy">
                                <strong x-text="thread.recipient.name"></strong>
                                <span x-text="thread.preview"></span>
                            </span>
                            <span class="messages-thread-meta">
                                <em x-text="thread.last_message_at || ''"></em>
                                <b x-show="thread.unread_count > 0" x-text="thread.unread_count > 9 ? '9+' : thread.unread_count"></b>
                            </span>
                        </button>
                    </template>
                </div>
            </aside>

            <section class="messages-conversation">
                <template x-if="!activeThreadId">
                    <div class="messages-empty">Choose a conversation to start messaging.</div>
                </template>

                <template x-if="activeThreadId">
                    <header class="messages-conversation-head">
                        <div class="messages-person">
                            <span class="messages-avatar">
                                <template x-if="recipient.avatar">
                                    <img :src="recipient.avatar" :alt="recipient.name">
                                </template>
                                <template x-if="!recipient.avatar">
                                    <span x-text="recipient.initials || '?'"></span>
                                </template>
                            </span>
                            <span>
                                <strong x-text="recipient.name || 'Conversation'"></strong>
                                <small x-text="loading ? 'Loading conversation...' : 'Active conversation'"></small>
                            </span>
                        </div>
                        <a class="messages-popout" :href="threadUrl || '#'" x-show="threadUrl">
                            <i class="fas fa-up-right-from-square"></i>
                            Pop out
                        </a>
                    </header>
                </template>

                <div class="messages-body" x-ref="messages">
                    <template x-if="loading">
                        <div class="messages-empty">Loading conversation...</div>
                    </template>

                    <template x-if="!loading && activeThreadId && messages.length === 0">
                        <div class="messages-empty">No messages yet. Say hello.</div>
                    </template>

                    <template x-for="message in messages" :key="message.id">
                        <div class="messages-row" :class="{ 'is-mine': message.is_mine }">
                            <div class="messages-bubble" :class="{ 'is-unsent': message.is_unsent }" @contextmenu.prevent="message.can_unsend && unsend(message)">
                                <p x-text="message.is_unsent ? 'Message unsent' : message.body"></p>
                                <time x-text="formatMessageTime(message)"></time>
                                <button
                                    type="button"
                                    class="messages-unsend"
                                    x-show="message.can_unsend"
                                    @click="unsend(message)"
                                >
                                    Unsend
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <form class="messages-compose" x-show="activeThreadId" @submit.prevent="send()">
                    <textarea
                        x-model="draft"
                        @keydown.enter.prevent="send()"
                        rows="1"
                        placeholder="Write a message..."
                        :disabled="sending || loading"
                    ></textarea>
                    <button type="submit" :disabled="sending || loading || !draft.trim()" aria-label="Send message">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
                <p class="messages-error" x-show="error" x-text="error"></p>
            </section>
        </section>
    </main>

    <script>
        window.messagesInbox = window.messagesInbox || function messagesInbox(config) {
            return {
                threads: config.threads || [],
                activeThreadId: config.selectedThreadId || null,
                threadUrl: '',
                recipient: {},
                messages: [],
                knownMessageIds: new Set(),
                draft: '',
                loading: false,
                sending: false,
                error: '',
                pollTimer: null,
                init() {
                    if (this.activeThreadId) {
                        this.selectThread(this.activeThreadId);
                    }

                    this.pollTimer = window.setInterval(() => {
                        this.refreshThreads();
                        this.refreshActiveThread();
                    }, 5000);
                },
                async selectThread(threadId) {
                    this.activeThreadId = threadId;
                    this.loading = true;
                    this.error = '';

                    try {
                        const response = await fetch(config.threadUrlTemplate.replace('__THREAD__', threadId), this.fetchOptions('GET'));
                        const payload = await this.parseResponse(response);
                        this.applyThread(payload);
                        await this.refreshThreads();
                    } catch (error) {
                        this.error = error.message || 'Could not open this conversation.';
                    } finally {
                        this.loading = false;
                    }
                },
                async refreshThreads() {
                    try {
                        const response = await fetch(config.summaryUrl, this.fetchOptions('GET'));
                        const payload = await this.parseResponse(response);
                        this.threads = payload.threads || [];
                    } catch (error) {
                        console.warn(error);
                    }
                },
                async refreshActiveThread() {
                    if (!this.activeThreadId || this.loading) {
                        return;
                    }

                    try {
                        const response = await fetch(config.threadUrlTemplate.replace('__THREAD__', this.activeThreadId), this.fetchOptions('GET'));
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

                    if (!body || !this.recipient.id || !this.activeThreadId) {
                        return;
                    }

                    this.sending = true;
                    this.error = '';

                    try {
                        const form = new FormData();
                        form.append('recipient_id', this.recipient.id);
                        form.append('thread_id', this.activeThreadId);
                        form.append('body', body);

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
                        await this.refreshThreads();
                    } catch (error) {
                        this.error = error.message || 'Message could not be sent.';
                    } finally {
                        this.sending = false;
                    }
                },
                async unsend(message) {
                    if (!message?.can_unsend || !window.confirm('Unsend this message?')) {
                        return;
                    }

                    try {
                        const response = await fetch(config.unsendUrlTemplate.replace('__MESSAGE__', message.id), {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': config.csrf,
                            },
                        });
                        const payload = await this.parseResponse(response);
                        this.applyThread(payload.thread || payload, false);
                        await this.refreshThreads();
                    } catch (error) {
                        this.error = error.message || 'Message could not be unsent.';
                    }
                },
                applyThread(payload, shouldScroll = true) {
                    this.activeThreadId = payload.id;
                    this.threadUrl = payload.url;
                    this.recipient = payload.recipient || {};
                    this.messages = payload.messages || [];
                    this.knownMessageIds = new Set(this.messages.map((message) => message.id).filter(Boolean));

                    if (shouldScroll) {
                        this.$nextTick(() => this.scrollToBottom());
                    }
                },
                scrollToBottom() {
                    const box = this.$refs.messages;

                    if (box) {
                        box.scrollTop = box.scrollHeight;
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
</x-app-layout>

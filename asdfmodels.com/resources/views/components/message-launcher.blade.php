@props(['count' => 0])

<div
    class="asdf-message-launcher"
    x-data="asdfMessageLauncher({
        count: {{ (int) $count }},
        summaryUrl: @js(route('messages.summary')),
        indexUrl: @js(route('messages.index')),
        threadUrlTemplate: @js(route('messages.thread', ['thread' => '__THREAD__']))
    })"
    x-init="$el.offsetParent !== null && init()"
>
    <button
        type="button"
        class="asdf-message-button"
        :class="{ 'is-open': open }"
        @click="toggle()"
        aria-label="Messages"
        :aria-expanded="open.toString()"
    >
        <i class="fas fa-comment-dots"></i>
        <span
            x-cloak
            x-show="count > 0"
            class="asdf-message-badge"
            x-text="count > 9 ? '9+' : count"
        ></span>
    </button>

    <aside
        x-cloak
        x-show="open"
        x-transition.opacity.duration.150ms
        @click.outside="open = false"
        class="asdf-message-panel"
    >
        <header class="asdf-message-panel-head">
            <div>
                <p>Messages</p>
                <h3>Your conversations</h3>
            </div>
            <a :href="indexUrl" title="Open messages">
                <i class="fas fa-up-right-from-square"></i>
            </a>
        </header>

        <div class="asdf-message-panel-body">
            <template x-if="loading">
                <div class="asdf-message-empty">Loading conversations...</div>
            </template>

            <template x-if="!loading && threads.length === 0">
                <div class="asdf-message-empty">No conversations yet.</div>
            </template>

            <template x-for="thread in threads" :key="thread.id">
                <button type="button" class="asdf-message-thread" @click="openThread(thread)">
                    <span class="asdf-message-avatar">
                        <template x-if="thread.recipient.avatar">
                            <img :src="thread.recipient.avatar" :alt="thread.recipient.name">
                        </template>
                        <template x-if="!thread.recipient.avatar">
                            <span x-text="thread.recipient.initials || '?'"></span>
                        </template>
                    </span>
                    <span class="asdf-message-thread-copy">
                        <strong x-text="thread.recipient.name"></strong>
                        <small x-text="thread.preview"></small>
                    </span>
                    <span class="asdf-message-thread-meta">
                        <em x-text="thread.last_message_at || ''"></em>
                        <b x-show="thread.unread_count > 0" x-text="thread.unread_count > 9 ? '9+' : thread.unread_count"></b>
                    </span>
                </button>
            </template>
        </div>
    </aside>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }

    .asdf-message-launcher {
        position: relative;
        display: inline-flex;
        align-items: center;
        overflow: visible;
    }

    .asdf-message-button {
        position: relative;
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: #111827;
        cursor: pointer;
        font-size: 17px;
        transition: background-color 150ms ease, transform 150ms ease;
    }

    .asdf-message-button:hover,
    .asdf-message-button.is-open {
        background: #f3f4f6;
    }

    .asdf-message-button:active {
        transform: translateY(1px);
    }

    .asdf-message-badge,
    .asdf-message-thread-meta b {
        align-items: center;
        background: #050505;
        border: 2px solid #fff;
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        font-size: 10px;
        font-weight: 900;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
    }

    .asdf-message-badge {
        position: absolute;
        right: -4px;
        top: 1px;
        box-shadow: 0 5px 14px rgba(0, 0, 0, .2);
    }

    .asdf-message-panel {
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        width: min(390px, calc(100vw - 28px));
        border: 2px solid #050505;
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .24);
        z-index: 90;
        overflow: hidden;
    }

    .asdf-message-panel-head {
        align-items: center;
        background: #050505;
        color: #fff;
        display: flex;
        justify-content: space-between;
        padding: 16px;
    }

    .asdf-message-panel-head p {
        color: #cbd5e1;
        font-size: 11px;
        font-weight: 850;
        letter-spacing: .12em;
        margin: 0 0 3px;
        text-transform: uppercase;
    }

    .asdf-message-panel-head h3 {
        font-size: 17px;
        font-weight: 900;
        margin: 0;
    }

    .asdf-message-panel-head a {
        align-items: center;
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        height: 34px;
        justify-content: center;
        text-decoration: none;
        width: 34px;
    }

    .asdf-message-panel-body {
        max-height: min(520px, calc(100vh - 130px));
        overflow-y: auto;
        padding: 8px;
    }

    .asdf-message-thread {
        align-items: center;
        background: #fff;
        border: 0;
        border-radius: 16px;
        color: #111827;
        cursor: pointer;
        display: grid;
        gap: 11px;
        grid-template-columns: 44px minmax(0, 1fr) auto;
        padding: 11px;
        text-align: left;
        width: 100%;
    }

    .asdf-message-thread:hover {
        background: #f8fafc;
    }

    .asdf-message-avatar {
        align-items: center;
        background: #111827;
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        font-size: 13px;
        font-weight: 900;
        height: 44px;
        justify-content: center;
        overflow: hidden;
        width: 44px;
    }

    .asdf-message-avatar img {
        display: block;
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

    .asdf-message-thread-copy {
        min-width: 0;
    }

    .asdf-message-thread-copy strong,
    .asdf-message-thread-copy small,
    .asdf-message-thread-meta em {
        display: block;
    }

    .asdf-message-thread-copy strong {
        font-size: 14px;
        font-weight: 900;
    }

    .asdf-message-thread-copy small {
        color: #64748b;
        font-size: 12px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .asdf-message-thread-meta {
        align-items: flex-end;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .asdf-message-thread-meta em {
        color: #94a3b8;
        font-size: 10px;
        font-style: normal;
        white-space: nowrap;
    }

    .asdf-message-empty {
        color: #64748b;
        font-size: 14px;
        padding: 34px 18px;
        text-align: center;
    }

    @media (max-width: 767px) {
        .asdf-message-panel {
            position: fixed;
            top: 74px;
            right: 12px;
            left: 12px;
            width: auto;
        }
    }
</style>

<script>
    window.asdfMessageLauncher = window.asdfMessageLauncher || function asdfMessageLauncher(config) {
        return {
            count: Number(config.count || 0),
            indexUrl: config.indexUrl || '#',
            open: false,
            loading: false,
            threads: [],
            pollTimer: null,
            init() {
                this.refresh(false);
                this.pollTimer = window.setInterval(() => this.refresh(false), 5000);

                window.addEventListener('asdf:new-message-notification', () => {
                    this.refresh(true);
                });
            },
            toggle() {
                this.open = !this.open;
                this.refresh(false);
            },
            async refresh(shouldPlaySound = false) {
                this.loading = this.threads.length === 0;

                try {
                    const response = await fetch(config.summaryUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload.message || 'Could not load messages.');
                    }

                    const previousCount = this.count;
                    this.count = Number(payload.unread_count || 0);
                    this.threads = payload.threads || [];

                    if (shouldPlaySound || this.count > previousCount) {
                        window.asdfSound?.play('message');

                        const unreadThread = this.threads.find((thread) => Number(thread.unread_count || 0) > 0);

                        if (unreadThread) {
                            window.dispatchEvent(new CustomEvent('asdf:open-message-thread', {
                                detail: { threadId: unreadThread.id },
                            }));
                        }
                    }
                } catch (error) {
                    console.warn(error);
                } finally {
                    this.loading = false;
                }
            },
            openThread(thread) {
                this.open = false;
                window.dispatchEvent(new CustomEvent('asdf:open-message-thread', {
                    detail: { threadId: thread.id },
                }));
            },
        };
    };
</script>

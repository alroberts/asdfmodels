@props(['count' => 0])

<div
    class="asdf-notification-shell"
    x-data="asdfNotificationBell({
        count: {{ (int) $count }},
        summaryUrl: @js(route('notifications.summary')),
        notificationsUrl: @js(route('notifications.index')),
        sounds: {
            message: @js(asset('assets/sounds/message-received.mp3')),
            notification: @js(asset('assets/sounds/notification.mp3')),
            doorbell: @js(asset('assets/sounds/doorbell.mp3')),
            done: @js(asset('assets/sounds/done.mp3'))
        }
    })"
    x-init="$el.offsetParent !== null && init()"
>
    <button
        type="button"
        class="asdf-notification-button"
        :class="{ 'is-open': open }"
        @click="toggle()"
        aria-label="Notifications"
        :aria-expanded="open.toString()"
    >
        <i class="fas fa-bell"></i>
        <span
            x-cloak
            x-show="count > 0"
            class="asdf-notification-badge"
            x-text="count > 9 ? '9+' : count"
        ></span>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.opacity.duration.150ms
        @click.outside="open = false"
        class="asdf-notification-popover"
    >
        <div class="asdf-notification-popover-header">
            <div>
                <p class="asdf-notification-kicker">Notifications</p>
                <h3>What needs your attention</h3>
            </div>
            <a :href="notificationsUrl">View all</a>
        </div>

        <div class="asdf-notification-popover-body">
            <template x-if="loading">
                <div class="asdf-notification-empty">Checking for updates...</div>
            </template>

            <template x-if="!loading && credits.length === 0 && notifications.length === 0">
                <div class="asdf-notification-empty">
                    <i class="fas fa-check-circle"></i>
                    <span>Nothing new right now.</span>
                </div>
            </template>

            <template x-if="credits.length > 0">
                <div class="asdf-notification-section">
                    <div class="asdf-notification-section-title">
                        <span>Credit requests</span>
                        <strong x-text="creditCount"></strong>
                    </div>
                    <template x-for="credit in credits" :key="credit.title + credit.body">
                        <a class="asdf-notification-card is-credit" :href="credit.url">
                            <span class="asdf-notification-card-icon"><i class="fas fa-user-tag"></i></span>
                            <span>
                                <strong x-text="credit.title"></strong>
                                <small x-text="credit.body"></small>
                            </span>
                        </a>
                    </template>
                </div>
            </template>

            <template x-if="notifications.length > 0">
                <div class="asdf-notification-section">
                    <div class="asdf-notification-section-title">
                        <span>Recent activity</span>
                    </div>
                    <template x-for="item in notifications" :key="item.id">
                        <a class="asdf-notification-card" :class="{ 'is-unread': item.is_unread }" :href="item.url">
                            <span class="asdf-notification-card-icon">
                                <i :class="iconFor(item.type)"></i>
                            </span>
                            <span>
                                <strong x-text="item.title"></strong>
                                <small x-text="item.body"></small>
                                <em x-text="item.created_at"></em>
                            </span>
                        </a>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <div class="asdf-notification-toasts" aria-live="polite" aria-atomic="true">
        <template x-for="toast in toasts" :key="toast.id">
            <a class="asdf-notification-toast" :href="toast.url || notificationsUrl" x-transition>
                <span class="asdf-notification-toast-icon"><i :class="toast.icon"></i></span>
                <span>
                    <strong x-text="toast.title"></strong>
                    <small x-text="toast.body"></small>
                </span>
            </a>
        </template>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }

    .asdf-notification-shell {
        position: relative;
        overflow: visible;
        display: inline-flex;
        align-items: center;
    }

    .asdf-notification-button {
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
        transition: background-color 150ms ease, color 150ms ease, transform 150ms ease;
    }

    .asdf-notification-button:hover,
    .asdf-notification-button.is-open {
        background: #f3f4f6;
        color: #000;
    }

    .asdf-notification-button:active {
        transform: scale(0.96);
    }

    .asdf-notification-badge {
        position: absolute;
        top: 2px;
        right: 2px;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        border-radius: 999px;
        background: #dc2626;
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        line-height: 1;
        transform: translate(35%, -35%);
        box-shadow: 0 6px 14px rgba(220, 38, 38, 0.28);
    }

    .asdf-notification-popover {
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        z-index: 90;
        width: min(390px, calc(100vw - 24px));
        overflow: hidden;
        border: 2px solid #111;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
    }

    .asdf-notification-popover::before {
        content: "";
        position: absolute;
        top: -8px;
        right: 17px;
        width: 14px;
        height: 14px;
        border-left: 2px solid #111;
        border-top: 2px solid #111;
        background: #fff;
        transform: rotate(45deg);
    }

    .asdf-notification-popover-header {
        position: relative;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 18px 14px;
        border-bottom: 1px solid #e5e7eb;
    }

    .asdf-notification-kicker {
        margin: 0 0 4px;
        color: #6b7280;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .asdf-notification-popover-header h3 {
        margin: 0;
        color: #111827;
        font-size: 16px;
        font-weight: 800;
    }

    .asdf-notification-popover-header a {
        color: #111827;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }

    .asdf-notification-popover-body {
        max-height: 440px;
        overflow-y: auto;
        padding: 12px;
    }

    .asdf-notification-section + .asdf-notification-section {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #e5e7eb;
    }

    .asdf-notification-section-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 0 4px 8px;
        color: #6b7280;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .asdf-notification-section-title strong {
        min-width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #111827;
        color: #fff;
        font-size: 11px;
        letter-spacing: 0;
    }

    .asdf-notification-card {
        display: grid;
        grid-template-columns: 36px minmax(0, 1fr);
        gap: 10px;
        padding: 11px 10px;
        border-radius: 14px;
        color: #111827;
        text-decoration: none;
        transition: background-color 150ms ease, transform 150ms ease;
    }

    .asdf-notification-card:hover {
        background: #f8fafc;
        transform: translateY(-1px);
    }

    .asdf-notification-card.is-unread {
        background: #fefce8;
    }

    .asdf-notification-card.is-credit {
        background: #f8fafc;
    }

    .asdf-notification-card-icon,
    .asdf-notification-toast-icon {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #111827;
        color: #fff;
        font-size: 14px;
    }

    .asdf-notification-card strong,
    .asdf-notification-toast strong {
        display: block;
        color: #111827;
        font-size: 13px;
        font-weight: 800;
    }

    .asdf-notification-card small,
    .asdf-notification-toast small {
        display: block;
        margin-top: 2px;
        color: #4b5563;
        font-size: 12px;
        line-height: 1.35;
    }

    .asdf-notification-card em {
        display: block;
        margin-top: 4px;
        color: #9ca3af;
        font-size: 11px;
        font-style: normal;
    }

    .asdf-notification-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 96px;
        color: #6b7280;
        font-size: 14px;
        text-align: center;
    }

    .asdf-notification-toasts {
        position: fixed;
        top: 78px;
        right: 18px;
        z-index: 120;
        display: grid;
        gap: 10px;
        width: min(360px, calc(100vw - 36px));
        pointer-events: none;
    }

    .asdf-notification-toast {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr);
        gap: 12px;
        padding: 14px;
        border: 2px solid #111;
        border-radius: 18px;
        background: #fff;
        color: #111827;
        text-decoration: none;
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.2);
        pointer-events: auto;
    }

    @media (max-width: 767px) {
        .asdf-notification-popover {
            position: fixed;
            top: 74px;
            right: 12px;
            left: 12px;
            width: auto;
        }

        .asdf-notification-popover::before {
            display: none;
        }
    }
</style>

<script>
    window.asdfSound = window.asdfSound || {
        urls: {},
        unlocked: false,
        unlockBound: false,
        init(urls = {}) {
            this.urls = { ...this.urls, ...urls };

            if (this.unlocked || this.unlockBound) {
                return;
            }

            const unlock = () => {
                this.unlocked = true;
                this.unlockBound = false;

                Object.values(this.urls).filter(Boolean).forEach((url) => {
                    try {
                        const audio = new Audio(url);
                        audio.volume = 0;
                        audio.muted = true;
                        audio.play()
                            .then(() => {
                                audio.pause();
                                audio.currentTime = 0;
                                audio.muted = false;
                            })
                            .catch(() => {});
                    } catch (error) {
                        console.warn(error);
                    }
                });

                document.removeEventListener('click', unlock);
                document.removeEventListener('keydown', unlock);
                document.removeEventListener('touchstart', unlock);
            };

            this.unlockBound = true;
            document.addEventListener('click', unlock);
            document.addEventListener('keydown', unlock);
            document.addEventListener('touchstart', unlock, { passive: true });
        },
        play(kind = 'notification') {
            const url = this.urls[kind] || this.urls.notification;

            if (!url) {
                return false;
            }

            try {
                const audio = new Audio(url);
                audio.volume = 0.45;
                audio.play().catch(() => {});
                return true;
            } catch (error) {
                console.warn(error);
                return false;
            }
        },
    };

    window.asdfNotificationBell = window.asdfNotificationBell || function asdfNotificationBell(config) {
        return {
            count: Number(config.count || 0),
            previousCount: Number(config.count || 0),
            open: false,
            loading: false,
            hasLoaded: false,
            creditCount: 0,
            credits: [],
            notifications: [],
            toasts: [],
            knownCreditKeys: new Set(),
            knownNotificationIds: new Set(),
            pollTimer: null,
            init() {
                window.asdfSound.init(config.sounds || {});
                this.refresh(false);
                this.pollTimer = window.setInterval(() => this.refresh(true), 12000);
            },
            toggle() {
                this.open = !this.open;
                if (this.open) {
                    this.refresh(false);
                }
            },
            async refresh(shouldToast) {
                this.loading = !this.hasLoaded;

                try {
                    const response = await fetch(config.summaryUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Notification summary failed.');
                    }

                    const data = await response.json();
                    const nextCount = Number(data.unread_count || 0);
                    const previousCreditCount = this.creditCount;

                    this.creditCount = Number(data.credit_count || 0);
                    this.credits = data.credits || [];
                    this.notifications = data.notifications || [];

                    this.handleFreshCredits(shouldToast, previousCreditCount);
                    this.handleFreshNotifications(shouldToast);

                    this.previousCount = this.count;
                    this.count = nextCount;
                    this.hasLoaded = true;
                } catch (error) {
                    console.warn(error);
                } finally {
                    this.loading = false;
                }
            },
            handleFreshNotifications(shouldToast) {
                const currentIds = new Set((this.notifications || []).map((item) => item.id).filter(Boolean));

                if (!this.hasLoaded) {
                    this.knownNotificationIds = currentIds;
                    return;
                }

                const fresh = (this.notifications || [])
                    .filter((item) => item.id && item.is_unread && !this.knownNotificationIds.has(item.id))
                    .reverse();

                fresh.forEach((item) => {
                    if (shouldToast) {
                        this.pushToast(item);
                        this.playSound(item.type === 'message' ? 'message' : 'notification');
                    }

                    window.dispatchEvent(new CustomEvent('asdf:new-notification', { detail: item }));

                    if (item.type === 'message') {
                        window.dispatchEvent(new CustomEvent('asdf:new-message-notification', { detail: item }));
                    }
                });

                this.knownNotificationIds = currentIds;
            },
            handleFreshCredits(shouldToast, previousCreditCount) {
                const currentKeys = new Set((this.credits || []).map((item) => [
                    item.title,
                    item.body,
                    item.count,
                    item.url,
                ].join('|')));

                if (!this.hasLoaded) {
                    this.knownCreditKeys = currentKeys;
                    return;
                }

                const hasNewCreditGroup = [...currentKeys].some((key) => !this.knownCreditKeys.has(key));
                const hasMoreCredits = this.creditCount > Number(previousCreditCount || 0);

                if (shouldToast && this.creditCount > 0 && (hasNewCreditGroup || hasMoreCredits)) {
                    this.pushToast();
                    this.playSound('notification');
                    window.dispatchEvent(new CustomEvent('asdf:new-notification', {
                        detail: {
                            type: 'credit_pending',
                            title: 'New credit request',
                            body: 'You have credit requests to review.',
                            url: config.notificationsUrl,
                        },
                    }));
                }

                this.knownCreditKeys = currentKeys;
            },
            pushToast(notification = null) {
                const toast = notification || {
                    title: 'New notification',
                    body: this.creditCount > 0 ? 'You have new credit requests to review.' : 'You have new activity.',
                    type: this.creditCount > 0 ? 'credit_pending' : 'account',
                    url: config.notificationsUrl,
                };

                const id = Date.now();
                this.toasts.unshift({
                    id,
                    title: toast.title || 'New notification',
                    body: toast.body || 'You have new activity.',
                    url: toast.url || config.notificationsUrl,
                    icon: this.iconFor(toast.type),
                });

                window.setTimeout(() => {
                    this.toasts = this.toasts.filter((item) => item.id !== id);
                }, 7000);
            },
            iconFor(type) {
                if (type === 'message') {
                    return 'fas fa-envelope';
                }

                if (type === 'credit_pending') {
                    return 'fas fa-user-tag';
                }

                return 'fas fa-bell';
            },
            playSound(kind) {
                window.asdfSound.play(kind);
            },
        };
    };
</script>

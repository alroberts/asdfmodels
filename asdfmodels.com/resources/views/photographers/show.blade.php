<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
        <style>
            .photographer-profile-page {
                margin: 0 auto;
                max-width: 1280px;
                padding: 48px 24px;
            }

            .photographer-hero {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1), 0 1px 2px rgba(15, 23, 42, 0.06);
                margin-bottom: 24px;
                overflow: visible;
            }

            .photographer-cover {
                background: #f3f4f6;
                border-radius: 16px 16px 0 0;
                height: 260px;
                overflow: hidden;
                position: relative;
            }

            .photographer-cover img {
                display: block;
                height: 100%;
                object-fit: cover;
                width: 100%;
            }

            .photographer-cover-empty {
                align-items: center;
                color: #9ca3af;
                display: flex;
                height: 100%;
                justify-content: center;
            }

            .photographer-cover-empty i {
                font-size: 30px;
            }

            .photographer-media-trigger {
                align-items: center;
                background: rgba(5, 5, 5, 0);
                border: 0;
                color: #fff;
                cursor: pointer;
                display: flex;
                inset: 0;
                justify-content: center;
                position: absolute;
                transition: background 0.18s ease;
                width: 100%;
            }

            .photographer-media-trigger:hover,
            .photographer-media-trigger:focus {
                background: rgba(5, 5, 5, 0.22);
                outline: none;
            }

            .photographer-media-trigger span,
            .photographer-avatar-manage {
                align-items: center;
                background: rgba(5, 5, 5, 0.78);
                border-radius: 999px;
                display: inline-flex;
                font-size: 13px;
                font-weight: 800;
                gap: 8px;
                opacity: 0;
                padding: 10px 14px;
                transition: opacity 0.18s ease;
            }

            .photographer-media-trigger:hover span,
            .photographer-media-trigger:focus span,
            .photographer-avatar-button:hover .photographer-avatar-manage,
            .photographer-avatar-button:focus .photographer-avatar-manage {
                opacity: 1;
            }

            .photographer-hero-body {
                align-items: flex-start;
                display: grid;
                gap: 24px;
                grid-template-columns: auto minmax(0, 1fr) auto;
                padding: 28px;
            }

            .photographer-avatar {
                align-items: center;
                background: #d1d5db;
                border: 4px solid #fff;
                border-radius: 999px;
                box-shadow: 0 16px 32px rgba(15, 23, 42, 0.16);
                color: #4b5563;
                display: flex;
                font-size: 42px;
                font-weight: 800;
                height: 150px;
                justify-content: center;
                margin-top: -82px;
                overflow: hidden;
                position: relative;
                width: 150px;
            }

            .photographer-avatar img {
                display: block;
                height: 100%;
                object-fit: cover;
                width: 100%;
            }

            .photographer-avatar-button {
                background: transparent;
                border: 0;
                border-radius: 999px;
                color: inherit;
                cursor: pointer;
                display: block;
                height: 100%;
                padding: 0;
                position: relative;
                width: 100%;
            }

            .photographer-avatar-button:focus {
                outline: 3px solid #050505;
                outline-offset: 4px;
            }

            .photographer-avatar-manage {
                color: #fff;
                left: 50%;
                position: absolute;
                top: 50%;
                transform: translate(-50%, -50%);
                white-space: nowrap;
            }

            .photographer-verified {
                align-items: center;
                background: #22c55e;
                border-radius: 999px;
                color: #fff;
                display: flex;
                flex: 0 0 auto;
                height: 28px;
                justify-content: center;
                width: 28px;
            }

            .photographer-title {
                color: #050505;
                font-size: clamp(30px, 4vw, 46px);
                font-weight: 800;
                letter-spacing: -0.04em;
                line-height: 1;
                margin: 0;
            }

            .photographer-name-row {
                align-items: center;
                display: flex;
                flex-wrap: wrap;
                gap: 14px;
            }

            .photographer-company-logo {
                align-items: center;
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 14px;
                display: inline-flex;
                height: 54px;
                justify-content: center;
                overflow: hidden;
                padding: 8px;
                width: 54px;
            }

            .photographer-company-logo img {
                height: 100%;
                object-fit: contain;
                width: 100%;
            }

            .photographer-meta {
                color: #4b5563;
                display: flex;
                flex-wrap: wrap;
                font-size: 14px;
                gap: 10px 18px;
                margin-top: 12px;
            }

            .photographer-company-name {
                color: #4b5563;
                display: flex;
                flex-wrap: wrap;
                font-size: 16px;
                font-weight: 700;
                gap: 8px;
                line-height: 1.5;
                margin: 8px 0 0;
            }

            .photographer-username {
                color: #6b7280;
                font-weight: 700;
            }

            .photographer-actions {
                align-items: flex-end;
                display: flex;
                flex-direction: column;
                gap: 16px;
                height: 100%;
                justify-content: space-between;
                min-width: 220px;
            }

            .photographer-social-actions,
            .photographer-primary-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: flex-end;
            }

            .photographer-social,
            .photographer-pill,
            .photographer-message {
                align-items: center;
                border-radius: 999px;
                display: inline-flex;
                font-size: 14px;
                font-weight: 700;
                gap: 8px;
                justify-content: center;
                text-decoration: none;
            }

            .photographer-social {
                border: 1px solid #e5e7eb;
                color: #374151;
                height: 40px;
                width: 40px;
            }

            .photographer-pill {
                background: #f3f4f6;
                color: #4b5563;
                font-size: 12px;
                letter-spacing: 0.08em;
                padding: 10px 13px;
                text-transform: uppercase;
            }

            .photographer-message {
                background: #050505;
                color: #fff;
                padding: 11px 16px;
            }

            .photographer-connect {
                background: #fff;
                border: 1px solid #d1d5db;
                color: #111827;
                cursor: pointer;
                padding: 10px 14px;
            }

            .connection-popover {
                position: relative;
                z-index: 70;
            }

            .connection-popover > summary {
                list-style: none;
            }

            .connection-popover > summary::-webkit-details-marker {
                display: none;
            }

            .connection-request-box {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 16px;
                box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
                margin-top: 0;
                padding: 14px;
                position: absolute;
                right: 0;
                top: calc(100% + 10px);
                width: min(320px, 90vw);
                z-index: 90;
            }

            .connection-request-box textarea {
                border: 1px solid #d1d5db;
                border-radius: 12px;
                min-height: 78px;
                padding: 10px;
                resize: vertical;
                width: 100%;
            }

            .connection-request-box button {
                border-radius: 999px;
                font-size: 12px;
                font-weight: 850;
                padding: 9px 12px;
            }

            .connection-card-grid {
                display: grid;
                gap: 12px;
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            }

            .connection-card {
                align-items: center;
                border: 1px solid #e5e7eb;
                border-radius: 16px;
                color: inherit;
                display: flex;
                gap: 12px;
                padding: 12px;
                text-decoration: none;
            }

            .connection-card-avatar {
                align-items: center;
                background: #f3f4f6;
                border-radius: 999px;
                color: #6b7280;
                display: flex;
                flex: 0 0 auto;
                font-weight: 850;
                height: 44px;
                justify-content: center;
                overflow: hidden;
                width: 44px;
            }

            .connection-card-avatar img {
                height: 100%;
                object-fit: cover;
                width: 100%;
            }

            .photographer-edit {
                border: 1px solid #d1d5db;
                color: #111827;
                padding: 10px 14px;
            }

            .photographer-layout {
                align-items: start;
                display: grid;
                gap: 24px;
                grid-template-columns: minmax(0, 2fr) minmax(300px, 1fr);
            }

            .photographer-main,
            .photographer-side {
                display: grid;
                gap: 24px;
                min-width: 0;
            }

            .photographer-side {
                position: sticky;
                top: 96px;
            }

            .photographer-card {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1), 0 1px 2px rgba(15, 23, 42, 0.06);
                padding: 28px;
            }

            .photographer-media-overlay {
                align-items: center;
                background: rgba(15, 23, 42, 0.72);
                backdrop-filter: blur(8px);
                display: none;
                inset: 0;
                justify-content: center;
                padding: 24px;
                position: fixed;
                z-index: 90;
            }

            .photographer-media-overlay.is-open {
                display: flex;
            }

            .photographer-media-modal {
                background: #fff;
                border-radius: 18px;
                box-shadow: 0 24px 80px rgba(15, 23, 42, 0.34);
                display: flex;
                flex-direction: column;
                max-height: 88vh;
                max-width: 980px;
                overflow: hidden;
                width: min(100%, 980px);
            }

            .photographer-media-header,
            .photographer-media-footer {
                align-items: center;
                display: flex;
                gap: 16px;
                justify-content: space-between;
                padding: 20px 24px;
            }

            .photographer-media-header {
                border-bottom: 1px solid #e5e7eb;
            }

            .photographer-media-footer {
                border-top: 1px solid #e5e7eb;
            }

            .photographer-media-body {
                background: #f8fafc;
                min-height: 0;
                overflow-y: auto;
                padding: 20px;
            }

            .photographer-media-panel {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 14px;
                padding: 18px;
            }

            .photographer-media-current {
                align-items: center;
                display: flex;
                flex-direction: column;
                gap: 14px;
                text-align: center;
            }

            .photographer-media-current .profile-preview {
                border-radius: 999px;
                height: 132px;
                object-fit: cover;
                width: 132px;
            }

            .photographer-media-current .cover-preview {
                aspect-ratio: 3 / 1;
                border-radius: 12px;
                object-fit: cover;
                width: min(100%, 520px);
            }

            .photographer-media-current-placeholder {
                align-items: center;
                aspect-ratio: 1 / 1;
                background: #f3f4f6;
                border-radius: 999px;
                color: #9ca3af;
                display: flex;
                justify-content: center;
                width: 132px;
            }

            .photographer-media-choice-row {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                justify-content: center;
            }

            .photographer-media-button {
                align-items: center;
                border: 1px solid #d1d5db;
                border-radius: 10px;
                cursor: pointer;
                display: inline-flex;
                font-weight: 800;
                gap: 8px;
                padding: 10px 14px;
            }

            .photographer-media-button.primary {
                background: #050505;
                border-color: #050505;
                color: #fff;
            }

            .photographer-media-close {
                align-items: center;
                background: transparent;
                border: 0;
                border-radius: 999px;
                color: #6b7280;
                cursor: pointer;
                display: flex;
                height: 38px;
                justify-content: center;
                width: 38px;
            }

            .photographer-media-close:hover {
                background: #f3f4f6;
                color: #111827;
            }

            .photographer-media-grid {
                display: grid;
                gap: 12px;
                grid-template-columns: repeat(auto-fill, minmax(138px, 1fr));
            }

            .photographer-media-card {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                cursor: pointer;
                overflow: hidden;
                padding: 0;
                text-align: left;
            }

            .photographer-media-card img,
            .photographer-media-card .empty {
                aspect-ratio: 1 / 1;
                background: #f3f4f6;
                display: block;
                object-fit: cover;
                width: 100%;
            }

            .photographer-media-card .empty {
                align-items: center;
                color: #9ca3af;
                display: flex;
                justify-content: center;
            }

            .photographer-media-card-body {
                padding: 10px;
            }

            .photographer-media-cropper {
                background: #111827;
                border-radius: 12px;
                max-height: 430px;
                overflow: hidden;
            }

            .photographer-media-cropper img {
                display: block;
                max-height: 430px;
                max-width: 100%;
            }

            .photographer-media-status {
                color: #15803d;
                font-size: 13px;
                font-weight: 800;
            }

            .photographer-media-status.is-error {
                color: #b91c1c;
            }

            .photographer-media-hidden {
                display: none !important;
            }

            .photographer-card-dashed {
                border: 1px dashed #d1d5db;
            }

            .photographer-card-header {
                align-items: flex-end;
                display: flex;
                gap: 16px;
                justify-content: space-between;
                margin-bottom: 20px;
            }

            .photographer-muted-icon {
                color: #d1d5db;
            }

            .photographer-kicker {
                color: #6b7280;
                font-size: 12px;
                font-weight: 800;
                letter-spacing: 0.2em;
                margin: 0;
                text-transform: uppercase;
            }

            .photographer-heading {
                color: #050505;
                font-size: 24px;
                font-weight: 800;
                margin: 4px 0 0;
            }

            .photographer-copy {
                color: #374151;
                font-size: 16px;
                line-height: 1.7;
                margin: 0;
                white-space: pre-line;
            }

            .profile-inline-actions {
                align-items: center;
                display: flex;
                gap: 10px;
                justify-content: space-between;
            }

            .profile-icon-action,
            .profile-text-action {
                align-items: center;
                border: 1px solid #d1d5db;
                background: #fff;
                color: #111827;
                cursor: pointer;
                display: inline-flex;
                font-weight: 700;
                gap: 8px;
                text-decoration: none;
                transition: border-color .15s ease, background .15s ease, color .15s ease;
            }

            .profile-icon-action {
                border-radius: 999px;
                height: 38px;
                justify-content: center;
                width: 38px;
            }

            .profile-text-action {
                border-radius: 999px;
                font-size: 13px;
                padding: 9px 13px;
            }

            .profile-icon-action:hover,
            .profile-text-action:hover {
                border-color: #111827;
                background: #111827;
                color: #fff;
            }

            .profile-bio-empty {
                border: 1px dashed #d1d5db;
                border-radius: 14px;
                color: #6b7280;
                padding: 18px;
            }

            .profile-section-empty {
                border: 1px dashed #d1d5db;
                border-radius: 14px;
                color: #6b7280;
                font-size: 14px;
                line-height: 1.6;
                padding: 16px;
            }

            .quick-edit-overlay {
                align-items: center;
                background: rgba(3, 7, 18, .72);
                bottom: 0;
                display: none;
                justify-content: center;
                left: 0;
                overflow-y: auto;
                overscroll-behavior: contain;
                padding: 24px;
                position: fixed;
                right: 0;
                top: 0;
                z-index: 95;
            }

            .quick-edit-overlay.is-open {
                display: flex;
            }

            .quick-edit-modal {
                background: #fff;
                border-radius: 18px;
                box-shadow: 0 24px 80px rgba(15, 23, 42, .32);
                display: flex;
                flex-direction: column;
                max-height: calc(100dvh - 48px);
                max-width: 760px;
                overflow: hidden;
                width: min(100%, 760px);
            }

            .quick-edit-header,
            .quick-edit-footer {
                align-items: center;
                border-bottom: 1px solid #e5e7eb;
                display: flex;
                gap: 14px;
                justify-content: space-between;
                padding: 20px 24px;
            }

            .quick-edit-footer {
                border-bottom: 0;
                border-top: 1px solid #e5e7eb;
            }

            .quick-edit-body {
                flex: 1 1 auto;
                min-height: 0;
                overflow-y: auto;
                overscroll-behavior: contain;
                padding: 24px;
            }

            .quick-edit-grid,
            .quick-edit-checklist {
                display: grid;
                gap: 16px;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .quick-edit-field label,
            .quick-edit-checklist-label {
                color: #374151;
                display: block;
                font-size: 13px;
                font-weight: 800;
                margin-bottom: 7px;
            }

            .quick-edit-field input,
            .quick-edit-field select {
                border: 1px solid #9ca3af;
                border-radius: 10px;
                display: block;
                padding: 10px 12px;
                width: 100%;
            }

            .quick-edit-check {
                align-items: center;
                border: 1px solid #e5e7eb;
                border-radius: 10px;
                display: flex;
                gap: 9px;
                padding: 10px 12px;
            }

            .quick-edit-message {
                color: #166534;
                font-size: 13px;
                font-weight: 800;
                margin-right: auto;
            }

            .quick-edit-message.is-error {
                color: #b91c1c;
            }

            @media (max-width: 720px) {
                .quick-edit-overlay {
                    align-items: flex-start;
                    padding: 12px;
                }

                .quick-edit-modal {
                    max-height: calc(100dvh - 24px);
                    width: 100%;
                }

                .quick-edit-grid,
                .quick-edit-checklist {
                    grid-template-columns: 1fr;
                }
            }

            .profile-inline-editor {
                display: none;
                margin-top: 18px;
            }

            .profile-inline-editor.is-open {
                display: block;
            }

            .profile-inline-editor textarea {
                border: 1px solid #9ca3af;
                border-radius: 14px;
                color: #111827;
                display: block;
                font: inherit;
                line-height: 1.6;
                min-height: 180px;
                padding: 14px 16px;
                resize: vertical;
                width: 100%;
            }

            .profile-inline-editor textarea:focus {
                border-color: #111827;
                box-shadow: 0 0 0 3px rgba(17, 24, 39, .12);
                outline: none;
            }

            .profile-inline-help {
                color: #6b7280;
                font-size: 13px;
                margin-top: 8px;
            }

            .profile-inline-buttons {
                align-items: center;
                display: flex;
                gap: 10px;
                justify-content: flex-end;
                margin-top: 14px;
            }

            .profile-inline-save,
            .profile-inline-cancel {
                border-radius: 10px;
                cursor: pointer;
                font-weight: 800;
                padding: 10px 16px;
            }

            .profile-inline-save {
                background: #111827;
                border: 1px solid #111827;
                color: #fff;
            }

            .profile-inline-cancel {
                background: #fff;
                border: 1px solid #d1d5db;
                color: #374151;
            }

            .profile-inline-status {
                color: #166534;
                font-size: 13px;
                font-weight: 700;
                margin-right: auto;
            }

            .profile-inline-status.is-error {
                color: #b91c1c;
            }

            .photographer-gallery-grid,
            .photographer-featured-grid,
            .photographer-tagged-grid {
                display: grid;
                gap: 12px;
            }

            .photographer-gallery-grid {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            }

            .photographer-featured-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .photographer-tagged-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .profile-credit-body {
                padding: 10px 12px 12px;
            }

            .profile-credit-body strong {
                color: #111827;
                display: block;
                font-size: 13px;
                line-height: 1.3;
            }

            .profile-credit-body span {
                color: #6b7280;
                display: block;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: .08em;
                margin-top: 5px;
                text-transform: uppercase;
            }

            .profile-pending-credit {
                align-items: center;
                background: #fffaf0;
                border: 1px solid #fde68a;
                border-radius: 16px;
                display: flex;
                gap: 14px;
                justify-content: space-between;
                padding: 14px;
            }

            .profile-pending-credit span {
                color: #92400e;
                display: block;
                font-size: 12px;
                margin-top: 3px;
            }

            .profile-credit-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: flex-end;
            }

            .profile-credit-actions button {
                border-radius: 999px;
                font-size: 12px;
                font-weight: 800;
                padding: 8px 10px;
            }

            .profile-credit-actions button:first-child {
                background: #111827;
                border: 1px solid #111827;
                color: #fff;
            }

            .profile-credit-actions button:not(:first-child) {
                background: #fff;
                border: 1px solid #d1d5db;
                color: #374151;
            }

            .photographer-gallery-card,
            .photographer-image-card {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 14px;
                color: inherit;
                display: block;
                min-width: 0;
                overflow: hidden;
                text-decoration: none;
            }

            .photographer-square {
                aspect-ratio: 1 / 1;
                background: #f3f4f6;
                overflow: hidden;
                position: relative;
            }

            .photographer-square img {
                display: block;
                height: 100%;
                object-fit: cover;
                transition: transform 220ms ease;
                width: 100%;
            }

            .photographer-square:hover img {
                transform: scale(1.04);
            }

            .photographer-gallery-body {
                padding: 12px;
            }

            .photographer-gallery-name {
                color: #111827;
                font-size: 15px;
                font-weight: 800;
                margin: 0;
            }

            .photographer-gallery-meta {
                color: #6b7280;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: 0.08em;
                margin-top: 6px;
                text-transform: uppercase;
            }

            .photographer-chip-list {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .photographer-chip {
                background: #f3f4f6;
                border-radius: 999px;
                color: #374151;
                display: inline-flex;
                font-size: 13px;
                font-weight: 700;
                padding: 8px 11px;
            }

            .photographer-brief-card {
                background: #050505;
                border-radius: 16px;
                color: #fff;
                overflow: hidden;
            }

            .photographer-brief-header {
                align-items: flex-start;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                display: flex;
                gap: 12px;
                justify-content: space-between;
                padding: 20px;
            }

            .photographer-brief-grid {
                background: rgba(255, 255, 255, 0.12);
                display: grid;
                gap: 1px;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .photographer-brief-item {
                background: #09090b;
                padding: 16px;
            }

            .photographer-brief-icon {
                align-items: center;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 999px;
                display: inline-flex;
                height: 38px;
                justify-content: center;
                margin-bottom: 10px;
                width: 38px;
            }

            .photographer-brief-action {
                align-items: center;
                border: 1px solid rgba(255, 255, 255, .2);
                border-radius: 999px;
                color: #fff;
                display: inline-flex;
                font-size: 12px;
                font-weight: 800;
                gap: 7px;
                padding: 8px 11px;
                text-decoration: none;
                transition: background .15s ease, border-color .15s ease;
            }

            .photographer-brief-action:hover {
                background: rgba(255, 255, 255, .12);
                border-color: rgba(255, 255, 255, .45);
            }

            .photographer-empty {
                border: 1px dashed #d1d5db;
                border-radius: 14px;
                color: #6b7280;
                padding: 32px 18px;
                text-align: center;
            }

            .photographer-lightbox {
                align-items: center;
                background: rgba(0, 0, 0, 0.9);
                display: none;
                inset: 0;
                justify-content: center;
                position: fixed;
                z-index: 90;
            }

            .photographer-lightbox.is-open {
                display: flex;
            }

            .photographer-lightbox img {
                max-height: 92vh;
                max-width: 92vw;
                object-fit: contain;
            }

            .photographer-lightbox button {
                color: #fff;
                font-size: 42px;
                position: absolute;
                right: 24px;
                top: 18px;
            }

            @media (max-width: 1023px) {
                .photographer-hero-body,
                .photographer-layout {
                    grid-template-columns: 1fr;
                }

                .photographer-actions {
                    align-items: flex-start;
                    height: auto;
                    justify-content: flex-start;
                }

                .photographer-social-actions,
                .photographer-primary-actions {
                    justify-content: flex-start;
                }

                .photographer-side {
                    position: static;
                }
            }

            @media (max-width: 640px) {
                .photographer-profile-page {
                    padding: 28px 16px;
                }

                .connection-popover[open]::before {
                    background: rgba(15, 23, 42, .28);
                    content: "";
                    inset: 0;
                    position: fixed;
                    z-index: 89;
                }

                .connection-request-box {
                    bottom: 20px;
                    left: 16px;
                    position: fixed;
                    right: 16px;
                    top: auto;
                    width: auto;
                    z-index: 90;
                }

                .photographer-card {
                    padding: 20px;
                }

                .photographer-card-header {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .photographer-featured-grid,
                .photographer-tagged-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }
        </style>
    @endpush

    @php
        $ownerCanManage = $ownerCanManage ?? (auth()->check() && auth()->id() === $user->id);
        $displayName = $profile->display_name;
        $companyName = trim((string) $profile->professional_name);
        $showCompanyName = $profile->shouldShowCompanyName();
        $isCompanyPrimary = $profile->display_name_format === 'professional_name' && $profile->isVerified();
        $personalName = $user->display_name ?: $user->name;
        $secondaryName = $isCompanyPrimary
            ? ($profile->show_company_on_profile ? $personalName : null)
            : ($showCompanyName ? $companyName : null);
        $specialtiesOptions = \App\Helpers\PhotographerOptions::specialties();
        $servicesOptions = \App\Helpers\PhotographerOptions::services();
        $validSpecialties = $profile->specialties ? array_intersect_key(array_flip($profile->specialties), $specialtiesOptions) : [];
        $validServices = $profile->services_offered ? array_intersect_key(array_flip($profile->services_offered), $servicesOptions) : [];
        $equipment = $profile->equipment ?? [];
        $publicGalleries = collect($publicGalleries ?? []);
        $platformMeta = [
            'instagram' => ['label' => 'Instagram', 'icon' => 'fab fa-instagram'],
            'facebook' => ['label' => 'Facebook', 'icon' => 'fab fa-facebook'],
            'x' => ['label' => 'X', 'icon' => 'fab fa-x-twitter'],
            'tiktok' => ['label' => 'TikTok', 'icon' => 'fab fa-tiktok'],
            'youtube' => ['label' => 'YouTube', 'icon' => 'fab fa-youtube'],
            'behance' => ['label' => 'Behance', 'icon' => 'fab fa-behance'],
            'linkedin' => ['label' => 'LinkedIn', 'icon' => 'fab fa-linkedin'],
            'website' => ['label' => 'Website', 'icon' => 'fas fa-globe'],
        ];
        $socialLinks = collect($profile->social_links ?? [])
            ->filter(fn ($link) => filled($link['url'] ?? null))
            ->map(function ($link) use ($platformMeta) {
                $platform = $link['platform'] ?? 'website';
                $meta = $platformMeta[$platform] ?? $platformMeta['website'];

                return [
                    'label' => $meta['label'],
                    'icon' => $meta['icon'],
                    'url' => $link['url'],
                ];
            });

        if ($socialLinks->isEmpty()) {
            $socialLinks = collect([
                $profile->instagram ? ['label' => 'Instagram', 'icon' => 'fab fa-instagram', 'url' => str_starts_with($profile->instagram, 'http') ? $profile->instagram : 'https://instagram.com/' . ltrim($profile->instagram, '@')] : null,
                $profile->facebook ? ['label' => 'Facebook', 'icon' => 'fab fa-facebook', 'url' => str_starts_with($profile->facebook, 'http') ? $profile->facebook : 'https://facebook.com/' . $profile->facebook] : null,
                $profile->twitter ? ['label' => 'X', 'icon' => 'fab fa-x-twitter', 'url' => str_starts_with($profile->twitter, 'http') ? $profile->twitter : 'https://x.com/' . ltrim($profile->twitter, '@')] : null,
                $profile->portfolio_website ? ['label' => 'Website', 'icon' => 'fas fa-globe', 'url' => $profile->portfolio_website] : null,
            ])->filter();
        }
        $profileLocation = collect([
            $profile->location_city,
            $profile->location_country ?: $profile->location_country_code,
        ])->filter()->implode(', ');
        $baseLocation = $profileLocation ?: $profile->studio_location;
        $briefItems = collect([
            $profile->experience_level ? ['label' => 'Level', 'value' => ucfirst($profile->experience_level), 'icon' => 'fas fa-camera'] : null,
            $profile->experience_start_year ? ['label' => 'Since', 'value' => $profile->experience_start_year, 'icon' => 'fas fa-calendar'] : null,
            $baseLocation ? ['label' => 'Location', 'value' => $baseLocation, 'icon' => 'fas fa-location-dot'] : null,
            $profile->available_for_travel ? ['label' => 'Travel', 'value' => 'Available', 'icon' => 'fas fa-plane'] : null,
        ])->filter()->values();
        $photographerMediaGroups = collect($portfolioMediaGroups ?? [])->map(fn ($group) => [
            'id' => $group['id'],
            'label' => $group['label'],
            'count' => $group['count'],
            'cover' => $group['cover'] ? asset($group['cover']) : '',
            'images' => collect($group['images'])->map(fn ($image) => [
                'id' => $image->id,
                'preview' => asset($image->thumbnail_path ?? $image->full_path),
                'full' => asset($image->full_path ?? $image->thumbnail_path),
            ])->values(),
        ])->values();
    @endphp

    <main class="photographer-profile-page">
        <section class="photographer-hero">
            <div class="photographer-cover">
                @if($profile->cover_photo_path)
                    <img src="{{ asset($profile->cover_photo_path) }}" alt="{{ $displayName }} cover photo" data-photographer-cover-image>
                @else
                    <div class="photographer-cover-empty" data-photographer-cover-empty>
                        <i class="fas fa-panorama"></i>
                    </div>
                @endif
                @if($ownerCanManage)
                    <button type="button" class="photographer-media-trigger" data-open-photographer-media="cover" aria-label="Manage cover photo">
                        <span><i class="fas fa-image"></i> Manage cover</span>
                    </button>
                @endif
            </div>

            <div class="photographer-hero-body">
                <div class="photographer-avatar">
                    @if($ownerCanManage)
                        <button type="button" class="photographer-avatar-button" data-open-photographer-media="profile" aria-label="Manage profile photo">
                            @if($profile->profile_photo_path)
                                <img src="{{ asset($profile->profile_photo_path) }}" alt="{{ $displayName }}" data-photographer-profile-image>
                            @else
                                <span data-photographer-profile-empty>{{ substr($displayName, 0, 1) }}</span>
                            @endif
                            <span class="photographer-avatar-manage"><i class="fas fa-camera"></i> Manage</span>
                        </button>
                    @else
                        @if($profile->profile_photo_path)
                            <img src="{{ asset($profile->profile_photo_path) }}" alt="{{ $displayName }}">
                        @else
                            <span>{{ substr($displayName, 0, 1) }}</span>
                        @endif
                    @endif
                </div>

                <div>
                    <div class="photographer-name-row">
                        <h1 class="photographer-title">{{ $displayName }}</h1>
                        @if($profile->isVerified())
                            <span class="photographer-verified" title="Verified profile" aria-label="Verified profile">
                                <i class="fas fa-check"></i>
                            </span>
                        @endif
                        @if($profile->isVerified() && $profile->logo_path)
                            <span class="photographer-company-logo" title="{{ $companyName ?: 'Company logo' }}">
                                <img src="{{ asset($profile->logo_path) }}" alt="{{ $companyName ?: $displayName }} logo">
                            </span>
                        @endif
                    </div>
                    <p class="photographer-company-name">
                        @if($secondaryName)
                            <span>{{ $secondaryName }}</span>
                            <span aria-hidden="true">|</span>
                        @endif
                        <span class="photographer-username">{{ '@' . $user->username }}</span>
                    </p>
                    <div class="photographer-meta">
                        @if($profile->location_city || $profile->location_country)
                            <span><i class="fas fa-map-marker-alt"></i> {{ $profile->location_city }}{{ $profile->location_city && $profile->location_country ? ', ' : '' }}{{ $profile->location_country }}</span>
                        @endif
                        @if($profile->experience_level)
                            <span><i class="fas fa-camera"></i> {{ ucfirst($profile->experience_level) }} photographer</span>
                        @endif
                    </div>
                </div>

                <div class="photographer-actions">
                    <div class="photographer-social-actions">
                        @foreach($socialLinks as $link)
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="photographer-social" aria-label="{{ $link['label'] }}" title="{{ $link['label'] }}">
                                <i class="{{ $link['icon'] }}"></i>
                            </a>
                        @endforeach
                    </div>

                    <div class="photographer-primary-actions">
                        @if($ownerCanManage)
                            <a href="{{ route('photographers.profile.edit') }}" class="photographer-social photographer-edit" aria-label="Edit profile">
                                <i class="fas fa-pen"></i>
                                <span>Edit</span>
                            </a>
                        @elseif(auth()->check() && auth()->id() !== $user->id)
                            <a href="{{ route('messages.create', ['user_id' => $user->id]) }}" class="photographer-message">
                                <i class="fas fa-envelope"></i>
                                <span>Message</span>
                            </a>
                            @if(!$viewerConnection)
                                <details class="connection-popover">
                                    <summary class="photographer-social photographer-connect">
                                        <i class="fas fa-user-plus"></i>
                                        <span>Connect</span>
                                    </summary>
                                    <form method="POST" action="{{ route('connections.store', $user) }}" class="connection-request-box">
                                        @csrf
                                        <label class="text-sm font-bold text-gray-900" for="connection-message-photographer">Add a note</label>
                                        <textarea id="connection-message-photographer" name="message" maxlength="125" placeholder="Optional, up to 125 characters"></textarea>
                                        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:10px;">
                                            <button type="submit" style="background:#050505;color:#fff;">Send request</button>
                                        </div>
                                    </form>
                                </details>
                            @elseif($viewerConnection->status === \App\Models\Connection::STATUS_PENDING)
                                <span class="photographer-pill">Connection pending</span>
                            @elseif($viewerConnection->status === \App\Models\Connection::STATUS_ACCEPTED)
                                <span class="photographer-pill">Connected</span>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="photographer-message">
                                <i class="fas fa-envelope"></i>
                                <span>Log in to message</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <div class="photographer-layout">
            <section class="photographer-main">
                @if($profile->bio || $ownerCanManage)
                    <article class="photographer-card" data-profile-bio-section>
                        <div class="photographer-card-header profile-inline-actions">
                            <div>
                                <p class="photographer-kicker">Profile</p>
                                <h2 class="photographer-heading">Bio</h2>
                            </div>
                            @if($ownerCanManage)
                                <button type="button" class="profile-icon-action" data-profile-bio-edit aria-label="Edit bio">
                                    <i class="fas fa-pen"></i>
                                </button>
                            @else
                                <i class="fas fa-quote-left photographer-muted-icon"></i>
                            @endif
                        </div>
                        <p class="photographer-copy {{ $profile->bio ? '' : 'profile-bio-empty' }}" data-profile-bio-display>{{ $profile->bio ?: 'Add a short bio so visitors understand your work, style, and booking preferences.' }}</p>
                        @if($ownerCanManage)
                            <form class="profile-inline-editor" data-profile-bio-form action="{{ route('photographers.profile.bio.update') }}">
                                @csrf
                                @method('PATCH')
                                <textarea name="bio" maxlength="1200" data-profile-bio-input>{{ $profile->bio }}</textarea>
                                <p class="profile-inline-help">Plain text only. Paragraphs and line breaks are kept; links, HTML, and embeds are stripped out.</p>
                                <div class="profile-inline-buttons">
                                    <span class="profile-inline-status" data-profile-bio-status></span>
                                    <button type="button" class="profile-inline-cancel" data-profile-bio-cancel>Cancel</button>
                                    <button type="submit" class="profile-inline-save">Save Bio</button>
                                </div>
                            </form>
                        @endif
                    </article>
                @endif

                <article class="photographer-card">
                    <div class="photographer-card-header">
                        <div>
                            <p class="photographer-kicker">Network</p>
                            <h2 class="photographer-heading">Connections</h2>
                        </div>
                        <i class="fas fa-user-group photographer-muted-icon"></i>
                    </div>
                    @if(($connections ?? collect())->isNotEmpty())
                        @foreach($connections as $roleLabel => $roleConnections)
                            <h3 style="font-size:14px;font-weight:850;margin:18px 0 10px;">{{ $roleLabel }}</h3>
                            <div class="connection-card-grid">
                                @foreach($roleConnections as $connectedUser)
                                    @php
                                        $connectedProfile = $connectedUser->is_photographer ? $connectedUser->photographerProfile : $connectedUser->modelProfile;
                                        $connectedName = $connectedProfile?->display_name ?: $connectedUser->display_name ?: $connectedUser->name;
                                        $connectedPhoto = $connectedProfile?->profile_photo_path;
                                        $connectedRoute = $connectedUser->is_photographer
                                            ? route('photographers.show', $connectedUser->profileRouteIdentifier())
                                            : route('models.show', $connectedUser->profileRouteIdentifier());
                                    @endphp
                                    <a href="{{ $connectedRoute }}" class="connection-card">
                                        <span class="connection-card-avatar">
                                            @if($connectedPhoto)
                                                <img src="{{ asset($connectedPhoto) }}" alt="">
                                            @else
                                                {{ mb_substr($connectedName, 0, 1) }}
                                            @endif
                                        </span>
                                        <span>
                                            <strong style="display:block;font-size:14px;">{{ $connectedName }}</strong>
                                            <small style="color:#6b7280;font-weight:700;">{{ '@' . $connectedUser->username }}</small>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        <p class="photographer-copy">No public connections yet.</p>
                    @endif
                </article>

                <article class="photographer-card">
                    <div class="photographer-card-header">
                        <div>
                            <p class="photographer-kicker">Portfolio</p>
                            <h2 class="photographer-heading">Galleries</h2>
                        </div>
                        <div class="profile-inline-actions">
                            @if($ownerCanManage)
                                <a href="{{ route('portfolio.galleries.index') }}" class="profile-text-action">
                                    <i class="fas fa-images"></i>
                                    <span>Manage Galleries</span>
                                </a>
                            @endif
                            @if($publicGalleries->isNotEmpty())
                                <span class="photographer-gallery-meta">{{ $publicGalleries->count() }} {{ $publicGalleries->count() === 1 ? 'gallery' : 'galleries' }}</span>
                            @endif
                        </div>
                    </div>

                    @if($publicGalleries->isNotEmpty())
                        <div class="photographer-gallery-grid">
                            @foreach($publicGalleries->take(4) as $gallery)
                                @php
                                    $galleryCover = $gallery->cover_image_path;
                                    if (!$galleryCover) {
                                        $galleryCover = \App\Models\PhotographerPortfolioImage::where('photographer_id', $user->id)
                                            ->where('album_id', $gallery->id)
                                            ->where('is_public', true)
                                            ->orderBy('display_order')
                                            ->value('thumbnail_path');
                                    }
                                @endphp
                                <a href="{{ route('public.galleries.show', $gallery->id) }}" class="photographer-gallery-card">
                                    <div class="photographer-square">
                                        @if($galleryCover)
                                            <img src="{{ asset($galleryCover) }}" alt="{{ $gallery->name }}">
                                        @else
                                            <div class="photographer-empty"><i class="fas fa-images"></i></div>
                                        @endif
                                    </div>
                                    <div class="photographer-gallery-body">
                                        <h3 class="photographer-gallery-name">{{ $gallery->name }}</h3>
                                        <p class="photographer-gallery-meta">{{ $gallery->images_count }} {{ $gallery->images_count === 1 ? 'image' : 'images' }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="photographer-empty">No public galleries yet.</div>
                    @endif
                </article>

                @if(($featuredAlbumCredits ?? collect())->isNotEmpty() || ($featuredImageCredits ?? collect())->isNotEmpty())
                    <article class="photographer-card">
                        <div class="photographer-card-header">
                            <div>
                                <p class="photographer-kicker">Collaborations</p>
                                <h2 class="photographer-heading">Featured In</h2>
                            </div>
                            <i class="fas fa-user-tag photographer-muted-icon"></i>
                        </div>

                        @if(($featuredAlbumCredits ?? collect())->isNotEmpty())
                            <div style="margin-bottom: 24px;">
                                <p class="photographer-kicker" style="margin-bottom: 12px;">Galleries</p>
                                <div class="photographer-tagged-grid">
                                    @foreach($featuredAlbumCredits->take(4) as $credit)
                                        @php
                                            $album = $credit->creditable;
                                            $albumCover = $album->cover_image_path ?? $album->coverImage?->thumbnail_path;
                                        @endphp
                                        <a href="{{ route('public.galleries.show', $album->id) }}" class="photographer-image-card">
                                            <div class="photographer-square">
                                                @if($albumCover)
                                                    <img src="{{ asset($albumCover) }}" alt="{{ $album->name }}">
                                                @else
                                                    <div class="photographer-empty"><i class="fas fa-images"></i></div>
                                                @endif
                                            </div>
                                            <div class="profile-credit-body">
                                                <strong>{{ $album->name }}</strong>
                                                <span>{{ $credit->owner?->display_name ?? $credit->owner?->name }}</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(($featuredImageCredits ?? collect())->isNotEmpty())
                            <div>
                                <p class="photographer-kicker" style="margin-bottom: 12px;">Photos</p>
                                <div class="photographer-tagged-grid">
                                    @foreach($featuredImageCredits->take(6) as $credit)
                                        @php
                                            $image = $credit->creditable;
                                            $imageUrl = asset($image->thumbnail_path ?? $image->full_path);
                                            $fullUrl = asset($image->full_path ?? $image->thumbnail_path);
                                        @endphp
                                        <button type="button" class="photographer-image-card photographer-square" onclick="openLightbox('{{ $fullUrl }}')">
                                            <img src="{{ $imageUrl }}" alt="Credited portfolio image">
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </article>
                @endif

                @if($featuredImages->count() > 0)
                    <article class="photographer-card">
                        <div class="photographer-card-header">
                            <h2 class="photographer-heading">Featured Work</h2>
                            <i class="fas fa-star photographer-muted-icon"></i>
                        </div>
                        <div class="photographer-featured-grid">
                            @foreach($featuredImages->take(8) as $image)
                                <button type="button" class="photographer-image-card photographer-square" onclick="openLightbox('{{ asset($image->full_path) }}')">
                                    <img src="{{ asset($image->thumbnail_path) }}" alt="{{ $image->title }}">
                                </button>
                            @endforeach
                        </div>
                    </article>
                @endif

                <article class="photographer-card photographer-card-dashed">
                    <div class="photographer-card-header">
                        <div>
                            <p class="photographer-kicker">Coming Next</p>
                            <h2 class="photographer-heading">Feed</h2>
                        </div>
                        <i class="fas fa-stream photographer-muted-icon"></i>
                    </div>
                    <p class="photographer-copy">This area will support photographer posts, shoot updates, and tagged work from collaborators.</p>
                </article>
            </section>

            <aside class="photographer-side">
                @if($briefItems->isNotEmpty() || $ownerCanManage)
                    <section class="photographer-brief-card">
                        <div class="photographer-brief-header">
                            <div>
                                <p class="photographer-kicker" style="color: rgba(255,255,255,.55);">Photographer Brief</p>
                                <h2 class="photographer-heading" style="color: #fff;">Profile Snapshot</h2>
                            </div>
                            @if($ownerCanManage)
                                <button type="button" data-open-quick-modal="photographer-professional" class="photographer-brief-action">
                                    <i class="fas fa-pen"></i>
                                    <span>Edit</span>
                                </button>
                            @endif
                        </div>
                        @if($briefItems->isNotEmpty())
                            <div class="photographer-brief-grid">
                                @foreach($briefItems as $item)
                                    <div class="photographer-brief-item">
                                        <span class="photographer-brief-icon"><i class="{{ $item['icon'] }}"></i></span>
                                        <p class="photographer-kicker" style="color: rgba(255,255,255,.45);">{{ $item['label'] }}</p>
                                        <p style="font-weight: 800; margin: 6px 0 0;">{{ $item['value'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p style="color: rgba(255,255,255,.65); font-size: 14px; line-height: 1.6; margin: 0; padding: 0 20px 20px;">Add your experience, studio, and travel details so models can understand your working setup quickly.</p>
                        @endif
                    </section>
                @endif

                @if(!empty($validSpecialties) || $ownerCanManage)
                    <section class="photographer-card">
                        <div class="photographer-card-header">
                            <h2 class="photographer-heading">Specialties</h2>
                            @if($ownerCanManage)
                                <button type="button" data-open-quick-modal="photographer-professional" class="profile-icon-action" aria-label="Edit specialties">
                                    <i class="fas fa-pen"></i>
                                </button>
                            @else
                                <i class="fas fa-bullseye photographer-muted-icon"></i>
                            @endif
                        </div>
                        @if(!empty($validSpecialties))
                            <div class="photographer-chip-list">
                                @foreach(array_keys($validSpecialties) as $specialty)
                                    <span class="photographer-chip">{{ $specialtiesOptions[$specialty] }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="profile-section-empty">Add specialties so models can quickly understand the type of work you shoot.</p>
                        @endif
                    </section>
                @endif

                @if(!empty($validServices) || $ownerCanManage)
                    <section class="photographer-card">
                        <div class="photographer-card-header">
                            <h2 class="photographer-heading">Services</h2>
                            @if($ownerCanManage)
                                <button type="button" data-open-quick-modal="photographer-professional" class="profile-icon-action" aria-label="Edit services">
                                    <i class="fas fa-pen"></i>
                                </button>
                            @else
                                <i class="fas fa-briefcase photographer-muted-icon"></i>
                            @endif
                        </div>
                        @if(!empty($validServices))
                            <div class="photographer-chip-list">
                                @foreach(array_keys($validServices) as $service)
                                    <span class="photographer-chip">{{ $servicesOptions[$service] }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="profile-section-empty">Add services so people know whether you offer tests, digitals, studio sessions, events, or other work.</p>
                        @endif
                    </section>
                @endif

            </aside>
        </div>
    </main>

    <div id="lightbox" class="photographer-lightbox" onclick="closeLightbox()">
        <img id="lightbox-image" src="" alt="">
        <button type="button" onclick="closeLightbox()">&times;</button>
    </div>

    @if($ownerCanManage)
        <div class="quick-edit-overlay" data-quick-modal="photographer-professional">
            <form class="quick-edit-modal" action="{{ route('photographers.profile.professional.update') }}" data-quick-edit-form>
                @csrf
                @method('PATCH')
                <div class="quick-edit-header">
                    <div>
                        <h2 class="text-xl font-bold text-gray-950">Edit Profile Details</h2>
                        <p class="mt-1 text-sm text-gray-600">Quick update your snapshot, specialties, and services.</p>
                    </div>
                    <button type="button" data-close-quick-modal class="profile-icon-action" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <div class="quick-edit-body">
                    <div class="quick-edit-grid">
                        <div class="quick-edit-field">
                            <label>Experience Level</label>
                            <select name="experience_level">
                                <option value="">Select level</option>
                                @foreach(['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'professional' => 'Professional'] as $key => $label)
                                    <option value="{{ $key }}" @selected($profile->experience_level === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="quick-edit-field">
                            <label>Started In</label>
                            <input type="number" name="experience_start_year" min="1900" max="{{ date('Y') }}" value="{{ $profile->experience_start_year }}">
                        </div>
                        <div class="quick-edit-field" style="grid-column: 1 / -1;">
                            <label>Base Location</label>
                            <input type="text" name="studio_location" value="{{ $profile->studio_location }}">
                        </div>
                        <label class="quick-edit-check">
                            <input type="checkbox" name="available_for_travel" value="1" @checked($profile->available_for_travel)>
                            <span>Available for travel</span>
                        </label>
                    </div>

                    <div class="mt-6">
                        <p class="quick-edit-checklist-label">Specialties</p>
                        <div class="quick-edit-checklist">
                            @foreach($specialtiesOptions as $key => $label)
                                <label class="quick-edit-check">
                                    <input type="checkbox" name="specialties[]" value="{{ $key }}" @checked(in_array($key, $profile->specialties ?? [], true))>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-6">
                        <p class="quick-edit-checklist-label">Services</p>
                        <div class="quick-edit-checklist">
                            @foreach($servicesOptions as $key => $label)
                                <label class="quick-edit-check">
                                    <input type="checkbox" name="services_offered[]" value="{{ $key }}" @checked(in_array($key, $profile->services_offered ?? [], true))>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="quick-edit-footer">
                    <span class="quick-edit-message" data-quick-edit-message></span>
                    <button type="button" data-close-quick-modal class="profile-inline-cancel">Cancel</button>
                    <button type="submit" class="profile-inline-save">Save</button>
                </div>
            </form>
        </div>

        <div class="photographer-media-overlay" data-photographer-media-modal>
            <form class="photographer-media-modal" action="{{ route('photographers.profile.media.update') }}" enctype="multipart/form-data" data-photographer-media-form>
                @csrf
                @method('PATCH')
                <input type="hidden" name="profile_photo_image_id" data-profile-image-id>
                <input type="hidden" name="cover_photo_image_id" data-cover-image-id>
                <input type="hidden" name="profile_photo_crop_data" data-profile-crop-data>
                <input type="hidden" name="cover_photo_crop_data" data-cover-crop-data>

                <div class="photographer-media-header">
                    <div>
                        <h2 class="photographer-heading" style="font-size: 20px; margin: 0;" data-media-title>Profile Photo</h2>
                        <p class="photographer-copy" style="font-size: 14px; margin: 6px 0 0;">Upload a new image or choose one from your portfolio.</p>
                    </div>
                    <button type="button" class="photographer-media-close" data-close-photographer-media aria-label="Close"><i class="fas fa-times"></i></button>
                </div>

                <div class="photographer-media-body">
                    <p class="photographer-media-status photographer-media-hidden" data-media-status></p>

                    <section class="photographer-media-panel" data-media-step="start">
                        <div class="photographer-media-current">
                            <img class="profile-preview photographer-media-hidden" data-current-profile-preview src="{{ $profile->profile_photo_path ? asset($profile->profile_photo_path) : '' }}" alt="">
                            <img class="cover-preview photographer-media-hidden" data-current-cover-preview src="{{ $profile->cover_photo_path ? asset($profile->cover_photo_path) : '' }}" alt="">
                            <div class="photographer-media-current-placeholder photographer-media-hidden" data-current-placeholder>
                                <i class="fas fa-image text-2xl"></i>
                            </div>
                            <div>
                                <p style="font-weight: 800; margin: 0;" data-start-heading>Add profile photo</p>
                                <p style="color: #6b7280; font-size: 14px; margin: 6px 0 0;">Choose how you want to update it.</p>
                            </div>
                            <div class="photographer-media-choice-row">
                                <button type="button" class="photographer-media-button primary" data-media-upload-choice><i class="fas fa-upload"></i> Upload</button>
                                <button type="button" class="photographer-media-button" data-media-portfolio-choice><i class="fas fa-images"></i> Portfolio</button>
                            </div>
                        </div>
                    </section>

                    <section class="photographer-media-panel photographer-media-hidden" data-media-step="upload">
                        <button type="button" class="photographer-media-button" data-media-back><i class="fas fa-arrow-left"></i> Back</button>
                        <label style="display: block; font-weight: 800; margin: 18px 0 8px;" data-upload-label>Upload profile photo</label>
                        <input type="file" name="profile_photo_upload" accept="image/jpeg,image/jpg,image/png,image/webp" data-profile-upload>
                        <input type="file" name="cover_photo_upload" accept="image/jpeg,image/jpg,image/png,image/webp" data-cover-upload class="photographer-media-hidden">
                    </section>

                    <section class="photographer-media-panel photographer-media-hidden" data-media-step="groups">
                        <div style="align-items: center; display: flex; justify-content: space-between; gap: 12px; margin-bottom: 14px;">
                            <button type="button" class="photographer-media-button" data-media-back><i class="fas fa-arrow-left"></i> Back</button>
                            <strong>Choose an album</strong>
                        </div>
                        <div class="photographer-media-grid" data-media-groups></div>
                    </section>

                    <section class="photographer-media-panel photographer-media-hidden" data-media-step="images">
                        <div style="align-items: center; display: flex; justify-content: space-between; gap: 12px; margin-bottom: 14px;">
                            <button type="button" class="photographer-media-button" data-media-groups-back><i class="fas fa-arrow-left"></i> Albums</button>
                            <strong data-media-group-title></strong>
                        </div>
                        <div class="photographer-media-grid" data-media-images></div>
                    </section>

                    <section class="photographer-media-panel photographer-media-hidden" data-media-step="crop">
                        <div style="align-items: center; display: flex; justify-content: space-between; gap: 12px; margin-bottom: 14px;">
                            <div>
                                <strong data-crop-heading>Crop your photo</strong>
                                <p style="color: #6b7280; font-size: 13px; margin: 4px 0 0;">Drag to reposition and resize the crop box.</p>
                            </div>
                            <button type="button" class="photographer-media-button" data-media-back><i class="fas fa-arrow-left"></i> Back</button>
                        </div>
                        <div class="photographer-media-cropper">
                            <img src="" alt="" data-media-crop-image>
                        </div>
                    </section>
                </div>

                <div class="photographer-media-footer">
                    <span class="photographer-media-status" data-media-footer-status></span>
                    <div style="display: flex; gap: 12px;">
                        <button type="button" class="photographer-media-button" data-close-photographer-media>Cancel</button>
                        <button type="submit" class="photographer-media-button primary" data-media-save disabled>Save</button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script>
        window.photographerMediaGroups = @json($photographerMediaGroups ?? []);

        function openLightbox(imageSrc) {
            document.getElementById('lightbox-image').src = imageSrc;
            document.getElementById('lightbox').classList.add('is-open');
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('is-open');
        }

        (() => {
            const modal = document.querySelector('[data-photographer-media-modal]');
            const form = document.querySelector('[data-photographer-media-form]');

            if (!modal || !form) {
                return;
            }

            const groups = window.photographerMediaGroups || [];
            const state = {
                slot: 'profile',
                step: 'start',
                previousStep: 'start',
                mode: 'upload',
                selectedImageId: '',
                cropper: null,
                scrollY: 0,
            };

            const els = {
                title: modal.querySelector('[data-media-title]'),
                status: modal.querySelector('[data-media-status]'),
                footerStatus: modal.querySelector('[data-media-footer-status]'),
                startHeading: modal.querySelector('[data-start-heading]'),
                profilePreview: modal.querySelector('[data-current-profile-preview]'),
                coverPreview: modal.querySelector('[data-current-cover-preview]'),
                placeholder: modal.querySelector('[data-current-placeholder]'),
                profileUpload: modal.querySelector('[data-profile-upload]'),
                coverUpload: modal.querySelector('[data-cover-upload]'),
                uploadLabel: modal.querySelector('[data-upload-label]'),
                groupsContainer: modal.querySelector('[data-media-groups]'),
                imagesContainer: modal.querySelector('[data-media-images]'),
                groupTitle: modal.querySelector('[data-media-group-title]'),
                cropImage: modal.querySelector('[data-media-crop-image]'),
                cropHeading: modal.querySelector('[data-crop-heading]'),
                saveButton: modal.querySelector('[data-media-save]'),
                profileImageId: modal.querySelector('[data-profile-image-id]'),
                coverImageId: modal.querySelector('[data-cover-image-id]'),
                profileCropData: modal.querySelector('[data-profile-crop-data]'),
                coverCropData: modal.querySelector('[data-cover-crop-data]'),
            };

            const lockPage = () => {
                state.scrollY = window.scrollY || document.documentElement.scrollTop || 0;
                document.body.style.position = 'fixed';
                document.body.style.top = `-${state.scrollY}px`;
                document.body.style.left = '0';
                document.body.style.right = '0';
                document.body.style.width = '100%';
                document.body.style.overflow = 'hidden';
            };

            const unlockPage = () => {
                const top = document.body.style.top;
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.left = '';
                document.body.style.right = '';
                document.body.style.width = '';
                document.body.style.overflow = '';
                window.scrollTo(0, Math.abs(parseInt(top || '0', 10)) || state.scrollY);
            };

            const destroyCropper = () => {
                if (state.cropper) {
                    state.cropper.destroy();
                    state.cropper = null;
                }
            };

            const showStep = (step) => {
                state.step = step;
                modal.querySelectorAll('[data-media-step]').forEach((panel) => {
                    panel.classList.toggle('photographer-media-hidden', panel.dataset.mediaStep !== step);
                });
                els.saveButton.disabled = step !== 'crop';
                els.status.classList.add('photographer-media-hidden');
                els.status.classList.remove('is-error');
                els.footerStatus.textContent = '';
            };

            const resetInputs = () => {
                els.profileUpload.value = '';
                els.coverUpload.value = '';
                els.profileImageId.value = '';
                els.coverImageId.value = '';
                els.profileCropData.value = '';
                els.coverCropData.value = '';
                state.selectedImageId = '';
            };

            const setCurrentPreview = () => {
                const isProfile = state.slot === 'profile';
                const preview = isProfile ? els.profilePreview : els.coverPreview;
                const hasPreview = !!preview.getAttribute('src');

                els.title.textContent = isProfile ? 'Profile Photo' : 'Cover Photo';
                els.startHeading.textContent = hasPreview
                    ? (isProfile ? 'Update profile photo' : 'Update cover photo')
                    : (isProfile ? 'Add profile photo' : 'Add cover photo');
                els.uploadLabel.textContent = isProfile ? 'Upload profile photo' : 'Upload cover photo';
                els.cropHeading.textContent = isProfile ? 'Crop your profile photo' : 'Crop your cover photo';
                els.profilePreview.classList.toggle('photographer-media-hidden', !isProfile || !hasPreview);
                els.coverPreview.classList.toggle('photographer-media-hidden', isProfile || !hasPreview);
                els.placeholder.classList.toggle('photographer-media-hidden', hasPreview);
                els.profileUpload.classList.toggle('photographer-media-hidden', !isProfile);
                els.coverUpload.classList.toggle('photographer-media-hidden', isProfile);
            };

            const openModal = (slot) => {
                state.slot = slot;
                state.mode = 'upload';
                state.previousStep = 'start';
                resetInputs();
                destroyCropper();
                setCurrentPreview();
                showStep('start');
                modal.classList.add('is-open');
                lockPage();
            };

            const closeModal = () => {
                destroyCropper();
                modal.classList.remove('is-open');
                unlockPage();
            };

            const renderGroups = () => {
                els.groupsContainer.innerHTML = '';

                if (groups.length === 0) {
                    els.groupsContainer.innerHTML = '<div class="photographer-media-panel">No portfolio images available yet.</div>';
                    return;
                }

                groups.forEach((group) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'photographer-media-card';
                    button.innerHTML = `
                        ${group.cover ? `<img src="${group.cover}" alt="">` : '<div class="empty"><i class="fas fa-images"></i></div>'}
                        <div class="photographer-media-card-body">
                            <strong>${group.label}</strong>
                            <p style="color:#6b7280;font-size:13px;margin:4px 0 0;">${group.count} image${group.count === 1 ? '' : 's'}</p>
                        </div>
                    `;
                    button.addEventListener('click', () => renderImages(group));
                    els.groupsContainer.appendChild(button);
                });
            };

            const renderImages = (group) => {
                els.groupTitle.textContent = group.label;
                els.imagesContainer.innerHTML = '';
                group.images.forEach((image) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'photographer-media-card';
                    button.innerHTML = `<img src="${image.preview}" alt="">`;
                    button.addEventListener('click', () => {
                        state.previousStep = 'images';
                        startCrop(image.full || image.preview, image.id, 'portfolio');
                    });
                    els.imagesContainer.appendChild(button);
                });
                showStep('images');
            };

            const startCrop = (src, imageId = '', mode = 'upload') => {
                state.mode = mode;
                state.selectedImageId = imageId;
                destroyCropper();
                els.cropImage.src = src;
                showStep('crop');

                setTimeout(() => {
                    state.cropper = new Cropper(els.cropImage, {
                        aspectRatio: state.slot === 'profile' ? 1 : 3,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: state.slot === 'profile' ? 0.82 : 0.9,
                        background: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                        wheelZoomRatio: 0.08,
                        ready: captureCrop,
                        crop: captureCrop,
                    });
                }, 60);
            };

            const captureCrop = () => {
                if (!state.cropper) {
                    return;
                }

                const cropData = JSON.stringify(state.cropper.getData(true));
                if (state.slot === 'profile') {
                    els.profileCropData.value = cropData;
                } else {
                    els.coverCropData.value = cropData;
                }
            };

            modal.querySelectorAll('[data-open-photographer-media]').forEach((button) => {
                button.addEventListener('click', () => openModal(button.dataset.openPhotographerMedia));
            });

            document.querySelectorAll('[data-open-photographer-media]').forEach((button) => {
                button.addEventListener('click', () => openModal(button.dataset.openPhotographerMedia));
            });

            modal.querySelectorAll('[data-close-photographer-media]').forEach((button) => {
                button.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeModal();
                }
            });

            modal.querySelector('[data-media-upload-choice]').addEventListener('click', () => {
                state.previousStep = 'start';
                state.mode = 'upload';
                resetInputs();
                showStep('upload');
            });

            modal.querySelector('[data-media-portfolio-choice]').addEventListener('click', () => {
                state.previousStep = 'start';
                state.mode = 'portfolio';
                resetInputs();
                renderGroups();
                showStep('groups');
            });

            modal.querySelectorAll('[data-media-back]').forEach((button) => {
                button.addEventListener('click', () => {
                    destroyCropper();
                    showStep(state.previousStep || 'start');
                });
            });

            modal.querySelector('[data-media-groups-back]').addEventListener('click', () => {
                renderGroups();
                showStep('groups');
            });

            els.profileUpload.addEventListener('change', (event) => {
                const file = event.target.files?.[0];
                if (!file) {
                    return;
                }
                state.previousStep = 'upload';
                const reader = new FileReader();
                reader.onload = (loadEvent) => startCrop(loadEvent.target.result, '', 'upload');
                reader.readAsDataURL(file);
            });

            els.coverUpload.addEventListener('change', (event) => {
                const file = event.target.files?.[0];
                if (!file) {
                    return;
                }
                state.previousStep = 'upload';
                const reader = new FileReader();
                reader.onload = (loadEvent) => startCrop(loadEvent.target.result, '', 'upload');
                reader.readAsDataURL(file);
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                captureCrop();

                if (state.mode === 'portfolio') {
                    if (state.slot === 'profile') {
                        els.profileImageId.value = state.selectedImageId;
                    } else {
                        els.coverImageId.value = state.selectedImageId;
                    }
                }

                els.footerStatus.textContent = 'Saving...';
                els.footerStatus.classList.remove('is-error');
                els.saveButton.disabled = true;

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        const errors = payload.errors || {};
                        const firstError = Object.values(errors).flat()[0];
                        throw new Error(firstError || payload.message || 'The image could not be saved.');
                    }

                    if (payload.profile_photo_url) {
                        els.profilePreview.src = payload.profile_photo_url;
                        const current = document.querySelector('[data-photographer-profile-image]');
                        const empty = document.querySelector('[data-photographer-profile-empty]');
                        if (current) {
                            current.src = payload.profile_photo_url;
                        } else if (empty) {
                            const img = document.createElement('img');
                            img.src = payload.profile_photo_url;
                            img.alt = @json($displayName);
                            img.setAttribute('data-photographer-profile-image', '');
                            empty.replaceWith(img);
                        }
                    }

                    if (payload.cover_photo_url) {
                        els.coverPreview.src = payload.cover_photo_url;
                        const current = document.querySelector('[data-photographer-cover-image]');
                        const empty = document.querySelector('[data-photographer-cover-empty]');
                        if (current) {
                            current.src = payload.cover_photo_url;
                        } else if (empty) {
                            const img = document.createElement('img');
                            img.src = payload.cover_photo_url;
                            img.alt = `${@json($displayName)} cover photo`;
                            img.setAttribute('data-photographer-cover-image', '');
                            empty.replaceWith(img);
                        }
                    }

                    els.footerStatus.textContent = payload.message || 'Profile media updated.';
                    resetInputs();
                    setTimeout(closeModal, 450);
                } catch (error) {
                    els.footerStatus.textContent = error.message || 'The image could not be saved.';
                    els.footerStatus.classList.add('is-error');
                    els.saveButton.disabled = false;
                }
            });
        })();

        document.querySelectorAll('[data-profile-bio-section]').forEach((section) => {
            const editButton = section.querySelector('[data-profile-bio-edit]');
            const cancelButton = section.querySelector('[data-profile-bio-cancel]');
            const form = section.querySelector('[data-profile-bio-form]');
            const input = section.querySelector('[data-profile-bio-input]');
            const display = section.querySelector('[data-profile-bio-display]');
            const status = section.querySelector('[data-profile-bio-status]');

            if (!form || !input || !display) {
                return;
            }

            const openEditor = () => {
                form.classList.add('is-open');
                input.focus();
            };

            const closeEditor = () => {
                form.classList.remove('is-open');
                status.textContent = '';
                status.classList.remove('is-error');
            };

            editButton?.addEventListener('click', openEditor);
            cancelButton?.addEventListener('click', closeEditor);

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                status.textContent = 'Saving...';
                status.classList.remove('is-error');

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        const errors = payload.errors || {};
                        const firstError = Object.values(errors).flat()[0];
                        throw new Error(firstError || payload.message || 'Bio could not be saved.');
                    }

                    input.value = payload.bio || '';
                    display.textContent = payload.bio || 'Add a short bio so visitors understand your work, style, and booking preferences.';
                    display.classList.toggle('profile-bio-empty', !payload.bio);
                    status.textContent = payload.message || 'Bio updated.';

                    setTimeout(closeEditor, 700);
                } catch (error) {
                    status.textContent = error.message || 'Bio could not be saved.';
                    status.classList.add('is-error');
                }
            });
        });

        let quickModalScrollY = 0;

        const lockQuickModalPage = () => {
            if (document.body.dataset.quickModalLocked === '1') {
                return;
            }

            quickModalScrollY = window.scrollY || document.documentElement.scrollTop || 0;
            document.body.dataset.quickModalLocked = '1';
            document.body.style.position = 'fixed';
            document.body.style.top = `-${quickModalScrollY}px`;
            document.body.style.left = '0';
            document.body.style.right = '0';
            document.body.style.width = '100%';
            document.body.style.overflow = 'hidden';
        };

        const unlockQuickModalPage = () => {
            if (document.querySelector('[data-quick-modal].is-open')) {
                return;
            }

            const top = document.body.style.top;
            document.body.dataset.quickModalLocked = '';
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.left = '';
            document.body.style.right = '';
            document.body.style.width = '';
            document.body.style.overflow = '';
            window.scrollTo(0, Math.abs(parseInt(top || '0', 10)) || quickModalScrollY);
        };

        const openQuickModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.classList.add('is-open');
            lockQuickModalPage();
        };

        const closeQuickModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.classList.remove('is-open');
            unlockQuickModalPage();
        };

        document.querySelectorAll('[data-open-quick-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                openQuickModal(document.querySelector(`[data-quick-modal="${button.dataset.openQuickModal}"]`));
            });
        });

        document.querySelectorAll('[data-close-quick-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                closeQuickModal(button.closest('[data-quick-modal]'));
            });
        });

        document.querySelectorAll('[data-quick-modal]').forEach((modal) => {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeQuickModal(modal);
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            closeQuickModal(document.querySelector('[data-quick-modal].is-open'));
        });

        document.querySelectorAll('[data-quick-edit-form]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const message = form.querySelector('[data-quick-edit-message]');
                message.textContent = 'Saving...';
                message.classList.remove('is-error');

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        const errors = payload.errors || {};
                        const firstError = Object.values(errors).flat()[0];
                        throw new Error(firstError || payload.message || 'Changes could not be saved.');
                    }

                    message.textContent = payload.message || 'Updated.';
                    setTimeout(() => window.location.reload(), 450);
                } catch (error) {
                    message.textContent = error.message || 'Changes could not be saved.';
                    message.classList.add('is-error');
                }
            });
        });

    </script>
</x-app-layout>

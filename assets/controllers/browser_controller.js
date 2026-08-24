import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['origin', 'path', 'query', 'viewport'];

    static values = {
        syncUrl: Boolean,
        url: String,
        showOrigin: { type: Boolean, default: true },
        showPath: { type: Boolean, default: true },
        showQuery: { type: Boolean, default: true },
    };

    initialize() {
        this.externalLoadingCount = 0;
        this.isLoading = false;
        this.handleLocationChange = () => this.syncUrl();
    }

    connect() {
        this.busyObserver = new MutationObserver(() => this.updateLoadingState());
        this.busyObserver.observe(this.viewportTarget, {
            attributes: true,
            attributeFilter: ['aria-busy', 'data-live-is-loading'],
            subtree: true,
        });

        this.updateLoadingState();
        this.renderUrl(false, this.syncUrlValue ? window.location.href : this.normalizedConfiguredUrl());

        if (!this.syncUrlValue) {
            return;
        }

        if ('navigation' in window) {
            window.navigation.addEventListener('currententrychange', this.handleLocationChange);
        } else {
            window.addEventListener('popstate', this.handleLocationChange);
            window.addEventListener('hashchange', this.handleLocationChange);
        }
    }

    disconnect() {
        this.busyObserver?.disconnect();
        this.clearUrlFlash();

        if (!this.syncUrlValue) {
            return;
        }

        if ('navigation' in window) {
            window.navigation.removeEventListener('currententrychange', this.handleLocationChange);
        } else {
            window.removeEventListener('popstate', this.handleLocationChange);
            window.removeEventListener('hashchange', this.handleLocationChange);
        }
    }

    startLoading() {
        this.externalLoadingCount++;
        this.updateLoadingState();
    }

    stopLoading() {
        this.externalLoadingCount = Math.max(0, this.externalLoadingCount - 1);
        this.updateLoadingState();
    }

    syncUrl({ detail } = {}) {
        if (!this.syncUrlValue) {
            return;
        }

        this.renderUrl(true, detail?.url);
    }

    updateLoadingState() {
        const wasLoading = this.isLoading;
        const hasBusyContent = this.viewportTarget.matches('[aria-busy="true"]')
            || this.viewportTarget.querySelector('[aria-busy="true"], [data-live-is-loading]') !== null;

        this.isLoading = this.externalLoadingCount > 0 || hasBusyContent;
        this.element.toggleAttribute('data-browser-loading', this.isLoading);

        if (wasLoading && !this.isLoading) {
            this.syncUrl();
        }
    }

    renderUrl(flash, value = window.location.href) {
        const parts = this.parseUrl(value);
        const changedTargets = [
            this.renderUrlPart(this.originTarget, parts.origin, this.showOriginValue),
            this.renderUrlPart(this.pathTarget, parts.path, this.showPathValue),
            this.renderUrlPart(this.queryTarget, parts.query, this.showQueryValue),
        ].filter(Boolean);

        if (!flash || 0 === changedTargets.length) {
            return;
        }

        this.clearUrlFlash();
        window.requestAnimationFrame(() => {
            changedTargets.forEach((target) => target.setAttribute('data-browser-url-changed', ''));
            this.urlFlashTimeout = window.setTimeout(() => this.clearUrlFlash(), 700);
        });
    }

    renderUrlPart(target, value, visible) {
        const changed = target.textContent !== value;

        target.textContent = value;
        target.hidden = !visible || '' === value;

        return changed && !target.hidden ? target : null;
    }

    clearUrlFlash() {
        window.clearTimeout(this.urlFlashTimeout);
        this.originTarget.removeAttribute('data-browser-url-changed');
        this.pathTarget.removeAttribute('data-browser-url-changed');
        this.queryTarget.removeAttribute('data-browser-url-changed');
    }

    parseUrl(value) {
        const url = new URL(value, window.location.origin);
        const configuredUrl = new URL(this.normalizedConfiguredUrl());

        return {
            origin: configuredUrl.host,
            path: url.pathname,
            query: `${url.search}${url.hash}`,
        };
    }

    normalizedConfiguredUrl() {
        return this.urlValue.includes('://') ? this.urlValue : `https://${this.urlValue}`;
    }
}

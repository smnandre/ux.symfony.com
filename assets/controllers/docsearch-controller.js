import { Controller } from '@hotwired/stimulus';

const SECTIONS = ['Packages', 'Kits', 'Demos', 'Resources'];

const sectionRank = (item) => {
    const rank = SECTIONS.indexOf(item.hierarchy.lvl0);

    return rank === -1 ? SECTIONS.length : rank;
};

const isSearchShortcut = (event) => event.key.toLowerCase() === 'k' && (event.metaKey || event.ctrlKey) && !event.altKey;

export default class extends Controller {
    static targets = ['button', 'mount'];
    static values = { appId: String, apiKey: String, index: String, stylesheet: String };

    #loading = null;

    disconnect() {
        this.#loading?.then((docsearch) => docsearch.destroy());
        this.#loading = null;
    }

    prefetch() {
        this.#load();
    }

    async open() {
        const docsearch = await this.#load();
        docsearch.open();
    }

    toggleOnShortcut(event) {
        if (event.defaultPrevented) {
            return;
        }

        if (isSearchShortcut(event)) {
            event.preventDefault();
            this.#toggle();
        }
    }

    async #toggle() {
        const docsearch = await this.#load();
        docsearch.isOpen ? docsearch.close() : docsearch.open();
    }

    #load() {
        this.#loading ??= Promise.all([import('@docsearch/js'), this.#loadStylesheet()])
            .then(([{ default: docsearch }]) => new Promise((resolve) => {
                const instance = docsearch({
                    container: this.mountTarget,
                    appId: this.appIdValue,
                    apiKey: this.apiKeyValue,
                    indices: [this.indexValue],
                    keyboardShortcuts: { 'Ctrl/Cmd+K': false, '/': false, 'Ctrl/Cmd+I': false },
                    // DocSearch lists sections in the order their first hit appears
                    transformItems: (items) => items.toSorted((a, b) => sectionRank(a) - sectionRank(b)),
                    onClose: () => this.buttonTarget.focus({ preventScroll: true }),
                    // onReady can fire during the docsearch() call, before `instance` is assigned
                    onReady: () => queueMicrotask(() => resolve(instance)),
                });

                if (instance.isReady) {
                    resolve(instance);
                }
            }));

        return this.#loading;
    }

    #loadStylesheet() {
        return new Promise((resolve) => {
            const style = document.createElement('style');
            style.textContent = `@import url("${this.stylesheetValue}") layer(base);`;
            style.addEventListener('load', resolve, { once: true });
            style.addEventListener('error', resolve, { once: true });
            document.head.append(style);
        });
    }
}

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['trigger', 'connector', 'line', 'endpoint'];

    static values = {
        targetSelector: String,
    };

    initialize() {
        this.scheduleDraw = this.scheduleDraw.bind(this);
    }

    connect() {
        this.resizeObserver = new ResizeObserver(this.scheduleDraw);
        this.resizeObserver.observe(this.triggerTarget);
        this.observeTarget();

        window.addEventListener('resize', this.scheduleDraw);
        window.addEventListener('scroll', this.scheduleDraw, true);
    }

    disconnect() {
        this.resizeObserver.disconnect();
        window.removeEventListener('resize', this.scheduleDraw);
        window.removeEventListener('scroll', this.scheduleDraw, true);
        window.cancelAnimationFrame(this.animationFrame);
    }

    draw() {
        const target = this.resolveTarget();

        if (!target) {
            this.connectorTarget.setAttribute('hidden', '');

            return;
        }

        this.connectorTarget.removeAttribute('hidden');
        this.observeTarget(target);

        const triggerRect = this.triggerTarget.getBoundingClientRect();
        const targetRect = target.getBoundingClientRect();
        const start = {
            x: triggerRect.left + triggerRect.width / 2,
            y: triggerRect.top + triggerRect.height / 2,
        };
        const end = this.closestPoint(targetRect, start);

        this.connectorTarget.setAttribute('viewBox', `0 0 ${window.innerWidth} ${window.innerHeight}`);
        this.lineTarget.setAttribute('x1', start.x);
        this.lineTarget.setAttribute('y1', start.y);
        this.lineTarget.setAttribute('x2', end.x);
        this.lineTarget.setAttribute('y2', end.y);
        this.endpointTarget.setAttribute('cx', end.x);
        this.endpointTarget.setAttribute('cy', end.y);
    }

    scheduleDraw() {
        window.cancelAnimationFrame(this.animationFrame);
        this.animationFrame = window.requestAnimationFrame(() => this.draw());
    }

    resolveTarget() {
        return document.querySelector(this.targetSelectorValue);
    }

    observeTarget(target = this.resolveTarget()) {
        if (!target || target === this.observedTarget) {
            return;
        }

        if (this.observedTarget) {
            this.resizeObserver.unobserve(this.observedTarget);
        }

        this.observedTarget = target;
        this.resizeObserver.observe(target);
    }

    closestPoint(rect, point) {
        return {
            x: Math.max(rect.left, Math.min(point.x, rect.right)),
            y: Math.max(rect.top, Math.min(point.y, rect.bottom)),
        };
    }
}

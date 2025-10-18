export type StateChangeCallback = (params: URLSearchParams, path: string, isInitialLoad: boolean) => void;

export class UrlStateManager {
    private stateChangeCallbacks: Set<StateChangeCallback> = new Set();
    private isInitialized = false;
    private readonly handlePopState: () => void; // Store bound function for proper cleanup
    private navigationHistory: string[] = []; // Track navigation history
    private currentHistoryIndex = -1; // Track current position in history

    constructor() {
        // Bind the handler once for proper cleanup
        this.handlePopState = this.handlePopStateEvent.bind(this);
        this.init();
    }

    /**
     * Initialize the URL state manager and set up event listeners
     */
    private init(): void {
        if (this.isInitialized) return;

        // Initialize history with current URL
        this.navigationHistory = [window.location.pathname];
        this.currentHistoryIndex = 0;

        // Listen for browser navigation (back/forward)
        window.addEventListener("popstate", this.handlePopState);

        // Handle initial page load
        this.notifyStateChange(true);
        this.isInitialized = true;
    }

    /**
     * Update a single URL parameter
     */
    updateParam(key: string, value: string | null): void {
        const url = new URL(window.location.href);
        const params = url.searchParams;

        if (value === null) {
            params.delete(key);
        } else {
            params.set(key, value);
        }

        this.updateURL(url);
    }

    /**
     * Update multiple URL parameters at once
     */
    updateParams(params: Record<string, string | null>): void {
        const url = new URL(window.location.href);

        Object.entries(params).forEach(([key, value]) => {
            if (value === null || value === "") {
                url.searchParams.delete(key);
            } else {
                url.searchParams.set(key, value);
            }
        });

        this.updateURL(url);
    }

    getParam(key: string): string | null {
        const url = new URL(window.location.href);
        return url.searchParams.has(key) ? url.searchParams.get(key) : null;
    }

    getParams(): Record<string, string> {
        const url = new URL(window.location.href);
        return Object.fromEntries(url.searchParams.entries());
    }

    /**
     * Update the URL path (pathname)
     */
    updatePath(path: string): void {
        const url = new URL(window.location.href);
        url.pathname = path;
        this.updateURL(url);
    }

    /**
     * Get the current path
     */
    getCurrentPath(): string {
        return window.location.pathname;
    }

    /**
     * Get path segments as an array
     */
    getPathSegments(): string[] {
        return window.location.pathname
            .split("/")
            .filter(segment => segment.length > 0);
    }

    /**
     * Update both path and parameters
     */
    updateState(path?: string, params?: Record<string, string | null>): void {
        const url = new URL(window.location.href);

        if (path !== undefined) {
            url.pathname = path;
        }

        if (params) {
            Object.entries(params).forEach(([key, value]) => {
                if (value === null || value === "") {
                    url.searchParams.delete(key);
                } else {
                    url.searchParams.set(key, value);
                }
            });
        }

        this.updateURL(url);
    }

    /**
     * Get the complete current state
     */
    getState(): { path: string, params: URLSearchParams, segments: string[] } {
        return {
            path: window.location.pathname,
            params: new URLSearchParams(window.location.search),
            segments: this.getPathSegments()
        };
    }

    /**
     * Replace the current URL without adding to history
     */
    replaceState(path?: string, params?: Record<string, string | null>): void {
        const url = new URL(window.location.href);

        if (path !== undefined) {
            url.pathname = path;
        }

        if (params) {
            Object.entries(params).forEach(([key, value]) => {
                if (value === null || value === "") {
                    url.searchParams.delete(key);
                } else {
                    url.searchParams.set(key, value);
                }
            });
        }

        window.history.replaceState(null, "", url.toString());
        this.notifyStateChange();
    }

    /**
     * Navigate to a new URL
     */
    navigateTo(path: string, params?: Record<string, string>): void {
        const url = new URL(window.location.origin + path);

        if (params) {
            Object.entries(params).forEach(([key, value]) => {
                url.searchParams.set(key, value);
            });
        }

        this.updateURL(url);
    }

    /**
     * Register a callback for state changes
     */
    onStateChange(callback: StateChangeCallback): () => void {
        this.stateChangeCallbacks.add(callback);

        // Return unsubscribe function
        return () => {
            this.stateChangeCallbacks.delete(callback);
        };
    }

    /**
     * Clear all parameters
     */
    clearParams(): void {
        const url = new URL(window.location.href);
        url.search = "";
        this.updateURL(url);
    }

    /**
     * Check if a parameter exists
     */
    hasParam(key: string): boolean {
        const params = new URLSearchParams(window.location.search);
        return params.has(key);
    }

    /**
     * Get the full URL as string
     */
    getFullURL(): string {
        return window.location.href;
    }

    /**
     * Parse a URL string and extract components
     */
    parseURL(urlString: string): { path: string, params: URLSearchParams, segments: string[] } {
        const url = new URL(urlString, window.location.origin);
        return {
            path: url.pathname,
            params: new URLSearchParams(url.search),
            segments: url.pathname.split("/").filter(segment => segment.length > 0)
        };
    }

    /**
     * Internal method to update the URL and notify listeners
     */
    private updateURL(url: URL): void {
        if (url.toString() !== window.location.href) {
            // Add to navigation history
            const newPath = url.pathname;
            this.addToHistory(newPath);

            window.history.pushState(null, "", url.toString());
            this.notifyStateChange();
        }
    }

    /**
     * Add a path to the navigation history
     */
    private addToHistory(path: string): void {
        // Remove any forward history if we're not at the end
        if (this.currentHistoryIndex < this.navigationHistory.length - 1) {
            this.navigationHistory = this.navigationHistory.slice(0, this.currentHistoryIndex + 1);
        }

        // Don't add duplicate consecutive entries
        if (this.navigationHistory[this.currentHistoryIndex] !== path) {
            this.navigationHistory.push(path);
            this.currentHistoryIndex++;
        }
    }

    /**
     * Handle browser back/forward navigation
     */
    private handlePopStateEvent(): void {
        // Update our internal history tracking when user uses browser back/forward
        const currentPath = window.location.pathname;
        const historyIndex = this.navigationHistory.lastIndexOf(currentPath);
        if (historyIndex !== -1) {
            this.currentHistoryIndex = historyIndex;
        }

        this.notifyStateChange();
    }

    /**
     * Notify all registered callbacks of state changes
     */
    private notifyStateChange(isInitialLoad = false): void {
        const params = new URLSearchParams(window.location.search);
        const path = window.location.pathname;

        this.stateChangeCallbacks.forEach(callback => {
            try {
                callback(params, path, isInitialLoad);
            } catch (error) {
                console.error("Error in URL state change callback:", error);
            }
        });
    }

    back(fallbackPath: string = "/") {
        // Check if we have a previous path in our internal history
        if (this.currentHistoryIndex > 0) {
            this.currentHistoryIndex--;
            window.history.back();
        } else {
            // No previous history, use fallback
            this.updatePath(fallbackPath);
        }
    }

    /**
     * Get the previous path from navigation history
     */
    getPreviousPath(): string | null {
        if (this.currentHistoryIndex > 0) {
            return this.navigationHistory[this.currentHistoryIndex - 1];
        }
        return null;
    }

    /**
     * Check if there's a previous page in history
     */
    canGoBack(): boolean {
        return this.currentHistoryIndex > 0;
    }

    /**
     * Destroy the URL state manager and clean up event listeners
     */
    destroy(): void {
        window.removeEventListener("popstate", this.handlePopState);
        this.stateChangeCallbacks.clear();
        this.isInitialized = false;
    }
}

export const urlStateManager = new UrlStateManager();
